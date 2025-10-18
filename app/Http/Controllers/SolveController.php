<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;

class SolveController extends Controller
{
    public function index()
    {
        return view('solve');
    }

    public function solve(Request $request): JsonResponse
    {
        Log::info('📥 Incoming /api/solve request', [
            'has_base64' => $request->has('image'),
            'has_file' => $request->hasFile('image'),
        ]);

        $base64 = $request->input('image');
        $imageFile = $request->file('image');

        if (!$base64 && !$imageFile) {
            Log::warning('❌ No image received');
            return response()->json([
                'ok' => false,
                'error' => 'No image provided. Please upload or take a photo.'
            ], 400);
        }

        // 🧩 转成纯 base64
        if ($base64) {
            $imageBase64 = preg_replace('#^data:image/\w+;base64,#i', '', $base64);
        } elseif ($imageFile) {
            $imageBase64 = base64_encode(file_get_contents($imageFile->getRealPath()));
        } else {
            return response()->json([
                'ok' => false,
                'error' => 'Invalid image format.'
            ], 400);
        }

        $apiKey = env('OPENAI_API_KEY');
        $model  = env('OPENAI_MODEL', 'gpt-4o-mini');
        $base   = rtrim(env('OPENAI_BASE_URL', 'https://api.openai.com/v1'), '/');

        if (!$apiKey) {
            Log::error('❌ Missing OPENAI_API_KEY');
            return response()->json([
                'ok' => false,
                'error' => 'OPENAI_API_KEY missing. Set it in Railway Variables.'
            ], 500);
        }

        // ✅ System 指令
        $system = <<<SYS
You are a precise question-solving tutor. Given a photo of a question (math/science/general), do the following:
1) Extract the question clearly.
2) Solve it and give the concise answer.
3) Provide 3–7 reasoning steps as a string array.
4) List 3–6 related knowledge points.
Return pure JSON: question, answer, reasoning, knowledge_points.
SYS;

        try {
            // 🧾 准备请求体
            $payload = [
                'model' => $model,
                'input' => [
                    [
                        'role' => 'system',
                        'content' => $system
                    ],
                    [
                        'role' => 'user',
                        'content' => [
                            ['type' => 'input_text', 'text' => 'Solve this question and return JSON.'],
                            ['type' => 'input_image', 'image_data' => $imageBase64]
                        ]
                    ]
                ],
                'temperature' => 0.2,
                'response_format' => ['type' => 'json_object']
            ];

            Log::info('🚀 Sending request to OpenAI', [
                'endpoint' => $base . '/responses',
                'model' => $model,
                'base64_length' => strlen($imageBase64),
            ]);

            // ✅ 调用 OpenAI API
            $resp = Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type'  => 'application/json',
            ])->withOptions([
                'verify' => true,
                'timeout' => 45,
            ])->post($base . '/responses', $payload);

            // 🔍 记录响应详情
            Log::info('📤 OpenAI response metadata', [
                'status' => $resp->status(),
                'ok' => $resp->ok(),
            ]);

            if (!$resp->ok()) {
                Log::error('❌ Upstream error from OpenAI', [
                    'status' => $resp->status(),
                    'body' => $resp->body(),
                ]);

                return response()->json([
                    'ok' => false,
                    'error' => 'Upstream error from OpenAI',
                    'status' => $resp->status(),
                    'details' => $resp->json() ?? $resp->body(),
                ], 502);
            }

            $json = $resp->json();
            Log::info('✅ Raw OpenAI response', [
                'keys' => array_keys($json),
            ]);

            $content = $json['output'][0]['content'][0]['text'] ?? '{}';
            $parsed = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::warning('⚠️ JSON parse failed', [
                    'raw_content' => $content,
                ]);

                $parsed = [
                    'question' => '(Parse failed)',
                    'answer' => $content,
                    'reasoning' => ['Model returned non-JSON response.'],
                    'knowledge_points' => []
                ];
            }

            Log::info('✅ Parsed JSON successfully', [
                'question' => $parsed['question'] ?? '(none)',
                'answer' => $parsed['answer'] ?? '(none)',
            ]);

            return response()->json([
                'ok' => true,
                'data' => $parsed
            ]);

        } catch (\Throwable $e) {
            Log::error('💥 Exception during processing', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'ok' => false,
                'error' => 'Server error: ' . $e->getMessage(),
            ], 500);
        }
    }
}

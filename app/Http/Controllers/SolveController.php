<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\JsonResponse;

class SolveController extends Controller
{
    public function index()
    {
        return view('solve');
    }

    public function solve(Request $request): JsonResponse
    {
        $base64 = $request->input('image');
        $imageFile = $request->file('image');

        if (!$base64 && !$imageFile) {
            return response()->json([
                'ok' => false,
                'error' => 'No image provided. Please upload or take a photo.'
            ], 400);
        }

        // ✅ 转为统一格式
        if ($base64 && str_starts_with($base64, 'data:image/')) {
            $dataUrl = $base64;
        } elseif ($imageFile) {
            $mime  = $imageFile->getMimeType() ?: 'image/png';
            $dataUrl = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($imageFile->getRealPath()));
        } else {
            return response()->json([
                'ok' => false,
                'error' => 'Invalid image format.'
            ], 400);
        }

        // ✅ 本地 MOCK 模式（调试时可设 MOCK=1）
        if (env('MOCK', false)) {
            return response()->json([
                'ok' => true,
                'data' => [
                    'question' => 'If 3x + 5 = 20, what is x?',
                    'answer' => 'x = 5',
                    'reasoning' => [
                        'Subtract 5 from both sides: 3x = 15',
                        'Divide both sides by 3: x = 5'
                    ],
                    'knowledge_points' => ['Linear equation', 'Inverse operations', 'Basic algebra']
                ],
                'mock' => true
            ]);
        }

        $apiKey = env('OPENAI_API_KEY');
        $model  = env('OPENAI_MODEL', 'gpt-4o-mini');
        $base   = rtrim(env('OPENAI_BASE_URL', 'https://api.openai.com/v1'), '/');

        if (!$apiKey) {
            return response()->json([
                'ok' => false,
                'error' => 'Missing OPENAI_API_KEY. Set it in Railway Variables or enable MOCK=1.'
            ], 500);
        }

        $system = <<<SYS
You are a precise question-solving tutor. Given a photo of a question (math/science/general), do the following in English:
1) Extract and rewrite the question text clearly as "question".
2) Solve it and provide a concise "answer".
3) Provide 3–7 step-by-step "reasoning" as an array of strings.
4) List 3–6 "knowledge_points".
Return a JSON object: question, answer, reasoning, knowledge_points.
SYS;

        try {
            // 🧩 去除 data:image/png;base64, 前缀
            $imageBase64 = preg_replace('#^data:image/\w+;base64,#i', '', $dataUrl);

            // ✅ 使用 Responses API（支持图像输入）
            $resp = Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type'  => 'application/json',
            ])->withOptions([
                'verify' => true, // Railway 环境支持 SSL
                'timeout' => 30,
            ])->post($base . '/responses', [
                'model' => $model,
                'input' => [
                    [
                        'role' => 'system',
                        'content' => $system
                    ],
                    [
                        'role' => 'user',
                        'content' => [
                            ['type' => 'input_text', 'text' => 'Solve this question from the photo and return the specified JSON.'],
                            ['type' => 'input_image', 'image_data' => $imageBase64]
                        ]
                    ]
                ],
                'temperature' => 0.2,
                'response_format' => ['type' => 'json_object']
            ]);

            if (!$resp->ok()) {
                return response()->json([
                    'ok' => false,
                    'error' => 'OpenAI upstream error',
                    'status' => $resp->status(),
                    'details' => $resp->json() ?? $resp->body(),
                ], 502);
            }


            $json = $resp->json();
            $content = $json['output'][0]['content'][0]['text'] ?? '{}';
            $parsed = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $parsed = [
                    'question' => '(Parse failed)',
                    'answer' => $content,
                    'reasoning' => ['Model returned non-JSON response.'],
                    'knowledge_points' => []
                ];
            }

            return response()->json(['ok' => true, 'data' => $parsed]);
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'error' => 'Server error: ' . $e->getMessage(),
            ], 500);
        }
    }
}

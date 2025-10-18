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
        // ✅ 支持前端上传 base64 或文件
        $base64 = $request->input('image');
        $imageFile = $request->file('image');

        if (!$base64 && !$imageFile) {
            return response()->json([
                'ok' => false,
                'error' => 'No image provided. Please upload or take a photo.'
            ], 400);
        }

        // ✅ 统一转成 data:image/png;base64 格式
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

        // ✅ MOCK 模式（本地测试用）
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
                'error' => 'OPENAI_API_KEY is missing. Set it in Railway Variables or enable MOCK=1.'
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
            $resp = Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type'  => 'application/json',
            ])->post($base . '/chat/completions', [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => $system],
                    ['role' => 'user', 'content' => [
                        ['type' => 'text', 'text' => 'Solve this question from the photo and return the specified JSON.'],
                        ['type' => 'image_url', 'image_url' => ['url' => $dataUrl]]
                    ]]
                ],
                'temperature' => 0.2,
                'response_format' => ['type' => 'json_object']
            ]);

            if (!$resp->ok()) {
                return response()->json([
                    'ok' => false,
                    'error' => 'Upstream error from OpenAI',
                    'details' => $resp->body(),
                ], 502);
            }

            $json = $resp->json();
            $content = $json['choices'][0]['message']['content'] ?? '{}';

            $parsed = json_decode($content, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $parsed = [
                    'question' => '(Parse failed)',
                    'answer' => $content,
                    'reasoning' => ['Model returned non-JSON response.'],
                    'knowledge_points' => []
                ];
            }

            return response()->json([
                'ok' => true,
                'data' => $parsed
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'error' => 'Server error: ' . $e->getMessage(),
            ], 500);
        }
    }
}

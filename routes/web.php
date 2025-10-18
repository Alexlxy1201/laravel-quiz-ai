<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SolveController;

// 首页
Route::get('/', [SolveController::class, 'index'])->name('solve.index');

// 解题 API（禁用 CSRF，方便前端 fetch）
Route::post('/api/solve', [SolveController::class, 'solve'])
    ->name('solve.api')
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

// Railway 环境检查
Route::get('/envcheck', function () {
    return response()->json([
        'app_url' => env('APP_URL'),
        'model' => env('OPENAI_MODEL'),
        'key_exists' => env('OPENAI_API_KEY') ? true : false,
        'key_preview' => substr(env('OPENAI_API_KEY') ?? '', 0, 8),
    ]);
});

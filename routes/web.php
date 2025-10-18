<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SolveController;

Route::get('/', [SolveController::class, 'index'])->name('solve.index');

Route::post('/api/solve', [SolveController::class, 'solve'])
    ->name('solve.api')
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

// 测试环境变量是否正确
Route::get('/envcheck', function () {
    return response()->json([
        'key_exists' => env('OPENAI_API_KEY') ? true : false,
        'key_preview' => substr(env('OPENAI_API_KEY'), 0, 8)
    ]);
});

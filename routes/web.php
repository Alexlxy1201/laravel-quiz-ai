<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SolveController;

// 主页：显示上传界面
Route::get('/', [SolveController::class, 'index'])->name('solve.index');

// Ping 测试（可用于检测部署成功）
Route::get('/ping', function () {
    return 'pong ✅';
});

// AI 解题接口（带路由名 solve.api）
Route::post('/api/solve', [SolveController::class, 'solve'])
    ->name('solve.api')
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

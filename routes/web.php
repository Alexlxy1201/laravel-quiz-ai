<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SolveController;

Route::get('/', [SolveController::class, 'index'])->name('solve.index');
Route::post('/api/solve', [SolveController::class, 'solve'])->name('solve.api');

Route::get('/ping', function () {
    return response()->json(['pong' => true]);
});

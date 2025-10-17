<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SolveController;

Route::get('/', [SolveController::class, 'index'])->name('solve.index');
#Route::post('/api/solve', [SolveController::class, 'solve'])->name('solve.api');
Route::post('/api/solve', function () {
    return response()->json(['ok' => true, 'msg' => 'Backend reachable ✅']);
});

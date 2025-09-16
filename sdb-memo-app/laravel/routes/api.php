<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\MemoController;
use App\Http\Controllers\CategoryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/createUser', [AuthController::class, 'createUser']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/memos/all', [MemoController::class, 'indexAll']);
    Route::get('/memos/paginate', [MemoController::class, 'indexPaginate']);
    Route::post('/memos', [MemoController::class, 'store']);
    Route::put('/memos/{id}', [MemoController::class, 'update']);
    Route::delete('/memos/{id}', [MemoController::class, 'destroy']);
// 削除したメモ用
    Route::get('/memos/deleted/all', [MemoController::class, 'deletedIndexAll']);
    Route::get('/memos/deleted/paginate', [MemoController::class, 'deletedIndexPaginate']);
    Route::patch('/memos/deleted/restore/{id}', [MemoController::class, 'restore']);
    Route::delete('/memos/deleted/{id}', [MemoController::class, 'permanentlyDestroy']);
});

// Categoryに関するルート
Route::get('/categories', [CategoryController::class, 'index']);

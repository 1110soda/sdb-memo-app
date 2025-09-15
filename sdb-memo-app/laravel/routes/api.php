<?php

use App\Http\Controllers\CategoryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MemoController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/memos/all', [MemoController::class, 'indexAll']);
Route::get('/memos/paginate', [MemoController::class, 'indexPaginate']);
//Route::middleware('auth:sanctum')->post('/memos', [MemoController::class, 'store']);
Route::post('/memos', [MemoController::class, 'store']); //上のコードの仮。ログインシステムを実装したらSanctumを活用した認証へと移行する。
Route::put('/memos/{id}', [MemoController::class, 'update']);
Route::delete('/memos/{id}', [MemoController::class, 'destroy']);

// 削除したメモ用
Route::get('/memos/deleted/all', [MemoController::class, 'deletedIndexAll']);
Route::get('/memos/deleted/paginate', [MemoController::class, 'deletedIndexPaginate']);
Route::patch('/memos/deleted/restore/{id}', [MemoController::class, 'restore']);
Route::delete('/memos/deleted/{id}', [MemoController::class, 'permanentlyDestroy']);

// Categoryに関するルート
Route::get('/categories', [CategoryController::class, 'index']);

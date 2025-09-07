<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MemoController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/memos/paginate', [MemoController::class, 'indexPaginate']);
Route::get('/memos/all', [MemoController::class, 'indexAll']);
//Route::middleware('auth:sanctum')->post('/memos', [MemoController::class, 'store']);
Route::post('/memos', [MemoController::class, 'store']); //上のコードの仮。ログインシステムを実装したらSanctumを活用した認証へと移行する。

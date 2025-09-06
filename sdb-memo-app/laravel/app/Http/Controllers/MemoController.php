<?php

namespace App\Http\Controllers;

use App\Models\Memo;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MemoController extends Controller
{
    /*
     * 新たに作成されたメモをストレージへ保存
     *
     * @param   \Illuminate\Http\Request $request
     * @return  \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
//      リクエストのバリデーションを実行、バリデーション済みのデータを保存
        $validatedData = $request->validate([
            'title' => 'nullable|string|max:255',
            'content' => 'required|string',
        ]);
//      $validatedData['user_id'] = $request->user()->id;
        $validatedData['user_id'] = 1; //上のコードの仮。ログインシステムを実装したらSanctumを活用した認証へと移行する。

        $memo = Memo::create($validatedData);

        return response()->json([
            'status' => 'success',
            'message' => 'メモが正常に保存されました。',
            'data' => $memo,
        ], 201); //201: HTTPステータスコード:作成完了
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Memo;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class MemoController extends Controller
{
    /*
     * 新たに作成されたメモをストレージへ保存
     *
     * @param   \Illuminate\Http\Request $request
     * @return  \Illuminate\Http\JsonResponse
     */
    public function indexPaginate() {
//      メモを更新日時順に並べ、JSONレスポンスとして返す
        $memos = Memo::orderBy('updated_at', 'desc')->paginate(5); //1ページあたり5件のメモを取得
        $formattedMemos = $memos->getCollection()->map(function ($memo) {
            return [
                'id' => $memo->id,
                'title' => $memo->title,
                'content' => $memo->content,
                'created_at' => Carbon::parse($memo->created_at)->format('Y/m/d H:i:s'), //日付をCarbonを使ってフォーマット
                'updated_at' => Carbon::parse($memo->updated_at)->format('Y/m/d H:i:s'),
            ];
        });
        $memos->setCollection($formattedMemos);
        return response()->json($memos);
    }
    public function indexALl() {
        //      メモを更新日時順に並べ、JSONレスポンスとして返す
        $memos = Memo::orderBy('updated_at', 'desc')->get(); //すべてのメモを取得
        $formattedMemos = $memos->map(function ($memo) {
            return [
                'id' => $memo->id,
                'title' => $memo->title,
                'content' => $memo->content,
                'created_at' => Carbon::parse($memo->created_at)->format('Y/m/d H:i:s'), //日付をCarbonを使ってフォーマット
                'updated_at' => Carbon::parse($memo->updated_at)->format('Y/m/d H:i:s'),
            ];
        });
        return response()->json($formattedMemos);
    }

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

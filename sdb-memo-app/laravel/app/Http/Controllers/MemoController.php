<?php

namespace App\Http\Controllers;

use App\Models\Memo;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use Exception;

class MemoController extends Controller
{
    /*
     * 新たに作成されたメモをストレージへ保存
     *
     * @param   \Illuminate\Http\Request $request
     * @return  \Illuminate\Http\JsonResponse
     */

    public function indexAll(): JsonResponse
    {
        //      メモを更新日時順に並べ、JSONレスポンスとして返す
        $memos = Memo::orderBy('updated_at', 'desc')->get(); //すべてのメモを取得
        $formattedMemos = $memos->map(function ($memo) {
            return [
                'id' => $memo->id,
                'title' => $memo->title,
                'content' => $memo->content,
                'created_at' => Carbon::parse($memo->created_at)->timezone('Asia/Tokyo')->format('Y/m/d H:i:s'), //日付をCarbonを使ってフォーマット
                'updated_at' => Carbon::parse($memo->updated_at)->timezone('Asia/Tokyo')->format('Y/m/d H:i:s'),
            ];
        });
        return response()->json($formattedMemos);
    }

    public function indexPaginate(): JsonResponse
    {
//      メモを更新日時順に並べ、JSONレスポンスとして返す
        $memos = Memo::orderBy('updated_at', 'desc')->paginate(5); //1ページあたり5件のメモを取得
        $formattedMemos = $memos->getCollection()->map(function ($memo) {
            return [
                'id' => $memo->id,
                'title' => $memo->title,
                'content' => $memo->content,
                'created_at' => Carbon::parse($memo->created_at)->timezone('Asia/Tokyo')->format('Y/m/d H:i:s'), //日付をCarbonを使ってフォーマット
                'updated_at' => Carbon::parse($memo->updated_at)->timezone('Asia/Tokyo')->format('Y/m/d H:i:s'),
            ];
        });
        $memos->setCollection($formattedMemos);
        return response()->json($memos);
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
        ], 201); //201: HTTPステータスコード: 作成完了
    }

    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $memo = Memo::findOrFail($id);
        //      リクエストのバリデーションを実行、バリデーション済みのデータを保存
            $validatedData = $request->validate([
                'title' => 'nullable|string|max:255',
                'content' => 'required|string',
            ]);
        //      $validatedData['user_id'] = $request->user()->id;
            $validatedData['user_id'] = 1; //上のコードの仮。ログインシステムを実装したらSanctumを活用した認証へと移行する。

            $memo->update($validatedData);

            $formattedMemo = [
                'id' => $memo->id,
                'title' => $memo->title,
                'content' => $memo->content,
                'created_at' => Carbon::parse($memo->created_at)->timezone('Asia/Tokyo')->format('Y/m/d H:i:s'),
                'updated_at' => Carbon::parse($memo->updated_at)->timezone('Asia/Tokyo')->format('Y/m/d H:i:s'),
            ];

            return response()->json([
                'status' => 'success',
                'message' => 'メモが正常に更新されました。',
                'data' => $formattedMemo,
            ], 200); //200: HTTPステータスコード: 更新成功
        } catch (Exception $e) {
            return response()->json(['message' => 'メモが見つかりません。'], 404);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $memo = Memo::findOrFail($id);
            $memo->delete();

            return response()->json(['message' => 'メモが正常に削除されました。']);
        } catch (Exception $e) {
            return response()->json(['message' => 'メモが見つかりません。'], 404); //404: HTTPステータスコード: Not Found
        }
    }

//  削除したメモ用
    public function deletedIndexAll(): JsonResponse
    {
        $memos = Memo::onlyTrashed()->orderBy('updated_at', 'desc')->get(); //すべての削除されたメモを取得
        $formattedMemos = $memos->map(function ($memo) {
            return [
                'id' => $memo->id,
                'title' => $memo->title,
                'content' => $memo->content,
                'created_at' => Carbon::parse($memo->created_at)->timezone('Asia/Tokyo')->format('Y/m/d H:i:s'), //日付をCarbonを使ってフォーマット
                'updated_at' => Carbon::parse($memo->updated_at)->timezone('Asia/Tokyo')->format('Y/m/d H:i:s'),
            ];
        });
        return response()->json($formattedMemos);
    }

    public function deletedIndexPaginate(): JsonResponse
    {
        $memos = Memo::onlyTrashed()->orderBy('updated_at', 'desc')->paginate(5); //1ページあたり5件のメモを取得
        $formattedMemos = $memos->getCollection()->map(function ($memo) {
            return [
                'id' => $memo->id,
                'title' => $memo->title,
                'content' => $memo->content,
                'created_at' => Carbon::parse($memo->created_at)->timezone('Asia/Tokyo')->format('Y/m/d H:i:s'), //日付をCarbonを使ってフォーマット
                'updated_at' => Carbon::parse($memo->updated_at)->timezone('Asia/Tokyo')->format('Y/m/d H:i:s'),
            ];
        });
        $memos->setCollection($formattedMemos);
        return response()->json($memos);
    }

    public function restore(string $id): JsonResponse
    {
        try {
            $memo = Memo::onlyTrashed()->findOrFail($id);
            $memo->restore();
            return response()->json(['message' => 'メモが正常に復元されました。']);
        } catch (Exception $e) {
            return response()->json(['message' => 'メモが見つかりません。'], 404);
        }
    }

    public function permanentlyDestroy(string $id): JsonResponse
    {
        try {
            $memo = Memo::onlyTrashed()->findOrFail($id);
            $memo->forceDelete();
            return response()->json(['message' => 'メモが完全に削除されました。']);
        } catch (Exception $e) {
            return response()->json(['message' => 'メモが見つかりません。'], 404);
        }
    }
}

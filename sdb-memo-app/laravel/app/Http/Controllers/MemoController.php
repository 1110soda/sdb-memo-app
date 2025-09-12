<?php

namespace App\Http\Controllers;

use App\Models\Memo;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use function Termwind\parse;

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
        $memos = Memo::with('categories')->orderBy('updated_at', 'desc')->get(); //すべてのメモを取得
        $formattedMemos = $memos->map(function ($memo) {
            return [
                'id' => $memo->id,
                'title' => $memo->title,
                'content' => $memo->content,
                'categories' => $memo->categories->map(function ($category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                        'color_code' => $category->color_code,
                    ];
                }),
                'deadline_at' => $memo->deadline_at ? Carbon::parse($memo->deadline_at)->timezone('Asia/Tokyo')->format('Y/m/d') : null,
                'created_at' => Carbon::parse($memo->created_at)->timezone('Asia/Tokyo')->format('Y/m/d H:i:s'), //日付をCarbonを使ってフォーマット
                'updated_at' => Carbon::parse($memo->updated_at)->timezone('Asia/Tokyo')->format('Y/m/d H:i:s'),
            ];
        });
        return response()->json($formattedMemos);
    }

    public function indexPaginate(): JsonResponse
    {
//      メモを更新日時順に並べ、JSONレスポンスとして返す
        $memos = Memo::with('categories')->orderBy('updated_at', 'desc')->paginate(5); //1ページあたり5件のメモを取得
        $formattedMemos = $memos->getCollection()->map(function ($memo) {
            return [
                'id' => $memo->id,
                'title' => $memo->title,
                'content' => $memo->content,
                'categories' => $memo->categories->map(function ($category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                        'color_code' => $category->color_code,
                    ];
                }),
                'deadline_at' => $memo->deadline_at ? Carbon::parse($memo->deadline_at)->timezone('Asia/Tokyo')->format('Y/m/d') : null,
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
            'category_ids' => 'nullable|array', //カテゴリーIDの配列
            'category_ids.*' => 'exists:categories,id', //各IDがCategoriesテーブルに存在するか
            'deadline_at' => 'nullable|date_format:Y/m/d',
        ]);
//      $validatedData['user_id'] = $request->user()->id;
        $validatedData['user_id'] = 1; //上のコードの仮。ログインシステムを実装したらSanctumを活用した認証へと移行する。

//      deadline_atをJSTとして受け取り、UTCに変換して保存
        if (isset($validatedData['deadline_at'])) {
            $validatedData['deadline_at'] = Carbon::createFromFormat('Y/m/d', $validatedData['deadline_at'], 'Asia/Tokyo')->utc();
        }

        $memo = Memo::create($validatedData);

//      カテゴリーを関連付け
        if (isset($validatedData['category_ids'])) {
            $memo->categories()->sync($validatedData['category_ids']);
        }

        $memo->load('categories');

        $formattedMemo = [
            'id' => $memo->id,
            'title' => $memo->title,
            'content' => $memo->content,
            'categories' => $memo->categories->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'color_code' => $category->color_code,
                ];
            }),
            'deadline_at' => $memo->deadline_at ? Carbon::parse($memo->deadline_at)->timezone('Asia/Tokyo')->format('Y/m/d') : null,
            'created_at' => Carbon::parse($memo->created_at)->format('Y/m/d H:i:s'),
            'updated_at' => Carbon::parse($memo->updated_at)->format('Y/m/d H:i:s'),
        ];

        return response()->json([
            'status' => 'success',
            'message' => 'メモが正常に保存されました。',
            'data' => $formattedMemo,
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
                'category_ids' => 'nullable|array',
                'category_ids.*' => 'exists:categories,id',
                'deadline_at' => 'nullable|date_format:Y/m/d',
            ]);
        //      $validatedData['user_id'] = $request->user()->id;
            $validatedData['user_id'] = 1; //上のコードの仮。ログインシステムを実装したらSanctumを活用した認証へと移行する。

            if (isset($validatedData['deadline_at'])) {
                $validatedData['deadline_at'] = Carbon::createFromFormat('Y/m/d', $validatedData['deadline_at'], 'Asia/Tokyo')->utc();
            } else {
                $validatedData['deadline_at'] = null;
            }

            $memo->update($validatedData);

            $memo->categories()->sync($validatedData['category_ids'] ?? []); //category_idsがリクエストにない場合は空の配列を渡して全ての関連付けを解除
            $memo->load('categories');

            $formattedMemo = [
                'id' => $memo->id,
                'title' => $memo->title,
                'content' => $memo->content,
                'categories' => $memo->categories->map(function ($category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                        'color_code' => $category->color_code,
                    ];
                }),
                'deadline_at' => $memo->deadline_at ? Carbon::parse($memo->deadline_at)->timezone('Asia/Tokyo')->format('Y/m/d') : null,
                'created_at' => Carbon::parse($memo->created_at)->timezone('Asia/Tokyo')->format('Y/m/d H:i:s'),
                'updated_at' => Carbon::parse($memo->updated_at)->timezone('Asia/Tokyo')->format('Y/m/d H:i:s'),
            ];

            return response()->json([
                'status' => 'success',
                'message' => 'メモが正常に更新されました。',
                'data' => $formattedMemo,
            ], 200); //200: HTTPステータスコード: 更新成功
        } catch (\Exception $e) {
            return response()->json(['message' => 'メモが見つかりません。'], 404);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $memo = Memo::findOrFail($id);
            $memo->delete();

            return response()->json(['message' => 'メモが正常に削除されました。']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'メモが見つかりません。'], 404); //404: HTTPステータスコード: Not Found
        }
    }

//  削除したメモ用
    public function deletedIndexAll(): JsonResponse
    {
        $memos = Memo::onlyTrashed()->with('categories')->orderBy('updated_at', 'desc')->get(); //すべての削除されたメモを取得
        $formattedMemos = $memos->map(function ($memo) {
            return [
                'id' => $memo->id,
                'title' => $memo->title,
                'content' => $memo->content,
                'categories' => $memo->categories->map(function ($category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                        'color_code' => $category->color_code,
                    ];
                }),
                'deadline_at' => $memo->deadline_at ? Carbon::parse($memo->deadline_at)->timezone('Asia/Tokyo')->format('Y/m/d') : null,
                'created_at' => Carbon::parse($memo->created_at)->timezone('Asia/Tokyo')->format('Y/m/d H:i:s'), //日付をCarbonを使ってフォーマット
                'updated_at' => Carbon::parse($memo->updated_at)->timezone('Asia/Tokyo')->format('Y/m/d H:i:s'),
            ];
        });
        return response()->json($formattedMemos);
    }

    public function deletedIndexPaginate(): JsonResponse
    {
        $memos = Memo::onlyTrashed()->with('categories')->orderBy('updated_at', 'desc')->paginate(5); //1ページあたり5件のメモを取得
        $formattedMemos = $memos->getCollection()->map(function ($memo) {
            return [
                'id' => $memo->id,
                'title' => $memo->title,
                'content' => $memo->content,
                'categories' => $memo->categories->map(function ($category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                        'color_code' => $category->color_code,
                    ];
                }),
                'deadline_at' => $memo->deadline_at ? Carbon::parse($memo->deadline_at)->timezone('Asia/Tokyo')->format('Y/m/d') : null,
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
        } catch (\Exception $e) {
            return response()->json(['message' => 'メモが見つかりません。'], 404);
        }
    }

    public function permanentlyDestroy(string $id): JsonResponse
    {
        try {
            $memo = Memo::onlyTrashed()->findOrFail($id);
            $memo->forceDelete();
            return response()->json(['message' => 'メモが完全に削除されました。']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'メモが見つかりません。'], 404);
        }
    }
}

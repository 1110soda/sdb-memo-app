<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $categories = Category::orderBy('name')->get();

            return response()->json([
                'status' => 'success',
                'message' => 'カテゴリー一覧を取得しました。',
                'data' => $categories
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'カテゴリーの取得に失敗しました。',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

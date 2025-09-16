<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function createUser(Request $request): Response
    {
//      バリデーション
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password), //パスワードをハッシュ化
        ]);

        Auth::login($user);
//      セッションの再生成
        $request->session()->regenerate();
//      成功レスポンス
        return response()->noContent();
    }

    public function login(Request $request): Response
    {
//      バリデーション
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);
//      認証試行
        if (! Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['認証情報が記録と一致しません。'],
            ]);
        }
//      セッションの再生成
        $request->session()->regenerate();
//      成功レスポンス204
        return response()->noContent();
    }

    public function logout(Request $request): Response
    {
//      ログアウト処理
        Auth::guard('web')->logout();
//      セッションを無効化
        $request->session()->invalidate();
//      CSRFトークンを再生成
        $request->session()->regenerateToken();
//      成功レスポンス
        return response()->noContent();
    }
}

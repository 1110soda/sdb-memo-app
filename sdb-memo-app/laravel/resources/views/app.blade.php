<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>Vue App</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/js/app.ts', 'resources/css/app.css'])
</head>
<body
    class="bg-primary-100 text-secondary-900 font-sans font-light"
{{--    実際のコード--}}
{{--    @auth data-user-id="{{ Auth::id() }}" data-user-name="{{ Auth::user()->name }}" data-is-logged-in="true" @endauth--}}
{{--    @guest data-is-logged-in="false" @endguest--}}
        data-user-id="999" data-user-name="テストユーザー" data-is-logged-in="true"> {{--ログイン後のテスト用の仮のコード--}}
    <div id="app"></div>
</body>
</html>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>Vue App</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/js/app.ts', 'resources/css/app.css'])
</head>
<body class="bg-secondary-50 text-secondary-900 font-sans font-light">
    <div id="app"></div>
</body>
</html>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Masuk - CAVA Parfums</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-background text-foreground antialiased flex items-center justify-center">
    <main
        data-page-content
        data-active-menu="login"
        class="w-full max-w-md p-4"
    >
        <div 
            data-vue-component="LoginManager"
            data-vue-props="{{ json_encode([
                'loginUrl' => url('/login'),
            ]) }}"
        ></div>
    </main>
</body>
</html>

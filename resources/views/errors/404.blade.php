<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Page Not Found</title>
    <style>
        body { font-family: sans-serif; text-align: center; margin-top: 50px; }
        h1 { font-size: 80px; color: #f44336; }
        p { font-size: 20px; }
        a { color: #2c3e50; text-decoration: none; }
    </style>
</head>
<body>
    <h1>404</h1>
    <p>Oops! The page you are looking for does not exist.</p>
    <p>
        <a href="{{ auth()->check() ? route(auth()->user()->is_admin ? 'admin.dashboard' : 'dashboard') : route('login') }}">
        Go back
        </a>
    </p>
</body>
</html>
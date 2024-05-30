<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Book Scanner</title>
</head>
<body>
    <h1>Welcome to Book Scanner</h1>
    <a href="{{ route('register') }}">Register</a>
    <a href="{{ route('login') }}">Login</a>
    <a href="{{ route('pricing') }}">Pricing</a>
    <a href="{{ route('support') }}">Support</a>
</body>
</html>
@include('layouts.footer')

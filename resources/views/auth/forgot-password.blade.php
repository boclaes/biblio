<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Book Scanner</title>
</head>
<body>
    <h1>Forgot Password</h1>
    @if (session('status'))
        <div>{{ session('status') }}</div>
    @endif
    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <input type="email" name="email" placeholder="Email" value="{{ old('email') }}" required>
        <button type="submit">Send Password Reset Link</button>
    </form>
    <a href="{{ route('welcome') }}"><button type="button">Back</button></a>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Book Scanner</title>
</head>
<body>
    <h1>Reset Password</h1>
    <form method="POST" action="{{ route('password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <input type="email" name="email" placeholder="Email" value="{{ $email ?? old('email') }}" required>
        <input type="password" name="password" placeholder="New Password" minlength="8" required>
        <input type="password" name="password_confirmation" placeholder="Confirm Password" minlength="8" required>
        <button type="submit">Reset Password</button>
    </form>
    <a href="{{ route('welcome') }}"><button type="button">Back</button></a>
</body>
</html>

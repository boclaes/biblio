<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Book Scanner</title>
</head>
<body>
    <h1>Register</h1>
    <form method="POST" action="{{ route('register') }}">
        @csrf
        <input type="text" name="name" placeholder="Name" value="{{ old('name') }}" required>
        @error('name')
            <div style="color: red;">{{ $message }}</div>
        @enderror

        <input type="email" name="email" placeholder="Email" value="{{ old('email') }}" required>
        @error('email')
            <div style="color: red;">{{ $message }}</div>
        @enderror

        <input type="password" name="password" placeholder="Password" minlength="8" required>
        @error('password')
            <div style="color: red;">{{ $message }}</div>
        @enderror

        <input type="password" name="password_confirmation" placeholder="Confirm Password" required>
        @error('password_confirmation')
            <div style="color: red;">{{ $message }}</div>
        @enderror

        <button type="submit">Register</button>
    </form>
    <a href="{{ route('welcome') }}"><button type="button">Back</button></a>
</body>
</html>

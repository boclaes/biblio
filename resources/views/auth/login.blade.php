<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Biblio</title>
</head>
<body>
    <h1>Login</h1>
    @if (session('status'))
        <div>
            {{ session('status') }}
        </div>
    @endif
    <form method="POST" action="{{ route('login') }}">
        @csrf
        @if ($errors->any())
            <div>
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif
        <input type="email" name="email" placeholder="Email" value="{{ old('email') }}" required>
        <input type="password" name="password" placeholder="Password" minlength="8" required>
        <button type="submit">Login</button>
    </form>
    <a href="{{ route('password.request') }}">Forgot Password?</a>
    <a href="{{ route('welcome') }}"><button type="button">Back</button></a>
</body>
</html>

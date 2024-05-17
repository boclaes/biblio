<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Book Scanner')</title>
</head>
<body>
    <!-- Error and success messages -->
    @if(session('error'))
        <div style="color: red;">{{ session('error') }}</div>
    @endif
    @if(session('success'))
        <div style="color: green;">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit">Logout</button>
    </form>
    <a href="{{ route('account.settings') }}"><button type="button">Account settings</button></a>

    @yield('content')
</body>
</html>

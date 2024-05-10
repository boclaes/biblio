<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scanning Page - Book Scanner</title>
</head>
<body>
    <h2>Enter ISBN:</h2>
    <form method="post" action="{{ route('scan') }}">
        @csrf
        <input type="text" name="isbn" placeholder="Enter ISBN" pattern="[0-9]{13}" title="Please enter a valid ISBN (13 digits)" required>
        <button type="submit">Scan ISBN</button>
    </form>
    <button onclick="window.location.href='/books';">Books</button>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit">Logout</button>
    </form>
</body>
</html>

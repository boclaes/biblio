<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scanning Page - Book Scanner</title>
</head>
<body>
    <h2>Enter ISBN or Book Title:</h2>

    <!-- Error and success messages -->
    @if(session('error'))
        <div style="color: red;">{{ session('error') }}</div>
    @endif
    @if(session('success'))
        <div style="color: green;">{{ session('success') }}</div>
    @endif

    <form id="searchForm" method="post" action="{{ route('search') }}">
        @csrf
        <input id="searchInput" type="text" name="query" placeholder="Enter ISBN or Book Title" required autofocus autocomplete="off">
        <button type="submit">Search Book</button>
    </form>
    <button onclick="window.location.href='/books';">Books</button>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit">Logout</button>
    </form>

    <script>
        window.onload = function() {
            document.getElementById('searchInput').focus();
        };

        document.getElementById('searchForm').addEventListener('input', function(event) {
            const input = event.target.value;
            if (input.length == 13 && !isNaN(input)) { // Additional check for non-numeric
                document.getElementById('searchForm').submit();
            }
        });
    </script>

</body>
</html>

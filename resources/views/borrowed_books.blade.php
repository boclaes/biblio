<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrowed Books</title>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body>
<div class="container">
    <h1>Borrowed Books</h1>
    <div class="list-group">
        @foreach ($borrowings as $borrowing)
            <div class="list-group-item">
                <img src="{{ $borrowing->book->cover }}" alt="Cover" style="width: 100px; height: auto;">
                <h4>{{ $borrowing->book->title }}</h4>
                <p>Author: {{ $borrowing->book->author }}</p>
                <p>Borrowed by: {{ $borrowing->borrower_name }}</p>
                <p>Borrowed on: {{ $borrowing->borrowed_since->format('Y-m-d') }}</p>
                <form action="{{ route('borrowings.return', $borrowing->id) }}" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit">Gave it back</button>
                </form>
            </div>
        @endforeach
    </div>
</div>
<a href="{{ route('books') }}"><button type="button">Back</button></a>
<script src="{{ asset('js/app.js') }}"></script>
</body>
</html>

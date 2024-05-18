<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Recommendation</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .button {
            display: inline-block;
            padding: 8px 16px;
            margin: 10px 0;
            font-size: 16px;
            cursor: pointer;
            text-align: center;
            outline: none;
            color: #fff;
            background-color: #4CAF50;
            border: none;
            border-radius: 5px;
            box-shadow: 0 9px #999;
        }
        
        .button:hover {background-color: #3e8e41}

        .button:active {
            background-color: #3e8e41;
            box-shadow: 0 5px #666;
            transform: translateY(4px);
        }
    </style>
</head>
<body>
    <div id="book-recommendation" data-book='@json($book)' data-google-books-id="{{ $book['id'] ?? '' }}">
        @if ($book)
            <img src="{{ $book['cover'] ?? asset('images/default_cover.jpg') }}" alt="Cover Image of {{ $book['title'] ?? 'No Title' }}">
            <h2>{{ $book['title'] ?? 'No Title' }}</h2>
            <p>{{ $book['description'] ?? 'No description available.' }}</p>
            <button onclick="handleDecision('accept', '{{ $book['id'] ?? '' }}')">Accept</button>
            <button onclick="handleDecision('reject', '{{ $book['id'] ?? '' }}')">Reject</button>
        @else
            <p>No recommendations available at the moment.</p>
        @endif
    </div>
    <a href="{{ route('books') }}" class="button">Back to Books</a>
    <script src="{{ asset('js/recommendationHandler.js') }}"></script>
</body>
</html>

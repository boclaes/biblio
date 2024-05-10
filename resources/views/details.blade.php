<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Details</title>
    <style>
        .container {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
        }
        .book-card {
            width: 300px;
            border: 1px solid #ccc;
            padding: 10px;
        }
        .book-details {
            width: 60%;
        }
        .book-image {
            width: 100%;
            height: auto;
        }
        .description-field {
            display: inline-block;
            margin-bottom: 10px;
            cursor: pointer;
        }
        textarea {
            width: 100%;
            min-height: 200px;
        }
        .stars {
            display: flex;
            align-items: center;
        }
        .star {
            font-size: 24px;
            cursor: pointer;
            color: #ccc;
        }
        .star.active {
            color: gold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="book-card">
            <h3>{{ $book->title }}</h3>
            <p><strong>Author(s):</strong> {{ $book->author }}</p>
            <p><strong>Year:</strong> {{ $book->year }}</p>
            <p><strong>Description:</strong> {{ $book->description }}</p>
            @if ($book->cover)
                <img src="{{ $book->cover }}" alt="Book Cover" class="book-image">
            @else
                <p>No Cover Image</p>
            @endif
        </div>
        <div class="book-details" data-book-id="{{ $book->id }}" data-csrf-token="{{ csrf_token() }}">
            <p><strong>Your Notes:</strong></p>
            <span class="description-field">
                {{ $book->notes_user ? $book->notes_user : 'No custom notes' }}
            </span>
            <br><br>
            <a href="{{ route('edit.notes', $book->id) }}"><button>Edit Notes</button></a>
            <div>
                <input type="checkbox" id="want_to_read" {{ $book->want_to_read ? 'checked' : '' }}>
                <label for="want_to_read">Want to Read</label>
            </div>
            <div>
                <input type="checkbox" id="reading" {{ $book->reading ? 'checked' : '' }}>
                <label for="reading">Reading</label>
            </div>
            <div>
                <input type="checkbox" id="done_reading" {{ $book->done_reading ? 'checked' : '' }}>
                <label for="done_reading">Done Reading</label>
            </div>
            <div class="stars">
                @php
                    $response = app()->call('App\Http\Controllers\BookController@getBookRating', ['id' => $book->id]);
                    $rating = $response->original['rating'];
                    $rating = $rating ? $rating : 0;
                @endphp
                @for ($i = 1; $i <= 5; $i++)
                    @php
                        $activeClass = $i <= $rating ? 'active' : '';
                    @endphp
                    <span class="star {{ $activeClass }}" data-value="{{ $i }}">&#9733;</span>
                @endfor
            </div>
        </div>
    </div>
    <a href="{{ route('books') }}"><button type="button">Back</button></a>
    <script src="{{ asset('js/books.js') }}"></script>
</body>
</html>

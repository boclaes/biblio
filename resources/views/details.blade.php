@extends('layouts.app')

@section('title', 'Book Details')

@section('content')
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

    <div class="container">
        <div class="book-card">
            <h3>{{ $book->title }}</h3>
            <p><strong>Author(s):</strong> {{ $book->author }}</p>
            <p><strong>Pages:</strong> {{ $book->pages }}</p>
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
            <p><strong>Your Review:</strong></p>
            <span class="description-field">
                {{ $book->review ? $book->review : 'No custom review' }}
            </span>
            <br><br>
            <a href="{{ route('edit.review', $book->id) }}"><button>Edit review</button></a>
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
                <form action="{{ route('delete.book', $book->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this book?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit">Delete</button>
                </form>
            </div>
        </div>
    </div>
    <a href="{{ route('books') }}"><button type="button">Back to library</button></a>
    <script src="{{ asset('js/books.js') }}"></script>
@endsection

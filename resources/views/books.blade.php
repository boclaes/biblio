@extends('layouts.app_with_filters', ['includeRatings' => true, 'includePages' => true, 'includeName' => true, 'includeAuthor' => true ])

@section('title', 'Simple Book List')

@section('content')
    <style>
        .book-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        .book-card {
            width: 200px;
            border: 1px solid #ccc;
            padding: 10px;
            transition: transform 0.2s ease, box-shadow 0.2s ease; /* Smooth transition */
        }
        .book-card:hover {
            transform: scale(1.05); /* Slightly larger on hover */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Add shadow effect */
        }
        .book-image {
            width: 100%;
            height: auto;
        }
        .stars {
            display: flex;
            align-items: center;
        }
        .star {
            font-size: 24px;
            color: gold;
        }
    </style>

    <h2>Your books</h2>
    <div class="book-container" id="bookContainer">
        @foreach ($books->sortBy('title') as $book)
            @php
                $rating = $book->reviews->avg('rating');
                $rating = $rating ? $rating : 0;
                $numStars = round($rating);
                $status = '';
                if ($book->want_to_read) {
                    $status = 'Want to Read';
                } elseif ($book->reading) {
                    $status = 'Reading';
                } elseif ($book->done_reading) {
                    $status = 'Done Reading';
                }
            @endphp
            <div class="book-card" data-title="{{ $book->title }}">
                <h3>{{ $book->title }}</h3>
                <p class="author">By: {{ $book->author }}</p>
                <p class="pages">Pages: {{$book->pages}}</p>
                @if ($status)
                    <p class="status">{{ $status }}</p>
                @endif
                <div class="stars" data-rating="{{ $numStars }}">
                    @for ($i = 1; $i <= $numStars; $i++)
                        <span class="star">&#9733;</span>
                    @endfor
                    @for ($i = $numStars + 1; $i <= 5; $i++)
                        <span class="star">&#9734;</span>
                    @endfor
                </div>
                @if ($book->cover)
                    <a href="{{ route('details.book', $book->id) }}">
                        <img src="{{ $book->cover }}" alt="Book Cover" class="book-image">
                    </a>
                @else
                    <p>No Cover Image</p>
                @endif
                <a href="{{ route('edit.book', $book->id) }}">Edit Book</a> <!-- New Edit Book button -->
                <form action="{{ route('show.book', $book->id) }}" method="POST">
                    @csrf
                    <button type="submit">Show Book</button>
                </form>
            </div>
        @endforeach
    </div>
    <div class="navigation-buttons">
        <a href="{{ route('book.recommend') }}" class="recommendation-button"><button type="button">Get Book Recommendations</button></a>
    </div>
    <div class="navigation-buttons">
        <a href="{{ route('accepted.books') }}" class="recommendation-button"><button type="button">Wishlist</button></a>
    </div>
    <form action="{{ route('register-rpi') }}" method="POST">
        @csrf
        <button type="submit">Register RPI</button>
    </form>
    <a href="{{ route('borrowed-books') }}" class="btn btn-info"><button type="button">View Borrowed books</button></a>
    <a href="{{ route('books.addBorrow') }}" class="btn btn-info"><button type="button">Add Borrow</button></a>
    <a href="{{ route('search.form') }}"><button type="button">Search books</button></a>
@endsection

@extends('layouts.app_with_filters', ['includeRatings' => false, 'includePages' => false, 'includeName' => false, 'includeAuthor' => false, 'includeDate' => true  ])

@section('title', 'Borrowed Books')

@section('content')
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
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
        }
        .book-image {
            width: 100%;
            height: auto;
        }
        .dropdown {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 999;
        }
    </style>

    <div class="container">
        <h1>Borrowed Books</h1>
        <div class="book-container" id="bookContainer">
            @foreach ($borrowings as $borrowing)
                <div class="book-card">
                    <img src="{{ $borrowing->book->cover }}" alt="Cover" class="book-image">
                    <h3>{{ $borrowing->book->title }}</h3>
                    <p class="author">By: {{ $borrowing->book->author }}</p>
                    <p>Borrowed by: {{ $borrowing->borrower_name }}</p>
                    <p class="borrowed-since">Borrowed on: {{ $borrowing->borrowed_since->format('Y-m-d') }}</p>
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
    <script src="{{ asset('js/sorting.js') }}"></script>
@endsection
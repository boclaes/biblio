<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accepted Books</title>
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
</head>
<body>
    <h2>Accepted Books</h2>
    <div class="dropdown">
        <label for="sort">Sort By:</label>
        <select id="sort">
            <option value="name_asc">Name (A-Z)</option>
            <option value="name_desc">Name (Z-A)</option>
            <option value="author">Author (A-Z)</option>
            <option value="pages">Pages</option>
        </select>
    </div>
    <div>
        <input type="text" id="search" placeholder="Search by book title..." autocomplete="off">
    </div>
    <div class="book-container" id="bookContainer">
    @foreach ($acceptedBooks as $book)
        <div class="book-card">
            <h3>{{ $book->title }}</h3>
            <p class="author">By: {{ $book->author }}</p>
            <p class="pages">Pages: {{$book->pages}}</p>
            @if ($book->cover)
                <img src="{{ $book->cover }}" alt="Book Cover" class="book-image">
            @endif
            @if ($book->purchase_link)
                <a href="{{ $book->purchase_link }}" target="_blank">Buy this book</a>
            @endif
            <form action="{{ route('delete.accepted.book', $book->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this book?');">
                @csrf
                @method('DELETE')
                <button type="submit">Delete</button>
            </form>
        </div>
        @endforeach
    </div>
    <a href="{{ route('books') }}" style="display: block; margin-top: 20px;">Back to Home</a>
    <script src="{{ asset('js/sorting.js') }}"></script>
</body>
</html>

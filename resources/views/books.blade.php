<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Book List</title>
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
        .stars {
            display: flex;
            align-items: center;
        }
        .star {
            font-size: 24px;
            color: gold;
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
    <h2>Your books</h2>
    <div class="dropdown">
        <label for="sort">Sort By:</label>
        <select id="sort">
            <option value="name_asc">Name (A-Z)</option>
            <option value="name_desc">Name (Z-A)</option>
            <option value="rating_asc">Rating (Lowest First)</option>
            <option value="rating_desc">Rating (Highest First)</option>
            <option value="author">Author (A-Z)</option>
            <option value="pages">Pages</option>
        </select>
    </div>
    <div>
        <input type="text" id="search" placeholder="Search by book title..." autocomplete="off">
    </div>
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
        <div class="book-card">
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
                <img src="{{ $book->cover }}" alt="Book Cover" class="book-image">
            @else
                <p>No Cover Image</p>
            @endif
            <a href="{{ route('details.book', $book->id) }}">Details</a>
            <a href="{{ route('edit.book', $book->id) }}">Edit Book</a> <!-- New Edit Book button -->
            <form action="{{ route('delete.book', $book->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this book?');">
                @csrf
                @method('DELETE')
                <button type="submit">Delete</button>
            </form>
        </div>
    @endforeach
    </div>
    <div class="navigation-buttons">
        <a href="{{ route('book.recommend') }}" class="recommendation-button">Get Book Recommendations</a>
    </div>
    <div class="navigation-buttons">
        <a href="{{ route('accepted.books') }}" class="recommendation-button">wishlist</a>
    </div>
    <a href="{{ route('home') }}"><button type="button">Search books</button></a>

    <script src="{{ asset('js/sorting.js') }}"></script>
</body>
</html>

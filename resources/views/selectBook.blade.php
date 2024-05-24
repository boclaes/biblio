<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Book</title>
    <style>
        .book-image {
            transition: transform 0.3s ease;
        }
        .book-image-hover:hover {
            transform: scale(1.1);
        }
    </style>
</head>
<body>
    <h2>Select a Book to Add to Your Library:</h2>

    @if ($errors->any())
        <div style="color: red;">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @forelse ($books as $book)
        <div style="margin-bottom: 20px;">
            @php
                $googleBooksId = $book['id'];
            @endphp

            @if (array_key_exists($googleBooksId, $userBookMap))
                <a href="{{ route('details.book.search', $userBookMap[$googleBooksId]) }}">
                    <img src="{{ $book['volumeInfo']['imageLinks']['thumbnail'] ?? asset('images/default_cover.jpg') }}" alt="Cover Image" class="book-image book-image-hover" style="height: 100px; vertical-align: middle; margin-right: 10px;">
                </a>
            @else
                <img src="{{ $book['volumeInfo']['imageLinks']['thumbnail'] ?? asset('images/default_cover.jpg') }}" alt="Cover Image" class="book-image" style="height: 100px; vertical-align: middle; margin-right: 10px;">
            @endif
            <div style="display: inline-block; vertical-align: middle;">
                <p>{{ $book['volumeInfo']['title'] }}
                    @if (isset($book['volumeInfo']['authors']))
                        by {{ implode(', ', $book['volumeInfo']['authors']) }}
                    @else
                        <em>Unknown Author</em>
                    @endif
                </p>

                @if (array_key_exists($googleBooksId, $userBookMap))
                    <form method="post" action="{{ route('delete.book', ['id' => $userBookMap[$googleBooksId]]) }}" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        @if (request('query'))
                            <input type="hidden" name="query" value="{{ request('query') }}">
                        @endif
                        <button type="submit">Delete from Library</button>
                    </form>
                    <form method="get" action="{{ route('edit.book', ['id' => $userBookMap[$googleBooksId]]) }}" style="display: inline;">
                        @if (request('query'))
                            <input type="hidden" name="query" value="{{ request('query') }}">
                        @endif
                        <button type="submit">Edit</button>
                    </form>
                @else
                <form method="post" action="{{ route('addBook') }}">
                    @csrf
                    <input type="hidden" name="bookId" value="{{ $book['id'] }}">
                    <input type="hidden" name="query" value="{{ request('query') }}">
                    <input type="hidden" name="searchType" value="title"> 
                    <button type="submit">Add to Library</button>
                </form>
                @endif
            </div>
        </div>
    @empty
        <p>No books found for your search query.</p>
    @endforelse

    <a href="{{ route('search.form') }}"><button type="button">Back</button></a>
</body>
</html>

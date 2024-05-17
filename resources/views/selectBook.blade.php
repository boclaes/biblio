<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Book</title>
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
            <img src="{{ $book['volumeInfo']['imageLinks']['thumbnail'] ?? asset('images/default_cover.jpg') }}" alt="Cover Image" style="height: 100px; vertical-align: middle; margin-right: 10px;">
            <div style="display: inline-block; vertical-align: middle;">
                <p>{{ $book['volumeInfo']['title'] }}
                    @if (isset($book['volumeInfo']['authors']))
                        by {{ implode(', ', $book['volumeInfo']['authors']) }}
                    @else
                        <em>Unknown Author</em>
                    @endif
                </p>
                <form method="post" action="{{ route('addBook') }}">
                    @csrf
                    <input type="hidden" name="bookId" value="{{ $book['id'] }}">
                    <button type="submit">Add to Library</button>
                </form>
            </div>
        </div>
    @empty
        <p>No books found for your search query.</p>
    @endforelse

    <a href="{{ route('home') }}"><button type="button">Back</button></a>
</body>
</html>

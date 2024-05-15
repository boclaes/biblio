<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Book</title>
</head>
<body>
    <h2>Select a Book to Add to Your Library:</h2>
    @foreach ($books as $book)
    <div style="margin-bottom: 20px;">
        <img src="{{ $book['volumeInfo']['imageLinks']['thumbnail'] ?? asset('images/default_cover.jpg') }}" alt="Cover Image" style="height: 100px; vertical-align: middle; margin-right: 10px;">
        <div style="display: inline-block; vertical-align: middle;">
            <p>{{ $book['volumeInfo']['title'] }} by {{ is_array($book['volumeInfo']['authors']) ? implode(', ', $book['volumeInfo']['authors']) : $book['volumeInfo']['authors'][0] }}</p>
            <form method="post" action="{{ route('addBook') }}">
                @csrf
                <input type="hidden" name="bookId" value="{{ $book['id'] }}">
                <button type="submit">Add to Library</button>
            </form>
        </div>
    </div>
    @endforeach

    <a href="{{ route('home') }}"><button type="button">Back</button></a>
</body>
</html>

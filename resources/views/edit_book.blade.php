<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Book</title>
</head>
<body>
    <h1>Edit Book Details for {{ $book->title }}</h1>
    <form method="POST" action="{{ route('update.book', $book->id) }}">
        @csrf
        @method('PUT')
        <label for="title">Title:</label><br>
        <input type="text" id="title" name="title" value="{{ $book->title }}"><br>

        <label for="author">Author:</label><br>
        <input type="text" id="author" name="author" value="{{ $book->author }}"><br>

        <label for="pages">Pages:</label><br>
        <input type="number" id="pages" name="pages" value="{{ $book->pages }}"><br>

        <label for="genre">Genre:</label><br>
        <input type="text" id="genre" name="genre" value="{{ $book->genre }}"><br>

        <label for="genre">Genre:</label><br>
        <input type="text" id="description" name="description" value="{{ $book->description }}"><br>

        <button type="submit">Save</button>
    </form>
    <a href="{{ route('books', $book->id) }}"><button type="button">Back to Books</button></a>
</body>
</html>

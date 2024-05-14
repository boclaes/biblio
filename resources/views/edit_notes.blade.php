<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Notes</title>
</head>
<body>
    <h1>Edit Notes for {{ $book->title }}</h1>
    <form method="POST" action="{{ route('save.notes', $book->id) }}">
        @csrf
        <label for="notes">Notes:</label><br>
        <textarea id="notes" name="notes" rows="4" cols="50">{{ $book->notes_user }}</textarea><br>
        <button type="submit">Save</button>
    </form>
    <a href="{{ route('details.book', $book->id) }}"><button type="button">Back to Details</button></a>
</body>
</html>

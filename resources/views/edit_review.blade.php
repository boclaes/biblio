<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Notes</title>
</head>
<body>
    <h1>Edit Review for {{ $book->title }}</h1>
    <form method="POST" action="{{ route('save.review', $book->id) }}">
        @csrf
        <label for="review">Review:</label>
        <textarea id="review" name="review" rows="4" cols="50">{{ $book->review_user }}</textarea><br>
        <button type="submit">Save</button>
    </form>
    <a href="{{ route('details.book', $book->id) }}"><button type="button">Back</button></a>
</body>
</html>


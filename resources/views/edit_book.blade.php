@extends('layouts.app')

@section('title', 'Book Details')

@section('content')

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

        <label for="genre">Description:</label><br>
        <input type="text" id="description" name="description" value="{{ $book->description }}"><br>

        <button type="submit">Save</button>
    </form>
    <a href="{{ route('books') }}"><button type="button">Back to Books</button></a>
@endsection
@extends('layouts.app')

@section('title', 'Edit Book')

@section('content')
    <h2>Edit Book</h2>

    @if ($errors->any())
        <div style="color: red;">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="post" action="{{ route('update.book', ['id' => $book->id]) }}">
        @csrf
        @method('PUT')

        <input type="hidden" name="query" value="{{ old('query', $query) }}">

        <div>
            <label for="title">Title:</label>
            <input type="text" id="title" name="title" value="{{ old('title', $book->title) }}" required>
        </div>

        <div>
            <label for="author">Author:</label>
            <input type="text" id="author" name="author" value="{{ old('author', $book->author) }}" required>
        </div>

        <div>
            <label for="pages">Pages:</label>
            <input type="number" id="pages" name="pages" value="{{ old('pages', $book->pages) }}" required>
        </div>

        <div>
            <label for="place">Place:</label>
            <input type="number" id="place" name="place" value="{{ old('place', $book->place) }}" required>
        </div>

        <div>
            <label for="description">Description:</label>
            <textarea id="description" name="description" required>{{ old('description', $book->description) }}</textarea>
        </div>

        <button type="submit">Update Book</button>
    </form>

    <button type="button" onclick="window.history.back();">Back</button>
@endsection

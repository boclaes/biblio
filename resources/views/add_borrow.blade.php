@extends('layouts.app')

@section('title', 'Add borrow')

@section('content')
<div class="container">
    <h1>Who borrowed my book?</h1>
    <form action="{{ route('books.storeBorrow') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="book_id">Select a book:</label>
            <select name="book_id" id="book_id" class="form-control" required>
                @foreach ($books as $book)
                    <option value="{{ $book->id }}">{{ $book->title }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="borrower_name">Who borrowed it:</label>
            <input type="text" id="borrower_name" name="borrower_name" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="borrowed_since">Borrowed since:</label>
            <input type="date" id="borrowed_since" name="borrowed_since" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Save</button>
        <a href="{{ route('books') }}"><button type="button">Cancel</button></a>
    </form>
</div>
<script src="{{ asset('js/app.js') }}"></script>
@endsection
@include('layouts.footer')

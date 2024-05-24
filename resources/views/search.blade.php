@extends('layouts.app')

@section('title', 'Scanning Page - Book Scanner')

@section('content')
    <h2>Enter ISBN or Book Title:</h2>

    <form id="searchForm" method="post" action="{{ route('search') }}">
        @csrf
        <input id="searchInput" type="text" name="query" placeholder="Enter ISBN or Book Title" required autofocus autocomplete="off">
        <input id="searchType" type="hidden" name="searchType" value="title"> <!-- Default to title -->
        <button type="submit">Search Book</button>
    </form>
    <a href="{{ route('books') }}"><button type="button">Library</button></a>
    
    <script>
        window.onload = function() {
            document.getElementById('searchInput').focus();A
        };

        document.getElementById('searchInput').addEventListener('input', function(event) {
            const input = event.target.value;
            const searchTypeInput = document.getElementById('searchType');
            // Updated condition to correctly identify ISBN
            if ((input.length === 10 || input.length === 13) && !isNaN(input)) {
                searchTypeInput.value = 'isbn';
            } else {
                searchTypeInput.value = 'title';
            }
        });
    </script>
@endsection

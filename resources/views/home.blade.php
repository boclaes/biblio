@extends('layouts.app')

@section('title', 'Scanning Page - Book Scanner')

@section('content')
    <h2>Enter ISBN or Book Title:</h2>

    <form id="searchForm" method="post" action="{{ route('search') }}">
        @csrf
        <input id="searchInput" type="text" name="query" placeholder="Enter ISBN or Book Title" required autofocus autocomplete="off">
        <button type="submit">Search Book</button>
    </form>
    <a href="{{ route('books') }}"><button type="button">Library</button></a>
    
    <script>
        window.onload = function() {
            document.getElementById('searchInput').focus();
        };

        document.getElementById('searchForm').addEventListener('input', function(event) {
            const input = event.target.value;
            if (input.length == 13 && !isNaN(input)) { // Additional check for non-numeric
                document.getElementById('searchForm').submit();
            }
        });
    </script>
@endsection

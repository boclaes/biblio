@extends('layouts.app')

@section('title', 'Scanning Page - Book Scanner')

@section('content')
    <h2>Enter ISBN or Book Title:</h2>

    <form id="searchForm" method="post" action="{{ route('search') }}">
        @csrf
        <div>
            <button type="button" id="searchByTitle" onclick="setSearchType('title')">Search by Title</button>
            <button type="button" id="searchByISBN" onclick="setSearchType('isbn')">Search by ISBN</button>
        </div>
        <input id="searchInput" type="text" name="query" placeholder="Enter Book Title" required autofocus autocomplete="off">
        <input id="searchType" type="hidden" name="searchType" value="title"> <!-- Default to title -->
        <button type="submit">Search Book</button>
    </form>
    <a href="{{ route('books') }}"><button type="button">Library</button></a>
    
    <script>
        window.onload = function() {
            document.getElementById('searchInput').focus();
        };

        function setSearchType(type) {
            const searchTypeInput = document.getElementById('searchType');
            searchTypeInput.value = type;

            const searchInput = document.getElementById('searchInput');
            searchInput.placeholder = type === 'isbn' ? 'Enter ISBN' : 'Enter Book Title';
            searchInput.value = '';
            searchInput.setAttribute('pattern', type === 'isbn' ? '\\d{10}|\\d{13}' : '.*');
            searchInput.setAttribute('title', type === 'isbn' ? 'Please enter a valid 10 or 13 digit ISBN.' : 'Please enter a valid book title.');
        }

        document.getElementById('searchForm').addEventListener('submit', function(event) {
            console.log('Form submitted'); // Debug: Check if the form submit is caught
            const searchType = document.getElementById('searchType').value;
            const query = document.getElementById('searchInput').value;

            if (searchType === 'isbn') {
                const isbnPattern = /^(?:\d{10}|\d{13})$/;
                console.log('Checking ISBN', query); // Debug: Log the query
                if (!isbnPattern.test(query)) {
                    console.log('Invalid ISBN'); // Debug: Log if invalid
                    alert('Please enter a valid 10 or 13 digit ISBN.');
                    event.preventDefault();
                }
            } else if (searchType === 'title') {
                console.log('Checking Title', query); // Debug: Log the title check
                if (!query.trim()) {
                    console.log('Invalid Title'); // Debug: Log if invalid
                    alert('Please enter a valid book title.');
                    event.preventDefault();
                }
            }
        });

    </script>
@endsection
@include('layouts.footer')

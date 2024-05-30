<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Book Scanner')</title>
    <style>
        .book-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        .book-card {
            width: 200px;
            border: 1px solid #ccc;
            padding: 10px;
        }
        .book-image {
            width: 100%;
            height: auto;
        }
        .stars {
            display: flex;
            align-items: center;
        }
        .star {
            font-size: 24px;
            color: gold;
        }
        .dropdown {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 999;
        }
        .alphabet-filter {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            justify-content: center;
        }
        .alphabet-filter a {
            text-decoration: none;
            font-size: 18px;
            color: #000;
        }
        .alphabet-filter a.active {
            font-weight: bold;
            color: #007bff;
        }
    </style>
</head>
<body>
    <!-- Error and success messages -->
    @if(session('error'))
        <div style="color: red;">{{ session('error') }}</div>
    @endif
    @if(session('success'))
        <div style="color: green;">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit">Logout</button>
    </form>

    <a href="{{ route('account.settings') }}"><button type="button">Account settings</button></a>

    <div class="dropdown">
        <label for="sort">Sort By:</label>
        <select id="sort">
        @isset($includeName)
            @if ($includeName)
                <option value="name_asc">Name (A-Z)</option>
                <option value="name_desc">Name (Z-A)</option>
            @endif
        @endisset
        @isset($includeRatings)
            @if ($includeRatings)
                <option value="rating_asc">Rating (Lowest First)</option>
                <option value="rating_desc">Rating (Highest First)</option>
            @endif
        @endisset
        @isset($includeAuthor)
            @if ($includeAuthor)
                <option value="author">Author (A-Z)</option>
            @endif
        @endisset
        @isset($includePages)
            @if ($includePages)
                <option value="pages">Pages</option>
            @endif
        @endisset
        @isset($includeDate)
            @if ($includeDate)
                <option value="date_asc">Date (Oldest First)</option>
                <option value="date_desc">Date (Newest First)</option>
            @endif
        @endisset
        </select>
    </div>
    <div>
        <input type="text" id="search" placeholder="Search by book title..." autocomplete="off">
    </div>
    <div class="alphabet-filter">
        @foreach(range('A', 'Z') as $letter)
            <a href="#" data-letter="{{ $letter }}">{{ $letter }}</a>
        @endforeach
    </div>

    @yield('content')
</body>
</html>

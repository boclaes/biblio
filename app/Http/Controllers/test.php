<?php
/*
    public function recommendBooksByFavoriteAuthors()
    {
        $user = Auth::user();
        // Assume you have a method to get favorite authors based on user's books or explicit preferences
        $favoriteAuthors = $this->getFavoriteAuthors($user->id);

        if (empty($favoriteAuthors)) {
            return redirect()->route('home')->with('error', 'No favorite authors found. Add some books or set your favorite authors to get recommendations!');
        }

        $exclusionList = $this->getExclusionList($user->id);
        $book = $this->getRecommendationByAuthors($favoriteAuthors, $exclusionList);

        if (!$book) {
            return redirect()->route('home')->with('error', 'No recommendations found for your favorite authors.');
        }

        return view('recommendation', compact('book'));
    }

    protected function getFavoriteAuthors($userId)
        {
            // Fetch the authors of the highest rated or most frequently interacted books by the user
            $authors = Book::where('user_id', $userId)
                        ->select('author')
                        ->groupBy('author')
                        ->orderByRaw('COUNT(*) DESC')
                        ->limit(3)
                        ->pluck('author')
                        ->toArray();

            return $authors;
        }   

    protected function getRecommendationByAuthors($authors, $exclusionList)
        {
            $authorQuery = implode('|', array_map(function ($author) {
                return 'inauthor:"' . addslashes($author) . '"';
            }, $authors));

            $query = "{$authorQuery} {$exclusionList}&maxResults=10&orderBy=relevance";

            Log::info("Query to Google Books API: " . $query);
            $response = Http::get("https://www.googleapis.com/books/v1/volumes?q={$query}");

            if ($response->successful()) {
                $books = $response->json('items');
                if (!empty($books)) {
                    $selectedBook = $books[array_rand($books)];
                    $bookInfo = $selectedBook['volumeInfo'];
                    $googleBookId = $selectedBook['id'];

                    return [
                        'id' => $googleBookId,
                        'title' => $bookInfo['title'],
                        'author' => implode(", ", $bookInfo['authors'] ?? []),
                        'year' => $bookInfo['publishedDate'] ?? null,
                        'description' => $bookInfo['description'] ?? 'Description not available',
                        'cover' => $bookInfo['imageLinks']['thumbnail'] ?? null,
                        'genre' => implode(", ", $bookInfo['categories'] ?? []),
                        'pages' => $bookInfo['pageCount'] ?? 'Page count not available',
                    ];
                }
            }

            Log::error("Failed to fetch recommendations or no books found.");
            return null;
        }
    */
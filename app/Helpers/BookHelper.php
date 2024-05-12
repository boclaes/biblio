<?php

namespace App\Helpers;

use Exception;
use Illuminate\Support\Facades\Http;
use App\Models\Book;
use Illuminate\Support\Facades\Log;

class BookHelper
{
    public function getBookDetails($isbn)
    {
        try {
            $response = Http::get("https://www.googleapis.com/books/v1/volumes?q=isbn:$isbn");
    
            if ($response->successful()) {
                $bookInfo = $response->json('items.0.volumeInfo');
    
                return [
                    'title' => $bookInfo['title'],
                    'author' => implode(", ", $bookInfo['authors'] ?? []),
                    'year' => $bookInfo['publishedDate'] ?? null,
                    'description' => $bookInfo['description'] ?? 'Description not available',
                    'cover' => $bookInfo['imageLinks']['thumbnail'] ?? null,
                    'genre' => implode(", ", $bookInfo['categories'] ?? []),
                    'pages' => isset($bookInfo['pageCount']) && $bookInfo['pageCount'] > 0 ? $bookInfo['pageCount'] : 'Page count not available',
                ];
            } else {
                return null;
            }
        } catch (Exception $e) {
            return null;
        }
    }
    
    public function getRecommendation(array $genres, $exclusionList)
    {
        $genreQuery = implode("|", $genres);
        $query = "subject:{$genreQuery}&maxResults=40&orderBy=relevance";  // Increased maxResults and set orderBy to relevance
    
        Log::info("Query to Google Books API: " . $query);
        $response = Http::get("https://www.googleapis.com/books/v1/volumes?q={$query}");
    
        if ($response->successful()) {
            $books = $response->json('items');
            if (!empty($books)) {
                $books = array_filter($books, function ($book) use ($exclusionList) {
                    $info = $book['volumeInfo'];
                    $title = $info['title'] ?? '';
                    $authors = implode(", ", $info['authors'] ?? []);
                    $publishedDate = substr($info['publishedDate'] ?? '', 0, 4);
    
                    foreach ($exclusionList as $excluded) {
                        if ($title === $excluded['title'] && $authors === $excluded['author'] && $publishedDate === $excluded['year']) {
                            return false;
                        }
                    }
                    return true;
                });
    
                if (!empty($books)) {
                    // Calculate weights based on ratings count
                    $totalRatings = array_sum(array_map(function ($book) {
                        return $book['volumeInfo']['ratingsCount'] ?? 0;
                    }, $books));
    
                    // If there are no ratings, fallback to random selection
                    if ($totalRatings > 0) {
                        $weightedBooks = [];
                        foreach ($books as $book) {
                            $count = $book['volumeInfo']['ratingsCount'] ?? 0;
                            $weight = $count / $totalRatings;
                            for ($i = 0; $i < $weight * 100; $i++) {
                                $weightedBooks[] = $book;
                            }
                        }
                        $selectedBook = $weightedBooks[array_rand($weightedBooks)];
                    } else {
                        $selectedBook = $books[array_rand($books)];
                    }
    
                    $bookInfo = $selectedBook['volumeInfo'];
                    $googleBookId = $selectedBook['id'];
    
                    return [
                        'id' => $googleBookId,
                        'title' => $bookInfo['title'],
                        'author' => implode(", ", $bookInfo['authors'] ?? []),
                        'year' => substr($bookInfo['publishedDate'] ?? '', 0, 4),
                        'description' => $bookInfo['description'] ?? 'Description not available',
                        'cover' => $bookInfo['imageLinks']['thumbnail'] ?? null,
                        'genre' => implode(", ", $bookInfo['categories'] ?? []),
                        'pages' => isset($bookInfo['pageCount']) && $bookInfo['pageCount'] > 0 ? $bookInfo['pageCount'] : 'Page count not available',
                    ];
                }
            }
        }
        Log::error("Failed to fetch recommendations or no books found.");
        return null;
    }    

    
    public function saveToDatabase($bookDetails)
    {
        try {
            Book::create($bookDetails);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}

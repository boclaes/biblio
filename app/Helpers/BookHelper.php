<?php

namespace App\Helpers;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Book;

class BookHelper
{
    private function cleanDescription($description)
    {
        $description = strip_tags($description);
        $description = preg_replace('/\s+/', ' ', $description);
        $maxLength = 1000; // Adjust the length as needed

        if (strlen($description) > $maxLength) {
            $truncated = substr($description, 0, $maxLength);

            // Find the last occurrence of sentence-ending punctuation within the truncated string
            $lastPunctuation = max(
                strrpos($truncated, '.'),
                strrpos($truncated, '!'),
                strrpos($truncated, '?')
            );

            // If a complete sentence end is found within the truncated string, truncate at that point
            if ($lastPunctuation !== false && $lastPunctuation >= $maxLength - 50) {
                $description = substr($truncated, 0, $lastPunctuation + 1) . '...';
            } else {
                // Otherwise, find the last space to avoid cutting a word in half
                $lastSpace = strrpos($truncated, ' ');
                if ($lastSpace !== false && $lastSpace >= $maxLength - 50) {
                    $description = substr($truncated, 0, $lastSpace) . '...';
                } else {
                    // Fallback to the original truncation if no space is found
                    $description = rtrim($truncated) . '...';
                }
            }
        }

        return $description;
    }

    private function selectRelevantGenre($genres)
    {
        $preferredGenres = [
            'Fantasy', 'Science Fiction', 'Mystery', 'Thriller', 'Romance',
            'Young Adult (YA) Fiction', 'Historical Fiction', 'Horror',
            'Literary Fiction', 'Adventure', 'Non-Fiction', 'Biography',
            'Memoir', 'Self-Help', 'Health & Wellness', 'Children’s Literature',
            'Crime', 'Graphic Novel', 'Paranormal', 'Classics', 'Humor',
            'Western', 'Dystopian', 'Contemporary', 'Psychological Thriller',
            'Action and Adventure', 'Espionage', 'Urban Fantasy', 'Epic Fantasy',
            'Middle Grade', 'Picture Books', 'Erotica', 'True Crime', 'War & Military',
            'History', 'Philosophy', 'Poetry', 'Self-Improvement', 'Business',
            'Science & Technology', 'Cookbooks', 'Art & Photography', 'Religious & Spiritual',
            'Gardening & Horticulture', 'Sports', 'Travel', 'True Adventure',
            'Music', 'Fairy Tales', 'Folklore', 'Drama', 'Crafts & Hobbies',
            'Parenting & Families', 'Health & Fitness', 'Medical', 'Political',
            'Legal Thriller', 'Sociology', 'Anthropology', 'Cyberpunk', 'Steam Punk',
            'Historical Romance', 'Regency Romance', 'Inspirational', 'Alternative History',
            'Realistic Fiction'
        ];        
    
        // Ensure $genres is an array
        if (is_string($genres)) {
            $genres = explode(' / ', $genres);
        }
    
        // Normalize genres by removing "&" and trimming spaces
        $normalizedGenres = array_map(function($genre) {
            $genre = str_replace('&', 'and', $genre); // Replace '&' with 'and'
            return trim($genre); // Trim spaces
        }, $genres);
    
        // Log the normalized genres
        Log::info('Normalized genres:', $normalizedGenres);
    
        // Filter genres against the preferred list
        $matchedGenres = array_intersect($normalizedGenres, $preferredGenres);
        if (!empty($matchedGenres)) {
            $selectedGenre = reset($matchedGenres); // Get the first matched genre
            Log::info('Selected genre: ' . $selectedGenre);
            return $selectedGenre;
        }
    
        // If no preferred genre is matched, fallback to the first available or 'Fiction'
        $fallbackGenre = !empty($normalizedGenres) ? reset($normalizedGenres) : 'Fiction';
        Log::info('Fallback genre: ' . $fallbackGenre);
        return $fallbackGenre;
    }     

    private function getGoogleImage($title)
    {
        // Explicitly load environment variables
        $apiKey = config('GOOGLE_CSE_API_KEY', env('GOOGLE_CSE_API_KEY'));
        $cx = config('GOOGLE_CSE_CX', env('GOOGLE_CSE_CX'));
        
        // Add logging for debugging
        Log::info("Google CSE API Key: " . $apiKey);
        Log::info("Google CSE CX: " . $cx);
    
        if (empty($apiKey) || empty($cx)) {
            Log::error("API key or CX is not set in the environment variables.");
            return asset('images/default_cover.jpg');
        }
    
        $query = urlencode($title . ' book cover');
        $url = "https://www.googleapis.com/customsearch/v1?q={$query}&cx={$cx}&key={$apiKey}&searchType=image&num=1";
    
        Log::info("Fetching Google Image with URL: {$url}");
    
        $response = Http::get($url);
        if ($response->successful()) {
            $data = $response->json();
            Log::info("Google Image Search Response: ", $data);
    
            if (!empty($data['items'][0]['link'])) {
                return $data['items'][0]['link'];
            }
        } else {
            Log::error("Failed to fetch Google Image: " . $response->body());
        }
    
        return asset('images/default_cover.jpg'); // Fallback to default image
    }    

    private function formatBookDetails($bookItem)
    {
        // Ensure we have volumeInfo
        $bookInfo = $bookItem['volumeInfo'] ?? [];
    
        if (!isset($bookInfo['title'])) {
            Log::error("Title not found in book information");
            return [
                'error' => 'Title not found in book information'
            ];
        }
    
        $title = $bookInfo['title'];
        $cover = $bookInfo['imageLinks']['thumbnail'] ?? $this->getGoogleImage($title);
        Log::info("Selected Cover Image for '{$title}': {$cover}");
    
        return [
            'google_books_id' => $bookItem['id'] ?? null, // Extract Google Books ID from the book item
            'title' => $title,
            'author' => implode(", ", $bookInfo['authors'] ?? []),
            'year' => $bookInfo['publishedDate'] ?? null,
            'description' => $this->cleanDescription($bookInfo['description'] ?? 'Description not available'),
            'cover' => $cover,
            'genre' => $this->selectRelevantGenre($bookInfo['categories'] ?? []),
            'pages' => isset($bookInfo['pageCount']) && $bookInfo['pageCount'] > 0 ? $bookInfo['pageCount'] : 0,
        ];
    }
    
             

    public function getBookDetailsByISBN($isbn)
    {
        $response = Http::get("https://www.googleapis.com/books/v1/volumes?q=isbn:$isbn");
        if ($response->successful()) {
            $bookItem = $response->json('items.0'); // Pass the entire item
            return $this->formatBookDetails($bookItem);
        }
        return null;
    }
    

    public function searchBooksByTitle($title)
    {
        $response = Http::get("https://www.googleapis.com/books/v1/volumes?q=" . urlencode($title));
        if ($response->successful()) {
            return $response->json()['items'] ?? [];
        }
        return null;
    }

    public function getBookDetailsById($bookId)
    {
        $response = Http::get("https://www.googleapis.com/books/v1/volumes/{$bookId}");
        if ($response->successful()) {
            $bookItem = $response->json(); // Pass the entire item
            return $this->formatBookDetails($bookItem);
        }
        return null;
    }
    

    public function getRecommendation(array $genres, $exclusionList)
    {
        $genreQuery = implode("|", array_map('urlencode', $genres)); // Ensure genres are URL-encoded properly
        $query = "subject:{$genreQuery}&maxResults=40&orderBy=relevance";
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
                    $description = $info['description'] ?? null;
    
                    // Check if the book has an author and description
                    if (empty($authors) || empty($description)) {
                        return false;
                    }
    
                    // Check if the book is in the exclusion list
                    foreach ($exclusionList as $excluded) {
                        if ($title === $excluded['title'] && $authors === $excluded['author'] && $publishedDate === $excluded['year']) {
                            return false;
                        }
                    }
    
                    return true;
                });
    
                if (!empty($books)) {
                    $totalRatings = array_sum(array_map(function ($book) {
                        return $book['volumeInfo']['ratingsCount'] ?? 0;
                    }, $books));
    
                    if ($totalRatings > 0) {
                        $weightedBooks = [];
                        foreach ($books as $book) {
                            $count = $book['volumeInfo']['ratingsCount'] ?? 0;
                            $weight = $count / $totalRatings;
                            for ($i = 0; $i < $weight * 100; ++$i) {
                                $weightedBooks[] = $book;
                            }
                        }
                        $selectedBook = $weightedBooks[array_rand($weightedBooks)];
                    } else {
                        $selectedBook = $books[array_rand($books)];
                    }
    
                    $bookInfo = $selectedBook['volumeInfo'];
                    $googleBookId = $selectedBook['id'];
    
                    return $this->formatBookDetails($bookInfo) + ['id' => $googleBookId];
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
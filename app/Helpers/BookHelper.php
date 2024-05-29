<?php

namespace App\Helpers;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\Book;
use App\Models\User;


class BookHelper
{
    private function cleanDescription($description)
    {
        $description = strip_tags($description);
        $description = preg_replace('/\s+/', ' ', $description);
        $maxLength = 1000; // Adjust the length as needed

        if (strlen($description) > $maxLength) {
            $truncated = substr($description, 0, $maxLength);

            $lastPunctuation = max(
                strrpos($truncated, '.'),
                strrpos($truncated, '!'),
                strrpos($truncated, '?')
            );

            if ($lastPunctuation !== false && $lastPunctuation >= $maxLength - 50) {
                $description = substr($truncated, 0, $lastPunctuation + 1) . '...';
            } else {
                $lastSpace = strrpos($truncated, ' ');
                if ($lastSpace !== false && $lastSpace >= $maxLength - 50) {
                    $description = substr($truncated, 0, $lastSpace) . '...';
                } else {
                    $description = rtrim($truncated) . '...';
                }
            }
        }

        return $description;
    }

    private function isBookValid($book, $exclusionList)
    {
        $info = $book['volumeInfo'] ?? [];
        $title = strtolower(trim($info['title'] ?? ''));
        $authors = isset($info['authors']) ? implode(", ", $info['authors']) : '';
        $authors = strtolower(trim($authors));
        $publishedDate = substr($info['publishedDate'] ?? '', 0, 4);
        $bookKey = "{$title}|{$authors}|{$publishedDate}";

        Log::info("Checking book: {$bookKey}");
        return !in_array($bookKey, $exclusionList);
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

        if (is_string($genres)) {
            $genres = explode(' / ', $genres);
        }

        $normalizedGenres = array_map(function($genre) {
            $genre = str_replace('&', 'and', $genre);
            return trim($genre);
        }, $genres);

        Log::info('Normalized genres:', $normalizedGenres);

        $matchedGenres = array_intersect($normalizedGenres, $preferredGenres);
        if (!empty($matchedGenres)) {
            $selectedGenre = reset($matchedGenres);
            Log::info('Selected genre: ' . $selectedGenre);
            return $selectedGenre;
        }

        $fallbackGenre = !empty($normalizedGenres) ? reset($normalizedGenres) : 'Fiction';
        Log::info('Fallback genre: ' . $fallbackGenre);
        return $fallbackGenre;
    }     

    private function getGoogleImage($title)
    {
        $apiKey = config('GOOGLE_CSE_API_KEY', env('GOOGLE_CSE_API_KEY'));
        $cx = config('GOOGLE_CSE_CX', env('GOOGLE_CSE_CX'));
        
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

        return asset('images/default_cover.jpg');
    }    

    private function formatBookDetails($bookItem)
    {
        $bookInfo = $bookItem['volumeInfo'] ?? [];

        if (empty($bookInfo['title'])) {
            Log::error("Title not found in book information: " . json_encode($bookInfo));
            return [
                'error' => 'Title not found in book information'
            ];
        }

        $title = $bookInfo['title'];
        $cover = $bookInfo['imageLinks']['thumbnail'] ?? $this->getGoogleImage($title);
        Log::info("Selected Cover Image for '{$title}': {$cover}");

        return [
            'google_books_id' => $bookItem['id'] ?? null,
            'title' => $title,
            'author' => implode(", ", $bookInfo['authors'] ?? ['Unknown Author']),
            'year' => $bookInfo['publishedDate'] ?? 'Unknown Year',
            'description' => $this->cleanDescription($bookInfo['description'] ?? 'Description not available'),
            'cover' => $cover,
            'genre' => $this->selectRelevantGenre($bookInfo['categories'] ?? ['Classics']),
            'pages' => isset($bookInfo['pageCount']) && $bookInfo['pageCount'] > 0 ? $bookInfo['pageCount'] : 'N/A',
        ];
    }  

    public function getBookDetailsByISBN($isbn)
    {
        Log::info("Fetching book details for ISBN: {$isbn}");
        $response = Http::get("https://www.googleapis.com/books/v1/volumes?q=isbn:$isbn");
    
        if ($response->successful()) {
            $bookItem = $response->json('items.0');
            if ($bookItem) {
                Log::info("Book details retrieved successfully for ISBN: {$isbn}");
                return $this->formatBookDetails($bookItem);
            } else {
                Log::error("No book items found in API response for ISBN: {$isbn}");
            }
        } else {
            Log::error("Failed to fetch data from Google Books API for ISBN: {$isbn}. Response: " . $response->body());
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
            $bookItem = $response->json();
            return $this->formatBookDetails($bookItem);
        }
        return null;
    }

    public function getRecommendation(array $genres, $exclusionList, $userId, $startIndex = 0)
    {
        $user = User::find($userId);
        $genreQuery = implode("|", array_map('urlencode', $genres));
        $version = Cache::get('books_cache_version', 1); // Retrieve current version or default to 1
        $cacheKey = "books_{$genreQuery}_{$startIndex}_{$version}"; // Use version in the cache key
    
        $books = Cache::get($cacheKey);
        $retryCount = 0;
        $retryLimit = 5; // Set the maximum number of retries to avoid infinite loops
    
        while (!$books && $retryCount < $retryLimit) {
            $query = "subject:{$genreQuery}&startIndex={$startIndex}&maxResults=40";
            $fullUrl = "https://www.googleapis.com/books/v1/volumes?q={$query}&langRestrict=en&orderBy=relevance";            
            Log::info("Querying Google Books API: " . $fullUrl);
            $response = Http::get($fullUrl);
    
            if ($response->successful()) {
                $books = $response->json('items');
                if (empty($books)) {
                    $attemptNumber = $retryCount + 1;
                    Log::info("No more books available at startIndex {$startIndex}, retrying... (Attempt {$attemptNumber} of {$retryLimit})");
                    $user->last_book_index = 0;
                    $user->save();
    
                    // Increment the version to invalidate old cache keys
                    $newVersion = $version + 1;
                    Cache::put('books_cache_version', $newVersion, 86400); // Store new version for a day
                    Cache::forget($cacheKey); // Optionally clear the old cache immediately
    
                    $startIndex = 0; // Reset startIndex for a new attempt
                    $retryCount++; // Increment the retry counter
                    continue; // Continue to the next iteration of the loop
                } else {
                    Cache::put($cacheKey, $books, 3600); // Cache the results for 1 hour
                    break; // Exit the loop as books have been found
                }
            } else {
                Log::error("Failed to fetch data from Google Books API: " . $response->body());
                return null; // Exit if there is an API error
            }
        }
    
        if ($retryCount >= $retryLimit) {
            Log::error("Maximum retries reached, no books found.");
            return null; // Exit the function if the retry limit is reached without success
        }
    
        foreach ($books as $index => $book) {
            if ($this->isBookValid($book, $exclusionList)) {
                $user->last_book_index = $startIndex + $index; // Update last book index
                $user->save();
                return $this->formatBookDetails($book);
            }
        }
    
        // If no valid book is found and retry limit hasn't been reached, increment startIndex and try again
        $startIndex += 40;
        return $this->getRecommendation($genres, $exclusionList, $userId, $startIndex);
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

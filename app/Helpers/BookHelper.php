<?php

namespace App\Helpers;

use Exception;
use Illuminate\Support\Facades\Http;
use App\Models\Book;

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

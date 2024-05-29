<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use App\Helpers\BookHelper;
use App\Models\Book;
use App\Models\Review;
use App\Models\RejectedBook;
use App\Models\Borrowing;
use App\Models\AcceptedBook;


class BookController extends Controller
{
    protected $bookHelper;

    public function __construct(BookHelper $bookHelper)
    {
        $this->bookHelper = $bookHelper;
    }

    public function home()
    {
        return view('home');
    }

    public function index()
    {
        return view('index');
    }

    public function search(Request $request)
    {
        $query = $request->input('query');
        $searchType = $request->input('searchType', 'title');
    
        // Validate search type
        if (!in_array($searchType, ['isbn', 'title'])) {
            return redirect()->route('search')->with('error', 'Invalid search type specified.');
        }
    
        if ($searchType === 'isbn') {
            return $this->searchByISBN($query);
        } else {
            return $this->searchByTitle($query);
        }
    }    
    
    
    private function searchByISBN($isbn)
    {
        Log::info("Searching for book with ISBN: {$isbn}");
        $bookDetails = $this->bookHelper->getBookDetailsByISBN($isbn);
    
        if (!$bookDetails) {
            Log::error("Book not found or API request failed for ISBN: {$isbn}");
            return redirect()->route('books')->with('error', 'Book not found or API request failed');
        }
    
        if (!isset($bookDetails['title'])) {
            Log::error("Book details are incomplete. Title is missing for ISBN: {$isbn}");
            return redirect()->route('books')->with('error', 'Book details are incomplete. Title is missing.');
        }
    
        try {
            $user = Auth::user();
            $existingBook = $user->books()
                ->where('title', $bookDetails['title'])
                ->where('google_books_id', $bookDetails['google_books_id'])
                ->first();
    
            if ($existingBook) {
                Log::info("Book already in collection: {$bookDetails['title']}");
                return redirect()->route('books')->with('error', 'This book is already in your collection');
            }
    
            $book = Book::create($bookDetails);
            $user->books()->attach($book);
    
            Log::info("Book saved to library: {$bookDetails['title']}");
            return redirect()->route('books')->with('success', 'Book saved to your library');
        } catch (Exception $exception) {
            Log::error("Failed to add book to collection: " . $exception->getMessage());
            return redirect()->route('books')->with('error', 'Failed to add book to your collection');
        }
    }
    
    
    private function searchByTitle($title)
    {
        if (empty($title)) {
            Log::error("Search query is empty for title search.");
            return redirect()->route('search')->with('error', 'Search query cannot be empty.');
        }
    
        Log::info("Searching for books with title: {$title}");
        $books = $this->bookHelper->searchBooksByTitle($title);
    
        if (empty($books)) {
            return redirect()->route('search')->with('error', 'No books found for the given title.');
        }
    
        $books = array_slice($books, 0, 5);
    
        $user = Auth::user();
        $userBooks = $user->books()->get();
    
        $userBookMap = $userBooks->pluck('id', 'google_books_id')->toArray();
    
        return view('selectBook', [
            'books' => $books,
            'userBookMap' => $userBookMap,
            'query' => $title,
        ]);
    }
    
     

    public function addBook(Request $request)
    {
        Log::info('Received data for adding book:', $request->all());

        $bookId = $request->input('bookId');
        $query = $request->input('query');
        $searchType = $request->input('searchType', 'isbn');

        $bookDetails = $this->bookHelper->getBookDetailsById($bookId);

        if (!$bookDetails) {
            Log::error('Book details not found for ID: ' . $bookId);
            return redirect()->route('search.form')->with('error', 'Failed to fetch book details.');
        }

        Log::info('Book details retrieved:', $bookDetails);

        try {
            $user = Auth::user();
            $existingBook = $user->books()
            ->where('title', $bookDetails['title'])
            ->where('google_books_id', $bookDetails['google_books_id'])
            ->first();

            if ($existingBook) {
                Log::warning('Attempt to add duplicate book: ' . $bookDetails['title'] . ' (Google Books ID: ' . $bookDetails['google_books_id'] . ')');
                return redirect()->route('search.form')->with('error', 'This book is already in your collection.');
            }

            $book = new Book($bookDetails);
            $book->save();
            $user->books()->attach($book);

            if ($searchType === 'title') {
                return redirect()->route('search', ['query' => $query])->with('success', 'Book added to your library successfully.');
            } else {
                return redirect()->route('search')->with('success', 'Book added to your library successfully.');
            }
        } catch (\Exception $e) {
            Log::error('Failed to add book: ' . $e->getMessage());
            return redirect()->route('search')->with('error', 'Failed to add book to your collection.');
        }
    }

    public function list()
    {
        $user = Auth::user();
        $books = $user->books()->with('reviews')->get();

        return view('books', compact('books'));
    }

    public function delete(Request $request, $id)
    {
        $book = Book::findOrFail($id);
        $book->delete();

        $user = Auth::user();
        $user->books()->detach($id);

        $query = $request->input('query');
        if ($query) {
            return redirect()->route('search', ['query' => $query])->with('success', 'Book deleted successfully.');
        }

        return redirect()->back()->with('success', 'Book deleted successfully.');
    }

    public function editBook(Request $request, $id)
    {
        $user = Auth::user();
        $book = $user->books()->find($id);

        if (!$book) {
            return redirect()->route('books')->with('error', 'You do not have permission to edit this book.');
        }

        $query = $request->input('query');
        return view('edit_book', compact('book', 'query'));
    }

    public function updateBook(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'pages' => 'required|integer|min:1',
            'place' => 'required|integer|min:0',
            'description' => 'required|string',
        ]);

        $user = Auth::user();
        $book = $user->books()->find($id);

        if (!$book) {
            return redirect()->route('books')->with('error', 'You do not have permission to edit this book.');
        }

        $book->title = $request->input('title');
        $book->author = $request->input('author');
        $book->pages = $request->input('pages');
        $book->place = $request->input('place');
        $book->description = $request->input('description');
        $book->save();

        $query = $request->input('query');
        if ($query) {
            return redirect()->route('search', ['query' => $query])->with('success', 'Book details updated successfully.');
        }

        return redirect()->route('books', $book->id)->with('success', 'Book details updated successfully.');
    }

    public function saveNotes(Request $request, $id)
    {
        try {
            $book = Book::findOrFail($id);
            $book->notes_user = $request->input('notes');
            $book->save();
            return redirect()->route('details.book', $book->id)->with('success', 'Notes saved successfully.');
        } catch (\Exception $e) {
            logger()->error('Error saving notes: ' . $e->getMessage());
            
            return back()->withInput()->withErrors(['error' => 'Failed to save notes. Please try again later.']);
        }
    }

    public function saveReview(Request $request, $id)
    {
        try {
            $book = Book::findOrFail($id);
            $book->review = $request->input('review');
            $book->save();
            return redirect()->route('details.book', $book->id)->with('success', 'Notes saved successfully.');
        } catch (\Exception $e) {
            logger()->error('Error saving review: ' . $e->getMessage());
            
            return back()->withInput()->withErrors(['error' => 'Failed to save notes. Please try again later.']);
        }
    }

    public function showDetails($id)
    {
        $user = Auth::user();
        $book = $user->books()->find($id);

        if (!$book) {
            return redirect()->route('books');
        }

        return view('details', compact('book'));
    }

    public function showDetailsSearch($id) {
        $user = Auth::user();
        $book = $user->books()->find($id);
    
        if (!$book) {
            return redirect()->route('books');
        }
    
        $query = request('query', '');
        return view('detailsSearch', ['book' => $book, 'query' => $query]);
    }
    

    public function editNotes($id)
    {
        $user = Auth::user();
        $book = $user->books()->find($id);

        if (!$book) {
            return redirect()->route('books')->with('error', 'You do not have permission to edit this book.');
        }

        return view('edit_notes', compact('book'));
    }

    public function editReview($id)
    {
        $user = Auth::user();
        $book = $user->books()->find($id);

        if (!$book) {
            return redirect()->route('books')->with('error', 'You do not have permission to edit this book.');
        }

        return view('edit_review', compact('book'));
    }

    public function rateBook(Request $request, $bookId)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
        ]);
    
        $logData = [
            'rating' => $request->rating,
            'user_id' => auth()->id(),
            'book_id' => $bookId,
            'timestamp' => now()->toDateTimeString(),
        ];
    
        $logFile = storage_path('logs/custom.log');
        file_put_contents($logFile, json_encode($logData) . PHP_EOL, FILE_APPEND);
    
        try {
            $review = Review::updateOrCreate(
                ['user_id' => auth()->id(), 'book_id' => $bookId],
                ['rating' => $request->rating]
            );
    
            return response()->json(['message' => 'Book rated successfully', 'review' => $review], 200);
        } catch (\Exception $e) {
            $errorLogData = [
                'error' => $e->getMessage(),
                'timestamp' => now()->toDateTimeString(),
            ];
            file_put_contents($logFile, json_encode($errorLogData) . PHP_EOL, FILE_APPEND);
    
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }    

    public function getBookRating($id)
    {
        $book = Book::findOrFail($id);

        $rating = $book->reviews()->avg('rating');

        return response()->json(['rating' => $rating]);
    }

    public function updateStatus(Request $request, $id)
    {
        $book = Book::findOrFail($id);

        Log::info('Received status update data:', $request->all());
        
        $fields = ['want_to_read', 'reading', 'done_reading'];
        foreach ($fields as $field) {
            if ($request->has($field)) {
                $book->$field = $request->$field;
            }
        }

        $book->save();
        
        return response()->json(['message' => 'Status updated successfully', 'book' => $book], 200);
    }

    protected function calculateTopGenre($userId)
    {
        $user = Auth::user();
        $books = $user->books()->whereNotNull('genre')->get();
    
        if ($books->isEmpty()) {
            return null;
        }
    
        $genreCounts = [];
    
        foreach ($books as $book) {
            $genres = explode(' / ', $book->genre);
            Log::info("Processing book ID: {$book->id}, genres: " . json_encode($genres));
            
            // Check if it is a single genre and skip if it is just "Fiction or General"
            if (count($genres) === 1 && (trim($genres[0]) === 'Fiction' || trim($genres[0]) === 'General')) {
                Log::info("Skipping genre '" . $genres[0] . "'");
                continue;
            }
            
            // Combine genres and count
            $combinedGenre = implode(' / ', array_map('trim', $genres));
            if (!isset($genreCounts[$combinedGenre])) {
                $genreCounts[$combinedGenre] = 0;
            }
            $genreCounts[$combinedGenre]++;
            Log::info("Genre '{$combinedGenre}' count incremented to: {$genreCounts[$combinedGenre]}");
        }
    
        Log::info("Final genre counts: " . json_encode($genreCounts));
    
        arsort($genreCounts);
    
        return array_key_first($genreCounts);
    }    


    public function recommendBook()
    {
        $user = Auth::user();
        $exclusionList = $this->getExclusionList($user->id);
    
        $topGenre = $this->calculateTopGenre($user->id);
        
        if (!$topGenre) {
            return redirect()->route('books')->with('error', 'No favorite genres found. Add some books to get recommendations!');
        }
    
        Log::info("Top genre being queried: " . $topGenre);
        $startIndex = 0;
    
        $book = $this->bookHelper->getRecommendation([$topGenre], $exclusionList, $user->id, $startIndex);
    
        if (!$book) {
            return redirect()->route('books')->with('error', 'No recommendations found for your favorite genres.');
        }
    
        return view('recommendation', compact('book'));
    }
    
    public function handleDecision(Request $request)
    {
        try {
            $decision = $request->input('decision');
            $bookDetails = $request->only(['google_books_id', 'title', 'author', 'year', 'description', 'cover', 'genre', 'pages']);
    
            $user = Auth::user();
            $userId = $user->id;
    
            if ($decision === 'reject') {
                $this->rejectBook($userId, $bookDetails);
            } elseif ($decision === 'accept') {
                $this->acceptBook($userId, $bookDetails);
            }
    
            $exclusionList = $this->getExclusionList($userId);
            $topGenre = $this->calculateTopGenre($userId);
    
            if (!$topGenre) {
                return response()->json(['error' => 'No favorite genres found'], 400);
            }
    
            $startIndex = $user->last_book_index + 1;
            Log::info("handleDecision updated startIndex to: {$startIndex}");
    
            $newBook = $this->bookHelper->getRecommendation([$topGenre], $exclusionList, $userId, $startIndex);
    
            return response()->json([
                'success' => true,
                'newBook' => $newBook
            ]);
        } catch (\Exception $e) {
            Log::error("Error handling decision: " . $e->getMessage());
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }      
    
    public function rejectBook($userId, $bookDetails)
    {
        Log::info('Rejecting book: ' . $bookDetails['title'] . ' by ' . $bookDetails['author']);
        if (empty($bookDetails['google_books_id'])) {
            return response()->json(['error' => 'Google Books ID is required'], 400);
        }
    
        RejectedBook::create([
            'user_id' => $userId,
            'google_books_id' => $bookDetails['google_books_id'],
            'title' => $bookDetails['title'],
            'author' => $bookDetails['author'],
            'year' => $bookDetails['year'],
        ]);
    
        $user = Auth::user();
        $exclusionList = $this->getExclusionList($userId);
        $topGenre = $this->calculateTopGenre($userId);
    
        if (!$topGenre) {
            return response()->json(['error' => 'No favorite genres found'], 400);
        }
    
        $startIndex = $user->last_book_index + 1;
        Log::info("rejectBook updated startIndex to: {$startIndex}");
    
        $newBook = $this->bookHelper->getRecommendation([$topGenre], $exclusionList, $userId, $startIndex);
    
        return response()->json([
            'success' => true,
            'newBook' => $newBook
        ]);
    }     

    protected function getExclusionList($userId)
    {
        $rejectedBooks = RejectedBook::where('user_id', $userId)->get(['title', 'author', 'year']);
        $acceptedBooks = AcceptedBook::where('user_id', $userId)->get(['title', 'author', 'year']);
        $excludedBooks = $rejectedBooks->concat($acceptedBooks);
    
        $exclusionList = $excludedBooks->map(function ($book) {
            $title = strtolower(trim($book->title));
            $author = strtolower(trim($book->author));
            $year = substr(trim($book->year), 0, 4); // Assuming year is in format YYYY-MM-DD or similar
            $key = "{$title}|{$author}|{$year}";
            Log::info("Adding to exclusion list: {$key}");
            return $key;
        })->toArray();
    
        Log::info("Complete exclusion list: " . json_encode($exclusionList));
        return $exclusionList;
    }
    
    

    public function acceptBook($userId, $bookDetails)
    {
        $defaultPurchaseLink = $this->generatePurchaseLink($bookDetails['title'], $bookDetails['author']);

        AcceptedBook::create([
            'user_id' => $userId,
            'google_books_id' => $bookDetails['google_books_id'],
            'title' => $bookDetails['title'],
            'author' => $bookDetails['author'],
            'year' => $bookDetails['year'],
            'description' => $bookDetails['description'],
            'cover' => $bookDetails['cover'],
            'genre' => $bookDetails['genre'],
            'pages' => $bookDetails['pages'],
            'purchase_link' => $bookDetails['purchase_link'] ?? $defaultPurchaseLink
        ]);
    }

    private function generatePurchaseLink($title, $author)
    {
        $baseURL = "https://www.amazon.com/s?k=";
        $query = urlencode("\"$title\" \"$author\"");
        return $baseURL . $query;
    }

    public function showAcceptedBooks()
    {
        $user = Auth::user();
        $acceptedBooks = AcceptedBook::where('user_id', $user->id)->get();
        return view('acceptedBooks', compact('acceptedBooks'));
    }

    public function deleteAcceptedBook($id)
    {
        $acceptedBook = AcceptedBook::findOrFail($id);
        $acceptedBook->delete();
        return redirect()->back()->with('success', 'Accepted book deleted successfully.');
    }

    public function showAddBorrow()
    {
        $user = Auth::user();
        $books = $user->books()->where('borrowed', 0)->get();
        return view('add_borrow', compact('books'));
    }

    public function storeBorrow(Request $request)
    {
        $request->validate([
            'book_id' => 'required|exists:books,id',
            'borrower_name' => 'required|string',
            'borrowed_since' => 'required|date',
        ]);

        $book = Book::findOrFail($request->book_id);
        $book->borrowed = true;
        $book->save();

        $user = Auth::user();

        $borrowing = new Borrowing([
            'book_id' => $request->book_id,
            'borrower_name' => $request->borrower_name,
            'borrowed_since' => $request->borrowed_since,
            'user_id' => $user->id,
        ]);
        $borrowing->save();

        return redirect()->route('books')->with('success', 'Book borrowing recorded successfully.');
    }

    public function showBorrowedBooks()
    {
        $user = Auth::user();
        $borrowings = Borrowing::with('book')->where('user_id', $user->id)->get();
        return view('borrowed_books', compact('borrowings'));
    }

    public function returnBook(Borrowing $borrowing)
    {
        $book = $borrowing->book;
        $book->borrowed = 0;
        $book->save();
        $borrowing->delete();
        return redirect()->route('borrowed-books')->with('success', 'Book returned successfully!');
    }

    public function searchForm()
    {
        return view('search'); // This now points to what was previously home.blade.php
    }
}

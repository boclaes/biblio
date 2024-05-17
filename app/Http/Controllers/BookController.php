<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\BookHelper;
use App\Models\Book;
use App\Models\Reviews;
use App\Models\RejectedBook;
use App\Models\Borrowing;
use App\Models\AcceptedBook;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;

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
        $searchType = $request->input('searchType');
    
        if ($searchType === 'isbn') {
            // Handle ISBN search
            $bookDetails = $this->bookHelper->getBookDetailsByISBN($query);
    
            if (!$bookDetails) {
                return redirect()->route('home')->with('error', 'Book not found or API request failed');
            }
    
            try {
                $user = Auth::user();
                $existingBook = $user->books()->where('title', $bookDetails['title'])->first();
    
                if ($existingBook) {
                    return redirect()->route('home')->with('error', 'This book is already in your collection');
                }
    
                $book = Book::create($bookDetails);
                $user->books()->attach($book);
    
                return redirect()->route('home')->with('success', 'Book saved to your library');
            } catch (ModelNotFoundException $exception) {
                return redirect()->route('home')->with('error', 'Failed to add book to your collection');
            }
        } else {
            // Handle book title search
            $books = $this->bookHelper->searchBooksByTitle($query);
    
            if (empty($books)) {
                return redirect()->route('home')->with('error', 'No books found with that title');
            }
    
            $books = array_slice($books, 0, 5);
    
            // Get the titles of books in the user's library
            $user = Auth::user();
            $userBooks = $user->books()->get();
    
            // Create a mapping of titles to local IDs
            $userBookMap = $userBooks->pluck('id', 'title')->toArray();
    
            return view('selectBook', [
                'books' => $books,
                'userBookMap' => $userBookMap,
                'query' => $query, // Pass the query back to the view
            ]);
        }
    }
    
    public function addBook(Request $request)
    {
        Log::info('Received data for adding book:', $request->all());
    
        $bookId = $request->input('bookId');
        $query = $request->input('query');
        $searchType = $request->input('searchType', 'isbn'); // Default to 'isbn' if not provided
    
        $bookDetails = $this->bookHelper->getBookDetailsById($bookId);
    
        if (!$bookDetails) {
            Log::error('Book details not found for ID: ' . $bookId);
            return redirect()->route('home')->with('error', 'Failed to fetch book details.');
        }
    
        try {
            $user = Auth::user();
            $existingBook = $user->books()->where('title', $bookDetails['title'])->first();
    
            if ($existingBook) {
                Log::warning('Attempt to add duplicate book: ' . $bookDetails['title']);
                return redirect()->route('home')->with('error', 'This book is already in your collection.');
            }
    
            $book = new Book($bookDetails);
            $book->save();
            $user->books()->attach($book);
    
            if ($searchType === 'title') {
                return redirect()->route('search', ['query' => $query])->with('success', 'Book added to your library successfully.');
            } else {
                return redirect()->route('home')->with('success', 'Book added to your library successfully.');
            }
        } catch (\Exception $e) {
            Log::error('Failed to add book: ' . $e->getMessage());
            return redirect()->route('home')->with('error', 'Failed to add book to your collection.');
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
            return redirect()->route('home')->with('error', 'You do not have permission to edit this book.');
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
            'description' => 'required|string',
        ]);
    
        $user = Auth::user();
        $book = $user->books()->find($id);
    
        if (!$book) {
            return redirect()->route('home')->with('error', 'You do not have permission to edit this book.');
        }
    
        $book->title = $request->input('title');
        $book->author = $request->input('author');
        $book->pages = $request->input('pages');
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
            return redirect()->route('home')->with('error', 'Book not found.');
        }
    
        return view('details', compact('book'));
    }

    public function editNotes($id)
    {
        $user = Auth::user();
        $book = $user->books()->find($id);
        
        if (!$book) {
            return redirect()->route('home')->with('error', 'You do not have permission to edit this book.');
        }
    
        return view('edit_notes', compact('book'));
    }
    

    public function editReview($id)
    {
        $user = Auth::user();
        $book = $user->books()->find($id);
    
        if (!$book) {
            return redirect()->route('home')->with('error', 'You do not have permission to edit this book.');
        }
    
        return view('edit_review', compact('book'));
    }
    

    public function rateBook(Request $request, $bookId)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
        ]);
    
        Log::info('Incoming rating data:', [
            'rating' => $request->rating,
            'user_id' => auth()->id(),
            'book_id' => $bookId,
        ]);
    
        try {
            $review = Reviews::updateOrCreate(
                ['user_id' => auth()->id(), 'book_id' => $bookId],
                ['rating' => $request->rating]
            );
    
            return response()->json(['message' => 'Book rated successfully', 'review' => $review], 200);
        } catch (\Exception $e) {
            Log::error('Error rating book: ' . $e->getMessage());
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
    
        // Log the received data
        Log::info('Received status update data:', $request->all());
        
        // Update the corresponding field based on the checkbox clicked
        $fields = ['want_to_read', 'reading', 'done_reading'];
        foreach ($fields as $field) {
            if ($request->has($field)) {
                $book->$field = $request->$field;
            }
        }
    
        $book->save();
        
        return response()->json(['message' => 'Status updated successfully', 'book' => $book], 200);
    }
    
    public function recommendBook()
    {
        $user = Auth::user();
        $userId = $user->id;
        $exclusionList = $this->getExclusionList($userId);
        $favoriteGenres = $user->books()->selectRaw('genre, COUNT(*) as count')->groupBy('genre')->orderBy('count', 'DESC')->take(3)->pluck('genre');
    
        if ($favoriteGenres->isEmpty()) {
            return redirect()->route('home')->with('error', 'No favorite genres found. Add some books to get recommendations!');
        }
    
        $book = $this->bookHelper->getRecommendation($favoriteGenres->toArray(), $exclusionList);
    
        if (!$book) {
            return redirect()->route('home')->with('error', 'No recommendations found for your favorite genres.');
        }
    
        return view('recommendation', compact('book'));
    }    
      
    public function handleDecision(Request $request) {
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
    
            // Re-fetch genres each time to maintain consistent genre targeting
            $favoriteGenres = $user->books()->selectRaw('genre, COUNT(*) as count')->groupBy('genre')->orderBy('count', 'DESC')->take(3)->pluck('genre')->toArray();
            $exclusionList = $this->getExclusionList($userId);
            $newBook = $this->bookHelper->getRecommendation($favoriteGenres, $exclusionList);
    
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
        Log::info('Rejecting book: ' . $bookDetails['title'] . ' by ' . $bookDetails['author']); // Log the action with more details
    
        if (empty($bookDetails['google_books_id'])) {
            return response()->json(['error' => 'Google Books ID is required'], 400);
        }
    
        // Add to rejected books
        RejectedBook::create([
            'user_id' => $userId,
            'google_books_id' => $bookDetails['google_books_id'],
            'title' => $bookDetails['title'],
            'author' => $bookDetails['author'],
            'year' => $bookDetails['year'],
        ]);
    
        // Fetch a new recommendation
        $exclusionList = $this->getExclusionList($userId);
        $newBook = $this->bookHelper->getRecommendation([], $exclusionList);
    
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
    
        // Transform into an array of arrays for easier handling
        $exclusionList = $excludedBooks->map(function ($book) {
            return [
                'title' => $book->title,
                'author' => $book->author,
                'year' => (string) $book->year,  // Make sure to cast to string if necessary
            ];
        })->toArray();
    
        Log::info("Exclusion List: ", $exclusionList);
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
    
        // Fetch all entries from accepted_books where the user_id matches the logged-in user's ID
        $acceptedBooks = AcceptedBook::where('user_id', $user->id)
                                     ->get();
    
        // Pass the accepted books to the view
        return view('acceptedBooks', compact('acceptedBooks'));
    }

    public function deleteAcceptedBook($id)
    {
        $acceptedBook = AcceptedBook::findOrFail($id); // Find the accepted book entry by its ID
        $acceptedBook->delete(); // Delete the accepted book entry
    
        return redirect()->back()->with('success', 'Accepted book deleted successfully.');
    }

    public function showAddBorrow()
    {
        $books = Book::where('borrowed', 0)->get(); // Fetch only books that are not currently borrowed
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
    
        $borrowing = new Borrowing([
            'book_id' => $request->book_id,
            'borrower_name' => $request->borrower_name,
            'borrowed_since' => $request->borrowed_since,
        ]);
        $borrowing->save();
    
        return redirect()->route('books')->with('success', 'Book borrowing recorded successfully.');
    }    

    public function showBorrowedBooks()
    {
        // Fetch all borrowings with related book details
        $borrowings = Borrowing::with('book')->get();

        return view('borrowed_books', compact('borrowings'));
    }

    public function returnBook(Borrowing $borrowing)
    {
        // Update the borrowed status of the book to 0
        $book = $borrowing->book;
        $book->borrowed = 0;
        $book->save();
    
        // Delete the borrowing record
        $borrowing->delete();
    
        // Redirect back with a success message
        return redirect()->route('borrowed-books')->with('success', 'Book returned successfully!');
    }    
    
}
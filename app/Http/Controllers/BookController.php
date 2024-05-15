<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\BookHelper;
use App\Models\Book;
use App\Models\Reviews;
use App\Models\RejectedBook;
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
    
        // Check if the input is a numeric ISBN (assuming ISBN-13 here)
        if (is_numeric($query) && (strlen($query) == 10 || strlen($query) == 13)) {
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
    
            return view('selectBook', ['books' => $books]); // Passing results to a view for selection
        }
    }  
    
    public function addBook(Request $request)
    {
        Log::info('Received data for adding book:', $request->all());
    
        $bookId = $request->input('bookId');  // Now using bookId instead of bookTitle
    
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
    
            return redirect()->route('home')->with('success', 'Book added to your library successfully.');
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

    public function delete($id)
    {
        $book = Book::findOrFail($id);
        $book->delete();

        // Also, remove the book from the pivot table (book_user) if it exists
        $user = Auth::user();
        $user->books()->detach($id);

        return redirect()->back()->with('success', 'Book deleted successfully.');
    }

    public function editBook($id)
    {
        $user = Auth::user();
        $book = $user->books()->find($id);

        if (!$book) {
            return redirect()->route('home')->with('error', 'You do not have permission to edit this book.');
        }

        return view('edit_book', compact('book'));
    }

    public function updateBook(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'pages' => 'required|integer|min:1',
            'genre' => 'required|string|max:255',
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
        $book->genre = $request->input('genre');
        $book->description = $request->input('description');
        $book->save();

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

}

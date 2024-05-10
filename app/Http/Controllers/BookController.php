<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\BookHelper;
use App\Models\Book;
use App\Models\User;
use App\Models\Reviews;
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

    public function scan(Request $request)
    {
        $isbn = $request->input('isbn');
        $bookDetails = $this->bookHelper->getBookDetails($isbn);
    
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
    
            return redirect()->route('home')->with('success', 'Book details saved to database');
        } catch (ModelNotFoundException $exception) {
            return redirect()->route('home')->with('error', 'Failed to add book to your collection');
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
        $book = Book::findOrFail($id);
        return view('edit_notes', compact('book'));
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
    

}

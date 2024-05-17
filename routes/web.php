<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\AccountController;

// Public Routes
Route::get('/', function (Request $request) {
    if ($request->user()) {
        return redirect()->route('home');
    }
    return view('welcome');
})->name('welcome');

Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Password Reset Routes
Route::get('forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');

// Authenticated Routes
Route::middleware(['auth'])->group(function () {
    // Home
    Route::get('/home', [BookController::class, 'home'])->name('home');

    // Book Management
    Route::get('/books', [BookController::class, 'list'])->name('books');
    Route::get('/books/{id}/details', [BookController::class, 'showDetails'])->name('details.book');
    Route::post('/search', [BookController::class, 'search'])->name('search');
    Route::get('/search', [BookController::class, 'search'])->name('search');
    Route::post('/add-book', [BookController::class, 'addBook'])->name('addBook');
    Route::delete('/book/{id}', [BookController::class, 'delete'])->name('delete.book');

    // Book Borrow
    Route::get('/books/add-borrow', [BookController::class, 'showAddBorrow'])->name('books.addBorrow');
    Route::post('/books/add-borrow', [BookController::class, 'storeBorrow'])->name('books.storeBorrow');    
    Route::get('/borrowed-books', [BookController::class, 'showBorrowedBooks'])->name('borrowed-books');
    Route::delete('/borrowings/{borrowing}', [BookController::class, 'returnBook'])->name('borrowings.return');
    
    // Book Notes and Reviews
    Route::post('/books/{id}/save-notes', [BookController::class, 'saveNotes'])->name('save.notes');
    Route::get('/books/{id}/edit-notes', [BookController::class, 'editNotes'])->name('edit.notes');
    Route::post('/books/{id}/save-review', [BookController::class, 'saveReview'])->name('save.review');
    Route::get('/books/{id}/edit-review', [BookController::class, 'editReview'])->name('edit.review');
    
    // Book Editing and Updating
    Route::get('/books/{id}/edit', [BookController::class, 'editBook'])->name('edit.book'); 
    Route::put('/books/{id}', [BookController::class, 'updateBook'])->name('update.book');
    Route::post('/books/{id}/rate', [BookController::class, 'rateBook'])->name('books.rate');
    Route::get('/books/{id}/rating', [BookController::class, 'getBookRating'])->name('books.rating');
    Route::post('/books/{id}/update-status', [BookController::class, 'updateStatus'])->name('books.updateStatus');
    
    // Book Recommendations and Decisions
    Route::get('/recommend', [BookController::class, 'recommendBook'])->name('book.recommend');
    Route::post('/book/decision', [BookController::class, 'handleDecision'])->name('book.decision');
    Route::post('/book/reject', [BookController::class, 'rejectBook'])->name('book.reject');
    
    // Accepted Books Management
    Route::get('/accepted-books', [BookController::class, 'showAcceptedBooks'])->name('accepted.books');
    Route::delete('/accepted-books/{id}', [BookController::class, 'deleteAcceptedBook'])->name('delete.accepted.book');
    
    // Fetch Books
    Route::get('/fetch-books', [BookController::class, 'recommendBook'])->name('fetch-books');

});

Route::group(['middleware' => 'auth', 'prefix' => 'account'], function () {
    Route::get('settings', [AccountController::class, 'showSettings'])->name('account.settings');
    Route::post('update-name', [AccountController::class, 'updateName'])->name('account.name');
    Route::post('update-email', [AccountController::class, 'updateEmail'])->name('account.email');
    Route::post('update-password', [AccountController::class, 'updatePassword'])->name('account.password');
    Route::post('delete', [AccountController::class, 'deleteAccount'])->name('account.delete');
});

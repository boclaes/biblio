<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BookController;

Route::middleware(['auth'])->group(function () {
    Route::get('/home', [BookController::class, 'home'])->name('home');
    Route::post('/scan', [BookController::class, 'scan'])->name('scan');
    Route::get('/books', [BookController::class, 'list'])->name('books');
    Route::get('/books/{id}/details', [BookController::class, 'showDetails'])->name('details.book');
    Route::delete('/book/{id}', [BookController::class, 'delete'])->name('delete.book');
    Route::post('/books/{id}/save-notes', [BookController::class, 'saveNotes'])->name('save.notes');
    Route::get('/books/{id}/edit-notes', [BookController::class, 'editNotes'])->name('edit.notes');
    Route::post('/books/{id}/rate', [BookController::class, 'rateBook'])->name('books.rate');
    Route::get('/books/{id}/rating', [BookController::class, 'getBookRating'])->name('books.rating');
    Route::post('/books/{id}/update-status', [BookController::class, 'updateStatus'])->name('books.updateStatus');

});

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






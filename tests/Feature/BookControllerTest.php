<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Book;

class BookControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testAddBook()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $postData = [
            'google_books_id' => '12345',
            'title' => 'Test Book',
            'author' => 'John Doe',
            'year' => '2021',
            'description' => 'A test book description',
            'cover' => 'url_to_cover_image',
            'genre' => 'Fiction',
            'pages' => 300
        ];

        $response = $this->post(route('addBook'), $postData);

        $this->assertDatabaseHas('books', [
            'title' => 'Test Book',
            'author' => 'John Doe'
        ]);

        $response->assertRedirect(route('search'));
        $response->assertSessionHas('success', 'Book added to your library successfully.');
    }

    public function testDeleteBook()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $this->actingAs($user);

        $response = $this->delete(route('delete.book', ['id' => $book->id]));

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Book deleted successfully.');
        $this->assertDeleted($book);
    }

    public function testUpdateBook()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $this->actingAs($user);

        $updateData = [
            'title' => 'Updated Title',
            'author' => 'Updated Author',
            'pages' => 350
        ];

        $response = $this->put(route('update.book', ['id' => $book->id]), $updateData);

        $response->assertRedirect(route('books', $book->id));
        $response->assertSessionHas('success', 'Book details updated successfully.');
        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => 'Updated Title',
            'author' => 'Updated Author'
        ]);
    }

    public function testSearchBook()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('search', ['query' => 'Valid Query', 'searchType' => 'title']));

        $response->assertStatus(200);
        $response->assertViewHas('books');
    }
}

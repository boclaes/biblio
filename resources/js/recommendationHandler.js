function handleDecision(decision, googleBooksId) {
    const bookData = document.getElementById('book-recommendation').dataset.book;
    const book = JSON.parse(bookData);

    fetch('/book/decision', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            decision: decision,
            google_books_id: googleBooksId,
            ...book
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateBookRecommendation(data.newBook);
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(error => console.error('Error:', error));
}


function updateBookRecommendation(book) {
    if(book) {
        document.querySelector('#book-recommendation').dataset.book = JSON.stringify(book);
        document.querySelector('#book-recommendation img').src = book.cover;
        document.querySelector('#book-recommendation h2').textContent = book.title;
        document.querySelector('#book-recommendation p').textContent = book.description;
    } else {
        document.querySelector('#book-recommendation').innerHTML = '<p>No recommendations available at the moment.</p>';
    }
}

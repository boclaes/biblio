document.addEventListener('DOMContentLoaded', () => {
    const stars = document.querySelectorAll('.star');
    const bookDetails = document.querySelector('.book-details');
    const bookId = bookDetails.getAttribute('data-book-id');
    const token = bookDetails.getAttribute('data-csrf-token');

    stars.forEach(star => {
        star.addEventListener('click', () => {
            const rating = parseInt(star.getAttribute('data-value'));
            fetch("/books/" + bookId + "/rate", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': token
                },
                body: JSON.stringify({
                    rating: rating,
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                console.log('Rating response:', data);
                stars.forEach(s => {
                    const sValue = parseInt(s.getAttribute('data-value'));
                    s.classList.toggle('active', sValue <= rating);
                });
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    });
});

document.addEventListener('DOMContentLoaded', () => {
    const checkboxes = document.querySelectorAll('input[type="checkbox"]');
    const bookDetails = document.querySelector('.book-details');
    const bookId = bookDetails.getAttribute('data-book-id');
    const token = bookDetails.getAttribute('data-csrf-token');

    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', () => {
            const field = checkbox.id;
            const value = checkbox.checked ? 1 : 0;
            const data = { [field]: value };

            checkboxes.forEach(cb => {
                if (cb !== checkbox && cb.checked) {
                    cb.checked = false;
                    data[cb.id] = 0;
                }
            });

            fetch(`/books/${bookId}/update-status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': token
                },
                body: JSON.stringify(data)
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                console.log('Status updated:', data);
                // Update the book object with the new data
                Object.assign(bookDetails, data.book);
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    });
});







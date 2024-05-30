document.addEventListener('DOMContentLoaded', () => {
    const stars = document.querySelectorAll('.star');
    const bookDetails = document.querySelector('.book-details');
    const bookId = bookDetails.getAttribute('data-book-id');
    const token = bookDetails.getAttribute('data-csrf-token');

    console.log('Document loaded. Book ID:', bookId, 'CSRF Token:', token);

    stars.forEach(star => {
        star.addEventListener('click', () => {
            const rating = parseInt(star.getAttribute('data-value'));
            console.log('Star clicked. Rating:', rating);

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
                console.log('Fetch response received. Status:', response.status);
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                console.log('Rating response data:', data);
                stars.forEach(s => {
                    const sValue = parseInt(s.getAttribute('data-value'));
                    s.classList.toggle('active', sValue <= rating);
                });
            })
            .catch(error => {
                console.error('Fetch error:', error);
            });
        });
    });
});

document.addEventListener('DOMContentLoaded', () => {
    const checkboxes = document.querySelectorAll('input[type="checkbox"]');
    const bookDetails = document.querySelector('.book-details');
    const bookId = bookDetails.getAttribute('data-book-id');
    const token = bookDetails.getAttribute('data-csrf-token');

    console.log('Document loaded. Book ID:', bookId, 'CSRF Token:', token);

    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', () => {
            const field = checkbox.id;
            const value = checkbox.checked ? 1 : 0;
            console.log('Checkbox changed. Field:', field, 'Value:', value);

            const data = { [field]: value };

            checkboxes.forEach(cb => {
                if (cb !== checkbox && cb.checked) {
                    cb.checked = false;
                    data[cb.id] = 0;
                }
            });

            console.log('Data to be sent:', data);

            fetch(`/books/${bookId}/update-status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': token
                },
                body: JSON.stringify(data)
            })
            .then(response => {
                console.log('Fetch response received. Status:', response.status);
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                console.log('Status update response data:', data);
                // Update the book object with the new data
                Object.assign(bookDetails, data.book);
            })
            .catch(error => {
                console.error('Fetch error:', error);
            });
        });
    });
});

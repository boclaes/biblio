document.addEventListener('DOMContentLoaded', function() {
    const bookContainer = document.getElementById('bookContainer');
    const books = Array.from(bookContainer.children);
    const searchInput = document.getElementById('search');

    // Initial sorting by book title
    books.sort((a, b) => a.querySelector('h3').textContent.localeCompare(b.querySelector('h3').textContent));
    books.forEach(book => bookContainer.appendChild(book));

    // Event listener for changes in the search input
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.trim().toLowerCase();

        books.forEach(book => {
            const title = book.querySelector('h3').textContent.toLowerCase();
            if (title.includes(searchTerm)) {
                book.style.display = 'block';
            } else {
                book.style.display = 'none';
            }
        });
    });

    // Event listener for sorting
    document.getElementById('sort').addEventListener('change', function() {
        const sortBy = this.value;

        switch (sortBy) {
            case 'name_asc':
                books.sort((a, b) => a.querySelector('h3').textContent.localeCompare(b.querySelector('h3').textContent));
                break;
            case 'name_desc':
                books.sort((a, b) => b.querySelector('h3').textContent.localeCompare(a.querySelector('h3').textContent));
                break;
            case 'rating_asc':
                books.sort((a, b) => parseInt(a.querySelector('.stars').dataset.rating) - parseInt(b.querySelector('.stars').dataset.rating));
                break;
            case 'rating_desc':
                books.sort((a, b) => parseInt(b.querySelector('.stars').dataset.rating) - parseInt(a.querySelector('.stars').dataset.rating));
                break;
            case 'author':
                books.sort((a, b) => {
                    const authorA = a.querySelector('.author').textContent.substring(4); // Skip "By: "
                    const authorB = b.querySelector('.author').textContent.substring(4); // Skip "By: "
                    return authorA.localeCompare(authorB);
                });
                break;
            case 'pages':
                books.sort((a, b) => {
                    const pagesA = a.querySelector('.pages').textContent.split(': ')[1];
                    const pagesB = b.querySelector('.pages').textContent.split(': ')[1];
                    return sortPages(pagesA, pagesB);
                });
                break;
        }

        // Reorder books in the container
        while (bookContainer.firstChild) {
            bookContainer.removeChild(bookContainer.firstChild);
        }

        books.forEach(book => bookContainer.appendChild(book));
    });

    function sortPages(pagesA, pagesB) {
        // Convert page numbers to integers, treating non-numeric values as less than any number
        let numPagesA = parseInt(pagesA);
        let numPagesB = parseInt(pagesB);

        if (isNaN(numPagesA) && isNaN(numPagesB)) return 0;
        if (isNaN(numPagesA)) return -1;
        if (isNaN(numPagesB)) return 1;

        return numPagesA - numPagesB;
    }
});

document.addEventListener('DOMContentLoaded', function() {
    const bookContainer = document.getElementById('bookContainer');
    const books = Array.from(bookContainer.children);
    const searchInput = document.getElementById('search');
    let activeLetter = null;

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
                if (document.querySelector('.stars')) {
                    books.sort((a, b) => parseInt(a.querySelector('.stars').dataset.rating) - parseInt(b.querySelector('.stars').dataset.rating));
                }
                break;
            case 'rating_desc':
                if (document.querySelector('.stars')) {
                    books.sort((a, b) => parseInt(b.querySelector('.stars').dataset.rating) - parseInt(a.querySelector('.stars').dataset.rating));
                }
                break;
            case 'author':
                books.sort((a, b) => {
                    const authorA = a.querySelector('.author').textContent.substring(4); // Skip "By: "
                    const authorB = b.querySelector('.author').textContent.substring(4); // Skip "By: "
                    return authorA.localeCompare(authorB);
                });
                break;
            case 'pages':
                if (document.querySelector('.pages')) {
                    books.sort((a, b) => {
                        const pagesA = a.querySelector('.pages').textContent.split(': ')[1];
                        const pagesB = b.querySelector('.pages').textContent.split(': ')[1];
                        return sortPages(pagesA, pagesB);
                    });
                }
                break;
            case 'date_asc':
                books.sort((a, b) => {
                    const dateA = new Date(a.querySelector('.borrowed-since').textContent);
                    const dateB = new Date(b.querySelector('.borrowed-since').textContent);
                    return dateA - dateB;
                });
                break;
            case 'date_desc':
                books.sort((a, b) => {
                    const dateA = new Date(a.querySelector('.borrowed-since').textContent);
                    const dateB = new Date(b.querySelector('.borrowed-since').textContent);
                    return dateB - dateA;
                });
                break;
        }

        // Reorder books in the container
        while (bookContainer.firstChild) {
            bookContainer.removeChild(bookContainer.firstChild);
        }

        books.forEach(book => bookContainer.appendChild(book));
    });

    // Event listener for alphabetical filter
    document.querySelectorAll('.alphabet-filter a').forEach(link => {
        link.addEventListener('click', function(event) {
            event.preventDefault();
            const letter = this.dataset.letter.toLowerCase();

            if (activeLetter === letter) {
                // Deselect the current letter and show all books
                activeLetter = null;
                document.querySelectorAll('.alphabet-filter a').forEach(a => a.classList.remove('active'));
                books.forEach(book => book.style.display = 'block');
            } else {
                // Select a new letter
                activeLetter = letter;
                document.querySelectorAll('.alphabet-filter a').forEach(a => a.classList.remove('active'));
                this.classList.add('active');

                books.forEach(book => {
                    const title = book.querySelector('h3').textContent.toLowerCase();
                    if (letter === 'all' || title.startsWith(letter)) {
                        book.style.display = 'block';
                    } else {
                        book.style.display = 'none';
                    }
                });
            }
        });
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

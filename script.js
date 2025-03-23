document.addEventListener('DOMContentLoaded', () => {
    console.log('DOMContentLoaded fired - script.js fully loaded');

    // Navbar functionality
    const navbarToggler = document.querySelector('.navbar-toggler');
    const navbar = document.querySelector('.navbar');

    if (navbarToggler && navbar) {
        navbarToggler.addEventListener('click', () => {
            navbar.classList.toggle('active');
        });

        document.addEventListener('click', (e) => {
            if (!navbar.contains(e.target) && !navbarToggler.contains(e.target) && navbar.classList.contains('active')) {
                navbar.classList.remove('active');
            }
        });

        function adjustLayout() {
            const navbarNav = document.querySelector('.navbar-nav');
            if (!navbarNav) return;
            if (window.innerWidth <= 800) {
                navbarNav.style.display = navbar.classList.contains('active') ? 'flex' : 'none';
            } else {
                navbarNav.style.display = 'flex';
                navbar.classList.remove('active');
            }
        }
        window.addEventListener('resize', adjustLayout);
        adjustLayout();
    }

    // Generic form submission handler
    function handleFormSubmission(formId, url, successCallback) {
        const form = document.getElementById(formId);
        if (form) {
            form.addEventListener('submit', async (e) => {
                e.preventDefault();

                if (!form.checkValidity()) {
                    e.stopPropagation();
                    form.classList.add('was-validated');
                    return;
                }

                const formData = new FormData(form);
                try {
                    const response = await fetch(url, {
                        method: 'POST',
                        body: formData
                    });

                    if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                    const data = await response.json();

                    if (data.success) {
                        successCallback(form, data);
                    } else {
                        alert(data.error || 'An error occurred.');
                    }
                } catch (error) {
                    console.error(`${formId} error:`, error);
                    alert('A network error occurred. Please try again.');
                }
            });
        }
    }

    // Sign-in form
    handleFormSubmission('signInForm', '/iwatch/signin_handler.php', (form) => {
        window.location.href = '/iwatch/index.php';
    });

    // Sign-up form
    handleFormSubmission('signUpForm', '/iwatch/signup_handler.php', (form) => {
        alert('Sign-up successful! Please sign in.');
        form.closest('.modal').querySelector('.btn-close').click();
        form.reset();
        form.classList.remove('was-validated');
    });

    // Review forms
    const reviewSuccessCallback = (form) => {
        alert('Review submitted successfully!');
        form.reset();
        form.classList.remove('was-validated');
    };
    handleFormSubmission('reviewFormMovie', '/iwatch/reviews_handler.php', reviewSuccessCallback);
    handleFormSubmission('reviewFormSeries', '/iwatch/reviews_handler.php', reviewSuccessCallback);

    // Favorite button handler
    document.querySelectorAll('.btn-favorite').forEach(button => {
        button.addEventListener('click', async (e) => {
            e.preventDefault();
            const mediaId = button.dataset.mediaId;
            const mediaType = button.dataset.mediaType === 'movie' ? 'movie' : 'tv';
            const isFavorited = button.classList.contains('active');
    
            const formData = new FormData();
            formData.append('media_id', mediaId);
            formData.append('media_type', mediaType);
            formData.append('action', 'toggle');
    
            try {
                const response = await fetch('/iwatch/favorites_handler.php', {
                    method: 'POST',
                    body: formData
                });
    
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                const data = await response.json();
    
                if (data.success) {
                    if (data.isFavorited) {
                        button.textContent = 'Favorited';
                        button.classList.add('active');
                        button.disabled = false;
                        alert('Added to favorites!');
                    } else {
                        button.textContent = 'Add to Favorites';
                        button.classList.remove('active');
                        button.disabled = false;
                        alert('Removed from favorites!');
                    }
                } else {
                    alert(data.error || 'Could not update favorites.');
                }
            } catch (error) {
                console.error('Favorite error:', error);
                alert('An error occurred. Please try again.');
            }
        });
    });

    // Watchlist handlers
    console.log('Searching for .btn-watchlist-status elements');
    document.querySelectorAll('.btn-watchlist-status').forEach(select => {
        select.addEventListener('change', async () => {
            const mediaId = select.dataset.mediaId;
            const mediaType = select.dataset.mediaType;
            const status = select.value;
            const action = status ? 'update' : 'remove';

            const formData = new FormData();
            formData.append('media_id', mediaId);
            formData.append('media_type', mediaType);
            formData.append('status', status);
            formData.append('action', action);

            console.log('Sending watchlist request:', { mediaId, mediaType, status, action });

            try {
                const response = await fetch('/iwatch/watchlist_handler.php', {
                    method: 'POST',
                    body: formData
                });
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                const data = await response.json();
                console.log('Response:', data);

                if (data.success) {
                    alert(status ? `Updated to ${status}!` : 'Removed from watchlist!');
                    location.reload();
                } else {
                    alert(data.error || 'Error updating watchlist.');
                }
            } catch (error) {
                console.error('Watchlist update error:', error);
                alert('An error occurred.');
            }
        });
    });

    document.querySelectorAll('.btn-remove-watchlist').forEach(button => {
        button.addEventListener('click', async () => {
            if (!confirm('Are you sure you want to remove this from your watchlist?')) return;

            const mediaId = button.dataset.mediaId;
            const mediaType = button.dataset.mediaType;

            const formData = new FormData();
            formData.append('media_id', mediaId);
            formData.append('media_type', mediaType);
            formData.append('status', '');
            formData.append('action', 'remove');

            console.log('Sending remove request:', { mediaId, mediaType });

            try {
                const response = await fetch('/iwatch/watchlist_handler.php', {
                    method: 'POST',
                    body: formData
                });
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                const data = await response.json();
                console.log('Response:', data);

                if (data.success) {
                    alert('Removed from watchlist!');
                    button.closest('.grid-item').remove();
                } else {
                    alert(data.error || 'Could not remove from watchlist.');
                }
            } catch (error) {
                console.error('Watchlist remove error:', error);
                alert('An error occurred. Please try again.');
            }
        });
    });

    // Search and genre functionality
    const searchForm = document.getElementById('searchForm');
    const searchInput = document.querySelector('input[name="query"]');
    const selectedGenresInput = document.getElementById('selectedGenresInput');
    const selectedGenresContainer = document.getElementById('selectedGenres');
    const clearGenresBtn = document.getElementById('clearGenres');

    if (searchForm && searchInput) {
        searchForm.addEventListener('submit', (e) => {
            const query = searchInput.value.trim();
            const genres = selectedGenresInput ? selectedGenresInput.value : '';
            if (!query && !genres) {
                e.preventDefault();
                alert('Please enter a search query or select at least one genre.');
            }
        });
    }

    if (selectedGenresInput && selectedGenresContainer) {
        document.querySelectorAll('.genre-item').forEach(item => {
            item.addEventListener('click', (e) => {
                e.preventDefault(); // Prevent default anchor behavior
                const genreId = item.dataset.genreId;
                let genresArray = selectedGenresInput.value ? selectedGenresInput.value.split(',').filter(Boolean) : [];

                if (!genresArray.includes(genreId)) {
                    genresArray.push(genreId);
                    selectedGenresInput.value = genresArray.join(',');

                    const genreBadge = document.createElement('span');
                    genreBadge.className = 'badge bg-danger d-flex align-items-center';
                    genreBadge.innerHTML = `${item.textContent} <button type="button" class="btn-close btn-close-white remove-genre ms-2" data-genre-id="${genreId}"></button>`;
                    selectedGenresContainer.appendChild(genreBadge);

                    searchForm.submit(); // Trigger search immediately
                }
            });
        });

        selectedGenresContainer.addEventListener('click', (event) => {
            if (event.target.classList.contains('remove-genre')) {
                const genreIdToRemove = event.target.dataset.genreId;
                let genresArray = selectedGenresInput.value.split(',').filter(g => g !== genreIdToRemove);
                selectedGenresInput.value = genresArray.join(',');
                event.target.parentElement.remove();
                searchForm.submit(); // Trigger search on removal
            }
        });

        if (clearGenresBtn) {
            clearGenresBtn.addEventListener('click', () => {
                selectedGenresInput.value = '';
                selectedGenresContainer.innerHTML = '';
                searchForm.submit(); // Trigger search on clear
            });
        }
    }
    
});
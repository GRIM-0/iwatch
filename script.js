document.addEventListener('DOMContentLoaded', () => {
    const navbarToggler = document.querySelector('.navbar-toggler');
    const navbar = document.querySelector('.navbar');
    const signInForm = document.getElementById('signInForm');
    const signUpForm = document.getElementById('signUpForm');

    // Navbar toggle functionality
    navbarToggler.addEventListener('click', () => {
        navbar.classList.toggle('active');
    });

    // Close navbar when clicking outside
    document.addEventListener('click', (e) => {
        if (!navbar.contains(e.target) && !navbarToggler.contains(e.target) && navbar.classList.contains('active')) {
            navbar.classList.remove('active');
        }
    });

    // Adjust layout based on screen size
    function adjustLayout() {
        if (window.innerWidth <= 800) {
            document.querySelector('.navbar-nav').style.display = navbar.classList.contains('active') ? 'flex' : 'none';
        } else {
            document.querySelector('.navbar-nav').style.display = 'flex';
            navbar.classList.remove('active');
        }
    }
    window.addEventListener('resize', adjustLayout);
    adjustLayout();
});
    // Sign-in form submission
    if (signInForm) {
        signInForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const formData = new FormData(signInForm);
            console.log('Sign-in form data:', Array.from(formData.entries())); // Debug form data

            fetch('/iwatch/signin_handler.php', {
                method: 'POST',
                body: formData
            })
                .then(response => {
                    console.log('Sign-in Response Status:', response.status);
                    if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                    return response.text();
                })
                .then(text => {
                    console.log('Sign-in Raw Response:', text);
                    const data = JSON.parse(text);
                    if (data.success) {
                        window.location.href = '/iwatch/index.php';
                    } else {
                        alert(data.error || 'Unknown error');
                    }
                })
                .catch(error => {
                    console.error('Sign-in error:', error);
                    alert('An error occurred during sign-in.');
                });
        });
    }

    // Sign-up form submission
    if (signUpForm) {
        signUpForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const formData = new FormData(signUpForm);
            console.log('Sign-up form data:', Array.from(formData.entries())); // Debug form data

            fetch('/iwatch/signup_handler.php', {
                method: 'POST',
                body: formData
            })
                .then(response => {
                    console.log('Sign-up Response Status:', response.status);
                    if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                    return response.text();
                })
                .then(text => {
                    console.log('Sign-up Raw Response:', text);
                    const data = JSON.parse(text);
                    if (data.success) {
                        alert('Sign-up successful! Please sign in.');
                        signUpForm.closest('.modal').querySelector('.btn-close').click();
                    } else {
                        alert(data.error || 'Unknown error');
                    }
                })
                .catch(error => {
                    console.error('Sign-up error:', error);
                    alert('An error occurred during sign-up.');
                });
        });
    }

    // Favorite button handler
    document.addEventListener('click', (e) => {
        if (e.target.classList.contains('btn-favorite')) {
            e.preventDefault();
            const mediaId = e.target.getAttribute('data-media-id');
            const mediaType = e.target.getAttribute('data-media-type');
            const formData = new FormData();
            formData.append('media_id', mediaId);
            formData.append('media_type', mediaType);
            formData.append('action', 'add');
            console.log('Sending favorite request:', { mediaId, mediaType });
    
            fetch('/iwatch/favorites_handler.php', {
                method: 'POST',
                body: formData
            })
                .then(response => {
                    console.log('Favorite Response Status:', response.status);
                    if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                    return response.text();
                })
                .then(text => {
                    console.log('Favorite Raw Response:', text);
                    const data = JSON.parse(text);
                    if (data.success) {
                        alert('Added to favorites!');
                        e.target.textContent = 'Favorited';
                        e.target.disabled = true;
                    } else {
                        alert(data.error || 'Unknown error');
                    }
                })
                .catch(error => {
                    console.error('Favorite error:', error);
                    alert('An error occurred. Please try again.');
                });
        }
    });

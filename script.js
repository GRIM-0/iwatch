document.addEventListener('DOMContentLoaded', () => {
    const navbar = document.querySelector('.navbar');
    const navbarToggler = document.querySelector('.navbar-toggler');

    // Toggle navbar on button click
    navbarToggler.addEventListener('click', () => {
        navbar.classList.toggle('active');
    });

    // Close navbar when clicking outside on mobile
    document.addEventListener('click', (e) => {
        if (window.innerWidth <= 768 && !navbar.contains(e.target) && !navbarToggler.contains(e.target)) {
            navbar.classList.remove('active');
        }
    });

    // Adjust layout on window resize
    window.addEventListener('resize', () => {
        if (window.innerWidth <= 768) {
            navbarToggler.style.display = 'block'; // Ensure toggler is visible
            navbar.classList.remove('active'); // Reset navbar state
            document.querySelector('.container').style.marginLeft = '0'; // Remove gap on mobile
        } else {
            navbarToggler.style.display = 'none'; // Hide toggler on desktop
            navbar.classList.remove('active'); // Reset navbar state
            document.querySelector('.container').style.marginLeft = '260px'; // Restore desktop margin
        }
    });

    // Initial check on page load
    window.dispatchEvent(new Event('resize'));
});
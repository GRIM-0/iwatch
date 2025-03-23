<nav class="navbar navbar-expand-lg">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">iWatch</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon">☰</span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" href="index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'movies.php' ? 'active' : ''; ?>" href="movies.php">Movies</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'tv-series.php' ? 'active' : ''; ?>" href="tv-series.php">TV Series</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'search.php' ? 'active' : ''; ?>" href="search.php">Search</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'watchlist.php' ? 'active' : ''; ?>" href="watchlist.php">Watchlist</a>
                </li>
            </ul>
            <ul class="navbar-nav ms-auto">
                <?php if (isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true): ?>
                    <li class="nav-item dropdown">
                        <a class="username-nav dropdown-toggle nav-link" href="#" id="accountDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <?php echo htmlspecialchars($_SESSION["username"]); ?>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="accountDropdown">
                            <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                            <li><a class="dropdown-item" href="watchlist.php">Watchlist</a></li>
                            <li><a class="dropdown-item" href="logout.php">Log Out</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link btn btn-red" href="#" data-bs-toggle="modal" data-bs-target="#signInModal">Sign In</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
<?php
require "config.php";

$user_id = $_SESSION["user_id"] ?? null;
$is_logged_in = isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true;

// Fetch genre list for movies
$genreUrl = "https://api.themoviedb.org/3/genre/movie/list?api_key=" . $tmdb_api_key;
$genreResponse = getCachedApiResponse($genreUrl);
$genresList = json_decode($genreResponse, true)["genres"] ?? [];
?>

<!-- Sign In Modal -->
<div class="modal fade" id="signInModal" tabindex="-1" aria-labelledby="signInModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content sign-box">
            <div class="modal-header">
                <h5 class="modal-title" id="signInModalLabel">Sign In</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="signInForm" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <input type="text" class="form-control" name="username" placeholder="Username" required>
                        <div class="invalid-feedback">Please enter your username.</div>
                    </div>
                    <div class="mb-3">
                        <input type="password" class="form-control" name="password" placeholder="Password" required>
                        <div class="invalid-feedback">Please enter your password.</div>
                    </div>
                    <button type="submit" class="btn btn-red w-100">Sign In</button>
                </form>
            </div>
            <div class="modal-footer">
                <p>Don't have an account? <a href="#" data-bs-toggle="modal" data-bs-target="#signUpModal" data-bs-dismiss="modal">Sign Up</a></p>
            </div>
        </div>
    </div>
</div>

<!-- Sign Up Modal -->
<div class="modal fade" id="signUpModal" tabindex="-1" aria-labelledby="signUpModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content sign-box">
            <div class="modal-header">
                <h5 class="modal-title" id="signUpModalLabel">Sign Up</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="signUpForm" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <input type="text" class="form-control" id="signUpUsername" name="username" placeholder="Username (max 25 characters)" maxlength="25" required>
                        <div class="invalid-feedback" id="usernameFeedback"></div>
                    </div>
                    <div class="mb-3">
                        <input type="email" class="form-control" id="signUpEmail" name="email" placeholder="Email" required>
                        <div class="invalid-feedback" id="emailFeedback"></div>
                    </div>
                    <div class="mb-3">
                        <input type="password" class="form-control" id="signUpPassword" name="password" placeholder="Password (exactly 8 characters)" maxlength="8" required>
                        <div class="invalid-feedback" id="passwordFeedback"></div>
                    </div>
                    <button type="submit" class="btn btn-red w-100">Sign Up</button>
                </form>
            </div>
            <div class="modal-footer">
                <p>Already have an account? <a href="#" data-bs-toggle="modal" data-bs-target="#signInModal" data-bs-dismiss="modal">Sign In</a></p>
            </div>
        </div>
    </div>
</div>

<!-- Preferences Modal -->
<div class="modal fade" id="preferencesModal" tabindex="-1" aria-labelledby="preferencesModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content sign-box">
            <div class="modal-header">
                <h5 class="modal-title" id="preferencesModalLabel">Select Your Preferences</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="preferencesForm">
                    <div class="mb-3">
                        <label class="form-label">Preferred Genres</label>
                        <div id="genresContainer" class="d-flex flex-wrap gap-2">
                            <?php foreach ($genresList as $genre): ?>
                                <div class="form-check">
                                    <input class="form-check-input genre-checkbox" type="checkbox" name="genres[]" value="<?php echo $genre['id']; ?>" id="genre-<?php echo $genre['id']; ?>">
                                    <label class="form-check-label" for="genre-<?php echo $genre['id']; ?>">
                                        <?php echo htmlspecialchars($genre['name']); ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-red">Save Preferences</button>
                </form>
            </div>
        </div>
    </div>
</div>
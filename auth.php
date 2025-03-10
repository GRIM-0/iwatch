<?php
function signIn($conn) {
    if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_POST["signInSubmit"])) {
        return "Invalid request.";
    }

    $username = trim($_POST["username"] ?? '');
    $password = trim($_POST["password"] ?? '');

    if (empty($username) || empty($password)) {
        return "Username and password are required.";
    }

    // Fetch user from database using mysqli
    $stmt = mysqli_prepare($conn, "SELECT id, username, password FROM users WHERE username = ?");
    if (!$stmt) {
        error_log("Database error: " . mysqli_error($conn));
        return "Database error: " . mysqli_error($conn);
    }
    mysqli_stmt_bind_param($stmt, "s", $username);
    if (!mysqli_stmt_execute($stmt)) {
        error_log("Query execution error: " . mysqli_stmt_error($stmt));
        return "Query execution error.";
    }
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION["logged_in"] = true;
        $_SESSION["username"] = $user['username'];
        $_SESSION["user_id"] = $user['id']; // Add user_id to session
        error_log("Sign-in successful for user: $username");
        return false;
    } else {
        error_log("Invalid credentials for user: $username");
        return "Invalid username or password.";
    }
}
?>
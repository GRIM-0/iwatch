<?php
function signIn($conn, $redirect = null) {
    $signInError = "";
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["signInSubmit"])) {
        $username = trim($_POST["username"] ?? '');
        $password = trim($_POST["password"] ?? '');

        if (empty($username) || empty($password)) {
            $signInError = "Please fill in all fields.";
        } else {
            error_log("Sign-in attempt for username: '$username'");
            $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE LOWER(username) = LOWER(?)");
            if ($stmt === false) {
                $signInError = "Database preparation failed: " . $conn->error;
                error_log("Database error: " . $conn->error);
            } else {
                $stmt->bind_param("s", $username);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows == 1) {
                    $user = $result->fetch_assoc();
                    if (password_verify($password, $user["password"])) {
                        $_SESSION["user_id"] = $user["id"];
                        $_SESSION["username"] = $user["username"];
                        $_SESSION["logged_in"] = true;
                        error_log("Sign-in successful for user: " . $user["username"]);
                        header("Location: " . ($redirect ?: basename($_SERVER['PHP_SELF'])));
                        exit;
                    } else {
                        $signInError = "Invalid password.";
                        error_log("Invalid password for username: '$username'");
                    }
                } else {
                    $signInError = "User not found.";
                    error_log("No user found for username: '$username'");
                }
                $stmt->close();
            }
        }
    }
    return $signInError;
}
?>
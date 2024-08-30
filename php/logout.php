<?php
session_start();

// Destroy the session data
session_unset();
session_destroy();

// Optionally, remove any session cookies
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Respond with a success status
http_response_code(200);
?>

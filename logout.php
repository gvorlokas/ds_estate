<?php
// Start or resume a session
session_start();
// Unset all session variables to log out the user
session_unset();
// Destroy the session to completely clear session data
session_destroy();
// Redirect the user to the login page after logout
header("Location: login.php");
exit();// Ensure no further script execution after redirect
?>

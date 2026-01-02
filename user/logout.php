<?php
require_once '../includes/session.php';

// Destroy session and logout
destroySession();

// Set flash message
setFlashMessage("You have been logged out successfully.", "success");

// Redirect to homepage
redirect('/index.php');
?>

<?php
require_once '../includes/session.php';
destroySession();
setFlashMessage("You have been logged out successfully.", "success");
redirect('/admin/login.php');
?>

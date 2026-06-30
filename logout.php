<?php
session_start();
session_unset();
session_destroy();
// Optionally delete cookie too
setcookie('last_user', '', time() - 3600);
header("Location: login.php?msg=You have been logged out.");
exit();
?>
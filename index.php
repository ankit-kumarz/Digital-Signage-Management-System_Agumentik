<?php
require_once 'includes/functions.php';
if (isLoggedIn()) {
    redirectToDashboard();
} else {
    header('Location:signup.php');
    exit();
}
?>

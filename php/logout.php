<?php
session_start();

$_SESSION = array();


if (session_id() !== '' || isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 42000, '/'); 
    // Delete the session cookie kasi putanginang bka may alam mag cookie hijack
}
session_destroy();

header('Location: ../auth.php');
exit;

//kung makita mo man to sir haha :,) ako lng gumawa neto lahat
?>
<?php
session_start();

// kolla om inloggad
if (empty($_SESSION['user'])) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header("Location: ../redigera/login.php");
    exit;
}

// tvinga utloggning om tiden gått ut
$timeout = 1; // antal sekunder
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
    session_unset();
    session_destroy();
    header("Location: ../redigera/redigera.html");
    exit;
}
$_SESSION['last_activity'] = time();
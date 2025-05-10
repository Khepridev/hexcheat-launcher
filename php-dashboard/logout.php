<?php
session_start();

// Oturumu temizle
$_SESSION = array();

// Çerezleri temizle
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Oturumu kaldır
session_destroy();

// Login sayfasına yönlendir
header('Location: login.php');
exit; 
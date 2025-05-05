<?php
require_once __DIR__ . '/config.php';

function isLoggedIn() {
    return !empty($_SESSION['user_id']);
}

function isAdmin() {
    return !empty($_SESSION['is_admin']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        header('HTTP/1.1 403 Forbidden');
        exit('Admins only');
    }
}

function flash($msg) {
    $_SESSION['flash'] = $msg;
}

function setFlash($key, $message) {
    $_SESSION[$key] = $message;
}

function getFlash($key) {
    if (!empty($_SESSION[$key])) {
        $message = $_SESSION[$key];
        unset($_SESSION[$key]);
        return $message;
    }
    return '';
}
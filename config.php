<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'u69120');
define('DB_USER', 'u69120');
define('DB_PASS', '7228987');
define('COOKIE_VALUES', 'form_data');
define('COOKIE_ERRORS', 'form_errors');
define('COOKIE_LIFETIME', time() + 31536000);

try {
    $db = new PDO(
        "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4", 
        DB_USER, 
        DB_PASS, 
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}

function setFormData($data) {
    setcookie(COOKIE_VALUES, json_encode($data), COOKIE_LIFETIME, '/');
}

function getFormData() {
    return isset($_COOKIE[COOKIE_VALUES]) ? json_decode($_COOKIE[COOKIE_VALUES], true) : [];
}

function setFormErrors($errors) {
    setcookie(COOKIE_ERRORS, json_encode($errors), 0, '/');
}

function getFormErrors() {
    return isset($_COOKIE[COOKIE_ERRORS]) ? json_decode($_COOKIE[COOKIE_ERRORS], true) : [];
}

function clearFormCookies() {
    //setcookie(COOKIE_VALUES, '', time() - 3600, '/');
    setcookie(COOKIE_ERRORS, '', time() - 3600, '/');
}
?>
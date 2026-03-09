<?php
// Настройки подключения к базе данных
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_NAME', 'amybradley');
define('DB_CHARSET', 'utf8mb4');

// Создание подключения
function getDBConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        
        return new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (PDOException $e) {
        die("Ошибка подключения к базе данных: " . $e->getMessage());
    }
}

// Функция для хеширования паролей
function hashPassword($password) {  // ← Исправлено: добавлен пробел и имя "hash"
    return password_hash($password, PASSWORD_DEFAULT);
}

// Функция для проверки пароля
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Функция для проверки авторизации
function checkAuth() {
    session_start();
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Функция для получения данных текущего пользователя
function getCurrentUser() {
    if (!checkAuth()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'role' => $_SESSION['role']
    ];
}

// Функция для проверки прав модератора/админа
function isAdmin() {
    $user = getCurrentUser();
    return $user && ($user['role'] === 'admin' || $user['role'] === 'moderator');
}

// Функция для проверки прав админа
function isSuperAdmin() {
    $user = getCurrentUser();
    return $user && $user['role'] === 'admin';
}
?>
<?php
require_once '../config.php'; // ← ИСПРАВЛЕНО: добавлен ../

header('Content-Type: application/json');

session_start();

// Проверяем, существует ли сессия
if (isset($_SESSION['user_id']) && isset($_SESSION['username']) && isset($_SESSION['role'])) {
    // Проверяем, не заблокирован ли пользователь
    $db = getDBConnection();
    $stmt = $db->prepare("SELECT id, username, role, is_blocked FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    if ($user && !$user['is_blocked']) {
        echo json_encode([
            'success' => true,
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'role' => trim(strtolower($user['role'])) // ← ИСПРАВЛЕНО: очистка роли
            ]
        ]);
    } else {
        // Пользователь заблокирован или не найден - уничтожаем сессию
        session_destroy();
        echo json_encode(['success' => false]);
    }
} else {
    echo json_encode(['success' => false]);
}
?>
<?php
require_once '../../config.php'; //  ИСПРАВЛЕНО: правильный путь

header('Content-Type: application/json');

// Запускаем сессию
session_start();

// Проверка авторизации
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    echo json_encode(['success' => false, 'message' => 'Требуется авторизация']);
    exit;
}

// Проверка прав
$allowedRoles = ['admin', 'moderator', 'mod'];
$sessionRole = strtolower(trim($_SESSION['role'] ?? ''));

$hasPermission = false;
foreach ($allowedRoles as $role) {
    if (strpos($sessionRole, $role) !== false) {
        $hasPermission = true;
        break;
    }
}

if (!$hasPermission) {
    // ← ИСПРАВЛЕНО: правильный синтаксис implode
    echo json_encode([
        'success' => false, 
        'message' => "Недостаточно прав. Ваша роль: '{$_SESSION['role']}', очищенная: '{$sessionRole}'. Разрешено: " . implode(', ', $allowedRoles)
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Неверный метод запроса']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$messageId = intval($data['message_id'] ?? 0);

if ($messageId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Неверный ID сообщения']);
    exit;
}

try {
    $db = getDBConnection();
    
    // Проверяем существование сообщения
    $stmt = $db->prepare("SELECT id FROM forum_messages WHERE id = ?");
    $stmt->execute([$messageId]);
    
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Сообщение не найдено']);
        exit;
    }
    
    // Удаляем сообщение
    $stmt = $db->prepare("DELETE FROM forum_messages WHERE id = ?");
    $stmt->execute([$messageId]);
    
    echo json_encode(['success' => true, 'message' => 'Сообщение успешно удалено']);
} catch (Exception $e) {
    error_log("Ошибка удаления сообщения: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Ошибка базы данных']);
}
?>
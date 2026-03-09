<?php
require_once '../config.php';

header('Content-Type: application/json');

// Проверка авторизации
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    echo json_encode(['success' => false, 'message' => 'Требуется авторизация']);
    exit;
}

// Проверка прав
if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'moderator') {
    echo json_encode(['success' => false, 'message' => 'Недостаточно прав']);
    exit;
}

try {
    $db = getDBConnection();
    $stmt = $db->prepare("SELECT * FROM theories WHERE status = 'pending' ORDER BY date DESC");
    $stmt->execute();
    $theories = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'theories' => $theories
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Ошибка: ' . $e->getMessage()]);
}
?>
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $theoryId = $data['id'] ?? '';
    $action = $data['action'] ?? ''; // 'approve', 'reject', 'delete'
    
    if (empty($theoryId) || empty($action)) {
        echo json_encode(['success' => false, 'message' => 'Неверные данные']);
        exit;
    }
    
    try {
        $db = getDBConnection();
        $stmt = $db->prepare("SELECT * FROM theories WHERE id = ?");
        $stmt->execute([$theoryId]);
        $theory = $stmt->fetch();
        
        if (!$theory) {
            echo json_encode(['success' => false, 'message' => 'Теория не найдена']);
            exit;
        }
        
        if ($action === 'approve') {
            $stmt = $db->prepare("UPDATE theories SET status = 'approved', moderated_at = NOW() WHERE id = ?");
            $stmt->execute([$theoryId]);
        } elseif ($action === 'reject') {
            $stmt = $db->prepare("UPDATE theories SET status = 'rejected', moderated_at = NOW() WHERE id = ?");
            $stmt->execute([$theoryId]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Неверное действие']);
            exit;
        }
        
        echo json_encode(['success' => true, 'message' => 'Действие выполнено успешно']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Ошибка: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Неверный метод запроса']);
}
?>
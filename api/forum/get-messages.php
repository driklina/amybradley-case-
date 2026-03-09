<?php
require_once '../../config.php';

header('Content-Type: application/json');

if (!checkAuth()) {
    echo json_encode(['success' => false, 'message' => 'Требуется авторизация']);
    exit;
}

$topicId = intval($_GET['topic_id'] ?? 0);
if ($topicId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Неверный ID темы']);
    exit;
}

try {
    $db = getDBConnection();
    
    $stmt = $db->prepare("
        SELECT fm.*, u.username AS author 
        FROM forum_messages fm
        JOIN users u ON fm.author_id = u.id
        WHERE fm.topic_id = ?
        ORDER BY fm.created_at ASC
    ");
    $stmt->execute([$topicId]);
    $messages = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'messages' => $messages
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Ошибка: ' . $e->getMessage()]);
}
?>
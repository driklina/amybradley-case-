<?php
require_once '../../config.php';

header('Content-Type: application/json');

if (!checkAuth()) {
    echo json_encode(['success' => false, 'message' => 'Требуется авторизация']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Неверный метод запроса']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$topicId = intval($data['topic_id'] ?? 0);
$messageText = trim($data['message'] ?? '');

if (empty($messageText) || $topicId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Заполните все поля']);
    exit;
}

try {
    $db = getDBConnection();
    $user = getCurrentUser();
    
    // Проверка существования темы
    $stmt = $db->prepare("SELECT id FROM forum_topics WHERE id = ?");
    $stmt->execute([$topicId]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Тема не найдена']);
        exit;
    }
    
    $stmt = $db->prepare("INSERT INTO forum_messages (topic_id, author_id, message_text) VALUES (?, ?, ?)");
    $stmt->execute([$topicId, $user['id'], $messageText]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Сообщение отправлено',
        'message_id' => $db->lastInsertId(),
        'author' => $user['username'],
        'date' => date('d.m.Y H:i')
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Ошибка: ' . $e->getMessage()]);
}
?>
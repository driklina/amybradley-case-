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
$title = trim($data['title'] ?? '');
$description = trim($data['description'] ?? '');

if (empty($title)) {
    echo json_encode(['success' => false, 'message' => 'Укажите название темы']);
    exit;
}

try {
    $db = getDBConnection();
    $user = getCurrentUser();
    
    $stmt = $db->prepare("INSERT INTO forum_topics (title, description, author_id) VALUES (?, ?, ?)");
    $stmt->execute([$title, $description, $user['id']]);
    
    $topicId = $db->lastInsertId();
    
    echo json_encode([
        'success' => true,
        'message' => 'Тема создана',
        'topic_id' => $topicId
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Ошибка БД: ' . $e->getMessage()]);
}
?>
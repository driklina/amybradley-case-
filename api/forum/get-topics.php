<?php
require_once '../../config.php';
header('Content-Type: application/json');

try {
    $db = getDBConnection();
    
    // Получаем все темы с количеством сообщений
    $stmt = $db->query("
        SELECT ft.*, 
               (SELECT COUNT(*) FROM forum_messages fm WHERE fm.topic_id = ft.id) as message_count
        FROM forum_topics ft
        ORDER BY ft.created_at DESC
    ");
    
    $topics = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'topics' => $topics
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Ошибка БД: ' . $e->getMessage()
    ]);
}
?>
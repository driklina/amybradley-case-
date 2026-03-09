<?php
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $description = trim($data['description'] ?? '');
    $email = trim($data['email'] ?? '');
    
    if (empty($description) || empty($email)) {
        echo json_encode(['success' => false, 'message' => 'Заполните все поля']);
        exit;
    }
    
    try {
        $db = getDBConnection();
        $stmt = $db->prepare("INSERT INTO theories (description, email, status) VALUES (?, ?, 'pending')");
        $stmt->execute([$description, $email]);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Теория отправлена на модерацию',
            'data' => ['description' => $description, 'email' => $email]
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Ошибка: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Неверный метод запроса']);
}
?>
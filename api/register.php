<?php
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $username = trim($data['username'] ?? '');
    $email = trim($data['email'] ?? '');
    $password = $data['password'] ?? '';
    
    if (empty($username) || empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Заполните все поля']);
        exit;
    }
    
    if (strlen($username) < 3) {
        echo json_encode(['success' => false, 'message' => 'Имя пользователя должно содержать не менее 3 символов']);
        exit;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Неверный формат email']);
        exit;
    }
    
    if (strlen($password) < 6) {
        echo json_encode(['success' => false, 'message' => 'Пароль должен содержать не менее 6 символов']);
        exit;
    }
    
    try {
        $db = getDBConnection();
        
        // Проверка существования пользователя
        $stmt = $db->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Пользователь с такими данными уже существует']);
            exit;
        }
        
        // Создание нового пользователя
        $hashedPassword = hashPassword($password);
        $stmt = $db->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'user')");
        
        if ($stmt->execute([$username, $email, $hashedPassword])) {
            echo json_encode([
                'success' => true,
                'message' => 'Регистрация успешна! Теперь вы можете войти.'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Ошибка при регистрации']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Ошибка: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Неверный метод запроса']);
}
?>
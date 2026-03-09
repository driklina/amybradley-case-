<?php
require_once 'config.php';

// Уничтожаем сессию
session_start();
session_destroy();

// Возвращаем успех
header('Content-Type: application/json');
echo json_encode(['success' => true]);
?>
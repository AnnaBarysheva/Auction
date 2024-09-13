<?php
session_start();
session_unset(); // Удаляем все переменные сессии
session_destroy(); // Уничтожаем сессию

echo json_encode(['success' => true]);
?>
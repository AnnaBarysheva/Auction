<?php
session_start();
session_unset(); // Удаляем все переменные сессии
session_destroy(); // Уничтожаем сессию

// // Функция для удаления всех cookies
// function deleteAllCookies() {
//     // Получаем все куки
//     foreach ($_COOKIE as $cookie_name => $cookie_value) {
//         // Устанавливаем срок действия куки в прошлое
//         setcookie($cookie_name, "", time() - 3600, "/");
//     }
// }

// // Очищаем все куки
// deleteAllCookies();

// Отправляем ответ на запрос
echo json_encode(['success' => true]);
?>

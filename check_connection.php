

<?php
header('Content-Type: application/json');


// // Настройки подключения к базе данных
// $host = 'localhost';  // Имя хоста (обычно localhost)
// $username = 'root';  // Имя пользователя MySQL
// $password = 'alina';  // Пароль пользователя MySQL
// $database = 'Auction';  // Имя базы данных

// Настройки подключения к базе данных
$host = 'localhost';  // Имя хоста (обычно localhost)
$username = 'root';  // Имя пользователя MySQL
$password = 'root_Passwrd132';  // Пароль пользователя MySQL
$database = 'Auction';  // Имя базы данных



// Попытка подключения к базе данных
$connection = new mysqli($host, $username, $password, $database);

// Проверяем соединение
if ($connection->connect_error) {
    // Соединение не удалось
    echo json_encode([
        'success' => false,
        'message' => 'Ошибка подключения к базе данных: ' . $connection->connect_error
    ]);
    exit();
}

// Соединение успешно
echo json_encode([
    'success' => true,
    'message' => 'Соединение с базой данных установлено успешно'
]);

// Закрываем соединение
$connection->close();
?>

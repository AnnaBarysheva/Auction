

<?php
header('Content-Type: application/json');


// // Настройки подключения к базе данных
// $host = 'localhost';  // Имя хоста (обычно localhost)
// $username = 'root';  // Имя пользователя MySQL
// $password = 'alina';  // Пароль пользователя MySQL
// $database = 'Auction';  // Имя базы данных

// // Настройки подключения к базе данных
// $host = 'localhost';  // Имя хоста (обычно localhost)
// $username = 'root';  // Имя пользователя MySQL
// $password = 'root_Passwrd132';  // Пароль пользователя MySQL
// $database = 'Auction';  // Имя базы данных

$connection = include 'db_connect.php';  // здесь мы подключаем db_connect.php

// try {
//     // Подключение к базе данных
//     $connection = new mysqli($host, $username, $password, $database);

//     // Проверяем подключение
//     if ($connection->connect_error) {
//         throw new Exception('Ошибка подключения к базе данных: ' . $connection->connect_error);
//     }

//     // Соединение успешно
//     echo json_encode([
//         'success' => true,
//         'message' => 'Соединение с базой данных установлено успешно.'
//     ]);
// } catch (Exception $e) {
//     // Если произошла ошибка подключения
//     echo json_encode([
//         'success' => false,
//         'message' => 'Ошибка подключения: ' . $e->getMessage()
//     ]);
// } finally {
//     // Закрываем соединение
//     if (isset($connection) && $connection->ping()) {
//         $connection->close();
//     }
// }




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
// // Соединение успешно
 echo json_encode([
     'success' => true,
     'message' => 'Соединение с базой данных установлено успешно'
 ]);

 // Закрываем соединение
 $connection->close();

 
 ?>

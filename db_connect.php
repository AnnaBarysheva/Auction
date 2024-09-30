<?php
// db_connect.php


// // Настройки подключения к базе данных
// $host = 'localhost';  // Имя хоста (обычно localhost)
// $username = 'root';  // Имя пользователя MySQL
// $password = 'alina';  // Пароль пользователя MySQL
// $database = 'Auction';  // Имя базы данных

// Настройки подключения к базе данных
$host = 'localhost';
$username = 'root';
$password = 'root_Passwrd132';
$database = 'Auction';

// Попытка подключения к базе данных
$connection = new mysqli($host, $username, $password, $database);

// Проверяем соединение
if ($connection->connect_error) {
    die('Ошибка подключения к базе данных: ' . $connection->connect_error);
}

// Возвращаем успешное подключение
return $connection;
?>

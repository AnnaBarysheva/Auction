<?php
session_start(); // Инициализация сессии

header('Content-Type: application/json'); // Указываем, что возвращаем JSON

// Подключение к базе данных
//  $link = mysqli_connect("localhost", "root", "alina", "Auction");
$link = mysqli_connect("localhost", "root", "root_Passwrd132", "Auction");

// Проверка соединения
if ($link === false) {
    echo json_encode(['success' => false, 'message' => "Ошибка подключения к базе данных."]);
    exit();
}

// Получение данных из формы
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = mysqli_real_escape_string($link, $_POST['name']);
    $login = mysqli_real_escape_string($link, $_POST['username']);
    $password = mysqli_real_escape_string($link, $_POST['password']);
    $confirm_password = mysqli_real_escape_string($link, $_POST['confirm_password']);

    // Проверка совпадения паролей
    if ($password !== $confirm_password) {
        echo json_encode(['success' => false, 'message' => "Пароли не совпадают."]);
        exit();
    }

    // Проверка уникальности логина
    $checkLoginQuery = "SELECT * FROM Users WHERE login = '$login'";
    $result = mysqli_query($link, $checkLoginQuery);

    if (mysqli_num_rows($result) > 0) {
        echo json_encode(['success' => false, 'message' => "Логин уже занят. Попробуйте другой."]);
        exit();
    }

    // Хеширование пароля
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // SQL-запрос на вставку
    $sql = "INSERT INTO Users (full_name, login, password) VALUES ('$full_name', '$login', '$hashed_password')";

    // Выполнение запроса и проверка результата
    if (mysqli_query($link, $sql)) {
        // Получение ID нового пользователя
        $user_id = mysqli_insert_id($link);
        $_SESSION['user_id'] = $user_id; // Сохранение ID пользователя в сессии

        echo json_encode(['success' => true, 'message' => "Регистрация прошла успешно."]);
    } else {
        echo json_encode(['success' => false, 'message' => "Ошибка: " . mysqli_error($link)]);
    }
}

// Закрытие соединения
mysqli_close($link);
?>
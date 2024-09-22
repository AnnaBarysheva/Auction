<?php
session_start();
header('Content-Type: application/json');

//   $link = mysqli_connect("localhost", "root", "alina", "Auction");
// $link = mysqli_connect("localhost", "root", "root_Passwrd132", "Auction");
$link = include 'db_connect.php';

if ($link === false) {
    echo json_encode(['success' => false, 'message' => "Ошибка подключения к базе данных."]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login = mysqli_real_escape_string($link, $_POST['username']);
    $password = mysqli_real_escape_string($link, $_POST['password']);

    $query = "SELECT * FROM Users WHERE login = '$login'";
    $result = mysqli_query($link, $query);

    if (mysqli_num_rows($result) === 0) {
        echo json_encode(['success' => false, 'message' => "Логин неверный. Проверьте на ошибки или зарегистрируйтесь."]);
        exit();
    }

    $user = mysqli_fetch_assoc($result);

    // Проверка пароля
    if (!password_verify($password, $user['password'])) {
        echo json_encode(['success' => false, 'message' => "Пароль неверный. Попробуйте снова."]);
        exit();
    }
    
    // Сохранение ID пользователя в сессии
    $_SESSION['user_id'] = $user['id_user'];

    // Перенаправление
    $redirect_url = isset($_GET['redirect']) ? $_GET['redirect'] : 'index.php';
    echo json_encode(['success' => true, 'redirect' => $redirect_url]);
    exit();
}

mysqli_close($link);
?>
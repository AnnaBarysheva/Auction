<?php
session_start();
header('Content-Type: application/json');

// Подключение к базе данных
$link = include 'db_connect.php';

if ($link === false) {
    echo json_encode(['success' => false, 'message' => "Ошибка подключения к базе данных."]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $id_user = $_SESSION['user_id'];
    $id_painting = intval($_POST['id_painting']);
    $checked = filter_var($_POST['checked'], FILTER_VALIDATE_BOOLEAN);

    if ($checked) {
        // Если чекбокс отмечен, добавляем запись в таблицу
        $query = "INSERT INTO PaintingUser (id_user, id_painting) VALUES (?, ?) ON DUPLICATE KEY UPDATE id_user = id_user";
        $stmt = mysqli_prepare($link, $query);
        mysqli_stmt_bind_param($stmt, 'ii', $id_user, $id_painting);
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Ошибка при добавлении заявки: ' . mysqli_error($link)]);
        }
    } else {
        // Если чекбокс снят, удаляем запись из таблицы
        $query = "DELETE FROM PaintingUser WHERE id_user = ? AND id_painting = ?";
        $stmt = mysqli_prepare($link, $query);
        mysqli_stmt_bind_param($stmt, 'ii', $id_user, $id_painting);
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Ошибка при удалении заявки: ' . mysqli_error($link)]);
        }
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Неверный запрос или пользователь не авторизован.']);
}

mysqli_close($link);
?>

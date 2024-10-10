<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Подключение к базе данных
$link = include 'db_connect.php';

if ($link == false) {
    die(json_encode(['success' => false, 'message' => "Ошибка: Невозможно подключиться к базе данных."]));
}

// Проверяем, что данные отправлены через POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Получаем данные из запроса
    $data = json_decode(file_get_contents('php://input'), true);

    // Отладочное сообщение для проверки полученных данных
    error_log("Полученные данные: " . print_r($data, true));

    // Проверяем, был ли передан id_style
    if (isset($data['id_style'])) {
        $id_style = $data['id_style'];

        // Удаляем стиль из базы данных
        $sql = "DELETE FROM Styles WHERE id_style = '$id_style'";
        
        // Отладочное сообщение перед выполнением запроса
        error_log("SQL-запрос: " . $sql);
        
        if (mysqli_query($link, $sql)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => "Ошибка выполнения запроса: " . mysqli_error($link)]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => "Ошибка: Необходимый параметр id_style отсутствует."]);
    }
} else {
    echo json_encode(['success' => false, 'message' => "Ошибка: Неверный метод запроса."]);
}

// Закрываем соединение
mysqli_close($link);
?>

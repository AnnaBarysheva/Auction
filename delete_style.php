<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Подключение к базе данных
$link = include 'db_connect.php';

if ($link == false) {
    echo json_encode(['success' => false, 'message' => "Ошибка: Невозможно подключиться к базе данных."]);
    exit();  // Завершаем выполнение скрипта после отправки ответа
}

// Проверяем, что данные отправлены через POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Получаем данные из запроса
    $data = json_decode(file_get_contents('php://input'), true);

    // Проверяем, был ли передан id_style
    if (isset($data['id_style'])) {
        $id_style = $data['id_style'];

        // 1. Обновляем картины, которые используют удаляемый стиль
        $updatePaintingsSql = "UPDATE Paintings SET id_style = NULL WHERE id_style = '$id_style'";
        
        if (mysqli_query($link, $updatePaintingsSql)) {
            // 2. Удаляем стиль из базы данных
            $deleteStyleSql = "DELETE FROM Styles WHERE id_style = '$id_style'";
            
            if (mysqli_query($link, $deleteStyleSql)) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => "Ошибка выполнения запроса: " . mysqli_error($link)]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => "Ошибка обновления картин: " . mysqli_error($link)]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => "Ошибка: Необходимый параметр id_style отсутствует."]);
    }
} else {
    echo json_encode(['success' => false, 'message' => "Ошибка: Неверный метод запроса."]);
}

// Закрываем соединение
mysqli_close($link);
exit();  // Завершаем выполнение скрипта после отправки ответа
?>

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

    // Проверяем, был ли передан id_material
    if (isset($data['id_material'])) {
        $id_material = $data['id_material'];

        // 1. Обновляем картины, которые используют удаляемый материал
        $updatePaintingsSql = "UPDATE Paintings SET id_material = NULL WHERE id_material = '$id_material'";
        
        if (mysqli_query($link, $updatePaintingsSql)) {
            // 2. Удаляем материал из базы данных
            $deleteMaterialSql = "DELETE FROM Materials WHERE id_material = '$id_material'";
            
            if (mysqli_query($link, $deleteMaterialSql)) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => "Ошибка выполнения запроса: " . mysqli_error($link)]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => "Ошибка обновления картин: " . mysqli_error($link)]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => "Ошибка: Необходимый параметр id_material отсутствует."]);
    }
} else {
    echo json_encode(['success' => false, 'message' => "Ошибка: Неверный метод запроса."]);
}

// Закрываем соединение
mysqli_close($link);
exit();  // Завершаем выполнение скрипта после отправки ответа
?>

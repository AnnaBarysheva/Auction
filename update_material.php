<?php
// Подключение к базе данных
$link = include 'db_connect.php';

if ($link === false) {
    die("Ошибка: Невозможно подключиться к базе данных " . mysqli_connect_error());
}

// Проверяем, что данные отправлены через POST и содержат необходимые поля
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_material']) && isset($_POST['material_name'])) {
    $id_material = $_POST['id_material'];
    $material_name = trim($_POST['material_name']);

    // Проверяем, что название материала не пустое
    if (!empty($material_name)) {
        // Проверка, существует ли материал с таким названием (кроме текущего)
        $stmt_check = $link->prepare("SELECT COUNT(*) FROM Materials WHERE material_name = ? AND id_material != ?");
        $stmt_check->bind_param("si", $material_name, $id_material);
        
        // Выполнение запроса
        $stmt_check->execute();
        $stmt_check->bind_result($count);
        $stmt_check->fetch();
        $stmt_check->close();

        // Проверка, существует ли другой материал с таким же названием
        if ($count > 0) {
            echo "Ошибка: Материал с таким названием уже существует.";
        } else {
            // Обновляем название материала
            $sql = "UPDATE Materials SET material_name = ? WHERE id_material = ?";
            $stmt_update = $link->prepare($sql);
            $stmt_update->bind_param("si", $material_name, $id_material);

            if ($stmt_update->execute()) {
                echo "Материал успешно обновлён";
            } else {
                echo "Ошибка: " . $stmt_update->error;
            }

            // Закрытие подготовленного запроса
            $stmt_update->close();
        }
    } else {
        echo "Ошибка: Название материала не может быть пустым.";
    }
} else {
    echo "Ошибка: Неверные данные формы";
}

// Закрываем соединение
mysqli_close($link);
?>

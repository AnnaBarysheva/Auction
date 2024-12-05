<?php
// Подключаем файл с базой данных
$connection = include 'db_connect.php';

// Проверка подключения к базе данных
if ($connection === false) {
    die("Ошибка: Невозможно подключиться к базе данных.");
}

// Проверка метода запроса
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Получение названия материала из формы и удаление лишних пробелов
    $material_name = trim($_POST['material_name']);

    // Проверка, что поле не пустое
    if (!empty($material_name)) {
        // Подготовка SQL-запроса для проверки существования материала
        $stmt_check = $connection->prepare("SELECT COUNT(*) FROM Materials WHERE material_name = ?");
        $stmt_check->bind_param("s", $material_name);
        
        // Выполнение запроса
        $stmt_check->execute();
        $stmt_check->bind_result($count);
        $stmt_check->fetch();
        $stmt_check->close();

        // Проверка, существует ли материал
        if ($count > 0) {
            echo "Ошибка: Материал с таким названием уже существует.";
        } else {
            // Подготовка SQL-запроса для вставки нового материала
            $stmt_insert = $connection->prepare("INSERT INTO Materials (material_name) VALUES (?)");
            $stmt_insert->bind_param("s", $material_name);

            // Выполнение запроса
            if ($stmt_insert->execute()) {
                // Успешное добавление материала — делаем перенаправление на index.php
                header('Location: index.php'); 
                exit(); // Останавливаем выполнение после перенаправления
            } else {
                // Сообщение об ошибке
                echo "Ошибка при добавлении материала: " . $stmt_insert->error;
            }

            // Закрытие подготовленного запроса
            $stmt_insert->close();
        }
    } else {
        echo "Ошибка: Название материала не может быть пустым.";
    }
}

// Закрытие подключения к базе данных
$connection->close();
?>

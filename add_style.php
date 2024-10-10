<?php
// Подключаем файл с базой данных
$connection = include 'db_connect.php';

// Проверка подключения к базе данных
if ($connection === false) {
    die("Ошибка: Невозможно подключиться к базе данных.");
}

// Проверка метода запроса
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Получение названия стиля из формы и удаление лишних пробелов
    $style_name = trim($_POST['style_name']);

    // Проверка, что поле не пустое
    if (!empty($style_name)) {
        // Подготовка SQL-запроса
        $stmt = $connection->prepare("INSERT INTO Styles (style_name) VALUES (?)");
        
        // Привязываем параметр (строка)
        $stmt->bind_param("s", $style_name);

        // Выполнение запроса
        if ($stmt->execute()) {
            // Успешное добавление стиля — делаем перенаправление на index.php
            header('Location: index.php'); 
            exit(); // Останавливаем выполнение после перенаправления
        } else {
            // Сообщение об ошибке
            echo "Ошибка при добавлении стиля: " . $stmt->error;
        }

        // Закрытие подготовленного запроса
        $stmt->close();
    } else {
        echo "Ошибка: Название стиля не может быть пустым.";
    }
}

// Закрытие подключения к базе данных
$connection->close();
?>

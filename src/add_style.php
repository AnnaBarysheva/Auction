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
    // Подготовка SQL-запроса для проверки существования стиля
    $stmt_check = $connection->prepare("SELECT COUNT(*) FROM Styles WHERE style_name = ?");
    $stmt_check->bind_param("s", $style_name);
    
    // Выполнение запроса
    $stmt_check->execute();
    $stmt_check->bind_result($count);
    $stmt_check->fetch();
    $stmt_check->close();

    // Проверка, существует ли стиль
    if ($count > 0) {
        echo "Ошибка: Стиль с таким названием уже существует.";
    } else {
        // Подготовка SQL-запроса для вставки нового стиля
        $stmt_insert = $connection->prepare("INSERT INTO Styles (style_name) VALUES (?)");
        $stmt_insert->bind_param("s", $style_name);

        // Выполнение запроса
        if ($stmt_insert->execute()) {
            // Успешное добавление стиля — делаем перенаправление на index.php
            header('Location: index.php'); 
            exit(); // Останавливаем выполнение после перенаправления
        } else {
            // Сообщение об ошибке
            echo "Ошибка при добавлении стиля: " . $stmt_insert->error;
        }

        // Закрытие подготовленного запроса
        $stmt_insert->close();
    }
    } else {
        echo "Ошибка: Название стиля не может быть пустым.";
    }
}

// Закрытие подключения к базе данных
$connection->close();
?>

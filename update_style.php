<?php
// Подключение к базе данных
$link = include 'db_connect.php';

if ($link === false) {
    die("Ошибка: Невозможно подключиться к MySQL " . mysqli_connect_error());
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_style'])) {
    // Получаем данные из формы
    $id_style = $_POST['id_style'];
    $style_name = trim($_POST['style_name']);

    // Проверяем, что название стиля не пустое
    if (empty($style_name)) {
        echo "Ошибка: Название стиля не может быть пустым.";
        exit();
    }

    // Проверка, существует ли другой стиль с таким же названием
    $stmt_check = $link->prepare("SELECT COUNT(*) FROM Styles WHERE style_name = ? AND id_style != ?");
    $stmt_check->bind_param("si", $style_name, $id_style);
    
    // Выполнение запроса
    $stmt_check->execute();
    $stmt_check->bind_result($count);
    $stmt_check->fetch();
    $stmt_check->close();

    // Проверка, существует ли другой стиль с таким же названием
    if ($count > 0) {
        echo "Ошибка: Стиль с таким названием уже существует.";
        exit();
    }

    // Обновляем информацию о стиле
    $update_sql = "UPDATE Styles SET style_name = ? WHERE id_style = ?";
    $stmt_update = $link->prepare($update_sql);
    $stmt_update->bind_param("si", $style_name, $id_style);

    if ($stmt_update->execute()) {
        echo "Успех: Стиль успешно обновлен.";
    } else {
        echo "Ошибка обновления стиля: " . $stmt_update->error;
    }

    // Закрытие подготовленного запроса
    $stmt_update->close();
} else {
    echo "Ошибка: Неверный метод запроса или отсутствуют необходимые данные.";
}

// Закрываем подключение к базе данных
mysqli_close($link);
?>

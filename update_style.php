<?php
// Подключение к базе данных
$link = include 'db_connect.php';

if ($link == false) {
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

    // Обновляем информацию о стиле
    $update_sql = "UPDATE Styles SET style_name = '$style_name' WHERE id_style = '$id_style'";

    if (mysqli_query($link, $update_sql)) {
        echo "Успех: Стиль успешно обновлен.";
    } else {
        echo "Ошибка обновления стиля: " . mysqli_error($link);
    }
} else {
    echo "Ошибка: Неверный метод запроса или отсутствуют необходимые данные.";
}

// Закрываем подключение к базе данных
mysqli_close($link);
?>

<?php
// Подключение к базе данных
$link = include 'db_connect.php';

if ($link === false) {
    die("Ошибка: Невозможно подключиться к базе данных " . mysqli_connect_error());
}

// Проверяем, что данные отправлены через POST и содержат необходимые поля
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id']) && isset($_POST['weight'])) {
    $id = intval($_POST['id']); // Получаем ID характеристики
    $weight = intval(trim($_POST['weight'])); // Получаем вес и преобразуем в целое число

    // Проверяем, что вес больше или равен 1
    if ($weight >= 1) {
        // Обновляем вес характеристики
        $sql = "UPDATE SortingWeights SET weight = ? WHERE id = ?";
        $stmt_update = $link->prepare($sql);
        $stmt_update->bind_param("ii", $weight, $id); // Привязываем параметры

        if ($stmt_update->execute()) {
            echo "Вес успешно обновлён"; // Успешное обновление
        } else {
            echo "Ошибка: " . $stmt_update->error; // Ошибка выполнения запроса
        }

        // Закрытие подготовленного запроса
        $stmt_update->close();
    } else {
        echo "Ошибка: Вес должен быть целым положительным числом и не может быть меньше 1."; // Ошибка валидации
    }
} else {
    echo "Ошибка: Неверные данные формы"; // Ошибка валидации данных
}

// Закрываем соединение
mysqli_close($link);
?>

<?php
// Подключение к базе данных
$link = include 'db_connect.php';

if ($link == false) {
    die("Ошибка: Невозможно подключиться к базе данных " . mysqli_connect_error());
}

// Проверяем, что данные отправлены через POST и содержат необходимые поля
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_material']) && isset($_POST['material_name'])) {
    $id_material = $_POST['id_material'];
    $material_name = $_POST['material_name'];

    // Обновляем название материала
    $sql = "UPDATE Materials SET material_name = '$material_name' WHERE id_material = '$id_material'";
    
    if (mysqli_query($link, $sql)) {
        echo "Материал успешно обновлён";
    } else {
        echo "Ошибка: " . mysqli_error($link);
    }
} else {
    echo "Ошибка: Неверные данные формы";
}

// Закрываем соединение
mysqli_close($link);
?>

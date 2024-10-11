<?php
// Подключаемся к базе данных
// Подключаемся к базе данных
$link = include 'db_connect.php';

if ($link == false) {
    die("Ошибка: Невозможно подключиться к MySQL " . mysqli_connect_error());
}

// Проверяем, была ли форма отправлена
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_painting'])) {
    $id_painting = $_POST['id_painting'];
    $paint_name = $_POST['paint_name'];
    $id_style = $_POST['editStyle']; // Убедитесь, что это имя поля соответствует имени в форме
    $creation_year = $_POST['creation_year'];
    $author = $_POST['author'];
    $seller = $_POST['seller'];
    $id_material = $_POST['editMaterial']; // Если добавлено поле материала, убедитесь, что его идентификатор правильный

    // Отладочные сообщения для проверки принимаемых значений
    error_log("Updating painting ID: $id_painting");
    error_log("New values: Name: $paint_name, Style ID: $id_style, Material ID: $id_material, Year: $creation_year, Author: $author, Seller: $seller");

    // Находим текущий id_seller по id_painting
    $seller_id_sql = "SELECT id_seller FROM Paintings WHERE id_painting = '$id_painting'";
    $seller_id_result = mysqli_query($link, $seller_id_sql);

    if (mysqli_num_rows($seller_id_result) > 0) {
        // Получаем id_seller
        $seller_row = mysqli_fetch_assoc($seller_id_result);
        $id_seller = $seller_row['id_seller'];

        // Обновляем имя продавца
        $update_seller_sql = "UPDATE Sellers SET full_name='$seller' WHERE id_seller='$id_seller'";
        mysqli_query($link, $update_seller_sql);
    } else {
        echo "Ошибка: Продавец не найден.";
        exit();
    }

    // Обновляем информацию о картине
    $update_sql = "UPDATE Paintings 
    SET paint_name='$paint_name', 
        id_style='$id_style', 
        id_material='$id_material', 
        creation_year='$creation_year', 
        author='$author' 
    WHERE id_painting='$id_painting'";

    if (mysqli_query($link, $update_sql)) {
        header('Location: index.php'); 
        exit();
    } else {
        error_log("Ошибка обновления картины: " . mysqli_error($link));
        echo "Ошибка обновления картины: " . mysqli_error($link);
    }
}

// Закрываем соединение
mysqli_close($link);

?>

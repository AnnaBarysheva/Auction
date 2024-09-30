<?php
    // $link = mysqli_connect("localhost", "root", "alina", "Auction");
// $link = mysqli_connect("localhost", "root", "root_Passwrd132", "Auction");

$link = include 'db_connect.php';

if ($link == false) {
    die("Ошибка: Невозможно подключиться к MySQL " . mysqli_connect_error());
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_painting'])) {
    $id_painting = $_POST['id_painting'];
    $paint_name = $_POST['paint_name'];
    $style = $_POST['style'];
    $creation_year = $_POST['creation_year'];
    $author = $_POST['author'];
    $seller = $_POST['seller'];

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
     $update_sql = "UPDATE Paintings SET paint_name='$paint_name', style='$style', creation_year='$creation_year', author='$author' WHERE id_painting='$id_painting'";
     
     if (mysqli_query($link, $update_sql)) {
         header('Location: index.php'); 
         exit();
     } else {
         echo "Ошибка обновления картины: " . mysqli_error($link);
     }
 }

mysqli_close($link);
?>
<?php
// $link = mysqli_connect("localhost", "root", "alina", "Auction");
$link = mysqli_connect("localhost", "root", "root_Passwrd132", "Auction");

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

    $update_sql = "UPDATE Paintings SET paint_name='$paint_name', style='$style', creation_year='$creation_year', author='$author' WHERE id_painting='$id_painting'";
    
    if (mysqli_query($link, $update_sql)) {
        header('Location: index.php'); 
        exit();
    } else {
        echo "Ошибка: " . mysqli_error($link);
    }
}

mysqli_close($link);
?>
<?php
$link = mysqli_connect("localhost", "root", "alina", "Auction");

if ($link == false) {
    die("Ошибка: Невозможно подключиться к MySQL " . mysqli_connect_error());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['paint_name'];
    $size = $_POST['size'];
    $materials = $_POST['materials'];
    $style = $_POST['style'];
    $year = $_POST['creation_year'];
    $author = $_POST['author'];
    $imagePath = $_POST['image_path'];
    $seller = $_POST['seller'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $lotNumber = $_POST['lot_number'];
    $startingPrice = $_POST['starting_price'];
    $startDate = $_POST['start_date'];
    $endDate = $_POST['end_date'];

    // Сначала вставляем продавца и получаем его ID
    $insertSeller = "INSERT INTO Sellers (full_name, phone, email) VALUES ('$seller', '$phone', '$email')";
    if (mysqli_query($link, $insertSeller)) {
        $sellerId = mysqli_insert_id($link);

        // Вставляем картину
        $insertPainting = "INSERT INTO Paintings (paint_name, size, materials, style, creation_year, author, image_path, id_seller) VALUES ('$name', '$size', '$materials', '$style', '$year', '$author', '$imagePath', '$sellerId')";
        if (mysqli_query($link, $insertPainting)) {
            $paintingId = mysqli_insert_id($link);

            // Вставляем в PaintingsOnAuction
            $insertAuction = "INSERT INTO PaintingsOnAuction (id_painting, id_auction, lot_number, starting_price) VALUES ('$paintingId', '1', '$lotNumber', '$startingPrice')"; // Предполагаем, что ID аукциона - 1
            if (mysqli_query($link, $insertAuction)) {
                // Успешно добавлено, перенаправляем на главную страницу
                header('Location: index.php');
                exit();
            } else {
                echo "Ошибка при вставке в PaintingsOnAuction: " . mysqli_error($link);
            }
        } else {
            echo "Ошибка при вставке картины: " . mysqli_error($link);
        }
    } else {
        echo "Ошибка при вставке продавца: " . mysqli_error($link);
    }

    mysqli_close($link);
}
?>
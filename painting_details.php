<?php
// Проверка, передан ли параметр id_painting
if (isset($_GET['id_painting'])) {
    $id_painting = intval($_GET['id_painting']); // Преобразование в целое число для безопасности
} else {
    die("Ошибка: id_painting не передан.");
}

// Подключение к базе данных
// $link = mysqli_connect("localhost", "root", "alina", "Auction");
$link = mysqli_connect("localhost", "root", "root_Passwrd132", "Auction");


if ($link == false) {
    die("Ошибка: Невозможно подключиться к MySQL " . mysqli_connect_error());
}

// SQL-запрос для получения полной информации о картине
$sql = "
    SELECT Paintings.paint_name, Paintings.size, Paintings.materials, Paintings.style, 
           Paintings.creation_year, Paintings.author, 
           Sellers.full_name AS seller_name, Sellers.phone AS seller_phone, Sellers.email AS seller_email,
           Auctions.start_date, Auctions.end_date,
           PaintingsOnAuction.purchase_price
    FROM Paintings
    JOIN Sellers ON Paintings.id_seller = Sellers.id_seller
    LEFT JOIN PaintingsOnAuction ON Paintings.id_painting = PaintingsOnAuction.id_painting
    LEFT JOIN Auctions ON PaintingsOnAuction.id_auction = Auctions.id_auction
    WHERE Paintings.id_painting = $id_painting
";

$result = mysqli_query($link, $sql);

if ($result) {
    // Проверка, есть ли записи в результате запроса
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        
        // Выводим подробную информацию о картине
        echo "<h1>Информация о картине: " . $row['paint_name'] . "</h1>";
        echo "<p><strong>Размер:</strong> " . $row['size'] . "</p>";
        echo "<p><strong>Материалы:</strong> " . $row['materials'] . "</p>";
        echo "<p><strong>Стиль:</strong> " . $row['style'] . "</p>";
        echo "<p><strong>Год создания:</strong> " . $row['creation_year'] . "</p>";
        echo "<p><strong>Автор:</strong> " . $row['author'] . "</p>";

        echo "<h2>Информация о продавце</h2>";
        echo "<p><strong>Имя продавца:</strong> " . $row['seller_name'] . "</p>";
        echo "<p><strong>Телефон продавца:</strong> " . $row['seller_phone'] . "</p>";
        echo "<p><strong>Email продавца:</strong> " . $row['seller_email'] . "</p>";

        echo "<h2>Информация об аукционе</h2>";
        echo "<p><strong>Дата начала аукциона:</strong> " . $row['start_date'] . "</p>";
        echo "<p><strong>Дата окончания аукциона:</strong> " . $row['end_date'] . "</p>";
        
        if ($row['purchase_price']) {
            echo "<p><strong>Цена покупки:</strong> $" . $row['purchase_price'] . "</p>";
        } else {
            echo "<p>Картина ещё не продана.</p>";
        }
    } else {
        echo "Картина с таким id не найдена.";
    }

    // Освобождаем память, занятую результатом запроса
    mysqli_free_result($result);
} else {
    echo "Ошибка выполнения запроса: " . mysqli_error($link);
}

// Закрываем соединение
mysqli_close($link);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HueHaven - Art Auction</title>
    <link rel="stylesheet" href="style.css">

    <!-- Other head content like icon links or meta tags -->
</head>
<body>
    <header>
        <img src="https://cdn-icons-png.flaticon.com/512/10613/10613919.png" alt="Art Gallery Logo">
        <h1 style="color: white;font-style: italic;">HueHaven</h1>
    </header>


<?php
// Проверка, передан ли параметр id_painting
if (isset($_GET['id_painting'])) {
    $id_painting = intval($_GET['id_painting']); // Преобразование в целое число для безопасности
} else {
    die("Ошибка: id_painting не передан.");
}

// Подключение к базе данных
  $link = mysqli_connect("localhost", "root", "alina", "Auction");
//$link = mysqli_connect("localhost", "root", "root_Passwrd132", "Auction");


if ($link == false) {
    die("Ошибка: Невозможно подключиться к MySQL " . mysqli_connect_error());
}

// SQL-запрос для получения полной информации о картине
$sql = "
    SELECT Paintings.paint_name, Paintings.size, Paintings.materials, Paintings.style, 
           Paintings.creation_year, Paintings.author, Paintings.image_path, 
           Sellers.full_name AS seller_name, Sellers.phone AS seller_phone, Sellers.email AS seller_email,
           Auctions.start_date, Auctions.end_date,  PaintingsOnAuction.starting_price,
           PaintingsOnAuction.purchase_price, PaintingsOnAuction.lot_number 
    FROM Paintings
    JOIN Sellers ON Paintings.id_seller = Sellers.id_seller
    LEFT JOIN PaintingsOnAuction ON Paintings.id_painting = PaintingsOnAuction.id_painting
    LEFT JOIN Auctions ON PaintingsOnAuction.id_auction = Auctions.id_auction
    WHERE Paintings.id_painting = $id_painting
";

$result = mysqli_query($link, $sql);

if ($result && mysqli_num_rows($result) > 0) {
    $painting = mysqli_fetch_assoc($result); // Сохраняем данные картины в переменной
} else {
    die("Картина с таким id не найдена или ошибка запроса.");
}

// Закрываем соединение
mysqli_close($link);
?>



<div class="painting-info">
    <!-- Вывод изображения картины -->
    <img src="<?= htmlspecialchars($painting['image_path']) ?>" alt="<?= htmlspecialchars($painting['paint_name']) ?>" class="painting-img">

    <!-- Вывод информации о картине -->
    <div class="painting-details">
        <h1><?= htmlspecialchars($painting['paint_name']) ?></h1>
        <p style="color: #00CED1; font-style: italic;">Лот № <?= htmlspecialchars($painting['lot_number']) ?></p>  
        <p><strong>Размер:</strong> <?= htmlspecialchars($painting['size']) ?></p> 
        <p><strong>Материалы:</strong> <?= htmlspecialchars($painting['materials']) ?></p>
        <p><strong>Стиль:</strong> <?= htmlspecialchars($painting['style']) ?></p>
        <p><strong>Год создания:</strong> <?= htmlspecialchars($painting['creation_year']) ?></p>
        <p><strong>Автор:</strong> <?= htmlspecialchars($painting['author']) ?></p> 
        <div class="price-info">
            <div class="price-details">
                <p><strong>Начальная цена:</strong> <?= htmlspecialchars($painting['starting_price']) ?></p>
                <p><strong>Текущая цена:</strong> <?= htmlspecialchars($painting['purchase_price']) ?></p>
            </div>
            <!-- <form action="index.php" method="get">
                <input type="hidden" name="id_painting" value="<?= htmlspecialchars($painting['id_painting']) ?>"> -->
                <button type="submit" class="bid-button">Сделать ставку</button>
            <!-- </form> -->
        </div>
    </div>
</div>   
<div class="other-info">    
    <!-- Вывод информации о продавце -->
    <div class="seller-info">
        <!-- <h2>Информация о продавце</h2> -->
        <p><strong>Имя продавца:</strong> <?= htmlspecialchars($painting['seller_name']) ?></p>
        <p><strong>Телефон продавца:</strong> <?= htmlspecialchars($painting['seller_phone']) ?></p>
        <p><strong>Email продавца:</strong> <?= htmlspecialchars($painting['seller_email']) ?></p>
    </div>

    <!-- Вывод информации об аукционе -->
    <div class="auction-info">
        <!-- <h2>Информация об аукционе</h2> -->
        <p><strong>Дата начала аукциона:</strong> <?= htmlspecialchars($painting['start_date']) ?></p>
        <p><strong>Дата окончания аукциона:</strong> <?= htmlspecialchars($painting['end_date']) ?></p>

        <?php if ($painting['purchase_price']): ?>
            <p><strong>Цена покупки:</strong> $<?= htmlspecialchars($painting['purchase_price']) ?></p>
        <?php else: ?>
            <p>Картина ещё не продана.</p>
        <?php endif; ?>
    </div>
</div>
 
<!-- Модальное окно для предложения цены -->
<div id="priceProposalModal" class="modal">
    <div class="modal-content">
        <span class="close-button">&times;</span>
        <h2>Предложение своей цены к лоту №<?= htmlspecialchars($painting['lot_number']) ?></h2>
        <form id="priceProposalForm" action="submit_bid.php" method="post">
            <!-- <input type="hidden" name="id_painting" value="<?= htmlspecialchars($painting['id_painting']) ?>"> -->           
            <input type="number" name="bid_price" id="bid_price" placeholder="Введите вашу цену" required>
            <button type="submit" class="bid-button">Предложить цену</button>
        </form>
    </div>
</div>

</body>
</html>

<script>
// Получаем элементы модального окна и кнопки
var modal = document.getElementById("priceProposalModal");
var btn = document.querySelector(".bid-button");
var closeButton = document.querySelector(".close-button");

// Открытие модального окна при нажатии на кнопку "Сделать ставку"
btn.addEventListener("click", function(event) {
    event.preventDefault(); // Предотвращаем переход по ссылке
    modal.style.display = "block"; // Показываем модальное окно
});

// Закрытие модального окна при нажатии на кнопку закрытия
closeButton.addEventListener("click", function() {
    modal.style.display = "none"; // Скрываем модальное окно
});

// Закрытие модального окна при клике вне его
window.addEventListener("click", function(event) {
    if (event.target === modal) {
        modal.style.display = "none"; // Скрываем модальное окно
    }
});
</script>


<!-- if ($result) {
    // Проверка, есть ли записи в результате запроса
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        
        // Выводим подробную информацию о картине
        echo "<div class='painting-info'>";
        echo "<img src='" . $row['image_path'] . "' alt='" . $row['paint_name'] . "' class='painting-img'>";
        
        echo "<div class='painting-details'>";
        echo "<h1>" . $row['paint_name'] . "</h1>";
        echo "<p><strong>Размер:</strong> " . $row['size'] . "</p>";
        echo "<p><strong>Материалы:</strong> " . $row['materials'] . "</p>";
        echo "<p><strong>Стиль:</strong> " . $row['style'] . "</p>";
        echo "<p><strong>Год создания:</strong> " . $row['creation_year'] . "</p>";
        echo "<p><strong>Автор:</strong> " . $row['author'] . "</p>";
        echo "</div>";
        echo "</div>";

        echo "<div class='painting-details'>";
        echo "<div class='seller-info'>";
        echo "<h2>Информация о продавце</h2>";
        echo "<p><strong>Имя продавца:</strong> " . $row['seller_name'] . "</p>";
        echo "<p><strong>Телефон продавца:</strong> " . $row['seller_phone'] . "</p>";
        echo "<p><strong>Email продавца:</strong> " . $row['seller_email'] . "</p>";
        echo "</div>";

        echo "<div class='auction-info'>";
        echo "<h2>Информация об аукционе</h2>";
        echo "<p><strong>Дата начала аукциона:</strong> " . $row['start_date'] . "</p>";
        echo "<p><strong>Дата окончания аукциона:</strong> " . $row['end_date'] . "</p>";
        
        if ($row['purchase_price']) {
            echo "<p><strong>Цена покупки:</strong> $" . $row['purchase_price'] . "</p>";
        } else {
            echo "<p>Картина ещё не продана.</p>";
        }
        echo "</div>";

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
?> -->
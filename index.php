<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HueHaven - Art Auction</title>

    <!-- Add this line to connect your style.css file -->
    <link rel="stylesheet" href="style.css">

    <!-- Other head content like icon links or meta tags -->
</head>
<body>
    <header>
        <img src="https://cdn-icons-png.flaticon.com/512/10613/10613919.png" alt="Art Gallery Logo">
        <h1 style="color: white;font-style: italic;">HueHaven</h1>
    </header>


<?php
// $link = mysqli_connect("localhost", "root", "alina", "Auction");
$link = mysqli_connect("localhost", "root", "root_Passwrd132", "Auction");

if ($link == false) {
    die("Ошибка: Невозможно подключиться к MySQL " . mysqli_connect_error());
}

// SQL-запрос для получения картин, которые не проданы, с начальной ценой и именами продавцов
$sql = "
    SELECT Paintings.*, PaintingsOnAuction.starting_price, Sellers.full_name
    FROM Paintings
    JOIN PaintingsOnAuction ON Paintings.id_painting = PaintingsOnAuction.id_painting
    JOIN Sellers ON Paintings.id_seller = Sellers.id_seller
    WHERE Paintings.is_sold = FALSE
";

$result = mysqli_query($link, $sql);
?>

<?php
if ($result) {
    if (mysqli_num_rows($result) > 0) {
        echo "<div id='paintingsGrid'>";
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<div class='painting' data-id='" . $row['id_painting'] . "'>";
            echo "<img src='" . $row['image_path'] . "' alt='" . $row['paint_name'] . "'>";
            echo "<div class='paintInfo'>";
            echo "<p>" . $row['paint_name'] . "</p>";
            echo "<p>Автор: " . $row['author'] . "</p>";
            echo "<p>Цена: $" . number_format($row['starting_price'], 2) . "</p>";
            echo "<a href='bid_page.php?id_painting=" . $row['id_painting'] . "' class='bid-button'>Сделать ставку</a>";
            echo "</div>";
            echo "</div>";
        }
        echo "</div>";
    } else {
        echo "Нет доступных картин для отображения.";
    }
    mysqli_free_result($result);
} else {
    echo "Ошибка выполнения запроса: " . mysqli_error($link);
}

mysqli_close($link);
?>


<script>
// Добавляем обработчик события для каждой картины
document.addEventListener('DOMContentLoaded', function() {
    var paintings = document.querySelectorAll('.painting');
    
    paintings.forEach(function(painting) {
        painting.addEventListener('click', function() {
            var id_painting = this.getAttribute('data-id');
            window.location.href = 'painting_details.php?id_painting=' + id_painting;
        });
    });

    // Функция для отображения модального окна
    function showModal() {
        const modal = document.getElementById('infoModal');
        modal.style.display = 'block';
    }

    // Закрытие модального окна при нажатии на кнопку
    const closeButton = document.querySelector('.close-button');
    closeButton.addEventListener('click', function() {
        const modal = document.getElementById('infoModal');
        modal.style.display = 'none';
    });

    // Закрытие модального окна при клике вне его
    window.addEventListener('click', function(event) {
        const modal = document.getElementById('infoModal');
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });

    // Показ модального окна после загрузки страницы
    showModal();
});
</script>

<!-- Модальное окно с информацией -->
<div id="infoModal" class="modal">
    <div class="modal-content">
        <span class="close-button">&times;</span>
        <div class="modal-header">
            <img src="https://cdn-icons-png.flaticon.com/512/10613/10613919.png" alt="Art Gallery Logo" style="width: 50px; height: auto; margin-right: 10px;">
            <h2 style="margin: 0; font-weight: bold;">Добро пожаловать в HueHaven!</h2>
        </div>
        <p style="font-style: italic; color: #555;">Погрузитесь в мир искусства с нашим онлайн-аукционом. Здесь вы можете приобретать уникальные произведения со всего мира. Участвуйте в торгах и найдите идеальное дополнение для своей коллекции.</p>
        <p style="font-weight: bold; text-align: center;">HueHaven — откройте мир искусства!</p>
    </div>
</div>

 <!-- SQL-запрос для -->
 <div id="infoModal1" class="modal">
    <div class="modal-content">

</div>

</body>
</html>
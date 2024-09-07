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

<header>
    <img src="https://cdn-icons-png.flaticon.com/512/10613/10613919.png" alt="Art Gallery Logo">
    <h1 style="color: white;font-style: italic;">HueHaven</h1>
</header>

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

<style>
body {
    margin: 0;
    font-family: Arial, sans-serif;
}

header {
    background-color: #00CED1; /* Цвет фона */
    display: flex;
    align-items: center; /* Центрирование элементов по вертикали */
    padding: 1px 10px; /* Уменьшено верхнее и нижнее значение */
}

header img {
    width: 50px; /* Ширина логотипа */
    height: auto; /* Автоматическая высота */
    margin-right: 10px; /* Отступ справа от логотипа */
}

#paintingsGrid {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    padding: 10px; /* Отступ внутри контейнера */
}

.painting {
    width: calc(33.33% - 10px); /* Три в ряд с пробелами */
    margin-bottom: 20px; /* Отступ снизу */
    text-align: center; /* Центрирование текста */
}

.painting img {
    width: 100%; /* Ширина изображения 100% от родителя */
    height: 300px; /* Фиксированная высота */
    object-fit: cover; /* Обрезка изображения для соответствия размеру */
}

.paintInfo {
    margin-top: 5px; /* Отступ сверху для текста */
}

/* Кнопка "Сделать ставку" */
.bid-button {
    display: inline-block;
    margin-top: 10px; /* Отступ сверху */
    padding: 10px 15px; /* Отступы внутри кнопки */
    background-color: #00CED1; /* Цвет фона */
    color: white; /* Цвет текста */
    text-decoration: none; /* Без подчеркивания */
    border-radius: 5px; /* Закругленные углы */
    transition: background-color 0.3s; /* Плавный переход цвета фона */
}

.bid-button:hover {
    background-color: #008B8B; /* Цвет при наведении */
}

/* Стили для модального окна */
.modal {
    display: none; /* Скрыто по умолчанию */
    position: fixed; /* Окно фиксировано на экране */
    z-index: 1000; /* Поверх других элементов */
    left: 0;
    top: 0;
    width: 100%; /* Полная ширина */
    height: 100%; /* Полная высота */
    background-color: rgba(0, 0, 0, 0.7); /* Полупрозрачный фон */
}

.modal-content {
    background-color: #fff; /* Белый фон для содержимого */
    margin: 15% auto; /* Центрирование */
    padding: 20px;
    border: 1px solid #888; /* Рамка */
    width: 60%; /* Установите желаемую ширину */
}

.modal-header {
    display: flex;
    align-items: center; /* Центрирование элементов по вертикали */
}

.modal-content p {
    margin: 5px 0; /* Отступы для параграфов */
}

.close-button {
    color: #aaa; /* Цвет кнопки закрытия */
    float: right; /* Справа */
    font-size: 28px; /* Размер шрифта */
    font-weight: bold; /* Жирный шрифт */
}

.close-button:hover,
.close-button:focus {
    color: black; /* Цвет при наведении */
    text-decoration: none; /* Без подчеркивания */
    cursor: pointer; /* Указатель курсора */
}
</style>

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
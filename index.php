<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HueHaven - Art Auction</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <img src="https://cdn-icons-png.flaticon.com/512/10613/10613919.png" alt="Art Gallery Logo">
        <h1 style="color: white;font-style: italic;">HueHaven</h1>
    </header>

    <div class="input-container">
    <div class="input-group">
        <input type="text" id="nameInput" placeholder="Название картины">
        <input type="text" id="styleInput" placeholder="Стиль">
        <input type="text" id="yearInput" placeholder="Год создания">
    </div>
    <div class="input-group">
        <input type="text" id="authorInput" placeholder="Автор">
        <input type="text" id="sellerInput" placeholder="Продавец">
        <div class="button-group">
            <button id="searchButton">Найти</button>
            <button id="resetButton">Сбросить фильтры</button>
        </div>
    </div>
    
    
</div>

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

if ($result) {
    if (mysqli_num_rows($result) > 0) {
        echo "<table border='1' id='paintingsTable'>";
        echo "<tr>
                <th>Название картины</th>
                <th>Стиль</th>
                <th>Год создания</th>
                <th>Автор</th>
                <th>Продавец</th>
              </tr>";

        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr data-id='" . $row['id_painting'] . "'>";            
            echo "<td>" . $row['paint_name'] . "</td>";
            echo "<td>" . $row['style'] . "</td>";
            echo "<td>" . $row['creation_year'] . "</td>";
            echo "<td>" . $row['author'] . "</td>";
            echo "<td>" . $row['full_name'] . "</td>";
            echo "</tr>";
        }

        echo "</table>";
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
    var table = document.getElementById('paintingsTable');
    var rows = table.getElementsByTagName('tr');
    var nameInput = document.getElementById('nameInput');
    var styleInput = document.getElementById('styleInput');
    var yearInput = document.getElementById('yearInput');
    var authorInput = document.getElementById('authorInput');
    var sellerInput = document.getElementById('sellerInput');
    var searchButton = document.getElementById('searchButton');

    // Проходим по всем строкам таблицы
    for (var i = 1; i < rows.length; i++) {
        rows[i].addEventListener('click', function() {
            var id_painting = this.getAttribute('data-id');
            window.location.href = 'painting_details.php?id_painting=' + id_painting;
        });
    }

    // Поиск по таблице
    searchButton.addEventListener('click', function() {
        var nameFilter = nameInput.value.toLowerCase();
        var styleFilter = styleInput.value.toLowerCase();
        var yearFilter = yearInput.value.toLowerCase();
        var authorFilter = authorInput.value.toLowerCase();
        var sellerFilter = sellerInput.value.toLowerCase();

        for (var i = 1; i < rows.length; i++) {
            var cells = rows[i].getElementsByTagName('td');
            var matches = true;

            if (nameFilter && !cells[0].textContent.toLowerCase().includes(nameFilter)) {
                matches = false;
            }
            if (styleFilter && !cells[1].textContent.toLowerCase().includes(styleFilter)) {
                matches = false;
            }
            if (yearFilter && !cells[2].textContent.toLowerCase().includes(yearFilter)) {
                matches = false;
            }
            if (authorFilter && !cells[3].textContent.toLowerCase().includes(authorFilter)) {
                matches = false;
            }
            if (sellerFilter && !cells[4].textContent.toLowerCase().includes(sellerFilter)) {
                matches = false;
            }

            rows[i].style.display = matches ? '' : 'none';
        }
    });

    // Обработчик для кнопки сброса
    resetButton.addEventListener('click', function() {
        // Очищаем поля ввода
        nameInput.value = '';
        styleInput.value = '';
        yearInput.value = '';
        authorInput.value = '';
        sellerInput.value = '';

        // Показываем все строки таблицы
        for (var i = 1; i < rows.length; i++) {
            rows[i].style.display = '';
        }
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

</body>
</html>
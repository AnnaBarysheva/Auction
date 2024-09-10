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
$link = mysqli_connect("localhost", "root", "alina", "Auction");

if ($link == false) {
    die("Ошибка: Невозможно подключиться к MySQL " . mysqli_connect_error());
}

// SQL-запрос для получения картин, которые не проданы, с начальной ценой и именами продавцов
$sql = "
    SELECT Paintings.*, PaintingsOnAuction.starting_price, Sellers.full_name
    FROM Paintings
    JOIN PaintingsOnAuction ON Paintings.id_painting = PaintingsOnAuction.id_painting
    JOIN Sellers ON Paintings.id_seller = Sellers.id_seller
    JOIN Auctions ON PaintingsOnAuction.id_auction = Auctions.id_auction
    WHERE Paintings.is_sold = FALSE
    AND Auctions.start_date <= CURDATE()
    AND Auctions.end_date >= CURDATE()
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
                <th>Действия</th>
              </tr>";

        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr data-id='" . $row['id_painting'] . "'>";            
            echo "<td>" . $row['paint_name'] . "</td>";
            echo "<td>" . $row['style'] . "</td>";
            echo "<td>" . $row['creation_year'] . "</td>";
            echo "<td>" . $row['author'] . "</td>";
            echo "<td>" . $row['full_name'] . "</td>";
            echo "<td>
            <button class='editButton' data-id='" . $row['id_painting'] . "'>Редактировать</button>
            <button class='deleteButton' data-id='" . $row['id_painting'] . "'>Удалить</button>
          </td>";
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
    var resetButton = document.getElementById('resetButton');

    var editButtons = document.getElementsByClassName('editButton');
    var editModal = document.getElementById('editModal');
    var closeEditButton = document.getElementById('closeEditModal');

   
    
     // Проверка, было ли уже показано модальное окно
     if (!localStorage.getItem('infoModalShown')) {
        showModal();
        localStorage.setItem('infoModalShown', 'true'); // Устанавливаем флаг в localStorage
    }

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

    // Обновите обработчик для кнопки редактирования
    for (var button of editButtons) {
        button.addEventListener('click', function(event) {
            event.stopPropagation(); // Предотвращаем всплытие события
            var id = this.getAttribute('data-id');
            var row = this.closest('tr');
            var cells = row.getElementsByTagName('td');

            document.getElementById('editId').value = id;
            document.getElementById('editName').value = cells[0].textContent;
            document.getElementById('editStyle').value = cells[1].textContent;
            document.getElementById('editYear').value = cells[2].textContent;
            document.getElementById('editAuthor').value = cells[3].textContent;
            document.getElementById('editSeller').value = cells[4].textContent;

            editModal.style.display = 'block'; // Открываем модальное окно
        });
    }

    closeEditButton.addEventListener('click', function() {
        editModal.style.display = 'none'; // Закрываем модальное окно
    });

    // Обновите обработчики для кнопки удаления
for (var button of document.getElementsByClassName('deleteButton')) {
    button.addEventListener('click', function(event) {
        event.stopPropagation(); // Предотвращаем всплытие события
        var id = this.getAttribute('data-id');

        if (confirm('Вы уверены, что хотите удалить эту запись?')) {
            fetch('delete_painting.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ id_painting: id })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Удаляем строку из таблицы
                    var row = this.closest('tr');
                    row.parentNode.removeChild(row);
                    alert('Запись успешно удалена.');
                } else {
                    alert('Ошибка при удалении записи: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Ошибка:', error);
            });
        }
    });
}

    // Функция для отображения модального окна
    function showModal() {
        const modal = document.getElementById('infoModal');
        modal.style.display = 'block';
    }

    // Закрытие модального окна при клике вне его
    window.addEventListener('click', function(event) {
        const editModal = document.getElementById('editModal');
        if (event.target === editModal) {
            editModal.style.display = 'none'; // Закрываем модальное окно
        }
    });

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

<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close-button" id="closeEditModal">&times;</span>
        <h2>Редактировать картину</h2>
        <form id="editForm" action="update_painting.php" method="POST">
            <input type="hidden" id="editId" name="id_painting" required>
            
            <label for="editName">Название картины:</label>
            <input type="text" class="modal-input" id="editName" name="paint_name" placeholder="Название картины" required maxlength="255">
            
            <label for="editStyle">Стиль:</label>
            <input type="text" class="modal-input" id="editStyle" name="style" placeholder="Стиль" required maxlength="50">
            
            <label for="editYear">Год создания:</label>
            <input type="text" class="modal-input" id="editYear" name="creation_year" placeholder="Год создания" pattern="\d{4}" maxlength="4" required title="Введите четыре цифры" max="<?php echo date('Y'); ?>">
            
            <label for="editAuthor">Автор:</label>
            <input type="text" class="modal-input" id="editAuthor" name="author" placeholder="Автор" required maxlength="255">
            
            <label for="editSeller">Продавец:</label>
            <input type="text" class="modal-input" id="editSeller" name="seller" placeholder="Продавец" required maxlength="255">
            
            <button type="submit" class="saveButton">Сохранить</button>
        </form>
    </div>
</div>

<script>
    document.getElementById('editForm').addEventListener('submit', function(event) {
        var yearInput = document.getElementById('editYear').value;
        var currentYear = new Date().getFullYear();
        
        if (!/^\d{4}$/.test(yearInput) || yearInput > currentYear) {
            alert('Пожалуйста, введите корректный год (от 1901 и не больше текущего года).');
            event.preventDefault(); // Отменяем отправку формы
        }
    });
</script>

</body>
</html>
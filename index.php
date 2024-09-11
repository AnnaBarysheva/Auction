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
//$link = mysqli_connect("localhost", "root", "root_Passwrd132", "Auction");

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

// AND Auctions.start_date <= CURDATE()
// AND Auctions.end_date >= CURDATE()

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

<button id="addPaintingButton" class="addButton">Добавить картину</button>

<script>
// Функция проверки соединения с сервером
async function checkConnection() {
    console.log("Checking connection to the server..."); // Проверка вызова функции

    try {
        const response = await fetch('check_connection.php');
        console.log("Response from server: ", response);

        // Проверяем, был ли успешен ответ с сервера
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }

        const data = await response.json();
        console.log("Data received from server: ", data);

        // Проверяем, есть ли ошибка в данных
        if (!data.success) {
            throw new Error(data.message || "Unknown error from server.");
        }

        return true; // Соединение успешно
    } catch (error) {
        console.error("Error in checkConnection: ", error); // Вывод полной ошибки в консоль

        // Показываем ошибку пользователю через модальное окно
        showErrorModal("Ошибка подключения к серверу: " + error.message);
        return false; // Соединение не удалось
    }
}

// Функция для отображения ошибки в модальном окне
function showErrorModal(message) {
    var modal = document.getElementById('errorModal');
    var errorMessage = document.getElementById('errorMessage');
    var closeModal = document.getElementById('closeErrorModal');

    // Устанавливаем текст ошибки
    errorMessage.textContent = message;

    // Показываем модальное окно
    modal.style.display = 'block';

    // Закрываем модальное окно при нажатии на "x"
    closeModal.onclick = function() {
        modal.style.display = 'none';
    };

    // Закрываем модальное окно, если кликнули вне его
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    };
}


// Универсальная функция, которая оборачивает действие в проверку соединения
async function handleWithConnection(callback) {
    const connectionOK = await checkConnection();

    if (!connectionOK) {
        console.log("No connection to the server.");
        return; // Прекращаем выполнение, если нет соединения
    }

    callback(); // Выполняем основное действие, если соединение успешно
}

// Добавляем обработчики событий после загрузки DOM
document.addEventListener('DOMContentLoaded', async function () {
    console.log("DOMContentLoaded event triggered");

    var table = document.getElementById('paintingsTable');
    var rows = table ? table.getElementsByTagName('tr') : [];
    var nameInput = document.getElementById('nameInput');
    var styleInput = document.getElementById('styleInput');
    var yearInput = document.getElementById('yearInput');
    var authorInput = document.getElementById('authorInput');
    var sellerInput = document.getElementById('sellerInput');
    var searchButton = document.getElementById('searchButton');
    var resetButton = document.getElementById('resetButton');
    var editButtons = document.getElementsByClassName('editButton');
    var deleteButtons = document.getElementsByClassName('deleteButton');
    var closeEditButton = document.getElementById('closeEditModal');
    const addPaintingButton = document.getElementById('addPaintingButton');
    const addModal = document.getElementById('addModal');
    const closeAddModal = document.getElementById('closeAddModal');

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
    searchButton.addEventListener('click', function () {
        handleWithConnection(() => {
            console.log("Search button clicked.");

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
    });

    // Обработчик для кнопки сброса
    resetButton.addEventListener('click', function () {
        handleWithConnection(() => {
            console.log("Reset button clicked.");

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

    // Закрытие модального окна для редактирования
    closeEditButton.addEventListener('click', function () {
        document.getElementById('editModal').style.display = 'none';
    });

    // Закрытие модального окна при клике вне его
    window.addEventListener('click', function(event) {
            const editModal = document.getElementById('editModal');
            if (event.target === editModal) {
                editModal.style.display = 'none'; // Закрываем модальное окно
            }
        });

        addPaintingButton.addEventListener('click', function () {
            addModal.style.display = 'block'; // Открываем модальное окно для добавления
        });

        closeAddModal.addEventListener('click', function () {
            addModal.style.display = 'none'; // Закрываем модальное окно

            // Очищаем поля ввода
            document.getElementById('addName').value = '';
            document.getElementById('addSize').value = '';
            document.getElementById('addMaterials').value = '';
            document.getElementById('addStyle').value = '';
            document.getElementById('addYear').value = '';
            document.getElementById('addAuthor').value = '';
            document.getElementById('addImageUrl').value = '';
            document.getElementById('addSeller').value = '';
            document.getElementById('addEmail').value = '';
            document.getElementById('addPhone').value = '';
            document.getElementById('addLotNumber').value = '';
            document.getElementById('addStartingPrice').value = '';
            document.getElementById('addStartDate').value = '';
            document.getElementById('addEndDate').value = '';
        });

        // Закрытие модального окна при клике вне его
        window.onclick = function (event) {
            if (event.target == addModal) {
                addModal.style.display = 'none';

                // Очищаем поля ввода
                document.getElementById('addName').value = '';
                document.getElementById('addSize').value = '';
                document.getElementById('addMaterials').value = '';
                document.getElementById('addStyle').value = '';
                document.getElementById('addYear').value = '';
                document.getElementById('addAuthor').value = '';
                document.getElementById('addImageUrl').value = '';
                document.getElementById('addSeller').value = '';
                document.getElementById('addEmail').value = '';
                document.getElementById('addPhone').value = '';
                document.getElementById('addLotNumber').value = '';
                document.getElementById('addStartingPrice').value = '';
                document.getElementById('addStartDate').value = '';
                document.getElementById('addEndDate').value = '';
            }
        };
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

<!-- Модальное окно ошибки -->
<div id="errorModal" class="modal" style="display:none;">
    <div class="modal-content">
        <span id="closeErrorModal" class="close-button">&times;</span>
        <h2>Ошибка</h2>
        <p id="errorMessage"></p>
    </div>
</div>

<div id="addModal" class="modal">
    <div class="modal-content">
        <span class="close-button" id="closeAddModal">&times;</span>
        <h2>Добавить картину</h2>
        <form id="addForm" action="add_painting.php" method="POST">
            <div class="input-container">
                <div class="input-column">
                    <label for="addName">Название картины:</label>
                    <input type="text" id="addName" name="paint_name" class="modal-input" placeholder="Название картины" required maxlength="255">
                    
                    <label for="addSize">Размер:</label>
                    <input type="text" id="addSize" name="size" class="modal-input" placeholder="XXxXX см" required maxlength="50" pattern="^\d{2}x\d{2} см$" title="Введите размер в формате XXxXX см">
                    
                    <label for="addMaterials">Материалы:</label>
                    <input type="text" id="addMaterials" name="materials" class="modal-input" placeholder="Материалы" required maxlength="255">
                    
                    <label for="addStyle">Стиль:</label>
                    <input type="text" id="addStyle" name="style" class="modal-input" placeholder="Стиль" required maxlength="50">
                    
                    <label for="addYear">Год создания:</label>
                    <input type="text" id="addYear" name="creation_year" class="modal-input" placeholder="Год создания" pattern="\d{4}" maxlength="4" required title="Введите четыре цифры">
                    
                    <label for="addAuthor">Автор:</label>
                    <input type="text" id="addAuthor" name="author" class="modal-input" placeholder="Автор" required maxlength="255">
                    
                    <label for="addImageUrl">URL картины:</label>
                    <input type="text" id="addImageUrl" name="image_path" class="modal-input" placeholder="URL картины" required maxlength="255">
                </div>
                
                <div class="input-column">
                    <label for="addSeller">Имя продавца:</label>
                    <input type="text" id="addSeller" name="seller" class="modal-input" placeholder="Имя продавца" required maxlength="255">
                    
                    <label for="addEmail">Email продавца:</label>
                    <input type="email" id="addEmail" name="email" class="modal-input" placeholder="Email продавца" required>
                    
                    <label for="addPhone">Телефон продавца:</label>
                    <input type="text" id="addPhone" name="phone" class="modal-input" placeholder="Телефон продавца" required maxlength="50">
                </div>
                
                <div class="input-column">
                    <label for="addLotNumber">Номер лота:</label>
                    <input type="number" id="addLotNumber" name="lot_number" class="modal-input" required>
                    
                    <label for="addStartingPrice">Стартовая цена:</label>
                    <input type="number" id="addStartingPrice" name="starting_price" class="modal-input" step="0.01" required>
                    
                    <label for="addStartDate">Дата начала аукциона:</label>
                    <input type="date" id="addStartDate" name="start_date" class="modal-input" required>
                    
                    <label for="addEndDate">Дата конца аукциона:</label>
                    <input type="date" id="addEndDate" name="end_date" class="modal-input" required>
                </div>
            </div>
            
            <button type="submit" class="saveButton">Добавить</button>
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

    const addSizeInput = document.getElementById('addSize');

// Устанавливаем формат при фокусе на поле
addSizeInput.addEventListener('focus', function () {
    if (this.value === '' || this.value === '__x__ см') {
        this.value = '__x__ см'; // Устанавливаем формат
    }
});

// Обработчик для ввода данных
addSizeInput.addEventListener('input', function () {
    let value = this.value.replace(/[^0-9]/g, ''); // Удаляем все, кроме цифр
    if (value.length > 4) {
        value = value.slice(0, 4); // Ограничиваем до 4 цифр
    }

    // Форматируем значение
    if (value.length === 4) {
        this.value = value.slice(0, 2) + 'x' + value.slice(2, 4) + ' см';
    } else {
        this.value = (value.length > 0 ? value : '__') + 'x' + '__' + ' см'; // Обновляем отображение
    }
});

// Убираем формат при потере фокуса, если поле пустое
addSizeInput.addEventListener('blur', function () {
    if (this.value === '__x__ см') {
        this.value = ''; // Очищаем поле, если пользователь не ввел данные
    }
});
</script>

</body>
</html>
<?php
session_start(); // Начинаем сессию в начале файла

$link = include 'db_connect.php';

// Проверка подключения к базе данных
if ($link === false) {
    echo "Ошибка подключения к базе данных.";
    exit();
}

// Проверяем, установлен ли user_id
if (!isset($_SESSION['user_id'])) {
    echo "<h1>Вы не авторизованы. Пожалуйста, войдите.</h1>";
    exit(); // Завершаем выполнение, если пользователь не авторизован
}

$user_id = $_SESSION['user_id']; // Сохраняем текущий user_id

// SQL запрос для получения картин, связанных с пользователем
$sql = "
    SELECT p.id_painting, p.paint_name, p.creation_year, p.author, 
           st.style_name, m.material_name, COUNT(u.id_user) AS request_count,  GROUP_CONCAT(u.login SEPARATOR ', ') AS logins
    FROM Paintings p
    JOIN PaintingUser pu ON p.id_painting = pu.id_painting
    LEFT JOIN Styles st ON p.id_style = st.id_style
    LEFT JOIN Materials m ON p.id_material = m.id_material
    LEFT JOIN Users u ON pu.id_user = u.id_user
    WHERE p.id_user = ?  
    GROUP BY p.id_painting
";


?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мои заявки</title>
    <link rel="stylesheet" href="style.css"> 
    <script src="script.js"></script>
</head>
<body>
<header>
        <div class="header-left">
            <img src="https://cdn-icons-png.flaticon.com/512/10613/10613919.png" alt="Art Gallery Logo" class="logo">
            <h1>HueHaven</h1>
        </div>

        <div class="header-right">
        <?php if (isset($_SESSION['user_id'])): ?>
               
                <button type="button" class="header-button" id="return-home-button">Вернуться на главную</button>


           
            <?php endif; ?>
        </div>
    </header>

<?php 

$stmt = mysqli_prepare($link, $sql);

if ($stmt === false) {
    echo "Ошибка подготовки запроса: " . mysqli_error($link);
    exit();
}

mysqli_stmt_bind_param($stmt, 'i', $user_id); // Привязываем user_id
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Проверка на наличие результатов
if ($result && mysqli_num_rows($result) > 0) {
    echo "<h1 style='text-align: center; color: black;'>Заявки на ваши картины</h1>";
    echo "<div class='table-wrapper'>";
    echo "<table border='1' id='requestsTable'>"; 
    echo "<thead>
            <tr>                
                <th>Название картины</th>
                <th>Год создания</th>
                <th>Автор</th>
                <th>Стиль</th>
                <th>Материал</th>
                <th>Количество заявок</th> 
            </tr>
          </thead>
          <tbody>";

          while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr data-id='" . $row['id_painting'] . "'>";
            echo "<td>" . htmlspecialchars($row['paint_name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['creation_year']) . "</td>";
            echo "<td>" . htmlspecialchars($row['author']) . "</td>";
            echo "<td>" . htmlspecialchars($row['style_name'] ?? 'Нет') . "</td>";
            echo "<td>" . htmlspecialchars($row['material_name'] ?? 'Нет') . "</td>";
            
            // Преобразуем логины в массив
            $logins = explode(', ', $row['logins']);
            
            // Генерируем список логинов
            echo "<td>
                    <span class='request-count' onclick='toggleLogins(this)'>" . htmlspecialchars($row['request_count']) . " &#x25BC;</span>
                    <ul class='login-list' style='display:none;'>";
                    
            // Выводим каждый логин в отдельном элементе списка
            foreach ($logins as $login) {
                echo "<li>" . htmlspecialchars($login) . "</li>";
            }
            
            echo "</ul>
                  </td>";
            echo "</tr>";
        }
        
    echo "</tbody></table>";
    echo "</div>";
}


// Освобождение памяти и закрытие соединения
mysqli_free_result($result);
mysqli_stmt_close($stmt);
mysqli_close($link);
?>

<script>
    // Получаем таблицу и все строки таблицы
var table = document.getElementById('requestsTable');
var rows = table.getElementsByTagName('tr');

// Проходим по всем строкам, начиная с первой после заголовка
for (var i = 1; i < rows.length; i++) {
    var requestCount = rows[i].querySelector('.request-count'); // Получаем элемент с количеством заявок

    if (requestCount) {
        requestCount.addEventListener('click', function(event) {
            event.stopPropagation(); // Останавливаем всплытие события
            var loginList = this.nextElementSibling; // Находим следующий элемент (список логинов)

            // Переключаем видимость списка логинов
            if (loginList.style.display === "none") {
                loginList.style.display = "block"; // Показываем список
                this.innerHTML = this.innerHTML.replace('&#x25BC;', '&#x25B2;'); // Меняем стрелочку на вверх
            } else {
                loginList.style.display = "none"; // Скрываем список
                this.innerHTML = this.innerHTML.replace('&#x25B2;', '&#x25BC;'); // Меняем стрелочку на вниз
            }
        });
    }

    // Обработчик клика для всей строки
    rows[i].addEventListener('click', function() {
        var id_painting = this.getAttribute('data-id');
        // Перенаправление на страницу с деталями
        window.location.href = 'painting_details.php?id_painting=' + id_painting;
    });
}

</script>

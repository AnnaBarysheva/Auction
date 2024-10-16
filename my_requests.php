<?php
session_start();
$link = include 'db_connect.php';

// Проверка подключения к базе данных
if ($link === false) {
    echo "Ошибка подключения к базе данных.";
    exit();
}

// Проверка, авторизован ли пользователь
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Перенаправляем на страницу входа
    exit();
}

$user_id = $_SESSION['user_id'];

// SQL запрос для получения картин, выбранных пользователем
$sql = "
    SELECT p.id_painting, p.paint_name, p.creation_year, p.author, 
           s.full_name AS seller_name, 
           st.style_name, m.material_name
    FROM Paintings p
    JOIN PaintingUser pu ON p.id_painting = pu.id_painting
    LEFT JOIN Styles st ON p.id_style = st.id_style
    LEFT JOIN Materials m ON p.id_material = m.id_material
    LEFT JOIN Sellers s ON p.id_seller = s.id_seller  
    WHERE pu.id_user = ?
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
                <!-- Если пользователь вошел, показываем кнопку "Выход" -->
                <!-- <button class="header-button" onclick="handleLogout()">Выход</button> -->
                <!-- <button class="header-button" id="logoutButton">Выход</button> -->
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

mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Проверка на наличие результатов
if ($result && mysqli_num_rows($result) > 0) {
    echo "<h1 style='text-align: center; color: black;'>Мои заявки на картины</h1>";
    echo "<div class='table-wrapper'>";
    echo "<table border='1' id='requestsTable'>"; 
    echo "<thead>
            <tr>                
                <th>Название картины</th>
                <th>Год создания</th>
                <th>Автор</th>
                <th>Стиль</th>
                <th>Материал</th>
                <th>Имя продавца</th>
            </tr>
          </thead>
          <tbody>";

    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
       
        echo "<td>" . htmlspecialchars($row['paint_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['creation_year']) . "</td>";
        echo "<td>" . htmlspecialchars($row['author']) . "</td>";
        echo "<td>" . htmlspecialchars($row['style_name'] ?? 'Нет') . "</td>";
        echo "<td>" . htmlspecialchars($row['material_name'] ?? 'Нет') . "</td>";
        echo "<td>" . htmlspecialchars($row['seller_name']) . "</td>";
        echo "</tr>";
    }
    echo "</tbody></table>";
    echo "</div>";
} else {
    echo "<h1>У вас нет заявок на картины.</h1>";
}

// Освобождение памяти и закрытие соединения
mysqli_free_result($result);
mysqli_stmt_close($stmt);
mysqli_close($link);
?>

</body>
</html>

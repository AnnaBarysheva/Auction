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

// Проверка роли пользователя 
$isAdmin = false;
$isSeller = false;
$isUser = false;

// Запрос для получения роли пользователя
$query = "SELECT role FROM Users WHERE id_user = ?";
$stmt = mysqli_prepare($link, $query);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, 'i', $user_id); // Привязываем id_user
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        $isSeller = ($user['role'] === 'seller');
        $isAdmin = ($user['role'] === 'admin');
        $isUser = ($user['role'] === 'user');
    }
}

// SQL запрос для получения картин, связанных с пользователем
$sql = "
    SELECT p.id_painting, p.paint_name, p.creation_year, p.author, 
           st.style_name, m.material_name
    FROM Paintings p
    JOIN PaintingUser pu ON p.id_painting = pu.id_painting
    LEFT JOIN Styles st ON p.id_style = st.id_style
    LEFT JOIN Materials m ON p.id_material = m.id_material
    WHERE p.id_user = ?  -- Фильтруем по ID текущего пользователя
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

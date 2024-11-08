<?php
session_start();
// Подключаемся к базе данных
$link = include 'db_connect.php';

// Проверяем, авторизован ли пользователь
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Подготовка запроса с использованием mysqli
$query = $link->prepare("SELECT full_name, login, profile_image FROM users WHERE id_user = ?");
$query->bind_param("i", $user_id);
$query->execute();

// Извлечение результата
$result = $query->get_result();
$user = $result->fetch_assoc();

//для дефолтной картинки
$defaultImagePath = 'uploads/default_profile.jpg';
// Чтение файла и конвертация в бинарные данные
if (file_exists($defaultImagePath)) {
    $fileData = file_get_contents($defaultImagePath);

    // Подготовка и выполнение SQL-запроса для обновления всех записей
    $query = $link->prepare("UPDATE users SET profile_image = ?");
    $query->bind_param("b", $fileData);

    if ($query->execute()) {
        echo "Все записи успешно обновлены.";
    } else {
        echo "Ошибка при обновлении записей: " . $link->error;
    }
} else {
    echo "Файл изображения не найден.";
}
//
// Устанавливаем картинку по умолчанию, если профильной картинки нет
$profile_picture = $user['profile_picture'] 
    ? 'data:image/jpeg;base64,' . base64_encode($user['profile_picture']) 
    : $defaultImagePath; // Подставляем путь к изображению по умолчанию

// Обработка загрузки новой картинки
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['profile_picture'])) {
    $file = $_FILES['profile_picture'];

    // Проверка на ошибки при загрузке
    if ($file['error'] === UPLOAD_ERR_OK) {
        $fileData = file_get_contents($file['tmp_name']);

        // Обновляем картинку профиля пользователя в базе данных
        $updateQuery = $link->prepare("UPDATE users SET profile_image = ? WHERE id_user = ?");
        $updateQuery->bind_param("bi", $fileData, $user_id);
        $updateQuery->send_long_data(0, $fileData);
        $updateQuery->execute();

        // Перезагружаем страницу, чтобы обновить картинку
        header("Location: my_profile.php");
        exit();
    } else {
        echo "Ошибка при загрузке файла.";
    }
}
?>


<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Мой профиль</title>
</head>
<body>
    <h1>Профиль пользователя</h1>
    <p>Имя пользователя: <?php echo htmlspecialchars($user['full_name']); ?></p>
    <p>Логин: <?php echo htmlspecialchars($user['login']); ?></p>
       
    <h2>Картинка профиля</h2>
    <img src="<?php echo $profile_picture; ?>" alt="Profile Picture" style="width: 150px; height: 150px;">

    <form action="my_profile.php" method="post" enctype="multipart/form-data">
        <label for="profile_picture">Загрузить новую картинку профиля:</label>
        <input type="file" name="profile_picture" id="profile_picture" accept="image/*">
        <button type="submit" name="upload">Загрузить</button>
    </form>
</body>
</html>

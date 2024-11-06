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

//-----
// Получение информации о пользователе из базы данных
$query = $link->prepare("SELECT name, email, profile_picture FROM users WHERE user_id = ?");
$query->execute([$user_id]);
$user = $query->fetch(PDO::FETCH_ASSOC);

// Устанавливаем картинку по умолчанию, если профильной картинки нет
$profile_picture = $user['profile_picture'] 
    ? 'data:image/jpeg;base64,' . base64_encode($user['profile_picture']) 
    : 'default.png';

// Обработка загрузки новой картинки
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['profile_picture'])) {
    $file = $_FILES['profile_picture'];

    // Проверка на ошибки при загрузке
    if ($file['error'] === UPLOAD_ERR_OK) {
        $fileData = file_get_contents($file['tmp_name']);

        // Обновляем картинку профиля пользователя в базе данных
        $updateQuery = $db->prepare("UPDATE users SET profile_picture = ? WHERE user_id = ?");
        $updateQuery->execute([$fileData, $user_id]);

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
    <p>Имя пользователя: <?php echo htmlspecialchars($user['username']); ?></p>
    <p>Email: <?php echo htmlspecialchars($user['email']); ?></p>
    
    <h2>Картинка профиля</h2>
    <img src="<?php echo $profile_picture; ?>" alt="Profile Picture" style="width: 150px; height: 150px;">

    <form action="my_profile.php" method="post" enctype="multipart/form-data">
        <label for="profile_picture">Загрузить новую картинку профиля:</label>
        <input type="file" name="profile_picture" id="profile_picture" accept="image/*">
        <button type="submit" name="upload">Загрузить</button>
    </form>
</body>
</html>

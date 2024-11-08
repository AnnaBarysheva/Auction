<?php
session_start();
// Подключаемся к базе данных
$link = @include 'db_connect.php';

// Флаг для ошибки подключения к серверу
$connectionError = false;
$error_messages = [];

// Проверка успешности подключения
if (!$link) {
    $connectionError = true;
    $error_messages[] = "Ошибка подключения к серверу. Пожалуйста, попробуйте позже.";
} else {
    // Если подключение установлено, продолжаем выполнение остального кода
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

    // Путь к дефолтной картинке
    $defaultImagePath = 'uploads/default_profile.jpg';

    // Устанавливаем путь к картинке: из БД или дефолтный
    $profile_picture = $user['profile_image']
        ? 'data:image/jpeg;base64,' . base64_encode($user['profile_image'])
        : $defaultImagePath;

    // Обработка загрузки новой картинки
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['profile_picture'])) {
        $file = $_FILES['profile_picture'];
        $uploadOk = 1;

        // Проверка на ошибки при загрузке
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $error_messages[] = "Ошибка при загрузке файла.";
            $uploadOk = 0;
        }

        // Проверка типа файла (только изображения)
        $fileType = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (!in_array($fileType, $allowedTypes)) {
            $error_messages[] = "Только JPG, JPEG, PNG и GIF файлы разрешены.";
            $uploadOk = 0;
        }

        // Проверка размера файла (например, не более 2MB)
        if ($file['size'] > 2000000) { // 2MB
            $error_messages[] = "Файл слишком большой.";
            $uploadOk = 0;
        }

        // Проверка на битый файл
        if ($uploadOk == 1) {
            // Проверяем, является ли файл валидным изображением
            if (@getimagesize($file['tmp_name']) === false) {
                $error_messages[] = "Файл поврежден или не является изображением.";
                $uploadOk = 0;
            }
        }

        // Если все проверки прошли успешно, обрабатываем файл
        if ($uploadOk == 1) {
            // Чтение содержимого файла
            $fileData = file_get_contents($file['tmp_name']);

            // Обновляем картинку профиля пользователя в базе данных
            $updateQuery = $link->prepare("UPDATE users SET profile_image = ? WHERE id_user = ?");
            $updateQuery->bind_param("bi", $fileData, $user_id);
            $updateQuery->send_long_data(0, $fileData);
            
            if ($updateQuery->execute()) {
                // Перезагружаем страницу, чтобы обновить картинку
                header("Location: my_profile.php?upload_success=1");
                exit();
            } else {
                $error_messages[] = "Ошибка при обновлении картинки: " . $link->error;
            }
            $updateQuery->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Мой профиль</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<script src="script.js"></script>
<header>
    <div class="header-left">
        <img src="https://cdn-icons-png.flaticon.com/512/10613/10613919.png" alt="Art Gallery Logo" class="logo">
        <h1>HueHaven</h1>
    </div>
    <div class="header-right">
        <div class="return-home">
            <button type="button" class="header-button" id="return-home-button" onclick="handleReturnHome()">Вернуться на главную</button>
        </div>
    </div>
</header>
<div class="profile-page">
    <?php if ($connectionError): ?>
        <!-- Если ошибка подключения, выводим alert -->
        <script>
            alert("<?php echo addslashes($error_messages[0]); ?>");
        </script>
    <?php else: ?>
        <h1>Профиль пользователя</h1>
        
        <!-- Информация о пользователе -->
        <div class="profile-details">
            <p>Имя пользователя: <?php echo htmlspecialchars($user['full_name']); ?></p>
            <p>Логин: <?php echo htmlspecialchars($user['login']); ?></p>
        </div>
    
        <img src="<?php echo htmlspecialchars($profile_picture); ?>" alt="Profile Picture" class="profile-picture">

        <!-- Форма загрузки новой картинки профиля -->
        <form action="my_profile.php" method="post" enctype="multipart/form-data" class="profile-upload-form">
            <label for="profile_picture" class="custom-file-upload">Выберите файл</label>
            <input type="file" name="profile_picture" id="profile_picture" accept="image/*">
            <button type="submit" name="upload">Загрузить</button>
        </form>
    <?php endif; ?>
</div>

<?php if (!empty($error_messages) && !$connectionError): ?>
    <script>
        alert('<?php echo addslashes(implode("\n", $error_messages)); ?>');
    </script>
<?php endif; ?>

</body>
</html>

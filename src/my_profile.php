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

// // Проверяем, доступен ли путь к картинке профиля из базы
// if ($user['profile_image']) {
//     $profile_picture = 'data:image/jpeg;base64,' . base64_encode($user['profile_image']);
// } else {
//     // Если путь к картинке недоступен, проверяем дефолтное изображение
//     if (!file_exists($defaultImagePath) || !is_readable($defaultImagePath)) {
//         $error_messages[] = "Файл изображения профиля отсутствует или поврежден.";
//         $profile_picture = '';
//     } else {
//         $profile_picture = $defaultImagePath;
//     }
// }

function isValidImageBlob($data) {
    if (!$data) {
        return false; // Пустой BLOB
    }

    // Проверяем, можно ли создать изображение из данных
    $image = @imagecreatefromstring($data);
    if ($image === false) {
        return false; // Некорректное изображение
    }

    // Очищаем ресурс изображения
    imagedestroy($image);
    return true;
}

if ($user['profile_image']) {
    if (isValidImageBlob($user['profile_image'])) {
        $profile_picture = 'data:image/jpeg;base64,' . base64_encode($user['profile_image']);
    } else {
        // Если BLOB поврежден или не является изображением
        echo "<script>alert('Изображение профиля повреждено или не является допустимым изображением. Будет установлено изображение по умолчанию.');</script>";
        
        // Проверяем дефолтное изображение
        if (!file_exists($defaultImagePath) || !is_readable($defaultImagePath)) {
            echo "<script>alert('Доступ к файлу изображения профиля по умолчанию закрыт. Изображение профиля будет отсутствовать.');</script>";
            $profile_picture = ''; // Оставляем пустым
        } else {
            // Дополнительная проверка целостности файла
            if (getimagesize($defaultImagePath) === false) {
                echo "<script>alert('Файл  изображения профиля по умолчанию поврежден и не является допустимым изображением. Изображение профиля будет отсутствовать.');</script>";
                $profile_picture = ''; // Оставляем пустым
            } else {
                $profile_picture = $defaultImagePath; // Устанавливаем дефолтное изображение
            }
        }
    }
} else {
    // Если в BLOB-е нет изображения, используем дефолтное
    if (!file_exists($defaultImagePath) || !is_readable($defaultImagePath)) {
        echo "<script>alert('Доступ к файлу изображения профиля по умолчанию закрыт. Изображение профиля будет отсутствовать.');</script>";
        $profile_picture = ''; // Оставляем пустым
    } else {
        // Дополнительная проверка целостности файла
        if (getimagesize($defaultImagePath) === false) {
            echo "<script>alert('Файл  изображения профиля по умолчанию поврежден и не является допустимым изображением. Изображение профиля будет отсутствовать.');</script>";
            $profile_picture = ''; // Оставляем пустым
        } else {
            $profile_picture = $defaultImagePath; // Устанавливаем дефолтное изображение
        }
    }
}




        // // Устанавливаем путь к картинке: из БД или дефолтный
        // $profile_picture = $user['profile_image']
        //     ? 'data:image/jpeg;base64,' . base64_encode($user['profile_image'])
        //     : $defaultImagePath;

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
            $error_messages[] = "Недопустимый формат файла. Только JPG, JPEG, PNG и GIF файлы разрешены.";
            $uploadOk = 0;
        }

        // Проверка размера файла
        if ($uploadOk && $file['size'] > 2000000) { 
            $error_messages[] = "Файл слишком большой. Максимальный размер: 2 MB.";
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
            <form action="my_profile.php" method="post" enctype="multipart/form-data" class="profile-upload-form" id="uploadForm">
                <label for="profile_picture" class="custom-file-upload">Выберите файл</label>
                <input type="file" name="profile_picture" id="profile_picture" accept="image/*">
                <button type="submit" name="upload" id="uploadButton">Загрузить</button>
            </form>
        <?php endif; ?>
    </div>

    <?php if (!empty($error_messages) && !$connectionError): ?>
        <script>
            alert('<?php echo addslashes(implode("\n", $error_messages)); ?>');
        </script>
    <?php endif; ?>

<div id="errorModal" class="modal" style="display:none;">
    <div class="modal-content">
        <span id="closeErrorModal" class="close-button">&times;</span>
        <h2>Ошибка</h2>
        <p id="errorMessage"></p>
    </div>
</div>

    </body>
    </html>

    <script>
        
    // Привязываем обработчик к кнопке отправки
    document.addEventListener('DOMContentLoaded', function() {
        // Подключаем обработчик только после загрузки DOM
        document.getElementById('uploadButton').addEventListener('click', handleUpload);
        
        // Проверка соединения при загрузке страницы
        checkConnection();
    });

    // Функция обработки отправки формы с проверкой соединения
    async function handleUpload(event) {
        event.preventDefault(); // Останавливаем отправку формы

        // Проверяем соединение перед отправкой
        const connectionOK = await checkConnection();
        if (!connectionOK) {
            showErrorModal("Не удалось подключиться к серверу. Пожалуйста, попробуйте позже.");
            return;
        }
        
        // Отправка формы, если соединение доступно
        document.getElementById('uploadForm').submit();
    }

    // Функция проверки соединения с сервером
    async function checkConnection() {
        console.log("Проверка соединения с сервером...");

        try {
            // Запрос для проверки соединения
            const response = await fetch('check_connection.php', {
                method: 'GET',
                headers: { 'Cache-Control': 'no-cache' },
            });

            if (!response.ok) {
                throw new Error("Ошибка сети: не удалось подключиться.");
            }

            const data = await response.json();
            console.log("Ответ сервера:", data);

            if (!data.success) {
                throw new Error(data.message || "Ошибка соединения с сервером.");
            }

            return true; // Соединение успешно
        } catch (error) {
            console.error("Ошибка при проверке соединения:", error.message);
            showErrorModal("Ошибка подключения к серверу. Попробуйте позже.");
            return false;
        }
    }

    // Функция отображения ошибки в модальном окне
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

        // Закрываем модальное окно при клике вне его
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        };
    }

</script>

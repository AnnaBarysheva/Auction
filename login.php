<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход</title>
    <link rel="stylesheet" href="style.css"> 
</head>
<body>
    <header>
        <div class="header-left">
        <img src="https://cdn-icons-png.flaticon.com/512/10613/10613919.png" alt="Art Gallery Logo" class="logo">
        <!-- <a href="index.php"><img src="https://cdn-icons-png.flaticon.com/512/10613/10613919.png" alt="Art Gallery Logo" class="logo"></a> -->
            <h1>HueHaven</h1>
        </div>
        <div class="header-right">
            <div class="return-home">
                <a href="index.php" class="header-button">Вернуться на главную</a>
            </div>
        </div>

    </header>

    <main>
       
        <div class="login-container">
            <h2>Вход в аккаунт</h2>
            <form id="loginForm" class="login-form">
                <input type="text" id="username" name="username" class="modal-input" required autocomplete="username" placeholder="Введите логин">
                <input type="password" id="password" name="password" class="modal-input" required autocomplete="current-password" placeholder="Введите пароль">
                <div class="button-container">
                    <button type="submit" class="login-button">Войти</button>
                    <button type="button" class="header-button alt-button" id="href-register-button" onclick="handleRegister()">Зарегистрироваться</button>
                </div>
            </form>
        </div>
    </main>


    <!-- Модальное окно ошибки -->
<div id="errorModal" class="modal" style="display:none;">
    <div class="modal-content">
        <span id="closeErrorModal" class="close-button">&times;</span>
        <h2>Ошибка</h2>
        <p id="errorMessage"></p>
    </div>
</div>

<!-- Модальное окно для сообщений -->
<div id="messageModal" class="modal" style="display:none;">
    <div class="modal-content">
        <span id="closeMessageModal" class="close-button">&times;</span>
        <h2 id="modalTitle"></h2>
        <p id="modalMessage"></p>
    </div>
</div>

</body>
</html>


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
        showErrorModal("Ошибка подключения к серверу. Попробуйте позже. ");
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
       // Обработчик для кнопки "Войти"
    document.getElementById('loginForm').addEventListener('submit', async function(event) {
        event.preventDefault(); // Отменяем стандартное действие отправки формы

        const formData = new FormData(this);
        const response = await fetch('login_handler.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();
        if (result.success) {
            window.location.href = result.redirect; // Перенаправляем на страницу, откуда пришел
        } else {
            showErrorModal(result.message); // Показываем сообщение об ошибке
        }
    });


        // Обработчик для кнопки "Зарегистрироваться"
        async function handleRegister() {
            await handleWithConnection(() => {
                location.href = 'register.php';
            });
        }

        //   // Обработчик отправки формы
        //   document.getElementById('registerForm').addEventListener('submit', async function(event) {
        //     event.preventDefault(); // Отменяем стандартное действие отправки формы
        //     await handleWithConnection(() => {
        //         document.getElementById('registerForm').submit(); // Отправляем форму, если соединение успешно
        //     });
        // });
</script>
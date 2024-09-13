<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration</title>
    <link rel="stylesheet" href="style.css"> 
</head>
<body>

<header>
        <div class="header-left">
            <!-- <img src="https://cdn-icons-png.flaticon.com/512/10613/10613919.png" alt="Art Gallery Logo" class="logo"> -->
            <a href="index.php"><img src="https://cdn-icons-png.flaticon.com/512/10613/10613919.png" alt="Art Gallery Logo" class="logo"></a>
            <h1>HueHaven</h1>
        </div>
        <div class="header-right">
            <div class="return-home">
                <a href="index.php" class="header-buttonы">Вернуться на главную</a>
            </div>
        </div>
</header>        

<div class="registration-container">
    <h2>Регистрация</h2>
    <form id="registerForm" class="register-form">
    <input type="text" id="name" name="name" class="modal-input" required autocomplete="name" placeholder="Введите имя">

    <input type="text" id="username" name="username" class="modal-input" required autocomplete="username" placeholder="Введите логин">

    <input type="password" id="password" name="password" class="modal-input" required autocomplete="new-password" placeholder="Введите пароль">

    <input type="password" id="confirm_password" name="confirm_password" class="modal-input" required autocomplete="new-password" placeholder="Повторите введённый пароль">

    <div class="button-container">
        <button type="submit" class="register-button">Зарегистрироваться</button>
        <button type="button" class="header-button alt-button" id="href-login-button" onclick="handleLogin()">Уже есть аккаунт?</button>
    </div>
</form>

</div>


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
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('registerForm').addEventListener('submit', async function(event) {
        event.preventDefault(); // Отменяем стандартное действие отправки формы

        const formData = new FormData(this);
        const response = await fetch('register_handler.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();
        if (result.success) {
            showMessageModal("Успех", result.message);
            setTimeout(() => {
                window.location.href = 'index.php';
            }, 2000);
        } else {
            showMessageModal("Ошибка", result.message);
        }
    });

    function showMessageModal(title, message) {
        document.getElementById('modalTitle').textContent = title;
        document.getElementById('modalMessage').textContent = message;
        document.getElementById('messageModal').style.display = 'block';
    }

    document.getElementById('closeMessageModal').onclick = function() {
        document.getElementById('messageModal').style.display = 'none';
    };

    // Закрытие модального окна при клике вне его
    document.getElementById('messageModal').onclick = function(event) {
        if (event.target === this) { // Проверяем, был ли клик по модальному фону
            this.style.display = 'none';
        }
    };
});
</script>

<script>
// Функция проверки соединения с сервером
async function checkConnection() {
    console.log("Checking connection to the server...");
    try {
        const response = await fetch('check_connection.php');
        console.log("Response from server: ", response);
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        const data = await response.json();
        console.log("Data received from server: ", data);
        if (!data.success) {
            throw new Error(data.message || "Unknown error from server.");
        }
        return true;
    } catch (error) {
        console.error("Error in checkConnection: ", error);
        showErrorModal("Ошибка подключения к серверу. Попробуйте позже.");
        return false;
    }
}

// Функция для отображения ошибки в модальном окне
function showErrorModal(message) {
    var modal = document.getElementById('errorModal');
    var errorMessage = document.getElementById('errorMessage');
    var closeModal = document.getElementById('closeErrorModal');
    errorMessage.textContent = message;
    modal.style.display = 'block';
    closeModal.onclick = function() {
        modal.style.display = 'none';
    };
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
        return;
    }
    callback();
}

// Обработчик для кнопки "Войти"
async function handleLogin() {
    await handleWithConnection(() => {
        location.href = 'login.php';
    });
}

// // Обработчик отправки формы
// document.getElementById('registerForm').addEventListener('submit', async function(event) {
//     event.preventDefault(); // Отменяем стандартное действие отправки формы
//     await handleWithConnection(() => {
//         document.getElementById('registerForm').submit(); // Отправляем форму, если соединение успешно
//     });
// });

// Функция для проверки соединения при загрузке страницы
async function checkConnectionOnLoad() {
    const connectionOK = await checkConnection();
    if (!connectionOK) {
        showErrorModal("Ошибка подключения к серверу. Попробуйте позже.");
    }
}

// Проверка соединения при загрузке страницы
document.addEventListener('DOMContentLoaded', checkConnectionOnLoad);

</script>
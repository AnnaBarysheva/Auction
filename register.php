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
                <!-- <a href="index.php" class="header-button">Вернуться на главную</a> -->
                <button type="button" class="header-button" id="return-home-button" onclick="handleReturnHome()">Вернуться на главную</button>
            </div>
        </div>
</header>        

<div class="registration-container">
    <h2>Регистрация</h2>
    <form id="registerForm" class="register-form">
    <input type="text" id="name" name="name" class="modal-input" required autocomplete="name" placeholder="Введите имя" maxlength="255">

    <input type="text" id="username" name="username" class="modal-input" required autocomplete="username" placeholder="Введите логин" maxlength="255">

    <input type="password" id="password" name="password" class="modal-input" required autocomplete="new-password" placeholder="Введите пароль" minlength="8">

    <input type="password" id="confirm_password" name="confirm_password" class="modal-input" required autocomplete="new-password" placeholder="Повторите введённый пароль">

    <select id="role" name="role" class="modal-input" required>
            <option value="" disabled selected>Выберите роль</option>
            <option value="buyer">Покупатель</option>
            <option value="seller">Продавец</option>
        </select>

    <div class="button-container">
        <button type="submit" class="register-button">Зарегистрироваться</button>
        <button type="button" class="header-button alt-button" id="href-login-button" onclick="handleLogin()">Уже есть аккаунт?</button>
    </div>
</form>

</div>
<style>
    #role option[value=""][disabled] {
        color: #ccc; /* Светло-серый цвет */
    }
</style>

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
    // Запретить ввод пробелов в поля
    const inputs = ['name', 'username', 'password', 'confirm_password'];
    inputs.forEach(id => {
        document.getElementById(id).addEventListener('keydown', function(event) {
            if (event.key === ' ') {
                event.preventDefault(); // Запретить ввод пробела
            }
        });
    });
    
    document.getElementById('registerForm').addEventListener('submit', async function(event) {
        event.preventDefault(); // Отменяем стандартное действие отправки формы

        if (!validateInput()) return; 

        await handleWithConnection(async () => {
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

    // Проверка на наличие только пробелов
    function validateInput() {
        const name = document.getElementById('name').value.trim();
        const username = document.getElementById('username').value.trim();
        const password = document.getElementById('password').value.trim();
        const confirmPassword = document.getElementById('confirm_password').value.trim();

        if (!name || !username || !password || !confirmPassword) {
            alert('Пожалуйста, заполните все поля, не оставляя только пробелы.');
            return false; // Останавливаем дальнейшее выполнение
        }

        // Дополнительная проверка для логина
        if (username.length === 0) {
            alert('Логин не может состоять только из пробелов.');
            return false;
        }

        return true; // Валидация прошла успешно
    }

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
// Обработчик для кнопки "Вернуться на главную"
async function handleReturnHome() {
    await handleWithConnection(() => {
        location.href = 'index.php';
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
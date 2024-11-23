<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HueHaven - Art Auction</title>
    <link rel="stylesheet" href="style.css">

    <!-- Other head content like icon links or meta tags -->
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
                <!-- <a href="index.php" class="header-button">Вернуться на главную</a> -->
                <button type="button" class="header-button" id="return-home-button" onclick="handleReturnHome()">Вернуться на главную</button>
            </div>
        </div>

    </header>


<?php
session_start();
//todo
//добавть алерт, если файл в uploads изменили (на битый файл)
// Функция для замера времени выполнения SQL-запроса
function executeQueryAndMeasureTime($link, $sql) {
    // Замеряем время начала выполнения запроса
    $startTime = microtime(true);

    // Выполняем запрос
    $result = mysqli_query($link, $sql);

    // Замеряем время окончания выполнения запроса
    $endTime = microtime(true);

    // Вычисляем время выполнения в миллисекундах
    $executionTime = ($endTime - $startTime) * 1000;

    // Выводим время выполнения запроса
    echo "Время выполнения запроса: " . $executionTime . " мс<br>";

    return $result;
}

// Проверка, передан ли параметр id_painting
if (isset($_GET['id_painting'])) {
    $id_painting = intval($_GET['id_painting']); // Преобразование в целое число для безопасности
} else {
    die("Ошибка: id_painting не передан.");
}

// Подключение к базе данных
    // $link = mysqli_connect("localhost", "root", "alina", "Auction");
// $link = mysqli_connect("localhost", "root", "root_Passwrd132", "Auction");
$link = include 'db_connect.php';

if ($link == false) {
    die("Ошибка: Невозможно подключиться к MySQL " . mysqli_connect_error());
}

// SQL-запрос для получения полной информации о картине
$sql = "
 SELECT Paintings.paint_name, Paintings.size, 
           Styles.style_name, Paintings.id_style, Materials.material_name, 
           Paintings.creation_year, Paintings.author, Paintings.image_path, 
           Sellers.full_name AS seller_name, Sellers.phone AS seller_phone, Sellers.email AS seller_email,
           Auctions.start_date, Auctions.end_date,  
           PaintingsOnAuction.starting_price, PaintingsOnAuction.purchase_price, PaintingsOnAuction.lot_number 
    FROM Paintings
    JOIN Sellers ON Paintings.id_seller = Sellers.id_seller
    LEFT JOIN PaintingsOnAuction ON Paintings.id_painting = PaintingsOnAuction.id_painting
    LEFT JOIN Auctions ON PaintingsOnAuction.id_auction = Auctions.id_auction
    LEFT JOIN Styles ON Paintings.id_style = Styles.id_style
    LEFT JOIN Materials ON Paintings.id_material = Materials.id_material
    WHERE Paintings.id_painting = $id_painting
";
// Измерение начального использования памяти
// $startMemory = memory_get_usage();
//  $sql = "
//     SELECT 
//         Paintings.paint_name, 
//         Paintings.size, 
//         Styles.style_name, 
//         Paintings.id_style, 
//         Materials.material_name, 
//         Paintings.creation_year, 
//         Paintings.author, 
//         Paintings.image_path, 
//         Sellers.full_name AS seller_name, 
//         Sellers.phone AS seller_phone, 
//         Sellers.email AS seller_email,
//         Auctions.start_date, 
//         Auctions.end_date,  
//         PaintingsOnAuction.starting_price, 
//         PaintingsOnAuction.purchase_price, 
//         PaintingsOnAuction.lot_number 
//     FROM 
//         Paintings
//     INNER JOIN Sellers ON Paintings.id_seller = Sellers.id_seller
//     INNER JOIN PaintingsOnAuction ON Paintings.id_painting = PaintingsOnAuction.id_painting
//     INNER JOIN Auctions ON PaintingsOnAuction.id_auction = Auctions.id_auction
//     INNER JOIN Styles ON Paintings.id_style = Styles.id_style
//     INNER JOIN Materials ON Paintings.id_material = Materials.id_material
//     WHERE 
//         Paintings.id_painting = $id_painting;

// ";

// executeQueryAndMeasureTime($link, $sql);


$result = mysqli_query($link, $sql);
// Измерение конечного использования памяти
// $endMemory = memory_get_usage();
// $peakMemory = memory_get_peak_usage();

// // Вывод результатов
// echo "Начальное использование памяти: " . ($startMemory / 1024) . " KB<br>";
// echo "Конечное использование памяти: " . ($endMemory / 1024) . " KB<br>";
// echo "Пиковое использование памяти: " . ($peakMemory / 1024) . " KB<br>";

if ($result && mysqli_num_rows($result) > 0) {
    $painting = mysqli_fetch_assoc($result); // Сохраняем данные картины в переменной


    if (isset($_SESSION['user_id'])) {
        $id_user = $_SESSION['user_id'];
        $id_style = $painting['id_style'];
    
        // Проверка, есть ли уже запись
        $check_sql = "SELECT count FROM UserActivity WHERE id_user = $id_user AND id_style = $id_style";
        $result = mysqli_query($link, $check_sql);
    
        if (mysqli_num_rows($result) > 0) {
            // Обновление счётчика
            $update_sql = "UPDATE UserActivity SET count = count + 1 WHERE id_user = $id_user AND id_style = $id_style";
            mysqli_query($link, $update_sql);
        } else {
            // Вставка новой записи
            $insert_sql = "INSERT INTO UserActivity (id_user, id_style, count) VALUES ($id_user, $id_style, 1)";
            mysqli_query($link, $insert_sql);
        }
    }

    

} else {
    die("Картина с таким id не найдена или ошибка запроса.");
}

// Закрываем соединение
mysqli_close($link);
?>



<div class="painting-info">
    <!-- Вывод изображения картины -->
    <!-- <img src="<?= htmlspecialchars($painting['image_path']) ?>" alt="<?= htmlspecialchars($painting['paint_name']) ?>" class="painting-img"> -->
    <img src="<?= htmlspecialchars($painting['image_path']) ?>" 
         alt="<?= htmlspecialchars($painting['paint_name']) ?>" 
         class="painting-img" 
         onerror="handleImageError(this)" 
         onload="handleImageLoad(this)">
    <!-- Вывод информации о картине -->
    <div class="painting-details">
    <h1><?= htmlspecialchars($painting['paint_name']) ?></h1>
    <p style="color: #00CED1; font-style: italic;">Лот № <?= htmlspecialchars($painting['lot_number']) ?></p>  
    <p><strong>Размер:</strong> <?= htmlspecialchars($painting['size']) ?></p> 
    
    <p><strong>Материалы:</strong>  
        <span style="color: <?= $painting['material_name'] ? 'black' : 'red' ?>;">
            <?= htmlspecialchars($painting['material_name'] ?? 'Материал недоступен') ?>
        </span>
    </p>
    <p><strong>Стиль:</strong>  
        <span style="color: <?= $painting['style_name'] ? 'black' : 'red' ?>;">
            <?= htmlspecialchars($painting['style_name'] ?? 'Стиль недоступен') ?>
        </span>
    </p>
    <p><strong>Год создания:</strong> <?= htmlspecialchars($painting['creation_year']) ?></p>
    <p><strong>Автор:</strong> <?= htmlspecialchars($painting['author']) ?></p> 
    <div class="price-info">
        <div class="price-details">
            <p><strong>Начальная цена:</strong> <?= htmlspecialchars($painting['starting_price']) ?></p>
            <p><strong>Текущая цена:</strong> <?= htmlspecialchars($painting['purchase_price'] ?? '--') ?></p>
        </div>
        <!-- <form action="index.php" method="get">
            <input type="hidden" name="id_painting" value="<?= htmlspecialchars($painting['id_painting']) ?>"> -->
            <button type="submit" class="bid-button">Сделать ставку</button>
        <!-- </form> -->
    </div>
</div>

</div>   
<div class="other-info">    
    <!-- Вывод информации о продавце -->
    <div class="seller-info">
        <!-- <h2>Информация о продавце</h2> -->
        <p><strong>Имя продавца:</strong> <?= htmlspecialchars($painting['seller_name']) ?></p>
        <p><strong>Телефон продавца:</strong> <?= htmlspecialchars($painting['seller_phone']) ?></p>
        <p><strong>Email продавца:</strong> <?= htmlspecialchars($painting['seller_email']) ?></p>
    </div>

    <!-- Вывод информации об аукционе -->
    <div class="auction-info">
        <!-- <h2>Информация об аукционе</h2> -->
        <p><strong>Дата начала аукциона:</strong> <?= htmlspecialchars($painting['start_date']) ?></p>
        <p><strong>Дата окончания аукциона:</strong> <?= htmlspecialchars($painting['end_date']) ?></p>

        <?php if ($painting['purchase_price']): ?>
            <p><strong>Цена покупки:</strong> $<?= htmlspecialchars($painting['purchase_price']) ?></p>
        <?php else: ?>
            <p>Картина ещё не продана.</p>
        <?php endif; ?>
    </div>
</div>
 
<!-- Модальное окно для предложения цены -->
<div id="priceProposalModal" class="modal">
    <div class="modal-content">
        <span class="close-button">&times;</span>
        <h2>Предложение своей цены к лоту №<?= htmlspecialchars($painting['lot_number']) ?></h2>
        <form id="priceProposalForm" action="submit_bid.php" method="post">
            <!-- <input type="hidden" name="id_painting" value="<?= htmlspecialchars($painting['id_painting']) ?>"> -->           
            <input type="number" name="bid_price" id="bid_price" placeholder="Введите вашу цену" required>
            <button type="submit" class="bid-button">Предложить цену</button>
        </form>
    </div>
</div>

    <!-- Модальное окно ошибки -->
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


// Обработчик для ошибки изображения
async function handleImageError(img) {
    if (!img.hasAttribute('data-error-handled')) {
        // Попробуем запросить файл напрямую через fetch
        try {
            const response = await fetch(img.src, { method: 'HEAD' }); // Только проверяем заголовки
            if (!response.ok) {
                alert("Ошибка: Доступ к изображению закрыт. Пожалуйста, обратитесь к администратору.");
            } else {
                alert("Ошибка: Изображение повреждено. Пожалуйста, обратитесь к администратору.");
            }
        } catch (error) {
            // Ошибки сети (например, файл не найден)
            alert("Ошибка: Не удалось загрузить изображение. Проверьте соединение или обратитесь к администратору.");
        }

        img.src = "path/to/placeholder-image.jpg"; // Путь к изображению-заглушке
        img.alt = "Изображение недоступно";
        img.setAttribute('data-error-handled', 'true');
    }
}


function handleImageLoad(img) {
    console.log("Изображение успешно загружено:", img.src);
}

// Получаем элементы модального окна и кнопки
var modal = document.getElementById("priceProposalModal");
var btn = document.querySelector(".bid-button");
var closeButton = document.querySelector(".close-button");

// Открытие модального окна при нажатии на кнопку "Сделать ставку"
btn.addEventListener("click", function(event) {
    event.preventDefault(); // Предотвращаем переход по ссылке
    modal.style.display = "block"; // Показываем модальное окно
});

// Закрытие модального окна при нажатии на кнопку закрытия
closeButton.addEventListener("click", function() {
    modal.style.display = "none"; // Скрываем модальное окно
});

// Закрытие модального окна при клике вне его
window.addEventListener("click", function(event) {
    if (event.target === modal) {
        modal.style.display = "none"; // Скрываем модальное окно
    }
});

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


// Обработчик для кнопки "Вернуться на главную"
async function handleReturnHome() {
    await handleWithConnection(() => {
        location.href = 'index.php';
    });
    }
</script>


<!-- if ($result) {
    // Проверка, есть ли записи в результате запроса
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        
        // Выводим подробную информацию о картине
        echo "<div class='painting-info'>";
        echo "<img src='" . $row['image_path'] . "' alt='" . $row['paint_name'] . "' class='painting-img'>";
        
        echo "<div class='painting-details'>";
        echo "<h1>" . $row['paint_name'] . "</h1>";
        echo "<p><strong>Размер:</strong> " . $row['size'] . "</p>";
        echo "<p><strong>Материалы:</strong> " . $row['materials'] . "</p>";
        echo "<p><strong>Стиль:</strong> " . $row['style'] . "</p>";
        echo "<p><strong>Год создания:</strong> " . $row['creation_year'] . "</p>";
        echo "<p><strong>Автор:</strong> " . $row['author'] . "</p>";
        echo "</div>";
        echo "</div>";

        echo "<div class='painting-details'>";
        echo "<div class='seller-info'>";
        echo "<h2>Информация о продавце</h2>";
        echo "<p><strong>Имя продавца:</strong> " . $row['seller_name'] . "</p>";
        echo "<p><strong>Телефон продавца:</strong> " . $row['seller_phone'] . "</p>";
        echo "<p><strong>Email продавца:</strong> " . $row['seller_email'] . "</p>";
        echo "</div>";

        echo "<div class='auction-info'>";
        echo "<h2>Информация об аукционе</h2>";
        echo "<p><strong>Дата начала аукциона:</strong> " . $row['start_date'] . "</p>";
        echo "<p><strong>Дата окончания аукциона:</strong> " . $row['end_date'] . "</p>";
        
        if ($row['purchase_price']) {
            echo "<p><strong>Цена покупки:</strong> $" . $row['purchase_price'] . "</p>";
        } else {
            echo "<p>Картина ещё не продана.</p>";
        }
        echo "</div>";

    } else {
        echo "Картина с таким id не найдена.";
    }

    // Освобождаем память, занятую результатом запроса
    mysqli_free_result($result);
} else {
    echo "Ошибка выполнения запроса: " . mysqli_error($link);
}

// Закрываем соединение
mysqli_close($link);
?> -->
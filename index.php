<?php
session_start(); // Начинаем сессию в начале файла

// Проверяем, установлен ли user_id
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = null; // Устанавливаем в null, если не установлен
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HueHaven - Art Auction</title>
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
            <?php if (isset($_SESSION['user_id'])): ?>
                <!-- Если пользователь вошел, показываем кнопку "Выход" -->
                <button class="header-button" onclick="handleLogout()">Выход</button>
            <?php else: ?>
                <!-- Если пользователь не вошел, показываем кнопки "Войти" и "Зарегистрироваться" -->
                <button class="header-button" onclick="handleLogin()">Войти</button>
                <button class="header-button" onclick="handleRegister()">Зарегистрироваться</button>
            <?php endif; ?>
        </div>
    </header>

    <!-- <div class="input-container">
    <div class="input-group">
        <input type="text" id="nameInput" placeholder="Название картины">
        <input type="text" id="styleInput" placeholder="Стиль">
        <input type="text" id="yearInput" placeholder="Год создания">
    </div>
    <div class="input-group">
        <input type="text" id="authorInput" placeholder="Автор">
        <input type="text" id="sellerInput" placeholder="Продавец">
        <div class="button-group">
            <button id="searchButton">Найти</button>
            <button id="resetButton">Сбросить фильтры</button>
        </div>
    </div> -->
    
    
</div>

<?php
// $link = mysqli_connect("localhost", "root", "alina", "Auction");
// $link = mysqli_connect("localhost", "root", "root_Passwrd132", "Auction");

// Подключение к базе данных
$link = include 'db_connect.php';

if ($link == false) {
    die("Ошибка: Невозможно подключиться к MySQL " . mysqli_connect_error());
}

// Проверка роли пользователя
$isAdmin = false;
$isSeller = false;
$isUser = false;
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    $query = "SELECT role FROM Users WHERE id_user = $userId";
    $result = mysqli_query($link, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        $isSeller = ($user['role'] === 'seller');
        $isAdmin = ($user['role'] === 'admin');
        $isUser = ($user['role'] === 'user');
    }
}


// Получение всех заявок пользователя, если он является обычным пользователем (user)
$userRequests = [];
if ($isUser) {
    $sqlRequests = "SELECT id_painting FROM PaintingUser WHERE id_user = ?";
    $stmtRequests = mysqli_prepare($link, $sqlRequests);
    mysqli_stmt_bind_param($stmtRequests, 'i', $userId);
    mysqli_stmt_execute($stmtRequests);
    $resultRequests = mysqli_stmt_get_result($stmtRequests);

    // Сохраняем ID всех заявок в массив
    while ($row = mysqli_fetch_assoc($resultRequests)) {
        $userRequests[] = $row['id_painting'];
    }
    mysqli_stmt_close($stmtRequests);

    //  echo "<pre>";
    //     print_r($userRequests);
    //     echo "</pre>";
}
// Подсчет предпочтений пользователя
$stylePreferences = [];
if ($isUser) {
    $sqlPreferences = "
        SELECT id_style, count
        FROM UserActivity
        WHERE id_user = ?
        ORDER BY count DESC
    ";
    $stmtPreferences = mysqli_prepare($link, $sqlPreferences);
    mysqli_stmt_bind_param($stmtPreferences, 'i', $userId);
    mysqli_stmt_execute($stmtPreferences);
    $resultPreferences = mysqli_stmt_get_result($stmtPreferences);

    // Получаем ID стилей и количество для каждого стиля
    while ($row = mysqli_fetch_assoc($resultPreferences)) {
        $stylePreferences[] = $row['id_style'];
    }
    mysqli_stmt_close($stmtPreferences);

    // Отладочный вывод для проверки
    // echo "<pre>";
    // print_r($stylePreferences);
    // echo "</pre>";
}


 // Отображение полей для поиска только если пользователь не администратор
 if (!$isAdmin) {
    ?>
    <div class="input-container">
        <div class="input-group">
            <input type="text" id="nameInput" placeholder="Название картины">
            <input type="text" id="styleInput" placeholder="Стиль">
            <input type="text" id="materialInput" placeholder="Материал">
            <input type="text" id="yearInput" placeholder="Год создания">
        </div>
        <div class="input-group">
            <input type="text" id="authorInput" placeholder="Автор">
            <input type="text" id="sellerInput" placeholder="Продавец">
            <div class="button-group">
                <button id="searchButton">Найти</button>
                <button id="resetButton">Сбросить фильтры</button>
            </div>
        </div>
    </div>
    <?php
    } 
?>

<?php


// // SQL-запрос для получения стилей из таблицы Styles
// $stylesQuery = "SELECT * FROM Styles";
// $stylesResult = mysqli_query($link, $stylesQuery);
// if (!$stylesResult) {
//     die("Ошибка выполнения запроса стилей: " . mysqli_error($link));
// }

// SQL-запрос для получения стилей из таблицы Styles
$stylesQuery = "SELECT * FROM Styles";
$stylesResult = mysqli_query($link, $stylesQuery);

if (!$stylesResult) {
    die("Ошибка выполнения запроса стилей: " . mysqli_error($link));
}
// Сохраняем стили в массив
$stylesArray = [];
while ($row = mysqli_fetch_assoc($stylesResult)) {
    $stylesArray[] = $row;
}

// Выполняем запрос к базе данных для получения списка материалов
$materialsQuery = "SELECT * FROM Materials";
$materialsResult = mysqli_query($link, $materialsQuery);

if (!$materialsResult) {
    die("Ошибка выполнения запроса материалов: " . mysqli_error($link));
}

// Преобразование результата в массив (с теми же ключами, что и для стилей)
$materialsArray = [];
while ($row = mysqli_fetch_assoc($materialsResult)) {
    $materialsArray[] = [
        'id_style' => $row['id_material'], // Используем тот же ключ 'id_style'
        'style_name' => $row['material_name'] // Используем тот же ключ 'style_name'
    ];
}

// SQL-запрос в зависимости от роли пользователя
if ($isSeller) {
    $sql = "
        SELECT Paintings.*, PaintingsOnAuction.starting_price, Sellers.full_name, 
               Styles.style_name, Materials.material_name
        FROM Paintings
        JOIN PaintingsOnAuction ON Paintings.id_painting = PaintingsOnAuction.id_painting
        JOIN Sellers ON Paintings.id_seller = Sellers.id_seller
        JOIN Auctions ON PaintingsOnAuction.id_auction = Auctions.id_auction
        LEFT JOIN Styles ON Paintings.id_style = Styles.id_style -- Используем LEFT JOIN для стилей
        LEFT JOIN Materials ON Paintings.id_material = Materials.id_material -- Используем LEFT JOIN для материалов
        WHERE Paintings.id_user = {$_SESSION['user_id']}
    ";
} elseif (!$isAdmin) {
    // Когда оба массива непустые
    if (!empty($stylePreferences) && !empty($userRequests)) { 
        $styleOrder = implode(',', $stylePreferences);
        $userRequestsList = implode(',', $userRequests);

        $sql = "
            SELECT Paintings.*, PaintingsOnAuction.starting_price, Sellers.full_name, 
                Styles.style_name, Materials.material_name,
                (CASE WHEN Styles.id_style IN ($styleOrder) THEN 1 ELSE 0 END) as is_interesting,
                (CASE WHEN Paintings.id_painting IN ($userRequestsList) THEN 1 ELSE 0 END) as has_request
            FROM Paintings
            JOIN PaintingsOnAuction ON Paintings.id_painting = PaintingsOnAuction.id_painting
            JOIN Sellers ON Paintings.id_seller = Sellers.id_seller
            JOIN Auctions ON PaintingsOnAuction.id_auction = Auctions.id_auction
            JOIN Styles ON Paintings.id_style = Styles.id_style
            JOIN Materials ON Paintings.id_material = Materials.id_material
            WHERE Paintings.is_sold = FALSE
            AND Auctions.start_date <= CURDATE()
            AND Auctions.end_date >= CURDATE()
            ORDER BY is_interesting DESC,
                    FIELD(Styles.id_style, $styleOrder) ASC,  
                    has_request ASC, 
                    Paintings.creation_year DESC
        ";

    // Когда непустой только массив стилей
    } elseif (!empty($stylePreferences)) {
        $styleOrder = implode(',', $stylePreferences);

        $sql = "
            SELECT Paintings.*, PaintingsOnAuction.starting_price, Sellers.full_name, 
                Styles.style_name, Materials.material_name,
                (CASE WHEN Styles.id_style IN ($styleOrder) THEN 1 ELSE 0 END) as is_interesting
            FROM Paintings
            JOIN PaintingsOnAuction ON Paintings.id_painting = PaintingsOnAuction.id_painting
            JOIN Sellers ON Paintings.id_seller = Sellers.id_seller
            JOIN Auctions ON PaintingsOnAuction.id_auction = Auctions.id_auction
            JOIN Styles ON Paintings.id_style = Styles.id_style
            JOIN Materials ON Paintings.id_material = Materials.id_material
            WHERE Paintings.is_sold = FALSE
            AND Auctions.start_date <= CURDATE()
            AND Auctions.end_date >= CURDATE()
            ORDER BY is_interesting DESC,
                    FIELD(Styles.id_style, $styleOrder) ASC,  
                    Paintings.creation_year DESC
        ";

    // Когда непустой только массив заявок
    } elseif (!empty($userRequests)) {
        // Преобразуем $userRequests в строку, разделенную запятыми для SQL-запроса
        $userRequestsList = implode(',', $userRequests);
       

        // Запрос для подсчета количества заявок по стилям
        $styleCountQuery = "
            SELECT Styles.id_style, COUNT(*) as style_count
            FROM Paintings
            JOIN Styles ON Paintings.id_style = Styles.id_style
            WHERE Paintings.id_painting IN ($userRequestsList)
            GROUP BY Styles.id_style
            ORDER BY style_count ASC
        ";

        $styleCountResult = mysqli_query($link, $styleCountQuery);
        $styleOrder = [];

        // Собираем порядок стилей по количеству заявок
        while ($row = mysqli_fetch_assoc($styleCountResult)) {
            $styleOrder[] = $row['id_style'];
        }
        // echo "<pre>";
        // print_r($styleOrder);
        // echo "</pre>";

        // Преобразуем $styleOrder в строку для использования в FIELD()
        $styleOrderString = implode(',', $styleOrder);

        // Основной запрос для вывода картин
        $sql = "
            SELECT Paintings.*, PaintingsOnAuction.starting_price, Sellers.full_name, 
                Styles.style_name, Materials.material_name,
                (CASE WHEN Paintings.id_painting IN ($userRequestsList) THEN 1 ELSE 0 END) as has_request
            FROM Paintings
            JOIN PaintingsOnAuction ON Paintings.id_painting = PaintingsOnAuction.id_painting
            JOIN Sellers ON Paintings.id_seller = Sellers.id_seller
            JOIN Auctions ON PaintingsOnAuction.id_auction = Auctions.id_auction
            JOIN Styles ON Paintings.id_style = Styles.id_style
            JOIN Materials ON Paintings.id_material = Materials.id_material
            WHERE Paintings.is_sold = FALSE
            AND Auctions.start_date <= CURDATE()
            AND Auctions.end_date >= CURDATE()
            ORDER BY FIELD(Styles.id_style, $styleOrderString) DESC, 
                    has_request ASC, 
                    Paintings.creation_year DESC
        ";
    
    // Стандартный запрос, если у пользователя нет предпочтений
    } else {
        $sql = "
            SELECT Paintings.*, PaintingsOnAuction.starting_price, Sellers.full_name, 
                   Styles.style_name, Materials.material_name
            FROM Paintings
            JOIN PaintingsOnAuction ON Paintings.id_painting = PaintingsOnAuction.id_painting
            JOIN Sellers ON Paintings.id_seller = Sellers.id_seller
            JOIN Auctions ON PaintingsOnAuction.id_auction = Auctions.id_auction
            JOIN Styles ON Paintings.id_style = Styles.id_style
            JOIN Materials ON Paintings.id_material = Materials.id_material
            WHERE Paintings.is_sold = FALSE
            AND Auctions.start_date <= CURDATE()
            AND Auctions.end_date >= CURDATE()
        ";
    }
}



// Запрос для получения стилей
$stylesQuery = "SELECT id_style AS id, style_name AS name FROM Styles";
$stylesResult = mysqli_query($link, $stylesQuery);

// Получение материалов
$materialsQuery = "SELECT id_material AS id, material_name AS name FROM Materials";
$materialsResult = mysqli_query($link, $materialsQuery);

// Проверьте, что оба результата не равны false
if (!$stylesResult || !$materialsResult) {
    die("Ошибка: Невозможно получить данные из базы данных. " . mysqli_error($link));
}





// Функция для создания выпадающего списка
function createDropdown($result, $dropdownId, $defaultOptionText) {
    $dropdown = "<select name='{$dropdownId}' id='{$dropdownId}' required>";
    $dropdown .= "<option value=''>{$defaultOptionText}</option>";
    
    while ($row = mysqli_fetch_assoc($result)) {
        $dropdown .= "<option value='{$row['id']}'>{$row['name']}</option>";
    }
    
    $dropdown .= "</select>";
    return $dropdown;
}

// function createDropdownFromArray($dataArray, $dropdownId, $defaultOptionText) {
//     $dropdown = "<select name='{$dropdownId}' id='{$dropdownId}' required>";
//     $dropdown .= "<option value=''>{$defaultOptionText}</option>";
    
//     foreach ($dataArray as $row) {
//         // Используем правильные ключи
//         if (isset($row['id_style']) && isset($row['style_name'])) {
//             $dropdown .= "<option value='{$row['id_style']}'>{$row['style_name']}</option>";
//         } else {
//             // Логируем или обрабатываем ошибку
//             $dropdown .= "<option value=''>Неверные данные</option>";
//         }
//     }
    
//     $dropdown .= "</select>";
//     return $dropdown;
// }

function createDropdownFromArray($dataArray, $dropdownId, $defaultOptionText, $class = '') {
    // Если класс передан, добавляем его в тег select
    $classAttribute = $class ? "class='{$class}'" : '';
    
    // Создаем тег select с заданным id, именем и классом
    $dropdown = "<select name='{$dropdownId}' id='{$dropdownId}' {$classAttribute} required>";
    $dropdown .= "<option value=''>{$defaultOptionText}</option>";
    
    foreach ($dataArray as $row) {
        // Проверяем, что массив данных содержит ключи 'id_style' и 'style_name'
        if (isset($row['id_style']) && isset($row['style_name'])) {
            $dropdown .= "<option value='{$row['id_style']}'>{$row['style_name']}</option>";
        } else {
            // Логируем или обрабатываем ошибку, если данные некорректны
            $dropdown .= "<option value=''>Неверные данные</option>";
        }
    }
    
    $dropdown .= "</select>";
    return $dropdown;
}




if (!$isAdmin) {
    $result = mysqli_query($link, $sql);

    if ($result) {
        if (mysqli_num_rows($result) > 0) {

            $missingStyleOrMaterial = false;


            if ($isSeller): // Кнопка будет видна только для продавцов ?>
                <button type="button" class="addButton" id="seller-requests-button" onclick="location.href='seller_requests.php'">
                    Заявки на мои картины
                </button>
            <?php endif; 

            if ($isUser): // Кнопка будет видна только для продавцов ?>
                <button type="button" class="addButton" id="user-requests-button" onclick="location.href='my_requests.php'">
                    Просмотреть мои заявки
                </button>
            <?php endif; 


            echo "<div class='table-wrapper'>";
            echo "<table border='1' id='paintingsTable'>";
            echo "<tr>
                    <th>Название картины</th>
                    <th>Стиль</th>
                    <th>Материал</th> <!-- Добавлено поле для материала -->
                    <th>Год создания</th>
                    <th>Автор</th>
                    <th>Продавец</th>";

            // Выводим столбец "Действия", если это продавец
            if ($isSeller) {
                echo "<th>Действия</th>";
            }

                        // Если пользователь - обычный пользователь (user), добавляем столбец "Заявка"
                        if ($isUser) {
                            echo "<th>Заявка</th>";
                        }

            echo "</tr>";

            // Вывод каждой строки данных
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<tr data-id='" . $row['id_painting'] . "'>";
                echo "<td>" . $row['paint_name'] . "</td>";
                    // Проверка, есть ли стиль, если нет — выводим "Стиль недоступен" красным
                    if ($row['style_name']) {
                    echo "<td>" . $row['style_name'] . "</td>";
                } else {
                    echo "<td style='color: red;'>Стиль недоступен</td>";
                    $missingStyleOrMaterial = true; 
                }
                 // Проверка, есть ли материал, если нет — выводим "Материал недоступен" красным
                 if ($row['material_name']) {
                    echo "<td>" . $row['material_name'] . "</td>";
                } else {
                    echo "<td style='color: red;'>Материал недоступен</td>";
                    $missingStyleOrMaterial = true;
                }
                echo "<td>" . $row['creation_year'] . "</td>";
                echo "<td>" . $row['author'] . "</td>";
                echo "<td>" . $row['full_name'] . "</td>";

                // Добавляем кнопки "Редактировать" и "Удалить", если это продавец
                if ($isSeller) {
                    echo "<td>
                            <button class='editButton' data-id='" . $row['id_painting'] . "'>Редактировать</button>
                            <button class='deleteButton' data-id='" . $row['id_painting'] . "'>Удалить</button>
                            </td>";
                }

                 // Если пользователь - обычный пользователь (user), добавляем чекбокс "Заявка"
                 if ($isUser) {
                 // Проверяем, есть ли ID картины в списке заявок пользователя
                 $checked = in_array($row['id_painting'], $userRequests) ? "checked" : "";
                 echo "<td>
                         <input type='checkbox' class='requestCheckbox' data-id='" . $row['id_painting'] . "' $checked>
                          </td>";
                }

                echo "</tr>";
            }
            echo "</table>";

            echo "</div>";

            // Кнопка "Добавить картину", доступная только для продавцов
            if ($isSeller) {
                echo "<div class='button-container'>";
                echo "<button id='addPaintingButton' class='addButton'>Добавить картину</button>";
                echo "</div>";
            }

            // Проверяем флаг, если продавец только что вошел
            if (isset($_SESSION['seller_logged_in']) && $_SESSION['seller_logged_in'] === true) {
                if ($missingStyleOrMaterial) {
                    echo "<script>
                            alert('Внимание: У некоторых ваших картин отсутствуют стиль или материал. Пожалуйста, обновите данные.');
                          </script>";
                }
                // После показа алерта сбрасываем флаг
                unset($_SESSION['seller_logged_in']);
            }

        } else {
            echo "Нет доступных картин для отображения.";
            if ($isSeller) {
                echo "<div class='button-container'>";
                echo "<button id='addPaintingButton' class='addButton'>Добавить картину</button>";
                echo "</div>";
            }
        }

        mysqli_free_result($result);
    } else {
        echo "Ошибка выполнения запроса: " . mysqli_error($link);
    }
}



// Если пользователь администратор, выводим таблицы стилей и материалов
if ($isAdmin) {
    // Запросы на получение данных из таблиц
    $stylesQuery = "SELECT * FROM Styles";
    $stylesResult = mysqli_query($link, $stylesQuery);
    
    $materialsQuery = "SELECT * FROM Materials";
    $materialsResult = mysqli_query($link, $materialsQuery);
    
    // Общий контейнер для таблиц с белой подложкой
    echo "<div class='main-tables-wrapper'>"; // Начало общего контейнера
    
    echo "<div class='tables-container'>"; // Flex-контейнер для обеих таблиц
    
    // Таблица стилей
    if ($stylesResult) {
        echo "<div class='styles-table-container'>"; // Контейнер для таблицы стилей
        
        echo "<table border='1' id='stylesTable'>";
        echo "<tr><th>Стиль</th><th>Действия</th></tr>";
        
        while ($row = mysqli_fetch_assoc($stylesResult)) {
            echo "<tr data-id='" . $row['id_style'] . "'>";
            echo "<td>" . $row['style_name'] . "</td>";
            echo "<td>
                    <button class='editStyleButton' data-id='" . $row['id_style'] . "'>Редактировать</button>
                    <button class='deleteStyleButton' data-id='" . $row['id_style'] . "'>Удалить</button>
                  </td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "</div>"; // Закрываем styles-table-container
    }

    // Таблица материалов
    if ($materialsResult) {
        echo "<div class='materials-table-container'>"; // Контейнер для таблицы материалов
        
        echo "<table border='1' id='materialsTable'>";
        echo "<tr><th>Материал</th><th>Действия</th></tr>";
        
        while ($row = mysqli_fetch_assoc($materialsResult)) {
            echo "<tr data-id='" . $row['id_material'] . "'>";
            echo "<td>" . $row['material_name'] . "</td>";
            echo "<td>
                    <button class='editMaterialButton' data-id='" . $row['id_material'] . "'>Редактировать</button>
                    <button class='deleteMaterialButton' data-id='" . $row['id_material'] . "'>Удалить</button>
                  </td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "</div>"; // Закрываем materials-table-container
    }

    echo "</div>"; // Закрываем flex-контейнер для таблиц

    echo "</div>"; // Закрываем общий div main-tables-wrapper

    // Кнопки для добавления стиля и материала
    echo "<div class='add-button-container'>";
    echo "<button id='addStyleButton' class='addButton'>Добавить стиль</button>";
    echo "<button id='addMaterialButton' class='addButton'>Добавить материал</button>";
    echo "</div>";
}


// Закрытие соединения
mysqli_close($link);
?>


<!-- Модальное окно для добавления стиля -->

<div id="addStyleModal" class="modal">
    <div class="modal-content">
        <span class="close-button" id="closeAddStyleModal">&times;</span>
        <h2>Добавить стиль</h2>
        <form id="styleForm" action="add_style.php" method="POST">
            <label for="style_name">Название стиля:</label>
            <input type="text" class="modal-input" id="styleName" name="style_name" placeholder="Название стиля" required maxlength="100">
            <button type="submit" class="saveButton">Сохранить</button>
        </form>
    </div>
</div>

<div id="addMaterialModal" class="modal">
    <div class="modal-content">
        <span class="close-button" id="closeAddMaterialModal">&times;</span>
        <h2>Добавить материал</h2>
        <form id="materialForm" action="add_material.php" method="POST">
            <label for="material_name">Название материала:</label>
            <input type="text" class="modal-input" id="materialName" name="material_name" placeholder="Название материала" required maxlength="100">
            <button type="submit" class="saveButton">Сохранить</button>
        </form>
    </div>
</div>



<div id="editStyleModal" class="modal">
    <div class="modal-content">
        <span class="close-button" id="closeEditStyleModal">&times;</span>
        <h2>Редактировать стиль</h2>
        <form id="editStyleForm" action="update_style.php" method="POST">
            <input type="hidden" id="editStyleId" name="id_style"> <!-- Скрытое поле для ID стиля -->

            <label for="editStyleName">Название картины:</label>
            <input type="text" class="modal-input" id="editStyleName" name="style_name" placeholder="Введите название стиля" required maxlength="255">

            <button type="submit" class="saveButton">Сохранить изменения</button>
        </form>
    </div>
</div>

<div id="editMaterialModal" class="modal">
    <div class="modal-content">
        <span class="close-button" id="closeEditMaterialModal">&times;</span>
        <h2>Редактировать материал</h2>
        <form id="editMaterialForm" action="update_material.php" method="POST">
            <input type="hidden" id="editMaterialId" name="id_material"> <!-- Скрытое поле для ID материала -->

            <label for="editMaterialName">Название материала:</label>
            <input type="text" class="modal-input" id="editMaterialName" name="material_name" placeholder="Введите название материала" required maxlength="255">

            <button type="submit" class="saveButton">Сохранить изменения</button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {

document.getElementById('addStyleButton').addEventListener('click', function() {
    // Открываем модальное окно
    document.getElementById('addStyleModal').style.display = 'block';
});

document.getElementById('closeAddStyleModal').addEventListener('click', function() {
    // Закрываем модальное окно
    document.getElementById('addStyleModal').style.display = 'none';
});

window.onclick = function(event) {
    // Закрываем модальное окно при клике вне его
    var modal = document.getElementById('addStyleModal');
    if (event.target === modal) {
        modal.style.display = 'none';
    }
};

// Обработчик отправки формы
document.getElementById('styleForm').addEventListener('submit', function(event) {
    event.preventDefault(); // Предотвращаем стандартную отправку формы

    var styleName = document.getElementById('styleName').value.trim();

    // Проверяем на пустое значение или наличие только пробелов
    if (styleName.length === 0) { // Условие изменено для более строгой проверки
        alert('Пожалуйста, введите название стиля.');
        return;
    }

    // Проверка на максимальную длину
    if (styleName.length > 50) { // Здесь длина изменена на 50 символов
        alert('Ошибка: название стиля не может превышать 50 символов.');
        return;
    }


    // Оборачиваем отправку в handleWithConnection (если требуется)
    handleWithConnection(() => {
        // Собираем данные формы
        var formData = new FormData(this);

        fetch('add_style.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            if (data.startsWith('Ошибка:')) {
                alert(data); // Если есть ошибка, выводим её
            } else {
                // Закрываем модальное окно и обновляем страницу или обновляем список стилей
                document.getElementById('addStyleModal').style.display = 'none';
                window.location.reload(); // Обновляем страницу
            }
        })
        .catch(error => {
            console.error('Ошибка:', error);
        });
    });
});

//////////////////////////////////////////////
// Открытие модального окна
document.getElementById('addMaterialButton').addEventListener('click', function() {
    document.getElementById('addMaterialModal').style.display = 'block';
});

// Закрытие модального окна
document.getElementById('closeAddMaterialModal').addEventListener('click', function() {
    document.getElementById('addMaterialModal').style.display = 'none';
});

// Закрытие при клике вне модального окна
window.onclick = function(event) {
    var modal = document.getElementById('addMaterialModal');
    if (event.target === modal) {
        modal.style.display = 'none';
    }
};

// Обработчик отправки формы
document.getElementById('materialForm').addEventListener('submit', function(event) {
    event.preventDefault(); // Предотвращаем стандартную отправку формы

    var materialName = document.getElementById('materialName').value.trim();

    // Проверяем на пустое значение или наличие только пробелов
    if (materialName.length === 0) { // Условие изменено для более строгой проверки
    alert('Пожалуйста, введите название стиля.');
    return;
    }

    // Проверка на максимальную длину
    if (materialName.length > 50) { // Здесь длина изменена на 50 символов
        alert('Ошибка: название стиля не может превышать 50 символов.');
        return;
    }


    // AJAX запрос для отправки данных
    var formData = new FormData(this);

    fetch('add_material.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        if (data.startsWith('Ошибка:')) {
            alert(data); // Выводим сообщение об ошибке, если что-то не так
        } else {
            document.getElementById('addMaterialModal').style.display = 'none'; // Закрываем модальное окно
            window.location.reload(); // Обновляем страницу
        }
    })
    .catch(error => {
        console.error('Ошибка:', error);
    });
});

//////////////////////////////////////////////////
/// Открытие модального окна для редактирования стиля
document.querySelectorAll('.editStyleButton').forEach(button => {
    button.addEventListener('click', function() {
        var styleId = this.getAttribute('data-id'); // Получаем ID стиля
        var row = this.closest('tr'); // Находим соответствующую строку таблицы
        var styleName = row.getElementsByTagName('td')[0].textContent; // Получаем текущее название стиля из первой ячейки

        document.getElementById('editStyleId').value = styleId; // Устанавливаем ID в скрытое поле формы
        document.getElementById('editStyleName').value = styleName; // Устанавливаем текущее название стиля в поле ввода

        // Открываем модальное окно
        document.getElementById('editStyleModal').style.display = 'block';
    });
});


// Закрываем модальное окно
document.getElementById('closeEditStyleModal').addEventListener('click', function() {
    document.getElementById('editStyleModal').style.display = 'none';
});
// Закрытие при клике вне модального окна
window.onclick = function(event) {
    var modal = document.getElementById('editStyleModal');
    if (event.target === modal) {
        modal.style.display = 'none';
    }
};

// Обработчик отправки формы для редактирования стиля
document.getElementById('editStyleForm').addEventListener('submit', function(event) {
    event.preventDefault(); // Предотвращаем стандартную отправку формы

    var styleName = document.getElementById('editStyleName').value.trim(); // Получаем текущее название стиля

    // Проверяем на пустое значение
    if (styleName.length === 0) { // Условие изменено для более строгой проверки
        alert('Пожалуйста, введите название стиля.');
        return;
    }

    // Проверка на максимальную длину
    if (styleName.length > 50) { // Здесь длина изменена на 50 символов
        alert('Ошибка: название стиля не может превышать 50 символов.');
        return;
    }

    var formData = new FormData(this); // Получаем данные формы

    fetch('update_style.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        if (data.startsWith('Ошибка:')) {
            alert(data); // Если возникла ошибка, показываем её
        } else {
            // Закрываем модальное окно и перезагружаем страницу
            document.getElementById('editStyleModal').style.display = 'none';
            window.location.reload(); // Обновляем страницу для применения изменений
        }
    })
    .catch(error => console.error('Ошибка:', error));
});

///////////////////////////////////////////////// Открытие модального окна для редактирования материала
document.querySelectorAll('.editMaterialButton').forEach(button => {
    button.addEventListener('click', function() {
        var materialId = this.getAttribute('data-id'); // Получаем ID материала
        var row = this.closest('tr'); // Находим соответствующую строку таблицы
        var materialName = row.getElementsByTagName('td')[0].textContent; // Получаем текущее название материала из первой ячейки

        document.getElementById('editMaterialId').value = materialId; // Устанавливаем ID в скрытое поле формы
        document.getElementById('editMaterialName').value = materialName; // Устанавливаем текущее название материала в поле ввода

        // Открываем модальное окно
        document.getElementById('editMaterialModal').style.display = 'block';
    });
});


// Закрываем модальное окно
document.getElementById('closeEditMaterialModal').addEventListener('click', function() {
    document.getElementById('editMaterialModal').style.display = 'none';
});

// Закрытие модального окна при клике вне его
window.addEventListener('click', function(event) {
    var editMaterialModal = document.getElementById('editMaterialModal');
    if (event.target === editMaterialModal) {
        editMaterialModal.style.display = 'none'; // Закрываем модальное окно
    }
});



// Обработчик отправки формы для редактирования материала
document.getElementById('editMaterialForm').addEventListener('submit', function(event) {
    event.preventDefault(); // Предотвращаем стандартную отправку формы

    var materialName = document.getElementById('editMaterialName').value.trim(); // Получаем текущее название стиля

    // Проверяем на пустое значение
    if (materialName.length === 0) { // Условие изменено для более строгой проверки
        alert('Пожалуйста, введите название материала.');
        return;
    }

    // Проверка на максимальную длину
    if (materialName.length > 50) { // Здесь длина изменена на 50 символов
        alert('Ошибка: название материала не может превышать 50 символов.');
        return;
    }

    var formData = new FormData(this); // Получаем данные формы

    fetch('update_material.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        if (data.startsWith('Ошибка:')) {
            alert(data); // Если возникла ошибка, показываем её
        } else {
            // Закрываем модальное окно и перезагружаем страницу
            document.getElementById('editMaterialModal').style.display = 'none';
            window.location.reload(); // Обновляем страницу для применения изменений
        }
    })
    .catch(error => console.error('Ошибка:', error));
});


////////////////////////////////////////// Обновите обработчики для кнопки удаления стиля
for (var button of document.getElementsByClassName('deleteStyleButton')) {
    button.addEventListener('click', function(event) {
        event.stopPropagation(); // Предотвращаем всплытие события

        // Обертываем основное действие в handleWithConnection
        handleWithConnection(() => {
            var id = this.getAttribute('data-id');

            console.log("Запрос на удаление стиля с ID:", id); // Отладочное сообщение

            if (confirm('Вы уверены, что хотите удалить этот стиль?')) {
                fetch('delete_style.php', {  // URL для удаления материала
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ id_style: id }) // Отправляем ID материала
                })
                .then(response => {
                    console.log("Ответ от сервера (raw):", response); // Отладочное сообщение
                    return response.text();  // Возвращаем текстовый ответ для парсинга
                })
                .then(data => {
                    console.log("Полученные данные (raw):", data); // Выводим текстовые данные перед попыткой парсинга
                    try {
                        var jsonData = JSON.parse(data); // Парсим JSON
                        if (jsonData.success) {
                            // Если удаление прошло успешно, обновляем UI
                            var rows = document.querySelectorAll('tr');
                            rows.forEach(row => {
                                if (row.querySelector('[data-id="' + id + '"]')) {
                                    row.remove(); // Удаляем строку с материалом
                                    alert('Стиль успешно удалён.');
                                }
                            });
                        } else {
                            alert('Ошибка при удалении стиля: ' + jsonData.message);
                        }
                    } catch (e) {
                        console.error("Ошибка при парсинге JSON:", e);
                        alert('Ответ сервера не является допустимым JSON.');
                    }
                })
                .catch(error => {
                    console.error('Ошибка:', error);
                    alert('Ошибка подключения к серверу. Попробуйте позже.');
                });
            }
        });
    });
}


///////////////////////////////////////// Обновите обработчики для кнопки удаления материала
for (var button of document.getElementsByClassName('deleteMaterialButton')) {
    button.addEventListener('click', function(event) {
        event.stopPropagation(); // Предотвращаем всплытие события

        // Обертываем основное действие в handleWithConnection
        handleWithConnection(() => {
            var id = this.getAttribute('data-id');

            console.log("Запрос на удаление материала с ID:", id); // Отладочное сообщение

            if (confirm('Вы уверены, что хотите удалить этот материал?')) {
                fetch('delete_material.php', {  // URL для удаления материала
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ id_material: id }) // Отправляем ID материала
                })
                .then(response => {
                    console.log("Ответ от сервера (raw):", response); // Отладочное сообщение
                    return response.text();  // Возвращаем текстовый ответ для парсинга
                })
                .then(data => {
                    console.log("Полученные данные (raw):", data); // Выводим текстовые данные перед попыткой парсинга
                    try {
                        var jsonData = JSON.parse(data); // Парсим JSON
                        if (jsonData.success) {
                            // Если удаление прошло успешно, обновляем UI
                            var rows = document.querySelectorAll('tr');
                            rows.forEach(row => {
                                if (row.querySelector('[data-id="' + id + '"]')) {
                                    row.remove(); // Удаляем строку с материалом
                                    alert('Материал успешно удалён.');
                                }
                            });
                        } else {
                            alert('Ошибка при удалении материала: ' + jsonData.message);
                        }
                    } catch (e) {
                        console.error("Ошибка при парсинге JSON:", e);
                        alert('Ответ сервера не является допустимым JSON.');
                    }
                })
                .catch(error => {
                    console.error('Ошибка:', error);
                    alert('Ошибка подключения к серверу. Попробуйте позже.');
                });
            }
        });
    });
}



});
</script>












<script>
    // Флаг для блокировки перезагрузки страницы
let blockUnload = false;
// // Функция проверки соединения с сервером
// async function checkConnection() {
//     console.log("Checking connection to the server..."); // Проверка вызова функции

//     try {
//         const response = await fetch('check_connection.php');
//         console.log("Response from server: ", response);

//         // Проверяем, был ли успешен ответ с сервера
//         if (!response.ok) {
//             throw new Error('Network response was not ok');
//         }

//         const data = await response.json();
//         console.log("Data received from server: ", data);

//         // Проверяем, есть ли ошибка в данных
//         if (!data.success) {
//             throw new Error(data.message || "Unknown error from server.");
//         }

//         return true; // Соединение успешно
//     } catch (error) {
//         console.error("Error in checkConnection: ", error); // Вывод полной ошибки в консоль

//         // Показываем ошибку пользователю через модальное окно
//         showErrorModal("Ошибка подключения к серверу. Попробуйте позже. ");
//         blockUnload = true; // Блокируем страницу при отсутствии соединения
//         return false; // Соединение не удалось
//     }
// }

// Функция проверки соединения с сервером
// async function checkConnection() {
//     console.log("Проверка соединения с сервером...");

//     try {
//         const response = await fetch('check_connection.php');
//         console.log("Ответ от сервера получен:", response);

//         // Проверяем, был ли успешен ответ с сервера
//         if (!response.ok) {
//             throw new Error('Ошибка сети: не удалось подключиться.');
//         }

//         const data = await response.json();
//         console.log("Получены данные от сервера:", data);

//         // Проверяем, есть ли ошибка в данных
//         if (!data.success) {
//             throw new Error(data.message || "Неизвестная ошибка от сервера.");
//         }

//         blockUnload = false; // Разблокируем страницу при успешном соединении
//         console.log("Соединение успешно.");
//         return true; // Соединение успешно
//     } catch (error) {
//         console.error("Ошибка при проверке соединения:", error.message);

//         // Показываем ошибку пользователю через модальное окно
//         showErrorModal("Ошибка подключения к серверу. Попробуйте позже.");
//         blockUnload = true; // Блокируем страницу при отсутствии соединения
//         return false; // Соединение не удалось
//     }
// }


// Функция проверки соединения с сервером
async function checkConnection() {
    console.log("Проверка соединения с сервером...");

    try {
        const response = await fetch('check_connection.php'); // Убедитесь, что путь правильный
        console.log("Ответ от сервера получен:", response);

        // Проверяем, был ли успешен ответ с сервера
        if (!response.ok) {
            throw new Error('Ошибка сети: не удалось подключиться.');
        }

        const data = await response.json();
        console.log("Получены данные от сервера:", data);

        // Проверяем, есть ли ошибка в данных
        if (!data.success) {
            throw new Error(data.message || "Неизвестная ошибка от сервера.");
        }

        // Если соединение успешно, ничего не делаем
        console.log("Соединение с базой данных успешно.");
        return true;
    } catch (error) {
        console.error("Ошибка при проверке соединения:", error.message);

        // Выводим alert с сообщением об ошибке
        // alert("Ошибка подключения к серверу MySQL: " + error.message);
        // return false;
         // Показываем ошибку пользователю через модальное окно
        showErrorModal("Ошибка подключения к серверу. Попробуйте позже.");
        return false; // Соединение не удалось
    }
}
// Вызовите функцию проверки соединения при загрузке страницы
// window.onload = checkConnection;


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
    console.log("Выполнение действия...");
    callback(); // Выполняем основное действие, если соединение успешно
}
       // Обработчик для кнопки "Войти"
async function handleLogin() {
    const currentUrl = window.location.href; // Получаем текущий URL
    await handleWithConnection(() => {
        location.href = 'login.php?redirect=' + encodeURIComponent(currentUrl); // Перенаправляем с параметром redirect
    });
}

        // Обработчик для кнопки "Зарегистрироваться"
async function handleRegister() {
    const currentUrl = window.location.href; // Получаем текущий URL
    await handleWithConnection(() => {
        location.href = 'register.php?redirect=' + encodeURIComponent(currentUrl); // Перенаправляем с параметром redirect
    });
}

// Обработчик для блокировки перезагрузки страницы
window.addEventListener('beforeunload', function(event) {
    if (blockUnload) {
        // Если соединение отсутствует, блокируем перезагрузку
        console.log("Попытка перезагрузки заблокирована.");
        event.preventDefault();
        alert('Страница не может быть перезагружена. Проверьте соединение с сервером.'); // Добавлено предупреждение
        event.returnValue = ''; // Для старых браузеров
        return ''; // Для современных браузеров
    }
});




// Функция для блокировки всех попыток перезагрузки страницы
window.addEventListener('keydown', function(event) {
    if (blockUnload && (event.key === 'F5' || (event.ctrlKey && event.key === 'r'))) {
        console.log("Попытка перезагрузки через клавишу заблокирована.");
        event.preventDefault();
        showErrorModal("Ошибка подключения к серверу. Перезагрузка страницы заблокирована.");
    }
});

window.addEventListener('click', function(event) {
    if (blockUnload && event.target.tagName === 'A') {
        console.log("Попытка перехода по ссылке заблокирована.");
        event.preventDefault();
        showErrorModal("Ошибка подключения к серверу. Переход заблокирован.");
    }
});

// Функция для повторной проверки соединения через несколько секунд
function retryConnection() {
    setTimeout(async () => {
        console.log("Повторная попытка проверки соединения...");

        const connectionOK = await checkConnection();

        if (!connectionOK) {
            retryConnection(); // Если соединение не удалось, пытаемся снова через несколько секунд
        }
    }, 5000); // Повторная проверка через 5 секунд
}





// Добавляем обработчики событий после загрузки DOM
document.addEventListener('DOMContentLoaded', async function () {
    console.log("Событие DOMContentLoaded произошло. Запуск проверки соединения...");

    // Проверка соединения с сервером
    const connectionOK = await checkConnection();

    if (!connectionOK) {
        console.log('Соединение не установлено. Повторная попытка...');
        showErrorModal("Ошибка подключения к серверу. Попробуйте позже.");
        retryConnection(); // Если соединение не удалось, пытаемся снова
    }

    var table = document.getElementById('paintingsTable');
    var rows = table ? table.getElementsByTagName('tr') : [];
    var nameInput = document.getElementById('nameInput');
    var styleInput = document.getElementById('styleInput');
    var yearInput = document.getElementById('yearInput');
    var authorInput = document.getElementById('authorInput');
    var sellerInput = document.getElementById('sellerInput');
    var searchButton = document.getElementById('searchButton');
    var resetButton = document.getElementById('resetButton');
    var editButtons = document.getElementsByClassName('editButton');
    var deleteButtons = document.getElementsByClassName('deleteButton');
    var closeEditButton = document.getElementById('closeEditModal');

    const addPaintingButton = document.getElementById('addPaintingButton');
    const addModal = document.getElementById('addModal');
    const closeAddModal = document.getElementById('closeAddModal');

        //  // Проверка, было ли уже показано модальное окно
        //  if (!localStorage.getItem('infoModalShown')) {
        // showModal();
        // localStorage.setItem('infoModalShown', 'true'); // Устанавливаем флаг в localStorage
    //}

// Проходим по всем строкам таблицы
// for (var i = 1; i < rows.length; i++) {
//     rows[i].addEventListener('click', function() {
//         // Оборачиваем действие в handleWithConnection
//         handleWithConnection(() => {
//             var id_painting = this.getAttribute('data-id');
//             // Перенаправление на страницу с деталями
//             window.location.href = 'painting_details.php?id_painting=' + id_painting;
//         });
//     });
// }

    // Проверка на существование кнопки
if (searchButton) {
    searchButton.addEventListener('click', function () {
        handleWithConnection(() => {
            console.log("Search button clicked.");

            var nameFilter = nameInput.value.toLowerCase();
            var styleFilter = styleInput.value.toLowerCase();
            var materialFilter = materialInput.value.toLowerCase();
            var yearFilter = yearInput.value.toLowerCase();
            var authorFilter = authorInput.value.toLowerCase();
            var sellerFilter = sellerInput.value.toLowerCase();

            for (var i = 1; i < rows.length; i++) {
                var cells = rows[i].getElementsByTagName('td');
                var matches = true;

                if (nameFilter && !cells[0].textContent.toLowerCase().includes(nameFilter)) {
                    matches = false;
                }
                if (styleFilter && !cells[1].textContent.toLowerCase().includes(styleFilter)) {
                    matches = false;
                }
                if (materialFilter && !cells[2].textContent.toLowerCase().includes(materialFilter)) {
                    matches = false;
                }
                if (yearFilter && !cells[3].textContent.toLowerCase().includes(yearFilter)) {
                    matches = false;
                }
                if (authorFilter && !cells[4].textContent.toLowerCase().includes(authorFilter)) {
                    matches = false;
                }
                if (sellerFilter && !cells[5].textContent.toLowerCase().includes(sellerFilter)) {
                    matches = false;
                }

                rows[i].style.display = matches ? '' : 'none';
            }
        });
    });
} else {
    console.warn("Search button not found.");
}


    // Обработчик для кнопки сброса
    resetButton.addEventListener('click', function () {
        handleWithConnection(() => {
            console.log("Reset button clicked.");

            // Очищаем поля ввода
            nameInput.value = '';
            styleInput.value = '';
            yearInput.value = '';
            authorInput.value = '';
            sellerInput.value = '';

            // Показываем все строки таблицы
            for (var i = 1; i < rows.length; i++) {
                rows[i].style.display = '';
            }
        });
    });

 // Обновите обработчик для кнопки редактирования
 for (var button of editButtons) {
    button.addEventListener('click', function(event) {
        event.stopPropagation(); // Предотвращаем всплытие события
        handleWithConnection(() => {
            var id = this.getAttribute('data-id'); // Получаем ID картины
            console.log('Editing painting ID:', id); // Отладочное сообщение
            var row = this.closest('tr'); // Находим ближайшую строку таблицы
            var cells = row.getElementsByTagName('td'); // Получаем все ячейки строки

            // Заполняем поля в модальном окне
            document.getElementById('editId').value = id;
            document.getElementById('editName').value = cells[0].textContent; // Название картины
            console.log('Paint name:', cells[0].textContent); // Отладочное сообщение

            // Получаем элемент выпадающего списка для стиля
            var styleDropdown = document.getElementById('editStyle');
            var selectedStyle = cells[1].textContent; 
            styleDropdown.value = Array.from(styleDropdown.options)
                .find(option => option.text === selectedStyle)?.value || ''; // Устанавливаем выбранный стиль
            console.log('Selected style:', selectedStyle); // Отладочное сообщение

            // Получаем элемент выпадающего списка для материала
            var materialDropdown = document.getElementById('editMaterial'); // Обновите ID если нужно
            var selectedMaterial = cells[2].textContent; // Предполагая, что материал в третьем столбце
            materialDropdown.value = Array.from(materialDropdown.options)
                .find(option => option.text === selectedMaterial)?.value || ''; // Устанавливаем выбранный материал
            console.log('Selected material:', selectedMaterial); // Отладочное сообщение

            document.getElementById('editYear').value = cells[3].textContent; // Год создания
            console.log('Creation year:', cells[3].textContent); // Отладочное сообщение
            document.getElementById('editAuthor').value = cells[4].textContent; // Автор
            console.log('Author:', cells[4].textContent); // Отладочное сообщение
            document.getElementById('editSeller').value = cells[5].textContent; // Продавец
            console.log('Seller:', cells[5].textContent); // Отладочное сообщение

            editModal.style.display = 'block'; // Открываем модальное окно
        });
    });
}




closeEditButton.addEventListener('click', function() {
    editModal.style.display = 'none'; // Закрываем модальное окно
});

 // Обновите обработчики для кнопки удаления
for (var button of document.getElementsByClassName('deleteButton')) {
    button.addEventListener('click', function(event) {
        event.stopPropagation(); // Предотвращаем всплытие события

        // Обертываем основное действие в handleWithConnection
        handleWithConnection(() => {
            var id = this.getAttribute('data-id');

            if (confirm('Вы уверены, что хотите удалить эту запись?')) {
                fetch('delete_painting.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ id_painting: id })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Удаляем строку из таблицы
                        var row = this.closest('tr');
                        row.parentNode.removeChild(row);
                        alert('Запись успешно удалена.');
                    } else {
                        alert('Ошибка при удалении записи: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Ошибка:', error);
                    showErrorModal('Ошибка подключения к серверу. Попробуйте позже.');
                });
            }
        });
    });
}

    // Закрытие модального окна для редактирования
    closeEditButton.addEventListener('click', function () {
        document.getElementById('editModal').style.display = 'none';
    });

    // Закрытие модального окна при клике вне его
    window.addEventListener('click', function(event) {
            const editModal = document.getElementById('editModal');
            if (event.target === editModal) {
                editModal.style.display = 'none'; // Закрываем модальное окно
            }
        });

        if (addPaintingButton) { // Проверяем, существует ли элемент
        addPaintingButton.addEventListener('click', function () {
            handleWithConnection(() => {
                addModal.style.display = 'block'; // Открываем модальное окно для добавления
            });
        });
    }

        

        closeAddModal.addEventListener('click', function () {
            addModal.style.display = 'none'; // Закрываем модальное окно

            // Очищаем поля ввода
            document.getElementById('addName').value = '';
            document.getElementById('addSize').value = '';
            document.getElementById('addMaterials').value = '';
            document.getElementById('addStyle').value = '';
            document.getElementById('addYear').value = '';
            document.getElementById('addAuthor').value = '';
            document.getElementById('addImageUrl').value = '';
            document.getElementById('addSeller').value = '';
            document.getElementById('addEmail').value = '';
            document.getElementById('addPhone').value = '';
            document.getElementById('addLotNumber').value = '';
            document.getElementById('addStartingPrice').value = '';
            document.getElementById('addStartDate').value = '';
            document.getElementById('addEndDate').value = '';
        });

        // Закрытие модального окна при клике вне его
        window.onclick = function (event) {
            if (event.target == addModal) {
                addModal.style.display = 'none';

                // Очищаем поля ввода
                document.getElementById('addName').value = '';
                document.getElementById('addSize').value = '';
                document.getElementById('addMaterials').value = '';
                document.getElementById('addStyle').value = '';
                document.getElementById('addYear').value = '';
                document.getElementById('addAuthor').value = '';
                document.getElementById('addImageUrl').value = '';
                document.getElementById('addSeller').value = '';
                document.getElementById('addEmail').value = '';
                document.getElementById('addPhone').value = '';
                document.getElementById('addLotNumber').value = '';
                document.getElementById('addStartingPrice').value = '';
                document.getElementById('addStartDate').value = '';
                document.getElementById('addEndDate').value = '';
            }
        };
});


// Функция выхода из аккаунта
async function handleLogout() {
    await handleWithConnection(async () => {
        const response = await fetch('logout.php');
        const result = await response.json();

        if (result.success) {
            // Перезагружаем страницу после выхода
            location.reload();
        } else {
            alert('Ошибка выхода: ' + result.message);
        }
    });
}


</script>


<!-- Модальное окно с информацией -->
<div id="infoModal" class="modal">
    <div class="modal-content">
        <span class="close-button">&times;</span>
        <div class="modal-header">
            <img src="https://cdn-icons-png.flaticon.com/512/10613/10613919.png" alt="Art Gallery Logo" style="width: 50px; height: auto; margin-right: 10px;">
            <h2 style="margin: 0; font-weight: bold;">Добро пожаловать в HueHaven!</h2>
        </div>
        <p style="font-style: italic; color: #555;">Погрузитесь в мир искусства с нашим онлайн-аукционом. Здесь вы можете приобретать уникальные произведения со всего мира. Участвуйте в торгах и найдите идеальное дополнение для своей коллекции.</p>
        <p style="font-weight: bold; text-align: center;">HueHaven — откройте мир искусства!</p>
    </div>
</div>

<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close-button" id="closeEditModal">&times;</span>
        <h2>Редактировать картину</h2>
        <form id="editForm" action="update_painting.php" method="POST">
            <input type="hidden" id="editId" name="id_painting" required>
            
            <label for="editName">Название картины:</label>
            <input type="text" class="modal-input" id="editName" name="paint_name" placeholder="Название картины" required maxlength="255">
            
            <label for="editStyleDropdown">Стиль:</label>
            <?= createDropdownFromArray($stylesArray, 'editStyle', 'Выберите стиль', 'modal-input') ?>

            <label for="editMaterial">Материал:</label>
            <?= createDropdownFromArray($materialsArray, 'editMaterial', 'Выберите материал', 'modal-input') ?>

            
            <label for="editYear">Год создания:</label>
            <input type="text" class="modal-input" id="editYear" name="creation_year" placeholder="Год создания" pattern="\d{4}" maxlength="4" required title="Введите четыре цифры" max="<?php echo date('Y'); ?>">
            
            <label for="editAuthor">Автор:</label>
            <input type="text" class="modal-input" id="editAuthor" name="author" placeholder="Автор" required maxlength="255">
            
            <label for="editSeller">Продавец:</label>
            <input type="text" class="modal-input" id="editSeller" name="seller" placeholder="Продавец" required maxlength="255">
            
            <button type="submit" class="saveButton">Сохранить</button>
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


<div id="addModal" class="modal">
    <div class="modal-content">
        <span class="close-button" id="closeAddModal">&times;</span>
        <h2>Добавить картину</h2>
        <form id="addForm" action="add_painting.php" method="POST">
            <div class="input-container">
                <div class="input-column">
                    <label for="addName">Название картины:</label>
                    <input type="text" id="addName" name="paint_name" class="modal-input" placeholder="Название картины" required maxlength="255">

                    <label for="addSize">Размер:</label>
                    <input type="text" id="addSize" name="size" class="modal-input" placeholder=" XxX см или XXxXX см или XXXxXXX см" required maxlength="50" pattern="^(?:\d{2}x\d{2} см|\d{3}x\d{3} см)$" title="Введите размер в формате XXxXX см или XXXxXXX см">                       
                    
                    <!-- <label for="styleDropdown">Стиль:</label> -->
                    <!-- <?= createDropdownFromArray($stylesArray, 'styles', 'Выберите стиль') ?> -->    
                    <!-- <label for="materialDropdown">Материал:</label> -->
                    <!-- <?= createDropdown($materialsResult, 'materials', 'Выберите материал') ?> -->

                        <label for="styleDropdown">Стиль:</label>
                    <?= createDropdownFromArray($stylesArray, 'styles', 'Выберите стиль', 'select-dropdown') ?>

                    <label for="materialDropdown">Материал:</label>
                    <?= createDropdownFromArray($materialsArray, 'materials', 'Выберите материал', 'select-dropdown') ?>

                    <label for="addYear">Год создания:</label>
                    <input type="text" id="addYear" name="creation_year" class="modal-input" placeholder="Год создания" pattern="\d{4}" maxlength="4" required title="Введите четыре цифры">

                    <label for="addAuthor">Автор:</label>
                    <input type="text" id="addAuthor" name="author" class="modal-input" placeholder="Автор" required maxlength="255">

                    <label for="addImageUrl">URL картины:</label>
                    <input type="url" id="addImageUrl" name="image_path" class="modal-input" placeholder="URL картины" required maxlength="255" title="Введите корректный URL (например, https://example.com/image.jpg)">
                </div>

                <div class="input-column">
                    <label for="addSeller">Имя продавца:</label>
                    <input type="text" id="addSeller" name="seller" class="modal-input" placeholder="Имя продавца" required maxlength="255">

                    <label for="addEmail">Email продавца:</label>
                    <input type="email" id="addEmail" name="email" class="modal-input" placeholder="Email продавца" required>

                    <label for="addPhone">Телефон продавца:</label>
                    <input type="text" id="addPhone" name="phone" class="modal-input" placeholder="Телефон продавца" required maxlength="15" pattern="^\+\d{0,14}$" title="Введите номер телефона, начиная с + и далее только цифры.">                </div>

                <div class="input-column">
                    <label for="addLotNumber">Номер лота:</label>
                    <input type="number" id="addLotNumber" name="lot_number" class="modal-input" required min="0" max="9999" maxlength="4>

                    <label for="addStartingPrice">Стартовая цена:</label>
                    <input type="number" id="addStartingPrice" name="starting_price" class="modal-input" step="0.01" required min="0.01" max="99999999.99" title="Введите цену от 0.01 до 99999999.99">

                    <label for="addStartDate">Дата начала аукциона:</label>
                    <input type="date" id="addStartDate" name="start_date" class="modal-input" required>

                    <label for="addEndDate">Дата конца аукциона:</label>
                    <input type="date" id="addEndDate" name="end_date" class="modal-input" required>
                </div>
            </div>

            <button type="submit" class="saveButton">Добавить</button>
        </form>
    </div>
</div>

<script>
document.getElementById('editForm').addEventListener('submit', function(event) {
    event.preventDefault(); // Предотвращаем стандартную отправку формы

    // Получаем значения полей
    var nameInput = document.getElementById('editName').value.trim();
    var styleInput = document.getElementById('editStyle').value; // Используем новый идентификатор для стиля
    var materialInput = document.getElementById('editMaterial').value; // Новый идентификатор для материала
    var yearInput = document.getElementById('editYear').value;
    var authorInput = document.getElementById('editAuthor').value.trim();
    var sellerInput = document.getElementById('editSeller').value.trim();
    var currentYear = new Date().getFullYear();

    // Отладочные сообщения для проверки значений перед отправкой
    console.log('Submitting form with values:', {
        nameInput,
        styleInput,
        materialInput,
        yearInput,
        authorInput,
        sellerInput
    });

    // Проверка на наличие только пробелов
    if (!nameInput || !styleInput ||  !materialInput || !authorInput || !sellerInput) {
        alert('Пожалуйста, заполните все поля, не оставляя только пробелы.');
        return; // Останавливаем дальнейшее выполнение
    }

    // Проверка на корректность года
    if (!/^\d{4}$/.test(yearInput) || yearInput < 1901 || yearInput > currentYear) {
        alert('Пожалуйста, введите корректный год (от 1901 и не больше текущего года).');
        return; // Останавливаем дальнейшее выполнение
    }

    // Оборачиваем в handleWithConnection
    handleWithConnection(() => {
        // Если проверка успешна, отправляем данные формы через AJAX
        var formData = new FormData(this); // Получаем данные формы

        fetch('update_painting.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            console.log('Server response:', data); // Отладочное сообщение
            if (data.startsWith("Ошибка:")) {
                alert(data); // Если возникла ошибка, показываем alert
            } else {
                // Если все прошло успешно, перенаправляем или скрываем модальное окно
                window.location.href = 'index.php'; // Перенаправление на index.php
            }
        })
        .catch(error => {
            console.error('Ошибка:', error);
        });
    });
});

</script>

<script>
document.getElementById('addForm').addEventListener('submit', function(event) {

    // Проверка на наличие только пробелов
    const inputFields = [
        'addName',
        'addSize',
        'addMaterials',
        'addStyle',
        'addYear',
        'addAuthor',
        'addImageUrl',
        'addSeller',
        'addEmail',
        'addPhone',
        'addLotNumber',
        'addStartingPrice',
    ];

    for (const fieldId of inputFields) {
        const inputValue = document.getElementById(fieldId).value;
        if (inputValue.startsWith(' ')) {
            alert(`Поля не должны начинаться с пробела.`);
            event.preventDefault(); // Отменяем отправку формы
            return; // Останавливаем дальнейшее выполнение
        }
    }

    var yearInput = document.getElementById('addYear').value;
    var currentYear = new Date().getFullYear();
    
    // Проверка на наличие только пробелов
    var nameInput = document.getElementById('addName').value.trim();
    var sizeInput = document.getElementById('addSize').value.trim();
    var materialsInput = document.getElementById('addMaterials').value.trim();
    var styleInput = document.getElementById('addStyle').value.trim();
    var authorInput = document.getElementById('addAuthor').value.trim();
    var imageUrlInput = document.getElementById('addImageUrl').value.trim();
    var sellerInput = document.getElementById('addSeller').value.trim();
    var emailInput = document.getElementById('addEmail').value.trim();
    var phoneInput = document.getElementById('addPhone').value.trim();

    if (!nameInput || !sizeInput || !materialsInput || !styleInput || !authorInput || !imageUrlInput || !sellerInput || !emailInput || !phoneInput) {
        alert('Пожалуйста, заполните все поля, не оставляя только пробелы.');
        event.preventDefault(); // Отменяем отправку формы
        return; // Останавливаем дальнейшее выполнение
    }

    // Проверка на корректность года
    if (!/^\d{4}$/.test(yearInput) || yearInput < 1901 || yearInput > currentYear) {
        alert('Пожалуйста, введите корректный год (от 1901 и не больше текущего года).');
        event.preventDefault(); // Отменяем отправку формы
        return; // Останавливаем дальнейшее выполнение
    }

    // Проверка на количество цифр в номере телефона
var phoneInputValue = phoneInput.replace(/\D/g, ''); // Убираем все нецифровые символы
if (phoneInputValue.length !== 12) {
    alert('Номер телефона должен содержать ровно 12 цифр после знака +.');
    event.preventDefault();
    return;
}

    // Проверка на корректность дат начала и конца аукциона
var startDate = new Date(document.getElementById('addStartDate').value);
var endDate = new Date(document.getElementById('addEndDate').value);

// Проверка на корректность года начала и конца аукциона
var startYear = startDate.getFullYear();
var endYear = endDate.getFullYear();

if (startYear < 1901 || startYear > 2155) {
    alert('Год начала аукциона должен быть от 1901 до 2155.');
    event.preventDefault();
    return;
}

if (endYear < 1901 || endYear > 2155) {
    alert('Год конца аукциона должен быть от 1901 до 2155.');
    event.preventDefault();
    return;
}

if (startDate >= endDate) {
    alert('Дата начала аукциона должна быть раньше даты конца аукциона.');
    event.preventDefault();
    return;
}

    // Проверка стартовой цены
    var startingPrice = parseFloat(document.getElementById('addStartingPrice').value);
    if (startingPrice <= 0) {
        alert('Стартовая цена должна быть больше нуля.');
        event.preventDefault();
        return;
    }

    // Оборачиваем в handleWithConnection
    handleWithConnection(() => {
        // Если проверка успешна, форма отправляется
        this.submit();
    });

    // Отменяем стандартную отправку формы до завершения проверки
    event.preventDefault();
});


document.getElementById('addPhone').addEventListener('focus', function() {
    if (this.value === '' || this.value === '+') {
        this.value = '+'; // Добавляем + в начале
    }
});

document.getElementById('addPhone').addEventListener('input', function() {
    // Удаляем все символы, кроме + и цифр
    this.value = this.value.replace(/[^+\d]/g, '');

    // Проверяем, чтобы количество цифр не превышало 7
    const digitsOnly = this.value.replace(/\D/g, ''); // Убираем все нецифровые символы
    if (digitsOnly.length > 12) {
        this.value = '+' + digitsOnly.slice(0, 12); // Оставляем только первые 7 цифр
    }

    // Если символ + был удален, восстанавливаем его
    if (this.value.charAt(0) !== '+') {
        this.value = '+' + this.value.replace(/^\+/, ''); // Восстанавливаем +
    }
});

document.getElementById('addLotNumber').addEventListener('input', function() {
    // Удаляем все символы, кроме цифр
    this.value = this.value.replace(/[^0-9]/g, '');

    // Преобразуем значение в число и проверяем, чтобы оно было больше 0
    if (this.value && parseInt(this.value, 10) < 1) {
        this.value = ''; // Очищаем поле, если значение меньше 1
    }
});

// Запрещаем ввод дополнительных символов +
document.getElementById('addPhone').addEventListener('keypress', function(event) {
    if (event.key === '+') {
        event.preventDefault(); // Запрещаем ввод символа +
    }
});



const addSizeInput = document.getElementById('addSize');

    addSizeInput.addEventListener('focus', function() {
        if (this.value === '') {
            this.value = ''; // Убираем текст, если поле пустое
        }
    });

    addSizeInput.addEventListener('blur', function() {
        if (this.value === '') {
            this.value = ''; // Удаляем текст при потере фокуса, если поле пустое
        }
    });

    addSizeInput.addEventListener('input', function() {
        // Удаляем все символы, кроме цифр и 'x'
        this.value = this.value.replace(/[^0-9x]/g, '');

        // Разделяем на части по 'x'
        const parts = this.value.split('x');

        // Ограничиваем количество символов перед 'x' до 3
        if (parts[0].length > 3) {
            this.value = parts[0].slice(0, 3) + 'x' + (parts[1] ? parts[1] : '');
        }

        // Ограничиваем количество символов после 'x' до 3
        if (parts.length > 1 && parts[1].length > 3) {
            this.value = parts[0] + 'x' + parts[1].slice(0, 3);
        }

        // Проверка на невалидные размеры
        if (/^(0+|0x0|00x00|000x000|[1-9][0-9]*x0|[1-9][0-9]*x00|[1-9][0-9]*x000)$/.test(this.value)) {
            if (parts.length > 1 && parts[1] === '0') {
                this.value = parts[0] + 'x'; // Удаляем только 0, оставляем 'x'
            } else {
                this.value = ''; // Очищаем поле, если значение невалидное
                alert('Введите корректный размер, например, 30x40 см.');
                return;
            }
        }
        
        // Если поле не содержит 'x', очищаем его
        if (parts[0] === '' && parts.length > 1) {
            this.value = ''; // Если перед 'x' ничего нет, очищаем поле
        }

        // Добавляем " см" после "x", если оно присутствует
        if (this.value.includes('x')) {
            const afterX = this.value.split('x')[1];
            this.value = this.value.replace(/(x\d*)(\s*см)?/, '$1 см');

            // Удаляем " см", если после "x" нет цифр
            if (afterX && afterX.trim() === '') {
                this.value = this.value.replace(/ см$/, ''); // Удаляем " см", если после "x" ничего нет
            }
        }
    });

    document.getElementById('addLotNumber').addEventListener('input', function() {
    // Удаляем все символы, кроме цифр
    this.value = this.value.replace(/[^0-9]/g, '');

    // Ограничиваем ввод до 4 цифр
    if (this.value.length > 4) {
        this.value = this.value.slice(0, 4);
    }

    // Преобразуем значение в число и проверяем, чтобы оно было больше 0
    if (this.value && parseInt(this.value, 10) < 1) {
        this.value = ''; // Очищаем поле, если значение меньше 1
    }
});

document.getElementById('addStartingPrice').addEventListener('input', function() {
    const value = parseFloat(this.value);
    if (value > 99999999.99) {
        alert('Стартовая цена не может превышать 99999999.99.');
        this.value = ''; // Очищаем поле, если значение слишком большое
    }
});
    
document.getElementById('addForm').addEventListener('submit', function(event) {
    const urlInput = document.getElementById('addImageUrl');
    const urlPattern = /^(ftp|http|https):\/\/[^ "]+$/;

    if (!urlPattern.test(urlInput.value)) {
        alert('Введите корректный URL, начинающийся с http:// или https://');
        event.preventDefault(); // Отменяем отправку формы
    }
}); 

</script>

</body>
</html>
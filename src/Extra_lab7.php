//Сортировка на фронте
if (!$isAdmin) {
    if (!empty($paintings)) {
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
        foreach ($paintings as $painting) {
            echo "<tr data-id='" . $painting['id_painting'] . "'>";
            echo "<td>" . $painting['paint_name'] . "</td>";

            // Проверка, есть ли стиль, если нет — выводим "Стиль недоступен" красным
            if ($painting['style_name']) {
                echo "<td>" . $painting['style_name'] . "</td>";
            } else {
                echo "<td style='color: red;'>Стиль недоступен</td>";
                $missingStyleOrMaterial = true; 
            }

            // Проверка, есть ли материал, если нет — выводим "Материал недоступен" красным
            if ($painting['material_name']) {
                echo "<td>" . $painting['material_name'] . "</td>";
            } else {
                echo "<td style='color: red;'>Материал недоступен</td>";
                $missingStyleOrMaterial = true;
            }

            echo "<td>" . $painting['creation_year'] . "</td>";
            echo "<td>" . $painting['author'] . "</td>";
            echo "<td>" . $painting['full_name'] . "</td>";

            // Добавляем кнопки "Редактировать" и "Удалить", если это продавец
            if ($isSeller) {
                echo "<td>
                        <button class='editButton' data-id='" . $painting['id_painting'] . "'>Редактировать</button>
                        <button class='deleteButton' data-id='" . $painting['id_painting'] . "'>Удалить</button>
                    </td>";
            }

            // Если пользователь - обычный пользователь (user), добавляем чекбокс "Заявка"
            if ($isUser) {
                $checked = in_array($painting['id_painting'], $userRequests) ? "checked" : "";
                echo "<td>
                        <input type='checkbox' class='requestCheckbox' data-id='" . $painting['id_painting'] . "' $checked>
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
}

//моя сортировка
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




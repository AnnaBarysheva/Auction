document.addEventListener('DOMContentLoaded', function() {
    console.log("Script.js is loaded and working!");
    console.log("Нет соединения с сервером.");

    var rows = document.querySelectorAll('#paintingsTable tr');

    // Проходим по всем строкам таблицы
    for (var i = 1; i < rows.length; i++) {
        rows[i].addEventListener('click', function(event) {
            if (event.target.type === 'checkbox') {
                event.stopPropagation(); // Останавливаем событие, чтобы не было перехода
                return;
            }

            handleWithConnection(() => {
                var id_painting = this.getAttribute('data-id');
                window.location.href = 'painting_details.php?id_painting=' + id_painting;
            });
        });
    }

    // Обработчик кликов для чекбоксов заявок
    document.querySelectorAll('.requestCheckbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            var paintingId = this.getAttribute('data-id');

            // AJAX запрос для сохранения заявки
            var formData = new FormData();
            formData.append('id_painting', paintingId);
            formData.append('checked', this.checked); // true если выбран, false если снят

            fetch('handle_request.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (this.checked) {
                        console.log('Заявка на картину с ID: ' + paintingId + ' подана.');
                    } else {
                        console.log('Заявка на картину с ID: ' + paintingId + ' отменена.');
                    }
                } else {
                    console.error('Ошибка при отправке заявки:', data.message);
                    this.checked = !this.checked; // Отменяем действие если ошибка
                }
            })
            .catch(error => {
                console.error('Ошибка:', error);
                this.checked = !this.checked; // Отменяем действие если ошибка
            });
        });
    });



    // Проверка на существование кнопки "Вернуться на главную"
    const returnHomeButton = document.getElementById('return-home-button');
    if (returnHomeButton) {
        console.log("Кнопка 'Вернуться на главную' найдена!");
        
        // Добавляем обработчик события
        returnHomeButton.addEventListener('click', async function() {
            console.log("Кнопка 'Вернуться на главную' была нажата.");
            await handleReturnHome();
        });
    } else {
        console.error("Кнопка 'Вернуться на главную' не найдена.");
    }

    // Функция для обработки нажатия кнопки "Вернуться на главную"
    async function handleReturnHome() {
        console.log("handleReturnHome вызвана");
        await handleWithConnection(() => {
            console.log("Переход на главную страницу.");
            location.href = 'index.php';
        });
    }

    // Проверка соединения с сервером
    async function checkConnection() {
        console.log("Проверка соединения с сервером...");

        try {
            const response = await fetch('check_connection.php');
            console.log("Response from server: ", response);

            // Проверка успешности ответа
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }

            const data = await response.json();
            console.log("Data received from server: ", data);

            if (!data.success) {
                throw new Error(data.message || "Unknown error from server.");
            }

            return true; // Соединение успешно
        } catch (error) {
            console.error("Ошибка в checkConnection: ", error);
            showErrorModal("Ошибка подключения к серверу. Попробуйте позже.");
            return false; // Соединение не удалось
        }
    }

    // Универсальная функция с проверкой соединения
    async function handleWithConnection(callback) {
        const connectionOK = await checkConnection();
        
        if (!connectionOK) {
            console.log("Нет соединения с сервером.");
            return; // Прекращаем выполнение, если нет соединения
        }

        console.log("Соединение успешно, выполняем callback.");
        callback(); // Выполняем основное действие
    }
    

 
});

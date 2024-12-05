document.addEventListener('DOMContentLoaded', function() {
    console.log("Script.js is loaded and working!");

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

    // document.querySelectorAll('.requestCheckbox').forEach(checkbox => {
    //     checkbox.addEventListener('change', function() {
    //         var paintingId = this.getAttribute('data-id');
    //         var isChecked = this.checked;
    
    //         // AJAX запрос для сохранения заявки через fetch
    //         var formData = new FormData();
    //         formData.append('id_painting', paintingId);
    //         formData.append('checked', isChecked); // true если выбран, false если снят
    
    //         fetch('handle_request.php', {
    //             method: 'POST',
    //             body: formData
    //         })
    //         .then(response => response.json())
    //         .then(data => {
    //             if (data.success) {
    //                 if (isChecked) {
    //                     console.log('Заявка на картину с ID: ' + paintingId + ' подана.');
    //                 } else {
    //                     console.log('Заявка на картину с ID: ' + paintingId + ' отменена.');
    //                 }
    //             } else {
    //                 console.error('Ошибка при отправке заявки:', data.message);
    //                 this.checked = !this.checked; // Отменяем действие если ошибка
    //             }
    //         })
    //         .catch(error => {
    //             console.error('Ошибка:', error);
    //             this.checked = !this.checked; // Отменяем действие если ошибка
    //         });
    
    //         //  запрос для отправки письма через fetch
    //     let emailData = new URLSearchParams();
    //     emailData.append('paintingId', paintingId);
    //     emailData.append('isChecked', isChecked);

    //     fetch('send_request_email.php', {
    //         method: 'POST',
    //         body: emailData
    //     })
    //     .then(response => response.text())
    //     .then(responseText => {
    //         console.log('Письмо отправлено на почту!', responseText);
    //     })
    //     .catch(error => {
    //         console.error('Ошибка при отправке письма:', error);
    //     });
    //     });
    // });
    




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
    
    
    
    
 //для показа названия файла
    // const fileNameDisplay = document.getElementById('fileNameDisplay');
    // const fileInput = document.getElementById('profile_picture');
    // const customLabel = document.querySelector('.custom-file-upload');

    // if (fileNameDisplay && fileInput && customLabel) {
    //     // Обработчик для изменения текста при выборе файла
    //     fileInput.addEventListener('change', function(event) {
    //         const file = event.target.files[0];
    //         fileNameDisplay.textContent = file ? file.name : 'Файл не выбран';
    //     });

    //     // Обработчик для клика по кастомному label
    //     customLabel.addEventListener('click', function(event) {
    //         // Проверяем, выбран ли файл, чтобы не повторять click на input
    //         if (!fileInput.files.length) {
    //             fileInput.click();
    //         }
    //     });
    // } else {
    //     console.error("Не удалось найти один или несколько элементов.");
    // }

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
    

// // Функция для загрузки файла профиля
// async function uploadProfilePicture(file) {
//     await handleWithConnection(async () => {
//         const formData = new FormData();
//         formData.append('profile_picture', file);

//         try {
//             const response = await fetch('upload_profile_picture.php', {
//                 method: 'POST',
//                 body: formData
//             });

//             if (!response.ok) {
//                 throw new Error('Ошибка загрузки файла на сервер.');
//             }

//             const result = await response.json();

//             if (!result.success) {
//                 throw new Error(result.message || 'Ошибка доступа к базе данных.');
//             }

//             console.log("Файл успешно загружен");
//             location.reload(); // Перезагружаем страницу для обновления изображения
//         } catch (error) {
//             console.error("Ошибка при загрузке файла: ", error);
//             showErrorModal("Ошибка при загрузке файла. Пожалуйста, попробуйте позже.");
//         }
//     });
// }



// // Пример вызова загрузки при выборе файла
// document.getElementById('profile_picture').addEventListener('change', async function(event) {
//     const file = event.target.files[0];
//     if (file) {
//         await uploadProfilePicture(file);
//     }
// });




});


///////
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

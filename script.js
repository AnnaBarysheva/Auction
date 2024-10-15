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
});

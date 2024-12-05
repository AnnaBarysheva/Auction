<?php
namespace App;

class Style_dropdown
{
    function createDropdownFromArray($dataArray, $dropdownId, $defaultOptionText, $class = '') {
        // Если класс передан, добавляем его в тег select
        $classAttribute = $class ? "class='{$class}'" : '';
        
        
        // Создаем тег select с заданным id, именем и классом
        //$dropdown = "<select name='{$dropdownId}' id='{$dropdownId}' {$classAttribute} required>";
        $dropdown = "<select name='{$dropdownId}' id='{$dropdownId}'{$classAttribute} required>";

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
}

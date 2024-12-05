<?php
namespace App;

class Paintings_DetailsTest
{
    // Функция для получения полной информации о картине
    function getPaintingDetails($link, $id_painting) {
        if (empty($id_painting)) {
            return false; // Если id_painting не передан, возвращаем false
        }
        // Подготовленный SQL-запрос
        $sql = "
            SELECT 
                Paintings.paint_name, 
                Paintings.size, 
                Styles.style_name, 
                Paintings.id_style, 
                Materials.material_name, 
                Paintings.creation_year, 
                Paintings.author, 
                Paintings.image_path, 
                Sellers.full_name AS seller_name, 
                Sellers.phone AS seller_phone, 
                Sellers.email AS seller_email,
                Auctions.start_date, 
                Auctions.end_date,  
                PaintingsOnAuction.starting_price, 
                PaintingsOnAuction.purchase_price, 
                PaintingsOnAuction.lot_number 
            FROM 
                Paintings
            INNER JOIN Sellers ON Paintings.id_seller = Sellers.id_seller
            INNER JOIN PaintingsOnAuction ON Paintings.id_painting = PaintingsOnAuction.id_painting
            INNER JOIN Auctions ON PaintingsOnAuction.id_auction = Auctions.id_auction
            INNER JOIN Styles ON Paintings.id_style = Styles.id_style
            INNER JOIN Materials ON Paintings.id_material = Materials.id_material
            WHERE 
                Paintings.id_painting = $id_painting;
        ";

        // Выполнение запроса
        $result = mysqli_query($link, $sql);

        // Проверка на наличие данных
        if ($result && mysqli_num_rows($result) > 0) {
            return mysqli_fetch_assoc($result); // Возвращаем данные картины
        } else {
            return false; // Если данных нет, возвращаем false
        }
    }
}

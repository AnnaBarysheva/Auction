<?php

use PHPUnit\Framework\TestCase;
use App\Paintings_DetailsTest;

class GetPaintingDetailsTest extends TestCase
{
    private $link;
    private $paintingDetails;

    protected function setUp(): void
    {
        // Создаем реальное соединение с тестовой базой данных
        $this->link = new mysqli('localhost', 'root', 'alina', 'Auction');

        if ($this->link->connect_error) {
            $this->fail("Connection failed: " . $this->link->connect_error);
        }

        // Создаем объект класса PaintingDetails
        $this->paintingDetails = new Paintings_DetailsTest();

        
    }

    

    public function testGetPaintingDetailsReturnsData()
    {
        $id_painting = 66; // ID тестовой картины

        // Устанавливаем значение $_GET['id_painting'] для теста
        $_GET['id_painting'] = $id_painting;

        // Вызов метода и проверка результата
        $paintingDetails = $this->paintingDetails->getPaintingDetails($this->link, $id_painting);

        // Проверка, что данные картины были возвращены
        $this->assertIsArray($paintingDetails);
        $this->assertEquals('Sunset at Sea', $paintingDetails['paint_name']);
        $this->assertEquals('Portrait', $paintingDetails['style_name']);
        

        // Очищаем значение $_GET после теста, чтобы оно не влияло на другие тесты
        unset($_GET['id_painting']);
    }

    public function testGetPaintingDetailsReturnsNullForInvalidId()
    {
        $invalidId = 9999; // Несуществующий ID картины

        // Устанавливаем значение $_GET['id_painting'] для теста
        $_GET['id_painting'] = $invalidId;

        // Вызов метода
        $paintingDetails = $this->paintingDetails->getPaintingDetails($this->link, $invalidId);

        // Проверка, что метод вернул null (или пустой массив, в зависимости от реализации)
        
        $this->assertEquals(false, $paintingDetails);

        // Очищаем значение $_GET после теста
        unset($_GET['id_painting']);
    }

    public function testGetPaintingDetailsHandlesEmptyId()
    {
        $_GET['id_painting'] = ''; // Пустое значение

        $paintingDetails = $this->paintingDetails->getPaintingDetails($this->link, $_GET['id_painting']);

        // Ожидаем null или false
       
        $this->assertEquals(false, $paintingDetails);

        unset($_GET['id_painting']);
    }
}
?>

<?php

use PHPUnit\Framework\TestCase;
use App\Style_dropdown;

class GetStyleDropdownTest extends TestCase
{
    private $styleDropdown;

    protected function setUp(): void
    {
        // Инициализируем объект перед каждым тестом
        $this->styleDropdown = new Style_dropdown();
    }

    public function testCreateDropdownWithValidData()
    {
        $dataArray = [
            ['id_style' => 1, 'style_name' => 'Abstract'],
            ['id_style' => 2, 'style_name' => 'Impressionism'],
        ];
        $dropdownId = 'styleDropdown';
        $defaultOptionText = 'Select a style';
    
        $expectedOutput = "<select name='styleDropdown' id='styleDropdown' required>";
        $expectedOutput .= "<option value=''>Select a style</option>";
        $expectedOutput .= "<option value='1'>Abstract</option>";
        $expectedOutput .= "<option value='2'>Impressionism</option>";
        $expectedOutput .= "</select>";
    
        $output = $this->styleDropdown->createDropdownFromArray($dataArray, $dropdownId, $defaultOptionText);
    
        $this->assertEquals($expectedOutput, $output);
        
    }


    public function testCreateDropdownWithEmptyArray()
    {
        $dataArray = [];
        $dropdownId = 'styleDropdown';
        $defaultOptionText = 'Select a style';

        $expectedOutput = "<select name='styleDropdown' id='styleDropdown' required>";
        $expectedOutput .= "<option value=''>Select a style</option>";
        $expectedOutput .= "</select>";

        $output = $this->styleDropdown->createDropdownFromArray($dataArray, $dropdownId, $defaultOptionText);

        $this->assertEquals($expectedOutput, $output, "Dropdown for empty data array is incorrect");
    }

}
?>

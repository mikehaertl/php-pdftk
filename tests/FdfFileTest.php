<?php
namespace tests;

use PHPUnit\Framework\TestCase;
use mikehaertl\pdftk\FdfFile;

class FdfFileTest extends TestCase
{
    public function testFdfFileCreation()
    {
        $data = array(
            'name' => 'Jürgen čárka čČćĆđĐ мирано',
            'email' => 'test@email.com',
            'checkbox 1' => 'Yes',
            'checkbox 2' => 0,
            'radio 1' => 2,
            "umlauts-in-value" => "öäüÖÄÜ",
            "öäüÖÄÜ" => "umlauts in key",
            "special-in-value" => "€ß&()",
            "€ key" => "special in key",
        );

        $oFdfFile = new FdfFile($data, null, null, __DIR__);
        $sFdfFilename = $oFdfFile->getFileName();

        $this->assertFileExists($sFdfFilename);
        $this->assertFileEquals(__DIR__ . "/files/FdfFileTest.fdf", $sFdfFilename);
    }
}

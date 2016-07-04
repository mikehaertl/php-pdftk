<?php
use mikehaertl\pdftk\XfdfFile;

class XfdfFileTest extends \PHPUnit_Framework_TestCase
{
    public function testXfdfFileCreation() {
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

        $oXfdfFile = new XfdfFile($data, null, null, __DIR__);
        $sXfdfFilename = $oXfdfFile->getFileName();

        $this->assertFileExists($sXfdfFilename);
        $this->assertFileEquals($sXfdfFilename, __DIR__."/files/XfdfFileTest.xfdf");
    }
}

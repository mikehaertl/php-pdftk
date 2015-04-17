<?php
use mikehaertl\pdftk\Pdf;
use mikehaertl\pdftk\FdfFile;

class FdfFileTest extends \PHPUnit_Framework_TestCase
{
    public function testFdfFileCreation() {
        $data = array(
            "standard" => "nothing special here",
            "umlauts-in-value" => "öäüÖÄÜ",
            "öäüÖÄÜ" => "umlauts in key",
            'checkbox 1' => 'Yes',
            'checkbox 2' => 0,
            'radio 1' => 2,
        );

        $oFdfFile = new FdfFile($data, null, null, __DIR__);
        $sFdfFilename = $oFdfFile->getFileName();
        $this->assertFileExists($sFdfFilename);
    }
}

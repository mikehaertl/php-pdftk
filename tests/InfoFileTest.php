<?php
use mikehaertl\pdftk\InfoFile;

class InfoFileTest extends \PHPUnit\Framework\TestCase
{
    public function testInfoFileCreation()
    {
        $data = array(
            'Creator' => 'php-pdftk',
            'Subject' => "öäüÖÄÜ",
        );

        $oInfoFile = new InfoFile($data, null, null, __DIR__);
        $sInfoFilename = $oInfoFile->getFileName();

        $this->assertFileExists($sInfoFilename);
        $this->assertFileEquals(__DIR__ . "/files/InfoFileTest.txt", $sInfoFilename);
    }
}

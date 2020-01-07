<?php
namespace tests;

use PHPUnit\Framework\TestCase;
use mikehaertl\pdftk\InfoFile;

class InfoFileTest extends TestCase
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

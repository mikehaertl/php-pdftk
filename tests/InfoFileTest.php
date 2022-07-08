<?php
namespace tests;

use PHPUnit\Framework\TestCase;
use mikehaertl\pdftk\InfoFile;

class InfoFileTest extends TestCase
{
    public function testInfoFileCreation()
    {
        $data = array(
            'Info' => array(
                'Creator' => 'php-pdftk',
                'Subject' => 'öäüÖÄÜ',
                'Title' => 'Title x',
            ),
            'PdfID0' => '8b93f76a0b28b720d0dee9a6eb2a780a',
            'PdfID1' => '8b93f76a0b28b720d0dee9a6eb2a780a',
            'NumberOfPages' => '5',
            'Bookmark' => array(
                array(
                    'Title' => 'Title 1',
                    'Level' => 1,
                    'PageNumber' => 1,
                ),
                array(
                    'Title' => 'Title 2',
                    'Level' => 2,
                    'PageNumber' => 10,
                ),
            ),
            'PageMedia' => array(
                array(
                    'Number' => '1',
                    'Rotation' => '0',
                    'Rect' => '0 0 595 842',
                    'Dimensions' => '595 842'
                ),
            ),
            'PageLabel' => array(
                array(
                    'NewIndex' => '1',
                    'Start' => '1',
                    'Prefix' => 'some name 1',
                    'NumStyle' => 'NoNumber',
                ),
            ),
        );

        $oInfoFile = new InfoFile($data, null, null, __DIR__);
        $sInfoFilename = $oInfoFile->getFileName();

        $this->assertFileExists($sInfoFilename);
        $this->assertFileEquals(__DIR__ . '/files/InfoFileTest.txt', $sInfoFilename);
    }

    public function testInfoFileCreationFromLegacyFormat()
    {
        $data = array(
            'Creator' => 'php-pdftk',
            'Subject' => 'öäüÖÄÜ',
            'NumberOfPages' => 17,
            'PdfID0' => '8b93f76a0b28b720d0dee9a6eb2a780a',
            'PdfID1' => '8b93f76a0b28b720d0dee9a6eb2a780a',
            'NumberOfPages' => '5',
            // Mix-in new format
            'Info' => array(
                'Title' => 'Title x',
            ),
            'Bookmark' => array(
                array(
                    'Title' => 'Title 1',
                    'Level' => 1,
                    'PageNumber' => 1,
                ),
                array(
                    'Title' => 'Title 2',
                    'Level' => 2,
                    'PageNumber' => 10,
                ),
            ),
            'PageMedia' => array(
                array(
                    'Number' => '1',
                    'Rotation' => '0',
                    'Rect' => '0 0 595 842',
                    'Dimensions' => '595 842'
                ),
            ),
            'PageLabel' => array(
                array(
                    'NewIndex' => '1',
                    'Start' => '1',
                    'Prefix' => 'some name 1',
                    'NumStyle' => 'NoNumber',
                ),
            ),
        );

        $oInfoFile = new InfoFile($data, null, null, __DIR__);
        $sInfoFilename = $oInfoFile->getFileName();

        $this->assertFileExists($sInfoFilename);
        $this->assertFileEquals(__DIR__ . '/files/InfoFileTest.txt', $sInfoFilename);
    }
}

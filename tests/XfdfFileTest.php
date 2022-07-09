<?php
namespace tests;

use PHPUnit\Framework\TestCase;
use mikehaertl\pdftk\XfdfFile;

class XfdfFileTest extends TestCase
{
    public function testXfdfFileCreation()
    {
        $data = array(
            'name' => 'Jürgen čárka čČćĆđĐ мирано',
            'email' => 'test@email.com',
            'checkbox 1' => 'Yes',
            'address.name' => 'some name',
            'checkbox 2' => 0,
            'radio 1' => 2,
            'address.street' => 'some street',
            "umlauts-in-value" => 'öäüÖÄÜ',
            'some.other.0' => 'val0',
            'some.other.value' => 'val1',
            'some.other.value2' => 'val2',
            'öäüÖÄÜ' => "umlauts in key",
            'special-in-value' => '€ß&()',
            '€ key' => 'special in key',
            'multivalue1' => array('val1', 'val2'),
            'nested.dummy' => 'valX',
            'nested.multivalue2' => array('val3', 'val4'),
            'nullValue' => null,
            'multilinefield' => "line1\nline2\nline3",
        );

        $oXfdfFile = new XfdfFile($data, null, null, __DIR__);
        $sXfdfFilename = $oXfdfFile->getFileName();

        $this->assertFileExists($sXfdfFilename);
        $this->assertFileEquals(__DIR__ . "/files/XfdfFileTest.xfdf", $sXfdfFilename);
    }
}

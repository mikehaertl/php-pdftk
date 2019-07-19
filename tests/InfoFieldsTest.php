<?php
use mikehaertl\pdftk\InfoFields;

class InfoFieldsTest extends \PHPUnit\Framework\TestCase
{
    public function testInfoFieldParsing()
    {
        $infoFields = new InfoFields($this->_testInput);
        $this->assertEquals($this->_parsedResult, $infoFields->__toArray());
    }

    protected $_testInput = <<<EOD
InfoBegin
InfoKey: CreationDate
InfoValue: D:20140709121536+02'00'
InfoBegin
InfoKey: Creator
InfoValue: Writer
InfoBegin
InfoKey: Producer
InfoValue: LibreOffice 4.2
PdfID0: 8b93f76a0b28b720d0dee9a6eb2a780a
PdfID1: 8b93f76a0b28b720d0dee9a6eb2a780a
NumberOfPages: 5
PageMediaBegin
PageMediaNumber: 1
PageMediaRotation: 0
PageMediaRect: 0 0 595 842
PageMediaDimensions: 595 842
PageMediaBegin
PageMediaNumber: 2
PageMediaRotation: 0
PageMediaRect: 0 0 595 842
PageMediaDimensions: 595 842
PageMediaBegin
PageMediaNumber: 3
PageMediaRotation: 0
PageMediaRect: 0 0 595 842
PageMediaDimensions: 595 842
PageMediaBegin
PageMediaNumber: 4
PageMediaRotation: 0
PageMediaRect: 0 0 595 842
PageMediaDimensions: 595 842
PageMediaBegin
PageMediaNumber: 5
PageMediaRotation: 0
PageMediaRect: 0 0 595 842
PageMediaDimensions: 595 842
EOD;
    
    protected $_parsedResult = array(
        "Info" => array(
            "CreationDate" => "D:20140709121536+02'00'",
            "Creator" => "Writer",
            "Producer" => "LibreOffice 4.2"
        ),
        "PdfID0" => "8b93f76a0b28b720d0dee9a6eb2a780a",
        "PdfID1" => "8b93f76a0b28b720d0dee9a6eb2a780a",
        "NumberOfPages" => "5",
        "Bookmark" => array(),
        "PageMedia" => array(
            array(
                "Number" => "1",
                "Rotation" => "0",
                "Rect" => "0 0 595 842",
                "Dimensions" => "595 842"
            ),
            array(
                "Number" => "2",
                "Rotation" => "0",
                "Rect" => "0 0 595 842",
                "Dimensions" => "595 842"
            ),
            array(
                "Number" => "3",
                "Rotation" => "0",
                "Rect" => "0 0 595 842",
                "Dimensions" => "595 842"
            ),
            array(
                "Number" => "4",
                "Rotation" => "0",
                "Rect" => "0 0 595 842",
                "Dimensions" => "595 842"
            ),
            array(
                "Number" => "5",
                "Rotation" => "0",
                "Rect" => "0 0 595 842",
                "Dimensions" => "595 842"
            ),
        )
        
    );
}

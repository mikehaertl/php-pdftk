<?php
use mikehaertl\pdftk\DataFields;

class DataFieldsTest extends \PHPUnit\Framework\TestCase
{
    public function testDataFieldParsing()
    {
        $dataFields = new DataFields($this->_testInput);
        $this->assertEquals($this->_parsedResult, $dataFields->__toArray());
    }

    public function testParsingOfDataFieldWithWarning()
    {
        $dataFields = new DataFields($this->_warningInput);
        $this->assertEquals($this->_parsedWarningResult, $dataFields->__toArray());
    }

    protected $_testInput = <<<DATA
---
FieldType: Text
FieldName: field1
FieldNameAlt: field1_alt
FieldFlags: 0
FieldJustification: Left
---
FieldType: Text
FieldName: field2
FieldNameAlt: field2_alt
FieldFlags: 0
FieldValue: value:with:colons
FieldJustification: Left
---
FieldType: Text
FieldName: field3
FieldNameAlt: field3_alt
FieldFlags: 0
FieldValue:
FieldJustification: Left
---
FieldType: Text
FieldName: field4
FieldNameAlt: field4_alt
FieldFlags: 0
FieldValue: field:with:colons

---more:colons:
and
multi lines

FieldJustification: Left
---
FieldType: Text
FieldName: field5
FieldNameAlt: field5_alt
FieldFlags: 0
FieldValue: field:with:colons

---more:colons:
and
multi lines

FieldValueDefault: default:with:colons

---more:colons:
and
multi lines
   even indented

FieldJustification: Left
DATA;

    protected $_parsedResult = [
        [
            'FieldType' => 'Text',
            'FieldName' => 'field1',
            'FieldNameAlt' => 'field1_alt',
            'FieldFlags' => 0,
            'FieldJustification' => 'Left',
        ],
        [
            'FieldType' => 'Text',
            'FieldName' => 'field2',
            'FieldNameAlt' => 'field2_alt',
            'FieldFlags' => 0,
            'FieldValue' => 'value:with:colons',
            'FieldJustification' => 'Left',
        ],
        [
            'FieldType' => 'Text',
            'FieldName' => 'field3',
            'FieldNameAlt' => 'field3_alt',
            'FieldFlags' => 0,
            'FieldValue' => '',
            'FieldJustification' => 'Left',
        ],
        [
            'FieldType' => 'Text',
            'FieldName' => 'field4',
            'FieldNameAlt' => 'field4_alt',
            'FieldFlags' => 0,
            'FieldValue' => "field:with:colons\n\n---more:colons:\nand\nmulti lines\n",
            'FieldJustification' => 'Left',
        ],
        [
            'FieldType' => 'Text',
            'FieldName' => 'field5',
            'FieldNameAlt' => 'field5_alt',
            'FieldFlags' => 0,
            'FieldValue' => "field:with:colons\n\n---more:colons:\nand\nmulti lines\n",
            'FieldValueDefault' => "default:with:colons\n\n---more:colons:\nand\nmulti lines\n   even indented\n",
            'FieldJustification' => 'Left',
        ]
    ];

    protected $_warningInput = <<<DATA
WARNING: The creator of the input PDF:
   example_pdf_with_password.pdf
   has set an owner password (which is not required to handle this PDF).
   You did not supply this password. Please respect any copyright.
---
FieldType: Text
FieldName: field1
FieldNameAlt: field1_alt
FieldFlags: 0
FieldJustification: Left
---
FieldType: Text
FieldName: field2
FieldNameAlt: field2_alt
FieldFlags: 0
FieldValue: value:with:colons
FieldJustification: Left
DATA;

    protected $_parsedWarningResult = [
        [
            'FieldType' => 'Text',
            'FieldName' => 'field1',
            'FieldNameAlt' => 'field1_alt',
            'FieldFlags' => 0,
            'FieldJustification' => 'Left',
        ],
        [
            'FieldType' => 'Text',
            'FieldName' => 'field2',
            'FieldNameAlt' => 'field2_alt',
            'FieldFlags' => 0,
            'FieldValue' => 'value:with:colons',
            'FieldJustification' => 'Left',
        ],
    ];
}

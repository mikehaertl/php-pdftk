<?php
namespace tests;

use PHPUnit\Framework\TestCase;
use mikehaertl\pdftk\DataFields;

class DataFieldsTest extends TestCase
{
    public function testDataFieldParsing()
    {
        $dataFields = new DataFields($this->_testInput);
        $this->assertEquals($this->_parsedResult, $dataFields->__toArray());
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

FieldJustification: Left
---
FieldType: Choice
FieldName: field6
FieldFlags: 2097152
FieldValue: 1
FieldValue: 2
FieldValue: 3
FieldValue: 4
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
            'FieldValueDefault' => "default:with:colons\n\n---more:colons:\nand\nmulti lines\n",
            'FieldJustification' => 'Left',
        ],
        [
            'FieldType' => 'Choice',
            'FieldName' => 'field6',
            'FieldFlags' => 2097152,
            'FieldValue' => [1, 2, 3, 4],
            'FieldJustification' => 'Left',
        ],
    ];
}

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
---
FieldType: Choice
FieldName: field7
FieldFlags: 524288
FieldValue: -- Value with dashes --
FieldValueDefault: -- Value with dashes --
FieldJustification: Left
FieldStateOption: -- Another value with dashes --
FieldStateOption: Value 2
FieldStateOption: Value 3
DATA;

    protected $_parsedResult = array(
        array(
            'FieldType' => 'Text',
            'FieldName' => 'field1',
            'FieldNameAlt' => 'field1_alt',
            'FieldFlags' => 0,
            'FieldJustification' => 'Left',
        ),
        array(
            'FieldType' => 'Text',
            'FieldName' => 'field2',
            'FieldNameAlt' => 'field2_alt',
            'FieldFlags' => 0,
            'FieldValue' => 'value:with:colons',
            'FieldJustification' => 'Left',
        ),
        array(
            'FieldType' => 'Text',
            'FieldName' => 'field3',
            'FieldNameAlt' => 'field3_alt',
            'FieldFlags' => 0,
            'FieldValue' => '',
            'FieldJustification' => 'Left',
        ),
        array(
            'FieldType' => 'Text',
            'FieldName' => 'field4',
            'FieldNameAlt' => 'field4_alt',
            'FieldFlags' => 0,
            'FieldValue' => "field:with:colons\n\n---more:colons:\nand\nmulti lines\n",
            'FieldJustification' => 'Left',
        ),
        array(
            'FieldType' => 'Text',
            'FieldName' => 'field5',
            'FieldNameAlt' => 'field5_alt',
            'FieldFlags' => 0,
            'FieldValue' => "field:with:colons\n\n---more:colons:\nand\nmulti lines\n",
            'FieldValueDefault' => "default:with:colons\n\n---more:colons:\nand\nmulti lines\n",
            'FieldJustification' => 'Left',
        ),
        array(
            'FieldType' => 'Choice',
            'FieldName' => 'field6',
            'FieldFlags' => 2097152,
            'FieldValue' => array(1, 2, 3, 4),
            'FieldJustification' => 'Left',
        ),
        array(
            'FieldType' => 'Choice',
            'FieldName' => 'field7',
            'FieldFlags' => 524288,
            'FieldValue' => '-- Value with dashes --',
            'FieldValueDefault' => '-- Value with dashes --',
            'FieldStateOption' => array(
                '-- Another value with dashes --',
                'Value 2',
                'Value 3',
            ),
            'FieldJustification' => 'Left',
        ),
    );
}

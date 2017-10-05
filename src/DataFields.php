<?php

namespace mikehaertl\pdftk;

use ArrayObject;

/**
 * Class DataFields
 *
 * @author Ray Holland <raymondaholland+php-pdftk@gmail.com>
 */
class DataFields extends ArrayObject
{
    private $_string;

    private $_array;

    /**
     * DataFields constructor.
     *
     * @param string $input
     * @param int $flags
     * @param string $iterator_class
     */
    public function __construct($input = null, $flags = 0, $iterator_class = "ArrayIterator")
    {
        $this->_string = $input ?: '';
        $this->_array  = $this->parseData($this->_string);

        return parent::__construct($this->_array, $flags, $iterator_class);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->_string;
    }

    /**
     * @return array
     */
    public function __toArray()
    {
        return $this->_array;
    }

    /**
     * Parse the output of dump_data_fields into something usable.
     * Derived from: http://stackoverflow.com/a/34864936/744228
     * Example input (includes '---' line):
     * ---
     * FieldType: Text
     * FieldName: Text1
     * FieldFlags: 0
     * FieldValue: University of Missouri : Ray-Holland
     * FieldValueDefault: University of Missouri : Ray-Holland
     * FieldJustification: Left
     * FieldMaxLength: 99
     *
     * @param $dataString
     * @return array
     */
    private function parseData($dataString)
    {
        $output = array();
        $field  = array();
        $currentField = "";
        foreach (explode("\n", $dataString) as $line) {
            $trimmedLine = trim($line);

            // ($trimmedLine === '' && $currentField != 'FieldValue')
            // Don't start new field for an empty line in a multi-line FieldValue
            if ($trimmedLine === '---' || ($currentField != 'FieldValue' && $trimmedLine === '')) {
                // Block completed; process it
                if (sizeof($field) > 0) {
                    $output[] = $field;
                }
                $field = array();
                continue;
            }

            // Process contents of data block
            $parts = explode(':', $line);
            $key   = null;
            $value = null;

            //Continue through lines already process from FieldValue
            if($currentField == 'FieldValue'
                && $parts[0] != 'FieldJustification'
                && !empty($field['FieldValue'])){

                continue;
            }

            // Handle colon in the value
            if (sizeof($parts) !== 2) {
                $key = $parts[0];
                unset($parts[0]);
                $value = implode(':', $parts);
            }

            $key   = $key   ?: trim($parts[0]);
            $value = $value ?: trim($parts[1]);

            if ($currentField == 'FieldValue' && !empty($value)) {
                $value = $this->getFieldValue($line,$dataString);
            } else if ($currentField == 'FieldValue'){
                $value = "";
            }

            if (isset($field[$key])) {
                $field[$key]   = (array) $field[$key];
                $field[$key][] = $value;
            }
            else {
                $field[$key] = $value;
            }
        }

        // process final block
        if (sizeof($field) > 0) {
            $output[] = $field;
        }

        return $output;
    }

    /**
     * Parses a FieldValue for Multiple Lines e.g.
     * FieldValue: Text
     *
     * MoreText
     * Something
     * ExtraText
     * OtherText
     *
     * FieldJustification: Left
     *
     * @param string        $line      The current line being searched
     * @param string        $dataString
     * @return bool|string
     */
    private function getFieldValue($line,$dataString)
    {
        // Offset 'FieldValue:'
        $pos1 = strpos($dataString, $line) + 11;
        $pos2 = strpos($dataString, "FieldJustification", $pos1);
        $length = $pos2 - $pos1;

        $value = substr(
            $dataString,
            $pos1,
            $length
        );

        return $value;
    }
}

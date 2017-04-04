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
        foreach (explode(PHP_EOL, $dataString) as $line) {
            $trimmedLine = trim($line);
            if ($trimmedLine === '---' || $trimmedLine === '') {
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

            // Handle colon in the value
            if (sizeof($parts) !== 2) {
                $key = $parts[0];
                unset($parts[0]);
                $value = implode(':', $parts);
            }

            $key   = $key   ?: trim($parts[0]);
            $value = $value ?: trim($parts[1]);
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
}

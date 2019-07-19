<?php

namespace mikehaertl\pdftk;

use ArrayObject;

/**
 * This class is an array representation of the dump_data_fields output of
 * pdftk.
 *
 * @author Ray Holland <raymondaholland+php-pdftk@gmail.com>
 * @author Michael Härtl <haertl.mike@gmail.com>
 * @license http://www.opensource.org/licenses/MIT
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
        $this->_array = self::parse($this->_string);

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
     * Parse the output of dump_data_fields into an array.
     *
     * The string to parse can either be a single block of `Xyz:value` lines
     * or a set of such blocks, separated by and starting with `---`.
     *
     *
     * Here's an example:
     *
     * ```
     * ---
     * FieldType: Text
     * FieldName: Text1
     * FieldFlags: 0
     * FieldValue: University of Missouri : Ray-Holland
     * extended line value
     * FieldValueDefault: University of Missouri : Ray-Holland
     * extended line2 value
     * FieldJustification: Left
     * FieldMaxLength: 99
     * ---
     * FieldType: Text
     * FieldName: Text2
     * ...
     * ...
     * ```
     *
     * @param $input the string to parse
     * @return array the parsed result
     */
    public static function parse($input)
    {
        if (strncmp('---', $input, 3) === 0) {
            // Split blocks only if '---' is followed by 'FieldType'
            $blocks = preg_split(
                '/^---(\r\n|\n|\r)(?=FieldType:)/m',
                substr($input, 3)
            );
            return array_map('\mikehaertl\pdftk\DataFields::parseBlock', $blocks);
        } else {
            return self::parseBlock($input);
        }
    }

    /**
     * Parses a block of this form:
     *
     * ```
     * Name1: Value1
     * Name2: Value2
     * Name3: Value3
     * ...
     * ```
     *
     * @param string $block the block to parse
     * @return array the parsed block values indexed by respective names
     */
    public static function parseBlock($block)
    {
        $data = array();
        $lines = preg_split("/(\r\n|\n|\r)/", trim($block));
        $continueKey = null;
        foreach ($lines as $n => $line) {
            if ($continueKey !== null) {
                $data[$continueKey] .= "\n" . $line;
                if (!self::lineContinues($lines, $n, $continueKey)) {
                    $continueKey = null;
                }
            } elseif (preg_match('/([^:]*): ?(.*)/', $line, $match)) {
                $key = $match[1];
                $value = $match[2];
                // Convert multiple keys like 'FieldStateOption' or 'FieldValue'
                // from Choice fields to array
                if (isset($data[$key])) {
                    $data[$key] = (array) $data[$key];
                    $data[$key][] = $value;
                } else {
                    $data[$key] = $value;
                }
                if (self::lineContinues($lines, $n, $key)) {
                    $continueKey = $key;
                }
            }
        }
        return $data;
    }

    /**
     * Checks whether the value for the given line number continues on the next
     * line, i.e. is a multiline string.
     *
     * This can be the case for 'FieldValue'  and 'FieldValueDefault' keys. To
     * find the end of the string we don't simply test for /^Field/, as this
     * would also match multiline strings where a line starts with 'Field'.
     *
     * Instead we assume that the string is always followed by one of these
     * keys:
     *
     *  - 'FieldValue:'
     *  - 'FieldValueDefault:'
     *  - 'FieldJustification:'
     *
     * @param array $lines all lines of the block
     * @param int $n the 0-based index of the current line
     * @param string the key for the value. Only 'FieldValue' and
     * 'FieldValueDefault' can span multiple lines
     * @return bool whether the value continues in line n + 1
     */
    protected static function lineContinues($lines, $n, $key)
    {
        return
            in_array($key, array('FieldValue', 'FieldValueDefault')) &&
            array_key_exists($n + 1, $lines) &&
            !preg_match('/^Field(Value|ValueDefault|Justification):/', $lines[$n + 1]);
    }
}

<?php
namespace mikehaertl\pdftk;

use ArrayObject;

/**
 * Class InfoFields
 * Derived from DataFields
 *
 * @author Burak USGURLU <burak@uskur.com.tr>
 * @license http://www.opensource.org/licenses/MIT
 */
class InfoFields extends ArrayObject
{
    private $_string;

    private $_array;

    /**
     * InfoFields constructor.
     *
     * @param string $input
     * @param int $flags
     * @param string $iterator_class
     */
    public function __construct($input = null, $flags = 0, $iterator_class = "ArrayIterator")
    {
        $this->_string = $input ?: '';
        $this->_array = $this->parseData($this->_string);

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
     * Parse the output of dump_data into something usable.
     * InfoBegin
     * InfoKey: Creator
     * InfoValue: Adobe Acrobat Pro DC 15.0
     * InfoBegin
     * InfoKey: Producer
     * InfoValue: XYZ
     * PdfID0: 1fdce9ed1153ab4c973334b512a67997
     * PdfID1: c7acc878cda02ad7bb401fa8080a8929
     * NumberOfPages: 11
     * BookmarkBegin
     * BookmarkTitle: First bookmark
     * BookmarkLevel: 1
     * BookmarkPageNumber: 1
     *
     * @param $dataString
     * @return array
     */
    private function parseData($dataString)
    {
        $expectType = null;
        $output = array('Info' => array(),'Bookmark' => array(),'PageMedia' => array());
        $field = array();
        $buffer = array();
        foreach (explode(PHP_EOL, $dataString) as $line) {
            $trimmedLine = trim($line);
            if ($trimmedLine === 'InfoBegin') {
                $expectType = 'Info';
                continue;
            }
            if ($trimmedLine === 'BookmarkBegin') {
                $expectType = 'Bookmark';
                continue;
            }
            if ($trimmedLine === 'PageMediaBegin') {
                $expectType = 'PageMedia';
                continue;
            }

            preg_match('/([^:]*): ?(.*)/', $trimmedLine, $match);
            $key = $match[1];
            $value = $match[2];

            if ($expectType === 'Info') {
                if ($key === 'InfoKey') {
                    $buffer['Key'] = $value;
                } elseif ($key === 'InfoValue') {
                    $buffer['Value'] = $value;
                }
                if (isset($buffer['Value'], $buffer['Key'])) {
                    $output['Info'][$buffer['Key']] = $buffer['Value'];
                    $buffer = array();
                    $expectType = null;
                }
                continue;
            }
            if ($expectType !== null) {
                if (strpos($key, $expectType) === 0) {
                    $buffer[str_replace($expectType, '', $key)] = $value;
                } else {
                    throw new \Exception("Unexpected input");
                }
                if ($expectType === 'Bookmark' && isset($buffer['Level'], $buffer['Title'], $buffer['PageNumber'])) {
                    $output[$expectType][] = $buffer;
                    $buffer = array();
                    $expectType = null;
                } elseif ($expectType === 'PageMedia' && isset($buffer['Number'], $buffer['Rotation'], $buffer['Rect'], $buffer['Dimensions'])) {
                    $output[$expectType][] = $buffer;
                    $buffer = array();
                    $expectType = null;
                }
                continue;
            } else {
                $output[$key] = $value;
            }
        }
        return $output;
    }
}

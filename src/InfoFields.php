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
     *
     * The expected string looks similar to this:
     *
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
     * BookmarkBegin
     * BookmarkTitle: Second bookmark
     * BookmarkLevel: 1
     * BookmarkPageNumber: 2
     *
     * @param $dataString
     * @return array
     */
    private function parseData($dataString)
    {
        $output = array();
        foreach (explode(PHP_EOL, $dataString) as $line) {
            $trimmedLine = trim($line);
            // Parse blocks of the form:
            // AbcBegin
            // AbcData1: Value1
            // AbcData2: Value2
            // AbcBegin
            // AbcData1: Value3
            // AbcData2: Value4
            // ...
            if (preg_match('/^(\w+)Begin$/', $trimmedLine, $matches)) {
                // Previous group ended - if any - so add it to output
                if (!empty($group) && !empty($groupData)) {
                    $output[$group][] = $groupData;
                }
                // Now start next group
                $group = $matches[1];   // Info, PageMedia, ...
                if (!isset($output[$group])) {
                    $output[$group] = array();
                }
                $groupData = array();
                continue;
            }
            if (!empty($group)) {
                // Check for AbcData1: Value1
                if (preg_match("/^$group(\w+): ?(.*)$/", $trimmedLine, $matches)) {
                    $groupData[$matches[1]] = $matches[2];
                    continue;
                } else {
                    // Something else, so group ended
                    if (!empty($groupData)) {
                        $output[$group][] = $groupData;
                        $groupData = array();
                    }
                    $group = null;
                }
            }
            if (preg_match('/([^:]*): ?(.*)/', $trimmedLine, $matches)) {
                $output[$matches[1]] = $matches[2];
            }
        }
        // There could be a final group left if it was not followed by another
        // line in the loop
        if (!empty($group) && !empty($groupData)) {
            $output[$group][] = $groupData;
        }

        // Info group is a list of ['Key' => 'x', 'Value' => 'y'], so
        // convert it to ['x' => 'y', ...]
        if (isset($output['Info'])) {
            $data = array();
            foreach ($output['Info'] as $infoGroup) {
                if (isset($infoGroup['Key'], $infoGroup['Value'])) {
                    $data[$infoGroup['Key']] = $infoGroup['Value'];
                }
            }
            $output['Info'] = $data;
        }
        return $output;
    }
}

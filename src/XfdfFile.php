<?php
namespace mikehaertl\pdftk;

use mikehaertl\tmp\File;

/**
 * XfdfFile
 *
 * This class represents a temporary XFDF file that can be used to fill a PDF
 * form with valid unicode characters.
 *
 * @author Tomas Holy <holy@interconnect.cz>
 * @author Michael Härtl <haertl.mike@gmail.com>
 * @license http://www.opensource.org/licenses/MIT
 */
class XfdfFile extends File
{
    // XFDF file header
    const XFDF_HEADER = <<<FDF
<?xml version="1.0" encoding="UTF-8"?>
<xfdf xmlns="http://ns.adobe.com/xfdf/" xml:space="preserve">
<fields>
FDF;

    // XFDF file footer
    const XFDF_FOOTER = <<<FDF
</fields>
</xfdf>
FDF;

    /**
     * Constructor
     *
     * @param array $data the form data as name => value
     * @param string|null $suffix the optional suffix for the tmp file
     * @param string|null $suffix the optional prefix for the tmp file. If null
     * 'php_tmpfile_' is used.
     * @param string|null $directory directory where the file should be
     * created. Autodetected if not provided.
     * @param string|null $encoding of the data. Default is 'UTF-8'.
     */
    public function __construct($data, $suffix = null, $prefix = null, $directory = null, $encoding = 'UTF-8')
    {
        if ($directory === null) {
            $directory = self::getTempDir();
        }
        if ($suffix === null) {
            $suffix = '.xfdf';
        }
        if ($prefix === null) {
            $prefix = 'php_pdftk_xfdf_';
        }

        $tempfile = tempnam($directory, $prefix);
        $this->_fileName = $tempfile . $suffix;
        rename($tempfile, $this->_fileName);

        $fields = $this->parseData($data, $encoding);
        $this->writeXml($fields);
    }

    /**
     * Parses an array of key/value data that may contain keys in dot notation.
     *
     * For example an array like this:
     *
     * ```
     * [
     *     'a' => 'value a',
     *     'b.a' => 'value b.a',
     *     'b.b' => 'value b.b',
     * ]
     * ```
     *
     * Will become:
     *
     * ```
     * [
     *     'a' => 'value a',
     *     'b' => [
     *         'a' => 'value b.a',
     *         'b' => 'value b.a',
     *     ],
     * ]
     *
     *
     * @param mixed $data the data to parse
     * @param string the encoding of the data
     * @return array the result array in UTF-8 encoding with dot keys converted
     * to nested arrays
     */
    protected function parseData($data, $encoding)
    {
        $result = array();
        foreach ($data as $key => $value) {
            if ($encoding !== 'UTF-8' && function_exists('mb_convert_encoding')) {
                $key = mb_convert_encoding($key, 'UTF-8', $encoding);
                $value = mb_convert_encoding($value, 'UTF-8', $encoding);
            }
            $keyParts = explode('.', $key);
            $lastPart = array_pop($keyParts);
            if (count($keyParts) === 0) {
                $result[$lastPart] = $value;
            } else {
                $target = &$result;
                foreach ($keyParts as $part) {
                    if (!isset($target[$part])) {
                        $target[$part] = array();
                    }
                    $target = &$target[$part];
                }
                $target[$lastPart] = $value;
            }
        }
        return $result;
    }

    /**
     * Write the given fields to an XML file
     *
     * @param array $fields the fields in a nested array structure
     */
    protected function writeXml($fields)
    {
        // Use fwrite, since file_put_contents() messes around with character encoding
        $fp = fopen($this->_fileName, 'w');
        fwrite($fp, self::XFDF_HEADER);
        $this->writeFields($fp, $fields);
        fwrite($fp, self::XFDF_FOOTER);
        fclose($fp);
    }

    /**
     * Write the fields to the given filepointer
     *
     * @param int $fp
     * @param mixed[] $fields an array of field values. A value can also be
     * another array in which case a nested field is written.
     */
    protected function writeFields($fp, $fields)
    {
        foreach ($fields as $key => $value) {
            $key = $this->xmlEncode($key);
            fwrite($fp, "<field name=\"$key\">\n");
            if (is_array($value)) {
                $this->writeFields($fp, $value);
            } else {
                $value = $this->xmlEncode($value);
                fwrite($fp, "<value>$value</value>\n");
            }
            fwrite($fp, "</field>\n");
        }
    }

    /**
     * @param string $value the value to encode
     * @return string the value correctly encoded for use in a XML document
     */
    protected function xmlEncode($value)
    {
        return defined('ENT_XML1') ?
            htmlspecialchars($value, ENT_XML1, 'UTF-8') :
            htmlspecialchars($value);
    }
}

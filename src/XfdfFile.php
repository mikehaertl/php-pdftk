<?php

namespace mikehaertl\pdftk;

use mikehaertl\tmp\File;

/**
 * XfdfFile
 *
 * This class represents a temporary XFDF file that can be used to fill a PDF
 * form with valid unicode characters.
 *
 * Form data must be passed to the constructor as an array in this form:
 *
 * ```
 * [
 *     // Field name => field value
 *     'Firstname' => 'John',
 *
 *     // Hierarchical/nested fields in dot notation
 *     'Address.Street' => 'Some Street',
 *     'Address.City' => 'Any City',
 *
 *     // Multi value fields
 *     'Pets' => ['Cat', 'Mouse'],
 * ]
 * ```
 *
 * This will result in the following XML structure (header/footer omitted):
 *
 * ```
 * <field name="Firstname">
 *   <Value>John</Value>
 * </field>
 * <field name="Address">
 *   <field name="Street">
 *     <Value>Some Street</Value>
 *   </field>
 *   <field name="City">
 *     <Value>Any City</Value>
 *   </field>
 * </field>
 * <field name="Pets">
 *   <Value>Cat</Value>
 *   <Value>Mouse</Value>
 * </field>
 * ```
 *
 * @author Tomas Holy <holy@interconnect.cz>
 * @author Michael Härtl <haertl.mike@gmail.com>
 * @license http://www.opensource.org/licenses/MIT
 */
class XfdfFile extends File
{
    // XFDF file header
    private const XFDF_HEADER = <<<FDF
        <?xml version="1.0" encoding="UTF-8"?>
        <xfdf xmlns="http://ns.adobe.com/xfdf/" xml:space="preserve">
        <fields>

        FDF;

    // XFDF file footer
    private const XFDF_FOOTER = <<<FDF
        </fields>
        </xfdf>

        FDF;

    /**
     * Constructor
     *
     *
     * @param array $data the form data as name => value
     * @param string|null $suffix the optional suffix for the tmp file
     * @param string|null $prefix the optional prefix for the tmp file. If null
     * 'php_tmpfile_' is used.
     * @param string|null $directory directory where the file should be
     * created. Autodetected if not provided.
     * @param string|null $encoding of the data. Default is 'UTF-8'.
     */
    public function __construct(
        array $data,
        ?string $suffix = null,
        ?string $prefix = null,
        ?string $directory = null,
        ?string $encoding = 'UTF-8',
    ) {
        if ($directory === null) {
            $directory = self::getTempDir();
        }
        if ($suffix === null) {
            $suffix = '.xfdf';
        }
        if ($prefix === null) {
            $prefix = 'php_pdftk_xfdf_';
        }

        parent::__construct('', $suffix, $prefix, $directory);

        $fields = $this->parseData($data, $encoding);
        $this->writeXml($fields);
    }

    /**
     * Parses an array of key/value data into a nested array structure.
     *
     * The data may use keys in dot notation (#55). Values can also be arrays in
     * case of multi value fields (#148). To make both distinguishable in the
     * result array keys that represent field names are prefixed with `_`. This
     * also allows for numeric field names (#260).
     *
     * For example an array like this:
     *
     * ```
     * [
     *     'a' => 'value a',
     *     'b.x' => 'value b.x',
     *     'b.y' => 'value b.y',
     *
     *     'c.0' => 'val c.0',
     *     'c.1' => 'val c.1',
     *
     *     'd' => ['m1', 'm2'],
     * ]
     * ```
     *
     * Will become:
     *
     * ```
     * [
     *     '_a' => 'value a',
     *     '_b' => [
     *         '_x' => 'value b.x',
     *         '_y' => 'value b.y',
     *     ],
     *     '_c' => [
     *         '_0' => 'value c.0',
     *         '_1' => 'value c.1',
     *     ],
     *     '_d' => [
     *         // notice the missing underscore in the keys
     *         0 => 'm1',
     *         1 => 'm2',
     *     ],
     * ]
     *
     * @param mixed $data the data to parse
     * @param string the encoding of the data
     * @param null|string $encoding
     *
     * @return array the result array in UTF-8 encoding with dot keys converted
     * to nested arrays
     */
    protected function parseData($data, ?string $encoding): array
    {
        $willConvert = $encoding !== 'UTF-8' && function_exists('mb_convert_encoding');
        $result = array();
        foreach ($data as $key => $value) {
            $key = (string) $key;
            if ($willConvert) {
                $key = mb_convert_encoding($key, 'UTF-8', $encoding);
                $value = mb_convert_encoding($value, 'UTF-8', $encoding);
            }
            if (strpos($key, '.') === false) {
                $result['_' . $key] = $value;
            } else {
                /** @var array<string, mixed> $target */
                $target = &$result;
                $keyParts = explode('.', $key);
                $lastPart = array_pop($keyParts);
                foreach ($keyParts as $part) {
                    if (!isset($target['_' . $part])) {
                        $target['_' . $part] = array();
                    }
                    /** @var array<string, mixed> $target */
                    $target = &$target['_' . $part];
                }
                $target['_' . $lastPart] = $value;
            }
        }
        return $result;
    }

    /**
     * Write the given fields to an XML file
     *
     * @param array $fields the fields in a nested array structure
     */
    protected function writeXml(array $fields): void
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
     * @param resource $fp
     * @param mixed[] $fields an array of field values as returned by
     * `parseData()`.
     */
    protected function writeFields($fp, array $fields): void
    {
        foreach ($fields as $key => $value) {
            $key = $this->xmlEncode(substr($key, 1));
            fwrite($fp, "<field name=\"$key\">\n");
            if (!is_array($value)) {
                $value = array($value);
            }
            if (array_key_exists(0, $value)) {
                // Numeric keys: single or multi-value field
                foreach ($value as $val) {
                    $val = $this->xmlEncode($val);
                    fwrite($fp, "<value>$val</value>\n");
                }
            } else {
                // String keys: nested/hierarchical fields
                $this->writeFields($fp, $value);
            }
            fwrite($fp, "</field>\n");
        }
    }

    /**
     * @param string|null $value the value to encode
     * @return string|null the value correctly encoded for use in a XML document
     */
    protected function xmlEncode(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }
        return defined('ENT_XML1') ?
            htmlspecialchars($value, ENT_XML1, 'UTF-8') :
            htmlspecialchars($value);
    }
}

<?php
namespace mikehaertl\pdftk;

use Exception;
use mikehaertl\tmp\File;

/**
 * InfoFile
 *
 * This class represents a temporary dump_data compatible file that can be used to update meta data of PDF
 * with valid unicode characters.
 *
 * @author Burak Usgurlu <burak@uskur.com.tr>
 * @license http://www.opensource.org/licenses/MIT
 */
class InfoFile extends File
{
    /**
     * @var string[] list of valid keys for the document information directory of
     * the PDF. These will be converted into `InfoBegin... InfoKey... InvoValue`
     * blocks on the output.
     *
     * See section 14.3.3 in https://opensource.adobe.com/dc-acrobat-sdk-docs/pdfstandards/PDF32000_2008.pdf
     */
    public static $documentInfoFields = array(
        'Title',
        'Author',
        'Subject',
        'Keywords',
        'Creator',
        'Producer',
        'CreationDate',
        'ModDate',
        'Trapped',
    );

    /**
     * Constructor
     *
     * @param array|InfoFields $data the data in this format:
     * ```
     * [
     *     'Info' => [
     *         'Title' => '...',
     *         'Author' => '...',
     *         'Subject' => '...',
     *         'Keywords' => '...',
     *         'Creator' => '...',
     *         'Producer' => '...',
     *         'CreationDate' => '...',
     *         'ModDate' => '...',
     *         'Trapped' => '...',
     *      ],
     *      'Bookmark' => [
     *          [
     *              'Title' => '...',
     *              'Level' => ...,
     *              'PageNumber' => ...,
     *          ],
     *      ],
     *      'PageMedia' => [ ... ],
     *      'PageLabel' => [ ... ],
     *      // ...
     *  ]
     *  ```
     *  This is the same format as the InfoFields object that is returned
     *  by `getData()` if you cast it to an array. You can also pass such an
     *  (optionally modified) object as input. Some fields like 'NumberOfPages'
     *  or 'PdfID0' are ignored as those are not part of the PDF's metadata.
     *  All array elements are optional.
     * @param string|null $suffix the optional suffix for the tmp file
     * @param string|null $suffix the optional prefix for the tmp file. If null
     * 'php_tmpfile_' is used.
     * @param string|null $directory directory where the file should be
     * created. Autodetected if not provided.
     * @param string|null $encoding of the data. Default is 'UTF-8'. If the
     * data has another encoding it will be converted to UTF-8. This requires
     * the mbstring extension to be installed.
     * @throws Exception on invalid data format or if mbstring extension is
     * missing and data must be converted
     */
    public function __construct($data, $suffix = null, $prefix = null, $directory = null, $encoding = 'UTF-8')
    {
        if ($suffix === null) {
            $suffix = '.txt';
        }
        if ($prefix === null) {
            $prefix = 'php_pdftk_info_';
        }
        if ($directory === null) {
            $directory = self::getTempDir();
        }

        $tempName = tempnam($directory, $prefix);
        $newName = $tempName . $suffix;
        rename($tempName, $newName);
        $this->_fileName = $newName;

        if ($encoding !== 'UTF-8' && !function_exists('mb_convert_encoding')) {
            throw new Exception('mbstring extension required.');
        }

        $fields = '';
        $normalizedData = self::normalize($data);

        foreach ($normalizedData as $block => $items) {
            $fields .= self::renderBlock($block, $items, $encoding);
        }

        // Use fwrite, since file_put_contents() messes around with character encoding
        $fp = fopen($this->_fileName, 'w');
        fwrite($fp, $fields);
        fclose($fp);
    }

    /**
     * Normalize the input data
     *
     * This also converts data from the legacy format (<0.13.0) to the new
     * input format described in the constructor.
     *
     * @param array $data the data to normalize
     * @return array a normalized array in the format described in the constructor
     */
    private static function normalize($data)
    {
        $normalized = array();
        foreach ($data as $key => $value) {
            if (in_array($key, self::$documentInfoFields)) {
                $normalized['Info'][$key] = $value;
            } elseif (is_array($value)) {
                if (!isset($normalized[$key])) {
                    $normalized[$key] = array();
                }
                $normalized[$key] = array_merge($normalized[$key], $value);
            }
        }
        return $normalized;
    }

    /**
     * Render a set of block fields
     *
     * @param string $block like 'Info', 'Bookmark', etc.
     * @param array $items the field items to render
     * @param string $encoding the encoding of the item data
     * @return string the rendered fields
     */
    private static function renderBlock($block, $items, $encoding)
    {
        $fields = '';
        foreach ($items as $key => $value) {
            if ($block === 'Info') {
                $fields .= self::renderField($block, $key, $value, $encoding, true);
            } else {
                $fields .= "{$block}Begin\n";
                foreach ($value as $subKey => $subValue) {
                    $fields .= self::renderField($block, $subKey, $subValue, $encoding, false);
                }
            }
        }
        return $fields;
    }

    /**
     * Render a field in a given input block
     *
     * @param string $prefix the prefix to use for the field
     * @param string $key the field key
     * @param string $value the field value
     * @param string $encoding the endoding of key and value
     * @param bool $isInfo whether it's an 'Info' field
     * @return string the rendered field
     */
    private static function renderField($prefix, $key, $value, $encoding, $isInfo)
    {
        if ($encoding !== 'UTF-8') {
            $value = mb_convert_encoding($value, 'UTF-8', $encoding);
            $key = mb_convert_encoding($key, 'UTF-8', $encoding);
            $value = defined('ENT_XML1') ? htmlspecialchars($key, ENT_XML1, 'UTF-8') : htmlspecialchars($key);
            $key = defined('ENT_XML1') ? htmlspecialchars($value, ENT_XML1, 'UTF-8') : htmlspecialchars($value);
        }
        if ($isInfo) {
            return "InfoBegin\nInfoKey: $key\nInfoValue: $value\n";
        } else {
            return "{$prefix}{$key}: $value\n";
        }

    }
}

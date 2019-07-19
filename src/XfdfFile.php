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
        $suffix = '.xfdf';
        $prefix = 'php_pdftk_xfdf_';

        $this->_fileName = tempnam($directory, $prefix);
        $newName = $this->_fileName . $suffix;
        rename($this->_fileName, $newName);
        $this->_fileName = $newName;

        $fields = array();
        foreach ($data as $key => $value) {
            // Always convert to UTF-8
            if ($encoding !== 'UTF-8' && function_exists('mb_convert_encoding')) {
                $value = mb_convert_encoding($value, 'UTF-8', $encoding);
                $key = mb_convert_encoding($key, 'UTF-8', $encoding);
            }

            //Sanitize input for use in XML
            $sanitizedKey = defined('ENT_XML1') ? htmlspecialchars($key, ENT_XML1, 'UTF-8') : htmlspecialchars($key);
            $sanitizedValue = defined('ENT_XML1') ? htmlspecialchars($value, ENT_XML1, 'UTF-8') : htmlspecialchars($value);

            // Key can be in dot notation like 'Address.name'
            $keys = explode('.', $sanitizedKey);
            $final = array_pop($keys);
            if (count($keys) === 0) {
                $fields[$final] = $sanitizedValue;
            } else {
                $target = & $fields;
                foreach ($keys as $part) {
                    if (!isset($target[$part])) {
                        $target[$part] = array();
                    }
                    $target = & $target[$part];
                }
                $target[$final] = $sanitizedValue;
            }
        }

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
     * another array
     * in which case a nested field is written.
     */
    protected function writeFields($fp, $fields)
    {
        foreach ($fields as $key => $value) {
            if (is_array($value)) {
                fwrite($fp, "<field name=\"$key\">\n");
                $this->writeFields($fp, $value);
                fwrite($fp, "</field>\n");
            } else {
                fwrite($fp, "<field name=\"$key\">\n<value>$value</value>\n</field>\n");
            }
        }
    }
}

<?php
namespace mikehaertl\pdftk;

use mikehaertl\tmp\File;

/**
 * XfdfFile
 *
 * This class represents a temporary XFDF file that can be used to fill a PDF form
 * with valid unicode characters.
 *
 * @author Tomas Holy <holy@interconnect.cz>
 * @author Michael Härtl <haertl.mike@gmail.com>
 * @version 0.2.2
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
     * @param string|null $suffix the optional prefix for the tmp file. If null 'php_tmpfile_' is used.
     * @param string|null $directory directory where the file should be created. Autodetected if not provided.
     * @param string|null $encoding of the data. Default is 'UTF-8'.
     */
    public function __construct($data, $suffix = null, $prefix = null, $directory = null, $encoding = 'UTF-8')
    {
        if ($directory===null) {
            $directory = self::getTempDir();
        }
        $suffix = '.xfdf';
        $prefix = 'php_pdftk_xfdf_';

        $this->_fileName = tempnam($directory,$prefix);
        $newName = $this->_fileName.$suffix;
        rename($this->_fileName, $newName);
        $this->_fileName = $newName;

        $fields = [];
        foreach ($data as $key=>$value) {
            // Always convert to UTF-8
            if ($encoding!=='UTF-8') {
                $value = mb_convert_encoding($value,'UTF-8', $encoding);
                $key = mb_convert_encoding($key,'UTF-8', $encoding);
            }

            //Sanitize input for use in XML
            $sanitizedKey = defined('ENT_XML1') ? htmlspecialchars($key, ENT_XML1, 'UTF-8') : htmlspecialchars($key);
            $sanitizedValue = defined('ENT_XML1') ? htmlspecialchars($value, ENT_XML1, 'UTF-8') : htmlspecialchars($value);

            $fields[] = "<field name=\"$sanitizedKey\">\n<value>$sanitizedValue</value>\n</field>\n";
        }

        // Use fwrite, since file_put_contents() messes around with character encoding
        $fp = fopen($this->_fileName, 'w');
        fwrite($fp, self::XFDF_HEADER);
        fwrite($fp, implode("\n", $fields));
        fwrite($fp, self::XFDF_FOOTER);
        fclose($fp);
    }
}

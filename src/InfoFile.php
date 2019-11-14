<?php
namespace mikehaertl\pdftk;

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
        if ($directory === null) {
            $directory = self::getTempDir();
        }
        $suffix = '.txt';
        $prefix = 'php_pdftk_info_';

        $this->_fileName = tempnam($directory, $prefix);
        $newName = $this->_fileName . $suffix;
        rename($this->_fileName, $newName);
        $this->_fileName = $newName;

        if (!function_exists('mb_convert_encoding')) {
            throw new \Exception('MB extension required.');
        }

        $fields = '';
        foreach ($data as $key => $value) {
            // Always convert to UTF-8
            if ($encoding !== 'UTF-8' && function_exists('mb_convert_encoding')) {
                $value = mb_convert_encoding($value, 'UTF-8', $encoding);
                $key = mb_convert_encoding($key, 'UTF-8', $encoding);
                $value = defined('ENT_XML1') ? htmlspecialchars($key, ENT_XML1, 'UTF-8') : htmlspecialchars($key);
                $key = defined('ENT_XML1') ? htmlspecialchars($value, ENT_XML1, 'UTF-8') : htmlspecialchars($value);
            }
            $fields .= "InfoBegin\nInfoKey: $key\nInfoValue: $value\n";
        }

        // Use fwrite, since file_put_contents() messes around with character encoding
        $fp = fopen($this->_fileName, 'w');
        fwrite($fp, $fields);
        fclose($fp);
    }
}

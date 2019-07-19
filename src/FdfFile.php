<?php
namespace mikehaertl\pdftk;

use mikehaertl\tmp\File;

/**
 * FdfFile
 *
 * This class represents a temporary FDF (1.2) file that can be used to fill a
 * PDF form with valid unicode characters.
 *
 * @author Michael Härtl <haertl.mike@gmail.com>
 * @license http://www.opensource.org/licenses/MIT
 */
class FdfFile extends File
{
    // FDF file header
    const FDF_HEADER = <<<FDF
%FDF-1.2
1 0 obj<</FDF<< /Fields[
FDF;

    // FDF file footer
    const FDF_FOOTER = <<<FDF
] >> >>
endobj
trailer
<</Root 1 0 R>>
%%EOF
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
        $suffix = '.fdf';
        $prefix = 'php_pdftk_fdf_';

        $this->_fileName = tempnam($directory, $prefix);
        $newName = $this->_fileName . $suffix;
        rename($this->_fileName, $newName);
        $this->_fileName = $newName;

        if (!function_exists('mb_convert_encoding')) {
            throw new \Exception('MB extension required.');
        }

        $fields = '';
        foreach ($data as $key => $value) {
            // Create UTF-16BE string encode as ASCII hex
            // See http://blog.tremily.us/posts/PDF_forms/
            $utf16Value = mb_convert_encoding($value, 'UTF-16BE', $encoding);

            /* Also create UTF-16BE encoded key, this allows field names containing
             * german umlauts and most likely many other "special" characters.
             * See issue #17 (https://github.com/mikehaertl/php-pdftk/issues/17)
             */
            $utf16Key = mb_convert_encoding($key, 'UTF-16BE', $encoding);

            // Escape parenthesis
            $utf16Value = strtr($utf16Value, array('(' => '\\(', ')' => '\\)'));
            $fields .= "<</T(" . chr(0xFE) . chr(0xFF) . $utf16Key . ")/V(" . chr(0xFE) . chr(0xFF) . $utf16Value . ")>>\n";
        }

        // Use fwrite, since file_put_contents() messes around with character encoding
        $fp = fopen($this->_fileName, 'w');
        fwrite($fp, self::FDF_HEADER);
        fwrite($fp, $fields);
        fwrite($fp, self::FDF_FOOTER);
        fclose($fp);
    }
}

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

        $fields = '';
        
        foreach ($data as $key=>$value) {
            //Initialize variable
            $field = '';

            //Sanitize input for use in XML
            $sanitizedKey = htmlentities($key);
            $sanitizedValue = htmlentities($value);
            
            //Make <field> part
            $field .= '<field name="'.$sanitizedKey.'">'."\n";
            $field .= '<value>'.$sanitizedValue.'</value>'."\n";
            $field .= '</field>'."\n";
            
            //Add field to $fields
            $fields .= $field;
        }

        // Use fwrite, since file_put_contents() messes around with character encoding
        $fp = fopen($this->_fileName, 'w');
        fwrite($fp, self::XFDF_HEADER);
        fwrite($fp, $fields);
        fwrite($fp, self::XFDF_FOOTER);
        fclose($fp);
    }
}

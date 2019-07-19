<?php
namespace mikehaertl\pdftk;

use mikehaertl\shellcommand\Command as BaseCommand;

/**
 * Command
 *
 * This class represents an pdftk shell command. It extends a standard
 * shellcommand and adds pdftk specific features to add options and operations.
 *
 * @author Michael Härtl <haertl.mike@gmail.com>
 * @license http://www.opensource.org/licenses/MIT
 */
class Command extends BaseCommand
{
    /**
     * @var string the pdftk binary
     */
    protected $_command = 'pdftk';

    /**
     * @var array list of input files to process as array('name' => $filename,
     * 'password' => $pw) indexed by handle
     */
    protected $_files = array();

    /**
     * @var array list of command options, either strings or array with
     * arguments to addArg()
     */
    protected $_options = array();

    /**
     * @var string the operation to perform
     */
    protected $_operation;

    /**
     * @var string|array operation arguments, e.g. a list of page ranges or a
     * filename or tmp file instance
     */
    protected $_operationArgument = array();

    /**
     * @var bool whether to force escaping of the operation argument e.g. for
     * filenames
     */
    protected $_escapeOperationArgument = false;

    /**
     * @param string $name the PDF file to add for processing
     * @param string $handle one or more uppercase letters A..Z to reference
     * this file later.
     * @param string|null $password the owner (or user) password if any
     * @return Command the command instance for method chaining
     * @throws \Exception
     */
    public function addFile($name, $handle, $password = null)
    {
        $this->checkExecutionStatus();
        $file = array(
            'name' => $name,
            'password' => $password,
        );
        $this->_files[$handle] = $file;
        return $this;
    }

    /**
     * @param string $option the pdftk option to add
     * @param string|File|null $argument the argument to add, either string,
     * File instance or null if none
     * @param null|bool whether to escape the option. Default is null meaning
     * use Command default setting.
     * @return Command the command instance for method chaining
     */
    public function addOption($option, $argument = null, $escape = null)
    {
        $this->_options[] = $argument === null ? $option : array($option, $argument, $escape);
        return $this;
    }

    /**
     * @param string $operation the operation to perform
     * @return Command the command instance for method chaining
     */
    public function setOperation($operation)
    {
        $this->checkExecutionStatus();
        $this->_operation = $operation;
        return $this;
    }

    /**
     * @return string|null the current operation or null if none set
     */
    public function getOperation()
    {
        return $this->_operation;
    }

    /**
     * @param string $value the operation argument
     * @param bool $escape whether to escape the operation argument
     * @return Command the command instance for method chaining
     */
    public function setOperationArgument($value, $escape = false)
    {
        $this->checkExecutionStatus();
        $this->_operationArgument = $value;
        $this->_escapeOperationArgument = $escape;
        return $this;
    }

    /**
     * @return string|array|null the current operation argument as string or
     * array or null if none set
     */
    public function getOperationArgument()
    {
        // Typecast to string in case we have a File instance as argument
        return is_array($this->_operationArgument) ? $this->_operationArgument : (string) $this->_operationArgument;
    }

    /**
     * @return int the number of files added to the command
     */
    public function getFileCount()
    {
        return count($this->_files);
    }

    /**
     * Add a page range as used by some operations
     *
     * @param int|string|array $start the start page number or an array of page
     * numbers. If an array, the other arguments will be ignored. $start can
     * also be bigger than $end for pages in reverse order.
     * @param int|string|null $end the end page number or null for single page
     * (or list if $start is an array)
     * @param string|null $handle the handle of the file to use. Can be null if
     * only a single file was added.
     * @param string|null $qualifier the page number qualifier, either 'even'
     * or 'odd' or null for none
     * @param string $rotation the rotation to apply to the pages.
     * @return Command the command instance for method chaining
     */
    public function addPageRange($start, $end = null, $handle = null, $qualifier = null, $rotation = null)
    {
        $this->checkExecutionStatus();
        if (is_array($start)) {
            if ($handle !== null) {
                $start = array_map(function ($p) use ($handle) {
                    return $handle . $p;
                }, $start);
            }
            $range = implode(' ', $start);
        } else {
            $range = $handle . $start;
            if ($end) {
                $range .= '-' . $end;
            }
            $range .= $qualifier . $rotation;
        }
        $this->_operationArgument[] = $range;
        return $this;
    }

    /**
     * @param string|null $filename the filename to add as 'output' option or
     * null if none
     * @return bool whether the command was executed successfully
     */
    public function execute($filename = null)
    {
        $this->checkExecutionStatus();
        $this->processInputFiles();
        $this->processOperation();
        $this->processOptions($filename);
        return parent::execute();
    }

    /**
     * Process input PDF files and create respective command arguments
     */
    protected function processInputFiles()
    {
        $passwords = array();
        foreach ($this->_files as $handle => $file) {
            $this->addArg($handle . '=', $file['name']);
            if ($file['password'] !== null) {
                $passwords[$handle] = $file['password'];
            }
        }
        if ($passwords !== array()) {
            $this->addArg('input_pw');
            foreach ($passwords as $handle => $password) {
                $this->addArg($handle . '=', $password);
            }
        }
    }

    /**
     * Process options and create respective command arguments
     * @param string|null $filename if provided an 'output' option will be
     * added
     */
    protected function processOptions($filename = null)
    {
        // output must be first option after operation
        if ($filename !== null) {
            $this->addArg('output', $filename, true);
        }
        foreach ($this->_options as $option) {
            if (is_array($option)) {
                $this->addArg($option[0], $option[1], $option[2]);
            } else {
                $this->addArg($option);
            }
        }
    }

    /**
     * Process opearation and create respective command arguments
     */
    protected function processOperation()
    {
        if ($this->_operation !== null) {
            $value = $this->_operationArgument ? $this->_operationArgument : null;
            if ($value instanceof TmpFile) {
                $value = (string) $value;
            }
            $this->addArg($this->_operation, $value, $this->_escapeOperationArgument);
        }
    }

    /**
     * Ensure that the command was not exectued yet. Throws exception
     * otherwise.
     * @throws \Exception
     */
    protected function checkExecutionStatus()
    {
        if ($this->getExecuted()) {
            throw new \Exception('Operation was already executed');
        }
    }
}

<?php
/**
 * This file is part of SoloProyectos common library.
 *
 * @author  Gonzalo Chumillas <gchumillas@email.com>
 * @license https://github.com/soloproyectos-php/http/blob/master/LICENSE The MIT License (MIT)
 * @link    https://github.com/soloproyectos-php/http
 */
namespace soloproyectos\http\upload;
use soloproyectos\arr\Arr;
use soloproyectos\http\exception\HttpException;
use soloproyectos\sys\file\SysFile;

/**
 * Class HttpUpload.
 *
 * This class is used to manage uploaded files.
 *
 * @package Http\Upload
 * @author  Gonzalo Chumillas <gchumillas@email.com>
 * @license https://github.com/soloproyectos-php/http/blob/master/LICENSE The MIT License (MIT)
 * @link    https://github.com/soloproyectos-php/http
 */
class HttpUpload
{
    /**
     * File target.
     * @var array of strings
     */
    private $_file;

    /**
     * Available errors.
     * @var array of strings
     */
    private $_errors;

    /**
     * Constructor.
     *
     * For example:
     * ```php
     * // moves the uploaded file
     * $upload = new HttpUpload("my_file");
     * $file = $upload->move("/path/to/your/folder");
     * echo "Your file was uploaded to: $file";
     * ```
     *
     * @param string $name Attribute name
     */
    public function __construct($name)
    {
        // initializes errors
        $this->_errors[UPLOAD_ERR_INI_SIZE]   = "The uploaded file exceeds the upload_max_filesize "
            . "directive in php.ini";
        $this->_errors[UPLOAD_ERR_FORM_SIZE]  = "The uploaded file exceeds the MAX_FILE_SIZE directive "
            . "that was specified in the HTML form";
        $this->_errors[UPLOAD_ERR_PARTIAL]    = "The uploaded file was only partially uploaded";
        $this->_errors[UPLOAD_ERR_NO_FILE]    = "No file was uploaded";
        $this->_errors[UPLOAD_ERR_NO_TMP_DIR] = "Missing a temporary folder";
        $this->_errors[UPLOAD_ERR_CANT_WRITE] = "Failed to write file to disk";
        $this->_errors[UPLOAD_ERR_EXTENSION]  = "A PHP extension stopped the file upload. PHP does not "
            . "provide a way to ascertain which extension caused the file upload to stop; examining "
            . "the list of loaded extensions with phpinfo() may help";

        if (!Arr::is($_FILES, $name)) {
            throw new HttpException("File key not found: $name");
        }

        $this->_file = Arr::get($_FILES, $name);
    }

    /**
     * Gets the uploaded file name.
     *
     * @return string
     */
    public function getName()
    {
        return Arr::get($this->_file, "name");
    }

    /**
     * Gets the uploaded file type.
     *
     * @return string
     */
    public function getType()
    {
        return Arr::get($this->_file, "type");
    }

    /**
     * Gets the uploaded temp name.
     *
     * This function returns the filename of the temp file stored on the server.
     *
     * @return string
     */
    public function getTempName()
    {
        return Arr::get($this->_file, "tmp_name");
    }

    /**
     * Gets the uploaded file size.
     *
     * @return integer
     */
    public function getSize()
    {
        return intval(Arr::get($this->_file, "size"));
    }

    /**
     * Gets the upload error code.
     *
     * When an error has occurred, this function returns the error code.
     *
     * @return integer
     */
    public function getErrorNumber()
    {
        return intval(Arr::get($this->_file, "error"));
    }

    /**
     * Gets the upload error message.
     *
     * When an error has occurred, this function returns the error message.
     *
     * @return string
     */
    public function getErrorMessage()
    {
        $errno = $this->getErrorNumber();
        return Arr::get($this->_errors, $errno, "Unknown error");
    }

    /**
     * Moves the uploaded file.
     *
     * This function moves the uploaded file into a directory or a specific file.
     * If $destination is a directory, the function moves the uploded file to an
     * available filename.
     *
     * This example moves the file into a directory:
     * ```php
     * $upload = new HttpUpload("my_file");
     * $file = $upload->move("/path/to/your/folder");
     * echo "Your file was uploaded to: $file";
     * ```
     *
     * Moves the file to test.txt:
     * ```php
     * $upload = new HttpUpload("my_file");
     * $file = $upload->move("/path/to/your/folder/test.txt");
     * echo "Your file was uploaded to test.txt";
     * ```
     *
     * @param string $destination A directory or a file.
     *
     * @return string Destination filename
     */
    public function move($destination)
    {
        if ($this->getErrorNumber() > 0) {
            throw new HttpException($this->getErrorMessage());
        }

        $filename = is_dir($destination)
            ? SysFile::getAvailName($destination, $this->getName())
            : $destination;

        if (!@move_uploaded_file($this->getTempName(), $filename)) {
            throw new HttpException("Could not move the uploaded file");
        }

        return $filename;
    }
}

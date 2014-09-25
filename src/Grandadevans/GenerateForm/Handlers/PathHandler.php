<?php namespace Grandadevans\GenerateForm\Handlers;

use Illuminate\Config\Repository as Config;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\File;


/**
 * The Console Command for Grandadevans\laravel-form-validator
 *
 * Class PathHandler
 *
 * @author  john Evans<john@grandadevans.com>
 * @licence https://github.com/GrandadEvans/laravel-form-validator/blob/master/LICENSE LICENSE MIT
 * @package Grandadevans\laravel-form-validator
 */
class PathHandler {

    /**
     * The reference to the full form path
     *
     * @var string
     */
    public $fullFormPath;

    /**
     * Array of details holding the directory and name etc
     *
     * @var array
     */
    public $details;

    /**
     * @var string
     */
    public $dir;

    /**
     * @var string
     */
    public $name;


    /**
     * Strip any extra directory separators from any paths such as home//john
     *
     * I would consider using this for both namespace replacement and double
     * directory separator replacement
     * 	    while(preg_replace("/(\\\\)|(\.\.)+/", DS, $in) !== $in) {}
     *
	 * @todo Look at using the above preg_replace
     *
     * @param $in   string
     *
     * @return      string
     */
    public function stripDoubleDirectorySeparators($in)
    {
        while(strstr($in, DS.DS)) {
            $in = str_replace(DS.DS, DS, $in);
        }

        return $in;
    }


	/**
	 * Convert Namespaces to paths
	 *
	 * @param string    $path
	 *
	 * @return string
	 */
	public function convertNamespaceToPath($path)
	{
		return str_replace('\\', DS, $path);
	}


	/**
	 * Set the forms full path to a property
     *
     * @param   Filesystem  $file   An instance of the Filesystem
     * @param   array       $details
     *
     * @return  string
	 */
	public function getFullPath(Filesystem $file, $details)
	{
		$this->file = $file;

		$name = $this->getFileName($details);

		$dir = $this->getDirectory($details);

		$this->makeSureFinalDirectoryExist($dir);

        $fullPath = $this->stripDoubleDirectorySeparators($dir . DS . $name);

		$this->fullFormPath = $fullPath;

        return $fullPath;
	}


	/**
     * Get the short file name eg FooForm.php
     *
	 * @param   array $details
     *
     * @return  string
	 */
	private function getFileName($details)
	{
		return $details['className'] . "Form.php";
	}


    /**
     * Get the directory to use
     *
     * The --dir option takes priority over everything else;
     * then we convert the --namespace into a PSR-0 directory;
     * finally we use the default directory
     *
     * @param $details
     *
     * @return string
     */
    private function getDirectory($details)
	{
		if ( ! empty($details['dir'])) {

			$dir = $details['dir'];

		} elseif ( ! empty($details['namespace'])) {

			$dir = $this->convertNamespaceToPath($details['namespace']);

		} else {

			$dir = app_path() . '/Forms';
		}

		return $dir;
	}


    /**
     * Check to see if the path already exists
     *
     * @param   string    $path
     *
     * @return  bool
     */
    public function doesPathExist($path)
	{
		if (false !== $this->file->exists($path)) {
			return true;
		}

		return false;
	}


    /**
     * Make sure thar the final directory exists, if not then create it
     *
     * @param string    $dir
     */
    private function makeSureFinalDirectoryExist($dir)
	{
		if ( ! $this->file->isDirectory($dir)) {
			$this->file->makeDirectory($dir, 0755, true);
		}
	}
} 

<?php

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Crux\Request Namespace ///////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

namespace Crux\Request;

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Imports //////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

use Crux\Collection;
use Crux\Core\Is;

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Crux\Request\File Class Definition ///////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class File
{
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Properties ///////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This property contains the error associated with the file upload
	 * @access protected
	 * @name \Crux\Request\File::$mError
	 * @package \Crux\Request\File
	 * @var int
	 */
	protected $mError = UPLOAD_ERR_OK;

	/**
	 * This property contains the form field name associated with the file upload
	 * @access protected
	 * @name \Crux\Request\File::$mFormField
	 * @package \Crux\Request\File
	 * @var string
	 */
	protected $mFormField = '';

	/**
	 * This property contains the mime-type associated with the file upload
	 * @access protected
	 * @name \Crux\Request\File::$mMimeType
	 * @package \Crux\Request\File
	 * @var string
	 */
	protected $mMimeType = '';

	/**
	 * This property contains the name associated with the file upload
	 * @access protected
	 * @name \Crux\Request\File::$mName
	 * @package \Crux\Request\File
	 * @var string
	 */
	protected $mName = '';

	/**
	 * This property contains the file size associated with the file upload
	 * @access protected
	 * @name \Crux\Request\File::$mSize
	 * @package \Crux\Request\File
	 * @var int
	 */
	protected $mSize = 0;

	/**
	 * This property contains the path to the uploaded file
	 * @access protected
	 * @name \Crux\Request\File::$mTempFile
	 * @package \Crux\Request\File
	 * @var string
	 */
	protected $mTempFile = '';

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Protected Static Methods /////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method normalizes the $_FILES array when dealing with array named form fields
	 * @access protected
	 * @name \Crux\Request\File::normalize()
	 * @package \Crux\Request\File
	 * @return array<string, array<mixed, mixed>>
	 * @static
	 * @uses \Crux\Core\Is::sequentialArray()
	 */
	protected static function normalize() : array
	{
		// Define our new array
		$arrNew = [];
		// Iterate over the files
		foreach ($_FILES as $strFormField => $arrUpload) {
			// Create the new array
			$arrNew[$strFormField] = [];
			// Iterate over the upload
			foreach ($arrUpload as $strName => $arrFile) {
				// Check for a sequential array
				if (Is::sequentialArray($arrFile)) {
					// Iterate over the current array
					foreach ($arrFile as $intIndex => $arrNestedFile) {
						// Check for the index
						if (!isset($arrNew[$strFormField][$intIndex])) {
							// Create the new array
							$arrNew[$strFormField][$intIndex] = [];
						}
						// Set the value into the new array
						$arrNew[$strFormField][$intIndex][$strName] = $arrNestedFile;
					}
				} else {
					// Set the value
					$arrNew[$strFormField][$strName] = $arrFile;
				}
			}
		}
		// We're done, return the new array
		return $arrNew;
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Public Static Methods ////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method converts the $_FILES global to a Crux construct
	 * @access public
	 * @name \Crux\Request\File:fromRequest()
	 * @package \Crux\Request\File
	 * @return \Crux\Collection\Map
	 * @static
	 * @uses \Crux\Collection\Map::__construct()
	 * @uses \Crux\Collection\Vector::__construct()
	 * @uses \Crux\Core\Is::sequentialArray()
	 * @uses \Crux\Request\File::normalize()
	 * @uses \Crux\Request\File::__construct()
	 * @uses \Crux\Request\File::error()
	 * @uses \Crux\Request\File::mimeType()
	 * @uses \Crux\Request\File::name()
	 * @uses \Crux\Request\File::size()
	 * @uses \Crux\Request\File::$mTempFile
	 */
	public static function fromRequest() : Collection\Map
	{
		// Define our map
		$mapFiles = new Collection\Map();
		// Iterate over the files
		foreach (static::normalize() as $strFormField => $mixFile) {
			// Check the file for an array
			if (Is::sequentialArray($mixFile)) {
				// Define our new vector
				$vecFiles = new Collection\Vector();
				// Iterate over the files
				foreach ($mixFile as $arrFile) {
					// Define our new file instance
					$reqFile = new self($strFormField);
					// Set the error into the instance
					$reqFile->error($arrFile['error']);
					// Set the mime-type into the instance
					$reqFile->mimeType($arrFile['type']);
					// Set the name into the instance
					$reqFile->name($arrFile['name']);
					// Set the size into the instance
					$reqFile->size($arrFile['size']);
					// Set the temporary file name into the instance
					$reqFile->tempFile($arrFile['tmp_name']);
					// Add the file instance to the vector
					$vecFiles->add($reqFile);
				}
				// Add the vector to the map
				$mapFiles->set($strFormField, $vecFiles);
			} else {
				// Define our new file instance
				$reqFile = new self($strFormField);
				// Set the error into the instance
				$reqFile->error($mixFile['error']);
				// Set the mime-type into the instance
				$reqFile->mimeType($mixFile['type']);
				// Set the name into the instance
				$reqFile->name($mixFile['name']);
				// Set the size into the instance
				$reqFile->size($mixFile['size']);
				// Set the temporary file name into the instance
				$reqFile->tempFile($mixFile['tmp_name']);
				// Set the file instance into the map
				$mapFiles->set($strFormField, $reqFile);
			}
		}
		// We're done, return the new map
		return $mapFiles;
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Constructor //////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method constructs a new Crux request file object
	 * @access public
	 * @name \Crux\Request\File::__construct()
	 * @package \Crux\Request\File
	 * @param string $strFormField
	 * @uses \Crux\Request\File::formField()
	 */
	public function __construct(string $strFormField)
	{
		// Set the form field into the instance
		$this->formField($strFormField);
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Public Methods ///////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////




	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Inline Methods ///////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method returns the file error from the instance with the ability to reset it inline
	 * @access public
	 * @name \Crux\Request\File::error()
	 * @package \Crux\Request\File
	 * @param int $intError [null]
	 * @return int
	 * @uses \Crux\Core\Is::null()
	 */
	public function error(int $intError = null) : int
	{
		// Check for a provided error
		if (!Is::null($intError)) {
			// Reset the error into the instance
			$this->mError = $intError;
		}
		// Return the error from the instance
		return $this->mError;
	}

	/**
	 * This method returns the form field name from the instance with the ability to reset it inline
	 * @access public
	 * @name \Crux\Request\File::formField()
	 * @package \Crux\Request\File
	 * @param string $strFormField [null]
	 * @return string
	 * @uses \Crux\Core\Is::null()
	 */
	public function formField(string $strFormField = null) : string
	{
		// Check for a provided form field
		if (!Is::null($strFormField)) {
			// Reset the form field into the instance
			$this->mFormField = $strFormField;
		}
		// Return the form field from the instance
		return $this->mFormField;
	}

	/**
	 * This method returns the file mime-type from the instance with the ability to reset it inline
	 * @access public
	 * @name \Crux\Request\File::mimeType()
	 * @package \Crux\Request\File
	 * @param string $strMimeType [null]
	 * @return string
	 * @uses \Crux\Core\Is::null()
	 */
	public function mimeType(string $strMimeType = null) : string
	{
		// Check for a provided mime-type
		if (!Is::null($strMimeType)) {
			// Reset the mime-type into the instance
			$this->mMimeType = $strMimeType;
		}
		// Return the mime-type from the instance
		return $this->mMimeType;
	}

	/**
	 * This method returns the file name from the instance with the ability to reset it inline
	 * @access public
	 * @name \Crux\Request\File::name()
	 * @package \Crux\Request\File
	 * @param string $strName [null]
	 * @return string
	 * @uses \Crux\Core\Is::null()
	 */
	public function name(string $strName = null) : string
	{
		// Check for a provided name
		if (!Is::null($strName)) {
			// Reset the name into the instance
			$this->mName = $strName;
		}
		// Return the name from the instance
		return $this->mName;
	}

	/**
	 * This method returns the file size from the instance with the ability to reset it inline
	 * @access public
	 * @name \Crux\Request\File::size()
	 * @package \Crux\Request\File
	 * @param int $intSize [null]
	 * @return int
	 * @uses \Crux\Core\Is::null()
	 */
	public function size(int $intSize = null) : int
	{
		// Check for a provided size
		if (!Is::null($intSize)) {
			// Reset the size into the instance
			$this->mSize = $intSize;
		}
		// Return the size from the instance
		return $this->mSize;
	}

	/**
	 * This method returns the temporary file path from the instance with the ability to reset it inline
	 * @access public
	 * @name \Crux\Request\File::tempFile()
	 * @package \Crux\Request\File
	 * @param string $strTempFile [null]
	 * @return string
	 * @uses \Crux\Core\Is::null()
	 */
	public function tempFile(string $strTempFile = null) : string
	{
		// Check for a provided temporary file name
		if (!Is::null($strTempFile)) {
			// Reset the temporary file name into the instance
			$this->mTempFile = $strTempFile;
		}
		// Return the temporary file name into the instance
		return $this->mTempFile;
	}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
} /// End Crux\Request\File Class Definition /////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

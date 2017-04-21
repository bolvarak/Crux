<?php

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Crux\Request Namespace Definition ////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

namespace Crux\Request;

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Imports //////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

use Crux\Collection;
use Crux\Core;
use Crux\Core\Util;
use Crux\Serialize;
use Crux\Type;

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// \Crux\Request\Container Class Definition /////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class Container extends Type\Variant\Map implements \JsonSerializable
{
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Constants ////////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This constant tells the action handler to execute a callback function
	 * @constant
	 * @name \Crux\Request\Container ::ACTION_CALLBACK
	 * @var int
	 */
	const ACTION_CALLBACK = 0x01;

	/**
	 * This constant tells the action handler to do nothing
	 * @constant
	 * @name \Crux\Request\Container ::ACTION_NONE
	 * @var int
	 */
	const ACTION_NONE = 0x02;

	/**
	 * This constant tells the action handler to simply return
	 * @constant
	 * @name \Crux\Request\Container ::ACTION_RETURN
	 * @var int
	 */
	const ACTION_RETURN = 0x03;

	/**
	 * This constant tells the action handler to return the missing parameters
	 * @constant
	 * @name \Crux\Request\Container ::ACTION_RETURN_MISSING
	 * @var int
	 */
	const ACTION_RETURN_MISSING = 0x04;
	/**
	 * This constant tells the action handler to set a default value
	 * @constant
	 * @name \Crux\Request\Container ::ACTION_SET_DEFAULT
	 * @var int
	 */
	const ACTION_SET_DEFAULT = 0x05;

	/**
	 * This constant tells the action handler to throw an exception
	 * @constant
	 * @name \Crux\Request\Container ::ACTION_THROW_EXCEPTION
	 * @var int
	 */
	const ACTION_THROW_EXCEPTION = 0x06;

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Properties ///////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This property contains the authorization object for authenticating request
	 * @access protected
	 * @name \Crux\Request\Container::$mAuthorization
	 * @package \Crux\Request\Container
	 * @var \Crux\Collection\Map
	 */
	protected $mAuthorization;

	/**
	 * This property contains the collective requests in one map
	 * @access protected
	 * @name \Crux\Request\Container ::$mCollective
	 * @var \Crux\Collection\Map
	 */
	protected $mCollective;

	/**
	 * This property contains the exception callback to use
	 * @access protected
	 * @name \Crux\Request\Container ::$mExceptionCallback
	 * @var mixed
	 */
	protected $mExceptionCallback = null;

	/**
	 * This property contains the isolated $_FILES request
	 * @access protected
	 * @name \Crux\Request\Container ::$mFiles
	 * @var \Crux\Collection\Map
	 */
	protected $mFiles;

	/**
	 * This property tells the instance whether or not the model was bootstrapped via CLI or not
	 * @access protected
	 * @name \Crux\Request\Container ::$mFromExternal
	 * @var bool
	 */
	protected $mFromExternal = false;

	/**
	 * This property contains the isolated $_HEADERS request
	 * @access protected
	 * @name \Crux\Request\Container ::$mHeaders
	 * @var \Crux\Collection\Map
	 */
	protected $mHeaders;

	/**
	 * This property contains the original cookie request
	 * @access protected
	 * @name \Crux\Request\Container ::$mOriginalCookies
	 * @var array
	 */
	protected $mOriginalCookies = [];

	/**
	 * This property contains the original uploaded files
	 * @access protected
	 * @name \Crux\Request\Container ::$mOriginalFiles
	 * @var array
	 */
	protected $mOriginalFiles = [];

	/**
	 * This property contains the original query parameters
	 * @access protected
	 * @name \Crux\Request\Container ::$mOriginalGet
	 * @var array
	 */
	protected $mOriginalGet = [];

	/**
	 * This property contains the original headers
	 * @access protected
	 * @name \Crux\Request\Container ::$mOriginalHeaders
	 * @var array
	 */
	protected $mOriginalHeaders = [];

	/**
	 * This property contains the original POST data
	 * @access protected
	 * @name \Crux\Request\Container ::$mOriginalPost
	 * @var array
	 */
	protected $mOriginalPost = [];

	/**
	 * This property contains the original server data
	 * @access protected
	 * @name \Crux\Request\Container ::$mOriginalServer
	 * @var array
	 */
	protected $mOriginalServer = [];

	/**
	 * This property contains the original session storage
	 * @access protected
	 * @name \Crux\Request\Container ::$mOriginalSession
	 * @var array
	 */
	protected $mOriginalSession = [];

	/**
	 * This property contains the raw POST data
	 * @access protected
	 * @name \Crux\Request\Container ::$mRawPost
	 * @var string
	 */
	protected $mRawPost = '';

	/**
	 * This property contains the isolated $_POST request
	 * @access protected
	 * @name \Crux\Request\Container ::$mPost
	 * @var \Crux\Collection\Map
	 */
	protected $mPost;

	/**
	 * This property contains the isolated $_GET request
	 * @access protected
	 * @name \Crux\Request\Container ::$mQuery
	 * @var \Crux\Collection\Map
	 */
	protected $mQuery;

	/**
	 * This property contains the isolated $_SERVER request
	 * @access protected
	 * @name \Crux\Request\Container ::$mServer
	 * @var \Crux\Collection\Map
	 */
	protected $mServer;

	/**
	 * This property contains the CLI source data for the model
	 * @access protected
	 * @name \Crux\Request\Container ::$mSource
	 * @var string
	 */
	protected $mSource = '';

	/**
	 * This property contains the token headers
	 * @access protected
	 * @name \Crux\Request\Container::$mToken
	 * @package \Crux\Request\Container
	 * @var \Crux\Collection\Map
	 */
	protected $mToken;

	/**
	 * This property contains the uploaded files processed by Crux
	 * @access protected
	 * @name \Crux\Request\Container::$mUploads
	 * @package \Crux\Request\Container
	 * @var \Crux\Collection\Map
	 */
	protected $mUploads;

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Factory //////////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method sets up the model interface
	 * @access public
	 * @name \Crux\Request\Container ::Factory()
	 * @param string $strSource ['']
	 * @return \Crux\Request\Container
	 * @static
	 */
	public static function Factory(string $strSource = '') : Container
	{
		// Return the new instance
		return new self($strSource);
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Constructor //////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method sets up the VariantMap with the POST and GET data
	 * @access public
	 * @name \Crux\Request\Container ::__construct()
	 * @package \Crux\Request\Container
	 * @param string $strSource ['']
	 * @uses \Crux\Core\Util::mapFactory()
	 * @uses \Crux\Core\Is::json()
	 * @uses \Crux\Request\Container::reloadFromSource()
	 * @uses \Crux\Request\Container::reload()
	 * @uses \Crux\Collection\Map::getIterator()
	 * @uses \Crux\Request\Container::set()
	 */
	public function __construct(string $strSource = '')
	{
		parent::__construct();
		// Setup the class
		$this->mAuthorization = Core\Util::mapFactory();
		$this->mToken = Core\Util::mapFactory();
		$this->mCollective = Core\Util::mapFactory();
		$this->mFiles = Core\Util::mapFactory();
		$this->mHeaders = Core\Util::mapFactory();
		$this->mPost = Core\Util::mapFactory();
		$this->mQuery = Core\Util::mapFactory();
		$this->mServer = Core\Util::mapFactory();
		// Check the source
		if (($strSource !== '') && (Core\Is::json($strSource))) {
			// Set the source into the instance
			$this->mSource = $strSource;
			// Reload the request data
			$this->reloadFromSource();
		} else {
			// Reload the request data
			$this->reload();
		}
		// Iterate over the collective
		foreach ($this->mCollective->getIterator() as $strName => $mixValue) {
			// Add the property to the instance
			$this->set($strName, $mixValue);
		}
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Implementations //////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method provides a data construct that is JSON serializable
	 * @access public
	 * @name \Crux\Request\Container::jsonSerialize()
	 * @package \Crux\Request\Container
	 * @return string
	 * @uses \Crux\Type\Map::toArray()
	 */
	public function jsonSerialize()
	{
		// Return the map state of the instance
		return $this->toArray();
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Protected Methods ////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method converts CLI files to PHP files
	 * @access protected
	 * @name \Crux\Request\Container ::bootstrapFiles()
	 * @return array
	 * @uses \Crux\Core\Is::associativeArray()
	 * @uses sys_get_temp_dir()
	 * @uses tempnam()
	 * @uses base64_decode()
	 * @uses file_put_contents()
	 * @uses mime_content_type()
	 * @uses filesize()
	 * @uses array_push()
	 */
	protected function bootstrapFiles(array $arrFiles) : array
	{
		// Create the array placeholder
		$arrContainer = [];
		// Iterate over the files
		foreach ($arrFiles as $strName => $arrContent) {
			// Set the form field name
			$arrContainer[$strName] = [];
			// Check for an associative array
			if (Core\Is::associativeArray($arrContent)) {
				// Create the temporary file
				$strFileName = tempnam(sys_get_temp_dir(), 'rdm_file');
				// Write the contents to the file
				file_put_contents($strFileName, base64_decode($arrContainer['data']));
				// Build the file structure
				$arrContainer[$strName] = [
					'name' => $arrContent['name'],
					'type' => mime_content_type($strFileName),
					'tmp_name' => $strFileName,
					'error' => UPLOAD_ERR_OK,
					'size' => filesize($strFileName)
				];
			} else {
				// Iterate over the content files
				foreach ($arrContent as $intIndex => $arrSubFile) {
					// Create the temporary file
					$strFileName = tempnam(sys_get_temp_dir(), 'rdm_file');
					// Write the contents to the file
					file_put_contents($strFileName, base64_decode($arrSubFile['data']));
					// Build the file structure
					array_push($arrContainer[$strName], [
						'name' => $arrContent['name'],
						'type' => mime_content_type($strFileName),
						'tmp_name' => $strFileName,
						'error' => UPLOAD_ERR_OK,
						'size' => filesize($strFileName)
					]);
				}
			}
		}
		// We're done, return the files
		return $arrContainer;
	}

	/**
	 * This method consumes the FILE request into the instance
	 * @access protected
	 * @name \Crux\Request\Container ::consumeFileRequest()
	 * @return void
	 * @uses \Crux\Collection\Map::fromArray()
	 * @uses \Crux\Request\File::fromRequest()
	 */
	protected function consumeFileRequest() : void
	{
		// Create our files placeholder
		$this->mFiles = Collection\Map::fromArray($_FILES);
		// Set the files into the instance
		$this->mUploads = File::fromRequest();
	}

	/**
	 * This method consumes the GET request into the instance
	 * @access protected
	 * @name \Crux\Request\Container ::consumeGetRequest()
	 * @return void
	 */
	protected function consumeGetRequest() : void
	{
		// Set the GET request into the instance
		$this->mGet = Core\Util::mapFactory($this->mOriginalGet);
		// Iterate over the GET parameters
		foreach ($this->mGet->getIterator() as $strName => $mixData) {
			// Add the parameter to the collective
			$this->mCollective->set($strName, $mixData);
		}
	}

	/**
	 * This method consumes the HEADER request into the instance
	 * @access protected
	 * @name \Crux\Request\Container ::consumeHeaderRequest()
	 * @return void
	 */
	protected function consumeHeaderRequest() : void
	{
		// Load the headers
		$this->loadHeaders();
		// Iterate over the HEADER parameters
		foreach ($this->mHeaders->getIterator() as $strName => $mixData) {
			// Add the header to the collective
			$this->mCollective->set($strName, $mixData);
		}
	}

	/**
	 * This method consumes the POST request into the instance
	 * @access protected
	 * @name \Crux\Request\Container ::consumePostRequest()
	 * @return void
	 */
	protected function consumePostRequest() : void
	{
		// Check for content
		if (empty($this->mRawPost)) {
			// We're done
			return;
		}
		// Check the encoding standard
		if (Core\Is::xml($this->mRawPost)) {
			// Instantiate our serializer
			$clsXml = new Serialize\Xml();
			// Decode the POST data
			$this->mPost = $clsXml->deserialize($this->mRawPost)->toCollection();
		} elseif (Core\Is::json($this->mRawPost)) {
			// Instantiate our serializer
			$clsJson = new Serialize\Json();
			// Decode the POST data
			$this->mPost = $clsJson->deserialize($this->mRawPost)->toCollection();
		} elseif (empty($this->mOriginalPost) === false) {
			// Set the POST data
			$this->mPost = Core\Util::mapFactory($this->mOriginalPost);
		} else {
			// Create our POST data container
			$arrPost = [];
			// Decode our POST data
			parse_str($this->mRawPost, $arrPost);
			// Decode the POST data
			$this->mPost = (Core\Is::associativeArray($arrPost) ? Core\Util::mapFactory($arrPost) : Core\Util::vectorFactory($arrPost));
		}
		// Check for a vector
		if (Core\Is::vector($this->mPost)) {
			// Set the post array
			$this->mCollective->set('__POST__', $this->mPost);
		} else {
			// Iterate over the POST data
			foreach ($this->mPost->getIterator() as $strName => $mixData) {
				// Add the parameter to the collective
				$this->mCollective->set($strName, $mixData);
			}
		}
	}

	/**
	 * This method consumes the SERVER data into the instance
	 * @access protected
	 * @name \Crux\Request\Container ::consumeServerData()
	 * @return void
	 */
	protected function consumeServerData() : void
	{
		// Load the SERVER data
		$this->mServer = Core\Util::mapFactory($this->mOriginalServer);
		// Iterate over the SERVER parameters
		foreach ($this->mServer->getIterator() as $strName => $mixData) {
			// Add the server parameter to the collective
			$this->mCollective->set($strName, $mixData);
		}
	}

	/**
	 * This method drills down into an object in the request
	 * @access protected
	 * @name \Crux\Request\Container ::entropy()
	 * @param string $strProperty
	 * @return \Crux\Type\Variant|\Crux\Type\Variant\Map|\Crux\Type\Variant\Scalar|\Crux\Type\Variant\Vector
	 * @uses \Crux\Type\IsVariant::contains()
	 * @uses \Crux\Type\Variant::Factory()
	 * @uses \Crux\Request\Container::get()
	 * @uses stripos()
	 * @uses explode()
	 */
	protected function entropy(string $strProperty)
	{
		// Localize the data
		$varData = $this;
		// Check for a separator
		if (stripos($strProperty, '.') !== false) {
			// Split the data
			$arrParts = explode('.', $strProperty);
			// Iterate over the parts
			foreach ($arrParts as $strProperty) {
				// Check for the data
				if ($varData->contains($strProperty)) {
					// Reset the data
					$varData = $varData->get($strProperty);
				} else {
					// Return an empty variant
					return Type\Variant::Factory(null);
				}
			}
			// Return the data
			return $varData;
		}
		// Return the data
		return $varData->get($strProperty);
	}

	/**
	 * This method loads the headers from the request
	 * @access protected
	 * @name \Crux\Request\Container ::loadHeaders()
	 * @return void
	 */
	protected function loadHeaders()
	{
		// Create the placeholder
		$arrHeaders = [];
		// Check the server
		if (empty($this->mOriginalHeaders)) {
			// Set the iterator
			$mixIterator = $this->mServer->getIterator();
		} else {
			// Set the iterator
			$mixIterator = $this->mOriginalHeaders;
		}
		// Iterate over the SERVER request
		foreach ($mixIterator as $strName => $mixData) {
			// Check for an HTTP header
			if (strtolower(substr($strName, 0, 5)) === 'http_') {
				// Add the header to the map
				$arrHeaders[strtolower(str_ireplace(' ', '-', ucwords(strtolower(str_ireplace('_', ' ', substr($strName, 5))))))] = $mixData;
			}
		}
		// Set the headers into the instance
		$this->mHeaders = Core\Util::mapFactory($arrHeaders);
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Public Methods ///////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method returns the authorization object from the instance with the ability to set it inline
	 * @access public
	 * @name \Crux\Request\Container::authorization()
	 * @package \Crux\Request\Container
	 * @param array<string, mixed>|\Crux\Collection\Map $mixAuth [null]
	 * @return \Crux\Collection\Map
	 * @uses \Crux\Core\Is::null()
	 * @uses \Crux\Core\Util::mapFactory()
	 */
	public function authorization($mixAuth = null) : Collection\Map
	{
		// Check for a provided authorization object
		if (!Core\Is::null($mixAuth)) {
			// Set the authorization object into the instance
			$this->mAuthorization = Core\Util::mapFactory($mixAuth);
		}
		// Return the authorization object from the instance
		return $this->mAuthorization;
	}

	/**
	 * This method checks the GET and POST request for an expected parameter
	 * @access public
	 * @name \Crux\Request\Container ::expect()
	 * @package \Crux\Request\Container
	 * @param string $strParameterName
	 * @param int $intAction [\Crux\Request\Container::ACTION_THROW_EXCEPTION]
	 * @param mixed $mixDefaultValue [null]
	 * @return bool
	 * @throws \Crux\Core\Exception\Request\Container
	 * @uses \Crux\Request\Container::entropy()
	 * @uses \Crux\Type\Variant::isEmpty()
	 * @uses \Crux\Type\Variant::isNull()
	 * @uses \Crux\Core\Exception\Request\Container::__construct()
	 */
	public function expect(string $strParameterName, int $intAction = self::ACTION_THROW_EXCEPTION, $mixDefaultValue = null) : bool
	{
		// Set the data
		$varParam = $this->entropy($strParameterName);
		// Check for the parameter
		if ($varParam->isEmpty() && $varParam->isNull()) {
			// Check for a default value
			if ($intAction === self::ACTION_THROW_EXCEPTION) {
				// Throw the exception
				$this->raise(sprintf('expect() could not find %s in the request', $strParameterName));
			} else {
				// Set the default value
				$this->set($strParameterName, $mixDefaultValue);
			}
			// We're done
			return false;
		}
		// We're done
		return true;
	}

	/**
	 * This method checks the GET and POST request for multiple expected parameters
	 * @access public
	 * @name \Crux\Request\Container::expectMulti()
	 * @param  $tvsParameterNames
	 * @param int $intAction [\Crux\Request\Container::ACTION_THROW_EXCEPTION]
	 * @param $mixDefaultValue [null]
	 * @return bool|\Crux\Collection\Vector
	 * @uses \Crux\Core\Util::vectorFactory()
	 * @uses \Crux\Collection\Vector::getIterator()
	 * @uses \Crux\Request\Container::expect()
	 * @uses \Crux\Collection\Vector::add()
	 * @uses \Crux\Collection\Vector::isEmpty()
	 */
	public function expectMulti($tvsParameterNames, int $intAction = self::ACTION_THROW_EXCEPTION, $mixDefaultValue = null)
	{
		// Create a new vector
		$vecParameterNames = Util::vectorFactory($tvsParameterNames);
		// Create the vector for the missing parameters
		$vecMissingParameters = Util::vectorFactory();
		// Iterate over the vector
		foreach ($vecParameterNames->getIterator() as $strParameterName) {
			// Check for the parameter
			if ($this->expect($strParameterName, $intAction, $mixDefaultValue) === false) {
				// Check the action
				if ($intAction === self::ACTION_RETURN_MISSING) {
					// Add the missing parameter
					$vecMissingParameters->add($strParameterName);
				} else {
					// We're done
					return false;
				}
			}
		}
		// We're done
		return ((($vecMissingParameters->isEmpty() === false) && ($intAction === self::ACTION_RETURN_MISSING)) ? $vecMissingParameters : true);
	}

	/**
	 * This method returns the host name from the server record
	 * @access public
	 * @name \Crux\Request\Container::hostName()
	 * @package \Crux\Request\Container
	 * @return string
	 */
	public function hostName() : string
	{
		// Return the host name from the instance
		return $_SERVER['HTTP_HOST'];
	}

	/**
	 * This method returns the remote user's IP address
	 * @access public
	 * @name \Crux\Request\Container::ipAddress()
	 * @package \Crux\Request\Container
	 * @return string
	 * @uses \Crux\Core\Is::empty()
	 * @uses array_key_exists()
	 */
	public function ipAddress() : string
	{
		// Check the headers
		if (array_key_exists('HTTP_CLIENT_IP', $_SERVER) && !Core\Is::empty($_SERVER['HTTP_CLIENT_IP'])) {
			// Send the IP
			return $_SERVER['HTTP_CLIENT_IP'];
		} else if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER) && !Core\Is::empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			// Send the IP
			return $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else if (array_key_exists('HTTP_X_REAL_IP', $_SERVER) && !Core\Is::empty($_SERVER['HTTP_X_REAL_IP'])) {
			// Send the IP
			return $_SERVER['HTTP_X_REAL_IP'];
		} else {
			// Send the IP
			return $_SERVER['REMOTE_ADDR'];
		}
	}

	/**
	 * This method determines whether or not the POST data is in JSON format
	 * @access public
	 * @name \Crux\Request\Container ::isJson()
	 * @return bool
	 */
	public function isJson() : bool
	{
		// Return the POST JSON status
		return ($this->isPost() ? Core\Is::json($this->mRawPost) : false);
	}

	/**
	 * This method return whether or not the request made was a POST request or not
	 * @access public
	 * @name \Crux\Request\Container ::isPost()
	 * @return bool
	 */
	public function isPost() : bool
	{
		// Return the POST status
		return (empty($this->mRawPost) ? false : true);
	}

	/**
	 * This method determines whether or not the POST data is in XML format
	 * @access public
	 * @name \Crux\Request\Container ::isXml()
	 * @return bool
	 */
	public function isXml() : bool
	{
		// Return the POST XML status
		return ($this->isPost() ? Core\Is::xml($this->mRawPost) : false);
	}

	/**
	 * This method returns whether or not a request key exists
	 * @access public
	 * @name \Crux\Request\Container ::has()
	 * @param string $strKey
	 * @return bool
	 * @uses VariantMap::contains()
	 */
	public function has(string $strKey) : bool
	{
		// Return the comparison
		return $this->contains($strKey);
	}

	/**
	 * This method provides an associative array of Request variables to be used as standalone or in extract()
	 * @access public
	 * @name \Crux\Request\Container ::localize()
	 * @param array $arrNames
	 * @param array $arrKeys
	 * @return array
	 */
	public function localize(array $arrNames, array $arrKeys) : array
	{
		// Create a return array
		$arrReturn = [];
		// Iterate over the keys
		foreach ($arrKeys as $intIndex => $strKey) {
			// Check for the name
			if (isset($arrNames[$intIndex])) {
				// Add the Variant
				$arrReturn[$arrNames[$intIndex]] = $this->get($strKey);
			} else {
				// Add the Variant
				$arrReturn[$strKey] = $this->get($strKey);
			}
		}
		// We're done
		return $arrReturn;
	}

	/**
	 * This method returns the query string from the SERVER headers
	 * @access public
	 * @name \Crux\Request\Container::queryString()
	 * @package \Crux\Request\Container
	 * @return string
	 */
	public function queryString() : string
	{
		// Return the query string
		return ($_SERVER['QUERY_STRING'] ?? '');
	}

	/**
	 * This method raises a clean exception
	 * @access public
	 * @name \Crux\Request\Container $modRequestData
	 * @param string $strMessage
	 * @param int $intCode [0]
	 * @return void
	 * @throws \Crux\Core\Exception\Request\Container
	 */
	public function raise(string $strMessage, int $intCode = 0)
	{
		// Raise the Exception
		throw new Core\Exception\Request\Container($strMessage, $intCode);
	}

	/**
	 * This method returns the HTTP referrer if it exists in the headers
	 * @access public
	 * @name \Crux\Request\Container::referrer()
	 * @package \Crux\Request\Container
	 * @return string
	 */
	public function referrer() : string
	{
		// Return the referrer if it exists
		return ($_SERVER['HTTP_REFERER'] ?? '');
	}

	/**
	 * This method reloads the GET and POST request into the model
	 * @access public
	 * @name \Crux\Request\Container ::reload()
	 * @return void
	 */
	public function reload()
	{
		// Create our GET container
		$arrGet = [];
		// Check for an empty query string
		if (!empty($_SERVER['QUERY_STRING'])) {
			// Load the query string
			parse_str(str_replace('+', '%20', $_SERVER['QUERY_STRING']), $arrGet);
		} else {
			// Default to the $_GET global
			$arrGet = $_GET;
		}
		// Set our data
		$this->mOriginalCookies = ($_COOKIE??[]);
		$this->mOriginalFiles = ($_FILES??[]);
		$this->mOriginalGet = $arrGet;
		$this->mOriginalServer = ($_SERVER??[]);
		$this->mOriginalSession = ($_SESSION??[]);
		$this->mOriginalPost = ($_POST??[]);
		$this->mRawPost = file_get_contents('php://input');
		// Consume the server
		$this->consumeServerData();
		// Consume the HEADER request
		$this->consumeHeaderRequest();
		// Consume the GET request
		$this->consumeGetRequest();
		// Consume the POST request
		$this->consumePostRequest();
		// Consume the FILES request
		$this->consumeFileRequest();
		// Execute the parent Constructor
		parent::__construct($this->mCollective);
	}

	/**
	 * This method bootstraps the request from the CLI
	 * @access public
	 * @name \Crux\Request\Container ::reloadFromSource()
	 * @return void
	 */
	public function reloadFromSource()
	{
		// Try to decode the source
		$arrSource = json_decode($this->mSource, true);
		// Check for a source
		if (empty($arrSource) === false) {
			// Set our data
			$this->mOriginalCookies = (array_key_exists('cookies', $arrSource) ? $arrSource['cookies'] : []);
			$this->mOriginalFiles = (array_key_exists('files', $arrSource) ? $this->bootstrapFiles($arrSource['files']) : []);
			$this->mOriginalGet = (array_key_exists('get', $arrSource) ? $arrSource['get'] : []);
			$this->mOriginalHeaders = (array_key_exists('headers', $arrSource) ? $arrSource['headers'] : []);
			$this->mOriginalServer = (array_key_exists('server', $arrSource) ? $arrSource['server'] : []);
			$this->mOriginalSession = (array_key_exists('session', $arrSource) ? $arrSource['session'] : []);
			$this->mOriginalPost = (array_key_exists('post', $arrSource) ? $arrSource['post'] : []);
			// Consume the server
			$this->consumeServerData();
			// Consume the HEADER request
			$this->consumeHeaderRequest();
			// Consume the GET request
			$this->consumeGetRequest();
			// Consume the POST request
			$this->consumePostRequest();
			// Consume the FILES request
			$this->consumeFileRequest();
			// Execute the parent Constructor
			parent::__construct($this->mCollective);
		}
	}

	/**
	 * This method returns the current request URI
	 * @access public
	 * @name \Crux\Request\Container::requestUri()
	 * @package \Crux\Request\Container
	 * @param bool $blnIncludeQueryString [true]
	 * @return string
	 * @uses str_replace()
	 */
	public function requestUri(bool $blnIncludeQueryString = true) : string
	{
		// Check the flag
		if ($blnIncludeQueryString) {
			// Return the request URI
			return $_SERVER['REQUEST_URI'];
		} else {
			// Return the request URI without the query string
			return str_replace([$this->queryString(), '?'], '', ($_SERVER['REQUEST_URI'] ?? ''));
		}
	}

	/**
	 * This method returns the server name from the server record
	 * @access public
	 * @name \Crux\Request\Container::serverName()
	 * @package \Crux\Request\Container
	 * @return string
	 */
	public function serverName() : string
	{
		// Return the server name from the instance
		return $_SERVER['SERVER_NAME'];
	}

	/**
	 * This method returns the token data from the instance with the ability to reset it inline
	 * @access public
	 * @name \Crux\Request\Container::token()
	 * @package \Crux\Request\Container
	 * @param mixed $mixToken [null]
	 * @return \Crux\Collection\Map
	 * @uses \Crux\Core\Is::null()
	 * @uses \Crux\Core\Util::mapFactory()
	 */
	public function token($mixToken = null) : Collection\Map
	{
		// Check for a provided token
		if (!Core\Is::null($mixToken)) {
			// Set the token into the instance
			$this->mToken = Core\Util::mapFactory($mixToken);
		}
		// Return the token from the instance
		return $this->mToken;
	}

	/**
	 * This method returns the uploaded files from the instance
	 * @access public
	 * @name \Crux\Request\Container::uploads()
	 * @package \Crux\Request\Container
	 * @return \Crux\Collection\Map
	 */
	public function uploads() : Collection\Map
	{
		// Return the uploads collection from the instance
		return $this->mUploads;
	}

	/**
	 * This method returns the remote user's browser string
	 * @access public
	 * @name \Crux\Request\Container::userAgent()
	 * @package \Crux\Request\Container
	 * @return string
	 */
	public function userAgent() : string
	{
		// Return the User-Agent string from the instance
		return $_SERVER['HTTP_USER_AGENT'];
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Getters //////////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method returns the raw POST content
	 * @access protected
	 * @name \Crux\Request\Container ::getRawPost()
	 * @return string
	 */
	public function getRawPost() : string
	{
		// Return the raw post data
		return $this->mRawPost;
	}

	/**
	 * This method returns the server data
	 * @access public
	 * @name \Crux\Request\Container ::getServerData()
	 * @return \Crux\Collection\Map
	 */
	public function getServerData() : Collection\Map
	{

		// Return the server data
		return $this->mServer;
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Setters //////////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method sets external data into the instance
	 * @access public
	 * @name \Crux\Request\Container ::setData()
	 * @param mixed $tvsSource
	 * @return \Crux\Request\Container $this
	 */
	public function setData($tvsSource) : Container
	{
		// Set the data into the instance
		parent::__construct($tvsSource);
		// Return the instance
		return $this;
	}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
} /// End Crux\Request\Container Class Definition ////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

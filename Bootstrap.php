<?php

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Requirements /////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////



///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Crux Class Definition ////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class Crux
{
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Constants ////////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This constant defines auto-loading as Folder\SubFolder\ClassName
	 * @name \Crux::AUTOLOAD_NAMESPACE
	 * @package \Crux
	 * @var int
	 */
	const AUTOLOAD_NAMESPACE = 0x01;

	/**
	 * This constant defines auto-loading as Folder_SubFolder_ClassName
	 * @name \Crux::AUTOLOAD_UNDERSCORE
	 * @package \Crux
	 * @var int
	 */
	const AUTOLOAD_UNDERSCORE = 0x02;

	/**
	 * This constant provides a placeholder for mixed variables in which the value can be any type and Crux\Core\Is::empty() nor Crux\Core\Is::null() will suffice
	 * @name Crux::NoValue
	 * @package \Crux
	 * @var string
	 */
	const NoValue = '__CRUX_NO_VALUE__';

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Environment Constants ////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This constant defines a beta environment
	 * @name \Crux::Beta
	 * @package \Crux
	 * @var string
	 */
	const Beta = 'beta';

	/**
	 * This constant defines a development environment
	 * @name \Crux::Development
	 * @package \Crux
	 * @var string
	 */
	const Development = 'devel';

	/**
	 * This constant defines a production environment
	 * @name \Crux::Production
	 * @package \Crux
	 * @var string
	 */
	const Production = 'prod';

	/**
	 * This constant defines a sandbox environment
	 * @name \Crux::Sandbox
	 * @package \Crux
	 * @var string
	 */
	const Sandbox = 'sbox';

	/**
	 * This constant defines a staging environment
	 * @name \Crux::Staging
	 * @package \Crux
	 * @var string
	 */
	const Staging = 'stg';

	/**
	 * This constant defines a test environment
	 * @name \Crux::Test
	 * @package \Crux
	 * @var string
	 */
	const Test = 'test';

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Protected Static Properties //////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This property contains the application environment level
	 * @access protected
	 * @name \Crux::$mApplicationEnvironment
	 * @package \Crux
	 * @static
	 * @var string
	 */
	protected static $mApplicationEnvironment = self::Development;

	/**
	 * This property contains the application's namespace
	 * @access protected
	 * @name \Crux::$mApplicationNamespace
	 * @package \Crux
	 * @static
	 * @var string
	 */
	protected static $mApplicationNamespace = '';

	/**
	 * This property contains the application path
	 * @access protected
	 * @name \Crux::$mApplicationPath
	 * @package \Crux
	 * @static
	 * @var string
	 */
	protected static $mApplicationPath = '';

	/**
	 * This property contains the base path for auto-loading
	 * @access protected
	 * @name \Crux::$mBasePath
	 * @package \Crux
	 * @static
	 * @var string
	 */
	protected static $mBasePath = '';

	/**
	 * This property contains the Crux path
	 * @access protected
	 * @name \Crux::$mFrameworkPath
	 * @package \Crux
	 * @static
	 * @var string
	 */
	protected static $mFrameworkPath = '';

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Constructor //////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method instantiates a new Crux framework object
	 * @access public
	 * @name \Crux::__construct()
	 * @package \Crux
	 * @uses \Crux::errorHandler()
	 * @uses \Crux::exceptionHandler()
	 * @uses \Crux::classLoader()
	 * @uses set_error_handler()
	 * @uses set_exception_handler()
	 * @uses spl_autoload_register()
	 */
	public function __construct()
	{
		// Define the error handler
		set_error_handler([$this, 'errorHandler'], E_ALL);
		// Define our exception handler
		set_exception_handler([$this, 'exceptionHandler']);
		// Define the class loader
		spl_autoload_register([$this, 'classLoader']);
		// Default the framework path
		self::$mFrameworkPath = dirname(__FILE__);
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// System Constructs ////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method provides the auto-loading or lazy-loading functionality for the framework
	 * @access public
	 * @name Crux::classLoader()
	 * @param string $strClassName
	 * @return void
	 * @throws \Crux\Core\Exception
	 * @uses \Crux\Core\Exception::__construct()
	 * @uses str_ireplace()
	 * @uses dirname()
	 * @uses sprintf()
	 * @uses file_exists()
	 */
	public function classLoader(string $strClassName)
	{
		// Set the loading class name
		$strLoadClass = str_ireplace('\\', DIRECTORY_SEPARATOR, $strClassName);
		// Check for a base path
		if (stripos($strClassName, 'Crux') !== false) {
			// Finalize the loading class name
			$strLoadClass = sprintf('%s%s%s.php', self::$mFrameworkPath, DIRECTORY_SEPARATOR, str_ireplace(sprintf('Crux%s', DIRECTORY_SEPARATOR), '', $strLoadClass));
		} elseif (empty(self::$mBasePath)) {
			// Finalize the loading class name
			$strLoadClass = sprintf('%s%s..%s.php', dirname(__FILE__), DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $strLoadClass);
		} else {
			// Finalize the loading class name
			$strLoadClass = sprintf('%s%s%s.php', self::$mBasePath, DIRECTORY_SEPARATOR, $strLoadClass);
		}
		// Check for the file
		if (!file_exists($strLoadClass)) {
			// Throw the exception
			throw new Crux\Core\Exception(sprintf('[Bootstrap]:  Unable to find class [%s] at [%s]', $strClassName, $strLoadClass));
		}
		// Load the class
		require_once($strLoadClass);
	}

	/**
	 * This method intercepts errors and encodes them into the proper response format
	 * @access public
	 * @name \Crux::errorHandler()
	 * @package \Crux
	 * @param int $intCode [-1]
	 * @param string $strError ['']
	 * @param string $strFile ['']
	 * @param int $intLine [0]
	 * @param array $arrContext [array()]
	 * @return void
	 * @uses \Crux::coreBlock()
	 * @uses \Crux\Request\Router::Initialize()
	 * @uses \Crux\Request\Router::response()
	 * @uses \Crux\Request\Router::xmlRootNode()
	 * @uses \Crux\Request\Response::format()
	 * @uses \Crux\Request\Response::content()
	 * @uses \Crux\Request\Response::value()
	 * @uses \Crux\Request\Response::send()
	 * @uses in_array()
	 * @uses ob_start()
	 * @uses dirname()
	 * @uses ob_get_clean()
	 * @uses sprintf()
	 */
	public function errorHandler(int $intCode = -1, string $strError = '', string $strFile = '', int $intLine = 0, array $arrContext = [])
	{
		// Determine the error
		switch ($intCode) {
			case E_ERROR :
				$strCode = 'Runtime Error';
				break;
			case E_NOTICE :
				$strCode = 'Runtime Notice';
				break;
			case E_PARSE:
				$strCode = 'Parse Error';
				break;
			case E_WARNING :
				$strCode = 'Runtime Warning';
				break;
			case E_CORE_ERROR :
				$strCode = 'Start-Up Error';
				break;
			case E_CORE_WARNING :
				$strCode = 'Start-Up Warning';
				break;
			case E_USER_DEPRECATED :
				$strCode = 'Deprecated';
				break;
			case E_USER_ERROR :
				$strCode = 'Error';
				break;
			case E_USER_NOTICE :
				$strCode = 'Notice';
				break;
			case E_USER_WARNING :
				$strCode = 'Warning';
				break;
			case E_COMPILE_ERROR :
				$strCode = 'Compile Error';
				break;
			case E_COMPILE_WARNING :
				$strCode = 'Compile Warning';
				break;
			default :
				$strCode = 'Unknown Error';
				break;
		}
		// Initialize the router
		Crux\Request\Router::Initialize();
		// Check the response format
		if (!in_array(Crux\Request\Router::response()->format(), [Crux\Request\Response::JSON, Crux\Request\Response::JSONP, Crux\Request\Response::PHP, Crux\Request\Response::XML])) {
			// Start the output buffer
			ob_start();
			// Load the block file
			require_once(self::coreBlock('error.phtml'));
			// Set the content
			Crux\Request\Router::response()->content(ob_get_clean());
		} else {
			// Set the XML root node
			Crux\Request\Router::xmlRootNode('error');
			// Set the exception flag
			Crux\Request\Router::response()->value('exception', false);
			// Set the code into the response
			Crux\Request\Router::response()->value('code', $intCode);
			// Set the type into the response
			Crux\Request\Router::response()->value('type', $strCode);
			// Set the message into the response
			Crux\Request\Router::response()->value('message', sprintf('%s(%s):  %s', $strCode, $intCode, $strError));
			// Set the file into the response
			Crux\Request\Router::response()->value('file', $strFile);
			// Set the line into the response
			Crux\Request\Router::response()->value('line', $intLine);
			// Check the environment
			if (!self::isProduction()) {
				// Set the version into the response
				Crux\Request\Router::response()->value('version', PHP_VERSION);
				// Set the operating system into the response
				Crux\Request\Router::response()->value('os', PHP_OS);
				// Set the context into the response
				Crux\Request\Router::response()->value('context', $arrContext);
			}
		}
		// Send the response code
		// header('Error', true, 400);
		// Send the response
		Crux\Request\Router::response()->send(false);
	}

	/**
	 * This method intercepts exceptions and encodes them into the proper response format
	 * @access public
	 * @name \Crux::exceptionHandler()
	 * @package \Crux
	 * @param \Exception $errException
	 * @return void
	 * @uses \Crux::coreBlock()
	 * @uses \Crux\Request\Router::Initialize()
	 * @uses \Crux\Request\Router::response()
	 * @uses \Crux\Request\Router::xmlRootNode()
	 * @uses \Crux\Request\Response::format()
	 * @uses \Crux\Request\Response::content()
	 * @uses \Crux\Request\Response::value()
	 * @uses \Crux\Request\Response::send()
	 * @uses in_array()
	 * @uses ob_start()
	 * @uses dirname()
	 * @uses ob_get_clean()
	 * @uses sprintf()
	 * @uses get_class()
	 * @uses http_send_status()
	 */
	public function exceptionHandler($errException)
	{
		// Initialize the router
		Crux\Request\Router::Initialize();
		// Check the response format
		if (!in_array(Crux\Request\Router::response()->format(), [Crux\Request\Response::JSON, Crux\Request\Response::JSONP, Crux\Request\Response::PHP, Crux\Request\Response::XML])) {
			// Start the output buffer
			ob_start();
			// Load the block file
			require_once(self::coreBlock('exception.phtml'));
			// Set the content
			Crux\Request\Router::response()->content(ob_get_clean());
		} else {
			// Set the XML root node
			Crux\Request\Router::xmlRootNode('error');
			// Set the exception flag
			Crux\Request\Router::response()->value('exception', true);
			// Set the code into the response
			Crux\Request\Router::response()->value('code', $errException->getCode());
			// Set the type into the response
			Crux\Request\Router::response()->value('type', get_class($errException));
			// Set the message into the response
			Crux\Request\Router::response()->value('message', sprintf('%s(%s):  %s', get_class($errException), $errException->getCode(), $errException->getMessage()));
			// Set the file into the response
			Crux\Request\Router::response()->value('file', $errException->getFile());
			// Set the line into the response
			Crux\Request\Router::response()->value('line', $errException->getLine());
			// Check the environment
			if (!self::isProduction()) {
				// Set the version into the response
				Crux\Request\Router::response()->value('version', PHP_VERSION);
				// Set the operating system into the response
				Crux\Request\Router::response()->value('os', PHP_OS);
				// Set the context into the response
				Crux\Request\Router::response()->value('trace', $errException->getTrace());
			}
		}
		// Send the response code
		// header('Exception', true, (($errException->getCode() === 0) ? 500 : $errException->getCode()));
		// Send the response
		Crux\Request\Router::response()->send(false);
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Static Constructor ///////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method boostraps the framework and initializes the core components
	 * @access public
	 * @name \Crux::Bootstrap()
	 * @package \Crux
	 * @param callable $funCallback
	 * @param string $strBasePath ['']
	 * @param string $strApplicationPath ['']
	 * @return void
	 * @static
	 * @uses \Crux::__construct()
	 * @uses \Crux::basePath()
	 * @uses \Crux::applicationPath()
	 * @uses \Crux::determineApplicationNamespace()
	 * @uses \Crux\Core\Is::empty()
	 * @uses \Crux\Core\Environment::Initialize()
	 * @uses \Crux\Request\Router::Initialize()
	 * @uses \Crux\Request\Router::request()
	 * @uses \Crux\Request\Router::go()
	 * @uses \Crux\Request\Router::response()
	 * @uses \Crux\Request\Response::send()
	 * @uses dirname()
	 * @uses realpath()
	 * @uses call_user_func_array()
	 */
	public static function Bootstrap(callable $funCallback, $strBasePath = '', string $strApplicationPath = '')
	{
		// Instantiate the base class
		$clsCrux = new Crux();
		// Set the base path
		self::basePath(Crux\Core\Is::empty($strBasePath) ? realpath(dirname(__FILE__) . '..' . DIRECTORY_SEPARATOR) : $strBasePath);
		// Set the application path
		self::applicationPath(Crux\Core\Is::empty($strApplicationPath) ? realpath(dirname(__FILE__) . '..' . DIRECTORY_SEPARATOR . 'Application') : $strApplicationPath);
		// Determine the application namespace
		self::determineApplicationNamespace();
		// Environment
		Crux\Core\Environment::Initialize();
		// Router
		Crux\Request\Router::Initialize();
		// Execute the callback
		call_user_func_array($funCallback, [Crux\Request\Router::request()]);
		// Execute the router
		Crux\Request\Router::go();
		// Send the response
		Crux\Request\Router::response()->send();
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Determinants /////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method determines whether or not the application environment is set to Beta
	 * @access public
	 * @name \Crux\isBeta()
	 * @package \Crux
	 * @return bool
	 * @static
	 * @uses \Crux::environment()
	 */
	public static function isBeta() : bool
	{
		// Return the comparison
		return (self::environment() === static::Beta);
	}

	/**
	 * This method determines whether or not the application environment is set to Development
	 * @access public
	 * @name \Crux\isDevelopment()
	 * @package \Crux
	 * @return bool
	 * @static
	 * @uses \Crux::environment()
	 */
	public static function isDevelopment() : bool
	{
		// Return the comparison
		return (self::environment() === static::Development);
	}

	/**
	 * This method determines whether or not the application environment is set to Production
	 * @access public
	 * @name \Crux\isProduction()
	 * @package \Crux
	 * @return bool
	 * @static
	 * @uses \Crux::environment()
	 */
	public static function isProduction() : bool
	{
		// Return the comparison
		return (self::environment() === static::Production);
	}

	/**
	 * This method determines whether or not the application environment is set to Sandbox
	 * @access public
	 * @name \Crux\isSandbox()
	 * @package \Crux
	 * @return bool
	 * @static
	 * @uses \Crux::environment()
	 */
	public static function isSandbox() : bool
	{
		// Return the comparison
		return (self::environment() === static::Sandbox);
	}

	/**
	 * This method determines whether or not the application environment is set to Staging
	 * @access public
	 * @name \Crux\isStaging()
	 * @package \Crux
	 * @return bool
	 * @static
	 * @uses \Crux::environment()
	 */
	public static function isStaging() : bool
	{
		// Return the comparison
		return (self::environment() === static::Staging);
	}

	/**
	 * This method determines whether or not the application environment is set to Test
	 * @access public
	 * @name \Crux\isTest()
	 * @package \Crux
	 * @return bool
	 * @static
	 * @uses \Crux::environment()
	 */
	public static function isTest() : bool
	{
		// Return the comparison
		return (self::environment() === static::Test);
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Protected Static Methods /////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////



	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Public Static Methods ////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method returns the application namespace from storage with the ability to set it inline
	 * @access public
	 * @name \Crux::applicationNamespace()
	 * @package \Crux
	 * @param string $strNamespace ['']
	 * @return string
	 * @static
	 * @uses \Crux\Core\Is::empty()
	 */
	public static function applicationNamespace(string $strNamespace = '') : string
	{
		// Check for a provided application namespace
		if (!Crux\Core\Is::empty($strNamespace)) {
			// Reset the application namespace
			self::$mApplicationNamespace = $strNamespace;
		}
		// Return the application namespace
		return self::$mApplicationNamespace;
	}

	/**
	 * This method returns the application path from storage with the ability to reset it inline
	 * @access public
	 * @name \Crux::applicationPath()
	 * @package \Crux
	 * @param string $strApplicationPath ['']
	 * @return string
	 * @static
	 * @uses \Crux\Core\Is::empty()
	 * @uses substr()
	 * @uses sprintf()
	 * @uses str_replace()
	 * @uses trim()
	 */
	public static function applicationPath(string $strApplicationPath = '') : string
	{
		// Check for a provided application path
		if (!Crux\Core\Is::empty($strApplicationPath)) {
			// Check for a relative path
			if (substr($strApplicationPath, 0, 2) === sprintf('~%s', DIRECTORY_SEPARATOR)) {
				// Reset the application path
				self::$mApplicationPath = sprintf('%s%s%s', self::$mBasePath, DIRECTORY_SEPARATOR, trim(str_replace(sprintf('~%s', DIRECTORY_SEPARATOR), '', $strApplicationPath), DIRECTORY_SEPARATOR));
			} else {
				// Reset the application path
				self::$mApplicationPath = rtrim($strApplicationPath, DIRECTORY_SEPARATOR);
			}
		}
		// Return the application path
		return self::$mApplicationPath;
	}

	/**
	 * This method returns the base path from storage with the ability to reset it inline
	 * @access public
	 * @name \Crux::basePath()
	 * @package \Crux
	 * @param string $strBasePath ['']
	 * @return string
	 * @static
	 * @uses \Crux\Core\Is::empty()
	 * @uses rtrim()
	 */
	public static function basePath(string $strBasePath = '') : string
	{
		// Check for a provided base path
		if (!Crux\Core\Is::empty($strBasePath)) {
			// Reset the base path
			self::$mBasePath = rtrim($strBasePath, DIRECTORY_SEPARATOR);
		}
		// Return the base path
		return self::$mBasePath;
	}

	/**
	 * This method returns the base block path from storage
	 * @access public
	 * @name \Crux::blockPath()
	 * @package \Crux
	 * @return string
	 * @static
	 * @uses sprintf()
	 */
	public static function blockPath() : string
	{
		// Return the block path
		return sprintf('%s%s%s', self::$mApplicationPath, DIRECTORY_SEPARATOR, 'Block');
	}

	/**
	 * This method determines whether or not a class exists without throwing exceptions
	 * @access public
	 * @name \Crux::classExists()
	 * @package \Crux
	 * @param string $strClass
	 * @param bool $blnLoad [true]
	 * @return bool
	 * @static
	 * @uses str_replace()
	 * @uses sprintf()
	 * @uses file_exists()
	 */
	public static function classExists(string $strClass, bool $blnLoad = true) : bool
	{
		// Set the class name
		$strClass = sprintf('%s%s%s.php', self::$mBasePath, DIRECTORY_SEPARATOR, ltrim(str_replace(['.', '\\'], DIRECTORY_SEPARATOR, $strClass), DIRECTORY_SEPARATOR));
		// Check for the class
		if (file_exists($strClass)) {
			// Check the load flag
			if ($blnLoad) {
				// Load the class file
				require_once($strClass);
			}
			// We're done
			return true;
		}
		// We're done
		return false;
	}

	/**
	 * This method returns a core block file path or the core block path
	 * @access public
	 * @name \Crux::coreBlock()
	 * @package \Crux
	 * @param string $strBlock ['']
	 * @return string
	 * @static
	 * @uses \Crux\Core\Is::empty()
	 * @uses \Crux::basePath()
	 * @uses sprintf()
	 * @uses ltrim()
	 */
	public static function coreBlock(string $strBlock = '') : string
	{
		// Check for a provided block
		if (Crux\Core\Is::empty($strBlock)) {
			// Return the block path
			return sprintf('%s%sBlock', self::frameworkPath(), DIRECTORY_SEPARATOR);
		} else {
			// Return the block path
			return sprintf('%s%sBlock%s%s', self::frameworkPath(), DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, ltrim($strBlock, DIRECTORY_SEPARATOR));
		}
	}

	/**
	 * This method determines the application's base namespace from the basePath and applicationPath
	 * @access public
	 * @name \Crux::determineApplication()
	 * @package \Crux
	 * @return void
	 * @static
	 * @uses \Crux::applicationPath()
	 * @uses \Crux::basePath()
	 * @uses \Crux::determineApplicationNamespace()
	 * @uses str_replace()
	 */
	public static function determineApplicationNamespace()
	{
		// Set the application namespace into storage
		self::applicationNamespace(str_replace(DIRECTORY_SEPARATOR, '\\', str_replace(self::basePath(), '', self::applicationPath())));
	}

	/**
	 * This method returns the application environment from storage with the ability to resset it inline
	 * @access public
	 * @name \Crux::environment()
	 * @package \Crux
	 * @param string $strEnvironment ['']
	 * @return string
	 * @uses \Crux\Core\Is::empty()
	 */
	public static function environment(string $strEnvironment = '') : string
	{
		// Check for a provided application environment
		if (!Crux\Core\Is::empty($strEnvironment)) {
			// Set the environment into storage
			self::$mApplicationEnvironment = $strEnvironment;
		}
		// Return the application environment from storage
		return self::$mApplicationEnvironment;
	}

	/**
	 * This method returns the framework path from storage with the ability to reset it inline
	 * @access public
	 * @name \Crux::frameworkPath()
	 * @package \Crux
	 * @param string $strPath ['']
	 * @return string
	 * @static
	 * @uses \Crux\Core\Is::empty()
	 */
	public static function frameworkPath(string $strPath = '') : string
	{
		// Check for a provided framework path
		if (!Crux\Core\Is::empty($strPath)) {
			// Reset the framework path
			self::$mFrameworkPath = $strPath;
		}
		// Return the framework path
		return self::$mFrameworkPath;
	}

	/**
	 * This method returns the base query-block path from storage
	 * @access public
	 * @name \Crux::queryPath()
	 * @package \Crux
	 * @return string
	 * @static
	 * @uses sprintf()
	 */
	public static function queryPath() : string
	{
		// Return the query path
		return sprintf('%s%s%s', self::$mApplicationPath, DIRECTORY_SEPARATOR, 'Query');
	}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
} /// End Crux Class Definition //////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

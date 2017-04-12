<?php

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Crux\Request Namespace ///////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

namespace Crux\Request;

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Imports //////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

use Crux\Core;
use Crux\Transport\Http\Request;

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Crux\Request\Router Class Definition //////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class Router
{
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Constants ////////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This constant defines a post-route hook placement
	 * @name \Crux\Request\Router::HookAfter
	 * @package \Crux\Request\Router
	 * @var int
	 */
	const HookAfter = 0x01;

	/**
	 * This constant defines a pre-route hook placement
	 * @name \Crux\Request\Router::HookBefore
	 * @package \Crux\Request\Router
	 * @var int
	 */
	const HookBefore = 0x02;

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Protected Static Properties //////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This property tells the router that it should try to auto-route the request
	 * @access protected
	 * @name \Crux\Request\Router::$mAutoRouting
	 * @package \Crux\Request\Router
	 * @static
	 * @var bool
	 */
	protected static $mAutoRouting = true;

	/**
	 * This property contains the request container
	 * @access protected
	 * @name \Crux\Request\Router::$mContainer
	 * @package \Crux\Request\Router
	 * @static
	 * @var \Crux\Request\Container
	 */
	protected static $mContainer;

	/**
	 * This property contains the default endpoint to use if one is not found
	 * @access protected
	 * @name \Crux\Request\Router::$mDefaultEndpoint
	 * @package \Crux\Request\Router
	 * @static
	 * @var string
	 */
	protected static $mDefaultEndpoint = '';

	/**
	 * This property contains the endpoint class name handling the current request
	 * @access protected
	 * @name \Crux\Request\Router::$mEndpoint
	 * @package \Crux\Request\Router
	 * @static
	 * @var string
	 */
	protected static $mEndpoint = '';

	/**
	 * This property contains the endpoint method name handling the current request
	 * @access protected
	 * @name \Crux\Request\Router::$mEndpointMethod()
	 * @package \Crux\Request\Router
	 * @static
	 * @var string
	 */
	protected static $mEndpointMethod = '';

	/**
	 * This property contains the defined routes that are searched if autoRouting is turned off or cannot be determined
	 * @access protected
	 * @name \Crux\Request\Router::$mDefinedRoutes
	 * @package \Crux\Request\Router
	 * @static
	 * @var array<string, \Crux\Request\Route>
	 */
	protected static $mDefinedRoutes = [];

	/**
	 * This property contains route match types for strict routing
	 * @access protected
	 * @name \Crux\Request\Router::$mMatchTypes
	 * @package \Crux\Request\Router
	 * @static
	 * @var array<string, string>
	 */
	protected static $mMatchTypes = [];

	/**
	 * This property contains the namespace to ignore in the routes
	 * @access protected
	 * @name \Crux\Request\Router::$mNamespace
	 * @package \Crux\Request\Router
	 * @static
	 * @var string
	 */
	protected static $mNamespace = '';

	/**
	 * This property contains pre-route hooks
	 * @access protected
	 * @name \Crux\Request\Router::$mPreHooks
	 * @package \Crux\Request\Router
	 * @static
	 * @var array<int, callable>
	 */
	protected static $mPreHooks = [];

	/**
	 * This property contains post-route hooks
	 * @access protected
	 * @name \Crux\Request\Router::$mPostHooks
	 * @package \Crux\Request\Router
	 * @static
	 * @var array<int, callable>
	 */
	protected static $mPostHooks = [];

	/**
	 * This property contains the response container
	 * @access protected
	 * @name \Crux\Request\Router::$mResponse
	 * @package \Crux\Request\Router
	 * @static
	 * @var \Crux\Request\Response
	 */
	protected static $mResponse;

	/**
	 * This property contains the root node name for XML responses
	 * @access protected
	 * @name \Crux\Request\Router::$mXmlRootNode
	 * @package \Crux\Request\Router
	 * @static
	 * @var string
	 */
	protected static $mXmlRootNode = 'payLoad';

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Static Constructor ///////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method initializes the router with defaults
	 * @access public
	 * @name \Crux\Request\Router::Initialize()
	 * @package \Crux\Request\Router
	 * @return void
	 * @static
	 * @uses \Crux\Request\Router::determineFormat()
	 * @uses \Crux\Request\Container::__construct()
	 * @uses \Crux\Request\Response::__construct()
	 */
	public static function Initialize()
	{
		// Reset the auto-routing flag
		static::$mAutoRouting = true;
		// Reset the request container
		static::$mContainer = new Container();
		// Reset the response container
		static::$mResponse = new Response();
		// Empty the defined routes
		static::$mDefinedRoutes = [];
		// Reset the route match types
		static::$mMatchTypes = [
			'aln' => '[0-9A-Za-z]++',
			'bln' => 'true|false|yes|no|y|n|1|0|on|off',
			'flt' => '[+-]?(\d*\.\d+([eE]?[+-]?\d+)?|\d+[eE][+-]?\d+)',
			'fmt' => 'css|hack|html|js|json|jsonp|php|text|txt|xml',
			'int' => '[0-9]++',
			'h' => '[0-9A-Fa-f]++',
			'*' => '.+?',
			'**' => '.++',
			'.' => '[^/\.]++',
			'aid' => '([A-Z]{1}[0-9]+)',
			'bid' => '([A-Z]{1}[0-9]+-([R|V]{1})?[0-9]+)',
			'rid' => '([A-Z]{1}[0-9]+-[R|V]{1}[0-9]+)',
			'rpt' => '(api|html|pdf)',
			'rrid' => '([A-Z]{1}[0-9]+-[0-9]+)',
			'tel' => '[+]?[0-9]{11}',
			'utc' => '[+,-]?[0-9]{1,3}',
			'vid' => '([A-Z]{1}[0-9]+-[V]{1}[0-9]+)'
		];
		// Set the default request URI
		$strRequestUri = str_replace(sprintf('?%s', static::$mContainer->queryString()), '', static::$mContainer->requestUri());
		// Determine the format
		static::determineFormat($strRequestUri);
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Protected Static Methods /////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method auto-routes the request using namespaces
	 * @access protected
	 * @name \Crux\Request\Router::autoRoute()
	 * @package \Crux\Request\Router
	 * @param string $strRequestUri
	 * @return bool
	 * @uses \Crux\Request\Router::determineFormat()
	 * @uses \Crux\Request\Router::determineTrueType()
	 * @uses \Crux\Request\Router::processBeforeHooks()
	 * @uses \Crux\Request\Endpoint::__bootstrap()
	 * @uses \Crux\Request\Endpoint::__init()
	 * @uses \Crux\Core\Is::empty()
	 * @uses \Crux::classExists()
	 * @uses str_replace()
	 * @uses str_ireplace()
	 * @uses explode()
	 * @uses urldecode()
	 * @uses array_walk()
	 * @uses ucfirst()
	 * @uses implode()
	 * @uses trim()
	 * @uses sprintf()
	 * @uses get_class_methods()
	 * @uses array_values()
	 * @uses count()
	 * @uses strtolower()
	 * @uses call_user_func_array()
	 */
	protected static function autoRoute(string $strRequestUri) : bool
	{
		// Determine the format
		static::determineFormat($strRequestUri);
		// Split the request URI
		$arrParts = explode('/', str_ireplace(str_replace('\\', '/', static::$mNamespace), '', $strRequestUri));
		// Define our working container
		$strWorkingContainer = static::$mNamespace;
		// Define our matched flags
		$blnClassMatched = false;
		$blnMethodMatched = false;
		// Iterate over the parts
		for ($intPart = 0; $intPart < count($arrParts); ++$intPart) {
			// Check for an empty part
			if (Core\Is::empty($arrParts[$intPart])) {
				// Remove the current key
				array_shift($arrParts);
				// Decrement the part
				--$intPart;
				// Next iteration please
				continue;
			}
			// Make the replacement
			$arrPart = explode('-', str_replace('_', '-', urldecode($arrParts[$intPart])));
			// Walk the array
			array_walk($arrPart, function(&$strValue, $intKey) {
				// Return the data
				$strValue = ucfirst($strValue);
			});
			// Add the part to the working container
			$strWorkingContainer = sprintf('%s\%s', $strWorkingContainer, trim(implode('', $arrPart)));
			// Shift the value on the copy
			array_shift($arrParts);
			// Try to load the class
			try {
				// Check the class
				if (class_exists($strWorkingContainer) || \Crux::classExists($strWorkingContainer)) {
					// Reset the matched flag
					$blnClassMatched = true;
					// Break out of the loop
					break;
				}
			} catch (\Exception $errException) {
				// Next iteration please
				continue;
			}
		}
		// Check for a class match
		if (!$blnClassMatched && !Core\Is::null(static::$mDefaultEndpoint)) {
			// Set the working container
			$strWorkingContainer = static::$mDefaultEndpoint;
		}
		// Set the endpoint
		static::endpoint($strWorkingContainer);
		// Instantiate the endpoint
		$clsEndpoint = new $strWorkingContainer();
		// Bootstrap the endpoint
		$clsEndpoint->__bootstrap(static::$mContainer, static::$mResponse);
		// Define the method container
		$strMethod = '';
		// Grab the public methods from the endpoint
		$arrMethods = get_class_methods($clsEndpoint);
		// Reset the working container
		$strWorkingContainer = '';
		// Re-index the array
		$arrParts = array_values($arrParts);
		// Iterate over the rest of the parts
		for ($intPart = 0; $intPart < count($arrParts); ++$intPart) {
			// Check the part
			if (Core\Is::empty($arrParts[$intPart])) {
				// Unset the part
				array_shift($arrParts);
				// Decrement the part
				--$intPart;
				// Next iteration please
				continue;
			}
			// Make the replacement
			$arrPart = explode('-', str_replace('_', '-', urldecode($arrParts[$intPart])));
			// Add the part to the working container
			$strWorkingContainer = sprintf('%s%s', $strWorkingContainer, trim(implode('', $arrPart)));
			// Unset the part
			array_shift($arrParts);
			// Define a found flag
			$blnFound = false;
			// Iterate over the methods
			for ($intMethod = 0; $intMethod < count($arrMethods); ++$intMethod) {
				// Check the method
				if (strtolower($strWorkingContainer) === strtolower($arrMethods[$intMethod])) {
					// Set the method
					$strMethod = $arrMethods[$intMethod];
					// We've found the the method
					$blnFound = true;
					// Break out of the iteration
					break;
				}
			}
			// Check the found flag
			if ($blnFound) {
				// Reset the matched flag
				$blnMethodMatched = true;
				// We're done with the iteration
				break;
			}
		}
		// Check the flag
		if (!$blnMethodMatched && method_exists($clsEndpoint, 'default')) {
			// Reset the method
			$strMethod = 'default';
		}
		// Walk the array
		array_walk($arrParts, function(&$strValue, $intKey) {
			// Reset the value
			$strValue = static::determineTrueType(urldecode($strValue));
		});
		// Set the endpoint method
		static::endpointMethod($strMethod);
		// Process the pre-route hooks
		static::processBeforeHooks(get_class($clsEndpoint), $strMethod);
		// Initialize the endpoint
		$clsEndpoint->__init();
		// Call the method
		call_user_func_array([$clsEndpoint, $strMethod], $arrParts);
		// We're done
		return true;
	}

	/**
	 * This method compiles a route match type
	 * @access protected
	 * @name \Crux\Request\Router::compileRoute()
	 * @package \Crux\Request\Router
	 * @param string $strRoute ['']
	 * @return string
	 * @static
	 * @uses preg_match_all()
	 * @uses array_key_exists()
	 * @uses str_replace()
	 */
	protected function compileRoute(string $strRoute = '') : string
	{
		// Check for route matches
		if (preg_match_all('`(/|\.|)\[([^:\]]*+)(?::([^:\]]*+))?\](\?|)`', $strRoute, $arrMatches, PREG_SET_ORDER)) {
			// Localize the match types
			$arrMatchTypes = static::$mMatchTypes;
			// Iterate over the matches
			foreach ($arrMatches as $arrMatch) {
				// Grab the parts
				list($strBlock, $strPrefix, $strType, $strParam, $strOptional) = $arrMatch;
				// Check for a type
				if (array_key_exists($strType, $arrMatchTypes)) {
					// Set the type
					$strType = $arrMatchTypes[$strType];
				}
				// Check the prefix
				if ($strPrefix === '.') {
					// Escape the prefix
					$strPrefix = '\.';
				}
				// Set the pattern
				$strPattern = '(?:' . (($strPrefix !== '') ? $strPrefix : null) . '(' . (($strParam !== '') ? '?P<' . $strParam . '>' : null) . $strType . '))' . (($strOptional !== '') ? '?' : null);
				// Make the replacement
				$strRoute = str_replace($strBlock, $strPattern, $strRoute);
			}
		}
		// Return the route
		return '`^' . $strRoute . '$`u';
	}

	/**
	 * This method determines the response format from the request URI
	 * @access protected
	 * @name \Crux\Request\Router::determineFormat()
	 * @package \Crux\Request\Router
	 * @param string $strRequestUri
	 * @return void
	 * @static
	 * @uses \Crux\Request\Router::response()
	 * @uses \Crux\Request\Router::request()
	 * @uses \Crux\Request\Response::format()
	 * @uses \Crux\Request\Container::has()
	 * @uses preg_match()
	 * @uses strtolower()
	 * @uses substr_replace()
	 */
	protected static function determineFormat(string &$strRequestUri)
	{
		// Check for explicit definitions
		if (preg_match('/(\/|\.)(css|htm|html|xhtml|dhtml|js|json|jsonp||pdf|php|phtm|phtml|text|txt|xml)$/i', $strRequestUri, $arrMatches)) {
			// Switch over the results
			switch (strtolower($arrMatches[2])) {
				case 'css'  :
					static::response()->format(Response::CSS);
					break;
				case 'htm'  :
					static::response()->format(Response::HTML);
					break;
				case 'html' :
					static::response()->format(Response::HTML);
					break;
				case 'xhtml' :
					static::response()->format(Response::HTML);
					break;
				case 'dhtml' :
					static::response()->format(Response::HTML);
					break;
				case 'js' :
					static::response()->format(Response::JAVASCRIPT);
					break;
				case 'json' :
					static::response()->format(Response::JSON);
					break;
				case 'jsonp' :
					static::response()->format(Response::JSONP);
					break;
				case 'pdf' :
					static::response()->format(Response::PDF);
					break;
				case 'php' :
					static::response()->format(Response::PHP);
					break;
				case 'phtm' :
					static::response()->format(Response::PHP);
					break;
				case 'phtml' :
					static::response()->format(Response::PHP);
					break;
				case 'text' :
					static::response()->format(Response::TEXT);
					break;
				case 'txt' :
					static::response()->format(Response::TEXT);
					break;
				case 'xml' :
					static::response()->format(Response::XML);
					break;
				default:
					static::response()->format(Response::JSON);
					break;
			}
			// Remove the format from the request URI
			$strRequestUri = substr_replace($strRequestUri, '', (strlen($arrMatches[0]) * -1), strlen($arrMatches[0]));
		}
		// Check for a callback
		if (static::request()->has('callback') && (static::response()->format() === Response::JSON)) {
			// Reset the format to JSONP
			static::response()->format(Response::JSONP);
		}
	}

	/**
	 * This method determines the PHP data type for a URL value
	 * @access protected
	 * @name \Crux\Request\Router::determineTrueType()
	 * @package \Crux\Request\Router
	 * @param mixed $mixSource
	 * @return bool|float|int|mixed|null|string
	 * @static
	 * @uses \Crux\Core\Is::string()
	 * @uses \Crux\Core\Is::null()
	 * @uses preg_match()
	 * @uses strtolower()
	 * @uses in_array()
	 * @uses filter_var()
	 * @uses floatval()
	 * @uses intval()
	 */
	protected static function determineTrueType($mixSource)
	{
		// Check the variable
		if (!Core\Is::string($mixSource)) {                                  // Non-String
			// Return the variable
			return $mixSource;
		} elseif (preg_match('/^(true|false|on|off)$/', $mixSource)) {       // Boolean
			// Return the true boolean value
			return in_array(strtolower($mixSource), ['true', 'on']);
		} elseif (filter_var($mixSource, FILTER_VALIDATE_FLOAT) !== false) { // Float
			// Return the real value
			return floatval($mixSource);
		} elseif (filter_var($mixSource, FILTER_VALIDATE_INT) !== false) {   // Integer
			// Return the integer value
			return intval($mixSource);
		} elseif (Core\Is::null($mixSource)) {                               // NULL
			// Return null
			return null;
		} else {                                                             // Unaltered Source
			// Return the source
			return $mixSource;
		}
	}

	/**
	 * This method matches the request URI against a defined route
	 * @access protected
	 * @name \Crux\Request\Router::matchRoute()
	 * @package \Crux\Request\Router
	 * @param string $strRequestUri
	 * @return bool
	 * @static
	 * @throws \Crux\Core\Exception\Request\Router
	 * @uses \Crux\Request\Router::determineFormat()
	 * @uses \Crux\Request\Router::compileRoute()
	 * @uses \Crux\Request\Router::determineTrueType()
	 * @uses \Crux\Request\Router::request()
	 * @uses \Crux\Request\Route::pattern()
	 * @uses \Crux\Request\Route::endpoint()
	 * @uses \Crux\Request\Route::method()
	 * @uses \Crux\Core\Is::empty()
	 * @uses \Crux\Core\Is::number()
	 * @uses \Crux\Request\Endpoint::__bootstrap()
	 * @uses \Crux\Request\Endpoint::__init()
	 * @uses \Crux\Core\Exception\Request\Router::__construct()
	 * @uses \Exception::getMessage()
	 * @uses \Exception::getCode()
	 * @uses isset()
	 * @uses substr()
	 * @uses sprintf()
	 * @uses preg_match()
	 * @uses unset()
	 * @uses urldecode()
	 * @uses array_push()
	 * @uses call_user_func()
	 */
	protected static function matchRoute(string $strRequestUri) : bool
	{
		// Determine the format
		static::determineFormat($strRequestUri);
		// Set a params placeholder
		$arrParams = array();
		// Iterate over the routes
		foreach (static::$mDefinedRoutes as $strName => $clsRoute) {
			// Grab the data
			$strPattern = $clsRoute->pattern();
			$clsController = $clsRoute->endpoint();
			$strMethod = $clsRoute->method();
			// Check for a wildcard route
			if ($strPattern === '*') {
				// We're matched
				$blnMatched = true;
			} elseif (isset($strPattern[0]) && ($strPattern[0] === '@')) { // Check for RegEx
				// Grab the pattern
				$strRegEx = sprintf('`%s`u', substr($strPattern, 1));
				// Determine if there is a match
				$blnMatched = preg_match($strRegEx, $strRequestUri, $arrParams);
			} else {
				// Create the route placeholder
				$strRoute = null;
				// Set the regex flag
				$blnRegEx = false;
				// Define some indexes
				$intIndex = 0;
				$intZetadex = 0;
				// Define the first character
				$strAlpha = (isset($strPattern[0]) ? $strPattern[0] : null);
				// Iterate until false
				while (true) {
					// Check for the index
					if (!isset($strPattern[$intIndex])) {
						// We're done
						break;
					} elseif ($blnRegEx === false) { // Check the RegEx flag
						// Set the current character
						$strCharacter = $strAlpha;
						// Set the RegEx flag
						$blnRegEx = (($strCharacter === '[') || ($strCharacter === '(') || ($strCharacter === '.'));
						// Check the RegEx flag and the next character
						if (($blnRegEx === false) && (isset($strPattern[($intIndex + 1)]) !== false)) {
							// Set the character
							$strAlpha = $strPattern[($intIndex + 1)];
							// Set the RegEx flag
							$blnRegEx = (($strAlpha === '?') || ($strAlpha === '+') || ($strAlpha === '*') || ($strAlpha === '{'));
						}
						// Check the RegEx flag, character and REQUEST_URI
						if (($blnRegEx === false) && ($strCharacter !== '/') && (!isset($strRequestURI[$intZetadex]) || ($strCharacter !== $strRequestURI[$intZetadex]))) {
							// Increment the iteration by 2
							continue 2;
						}
						// Increment the zetadex
						++$intZetadex;
					}
					// Append to the route
					$strRoute .= $strPattern[$intIndex++];
				}
				// Make the comparison
				$blnMatched = preg_match(static::compileRoute($strRoute), $strRequestUri, $arrParams);
			}
			// Check the matched flag
			if ($blnMatched || ($blnMatched > 0)) {
				// Check for params
				if (!Core\Is::empty($arrParams)) {
					// Iterate over the params
					foreach ($arrParams as $strKey => $anyValue) {
						// Check the key type
						if (Core\Is::number($strKey)) {
							// Remove the param
							unset($arrParams[$strKey]);
						} else {
							// Set the parameters into the array
							array_push($arrParams, static::determineTrueType(urldecode($anyValue)));
							// Set the parameter into the request data model
							static::request()->set($strKey, static::determineTrueType(urldecode($anyValue)));
						}
					}
				}
				// Try to call the method
				try {
					// Bootstrap the endpoint
					$clsController->__bootstrap(static::$mContainer, static::$mResponse);
					// Initialize the endpoint
					$clsController->__init();
					// Call the method
					call_user_func([$clsController, $strMethod], $arrParams);
					// We're done
					return true;
				} catch (\Exception $errException) {
					// Throw the new exception
					throw new Core\Exception\Request\Router($errException->getMessage(), $errException->getCode(), $errException);
				}
			}
		}
		// No route, we're done
		return false;
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Public Static Methods ////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method builds and adds a new route object to storage
	 * @access public
	 * @name \Crux\Request\Router::addRoute()
	 * @param string $strName
	 * @param string $strPattern
	 * @param \Crux\Request\Endpoint $rteHandler
	 * @param string $strMethod
	 * @return void
	 * @static
	 * @uses \Crux\Request\Route::__construct()
	 */
	public static function addRoute(string $strName, string $strPattern, Endpoint $rteHandler, string $strMethod)
	{
		// Add the route to storage
		static::$mDefinedRoutes[$strName] = new Route($rteHandler, $strMethod, $strPattern);
	}

	/**
	 * This method sets the default endpoint class name into the instance
	 * @access public
	 * @name \Crux\Request\Router::defaultEndpoint()
	 * @param string $strClass
	 * @return void
	 * @static
	 */
	public static function defaultEndpoint(string $strClass)
	{
		// Set the default endpoint into the instance
		static::$mDefaultEndpoint = str_replace('.', '\\', $strClass);
	}

	/**
	 * This method tells the router TO try and auto-route requests (default)
	 * @access public
	 * @name \Crux\Request\Router::doAutoRoute()
	 * @package \Crux\Request\Router
	 * @return void
	 * @static
	 */
	public static function doAutoRoute()
	{
		// Reset the auto routing flag
		static::$mAutoRouting = true;
	}

	/**
	 * This method tells the router NOT TO try and auto-route requests
	 * @access public
	 * @name \Crux\Request\Router::doNotAutoRoute()
	 * @package \Crux\Request\Router
	 * @return void
	 * @static
	 */
	public static function doNotAutoRoute()
	{
		// Reset the auto routing flag
		static::$mAutoRouting = false;
	}

	/**
	 * This method returns the name of the class handling the current request, with the ability to reset it inline
	 * @access public
	 * @name \Crux\Request\Router::endpoint()
	 * @package \Crux\Request\Router
	 * @param string $strClass [null]
	 * @return string
	 * @static
	 */
	public static function endpoint(string $strClass = null) : string
	{
		// Check for a provided class name
		if (!Core\Is::null($strClass)) {
			// Reset the endpoint class name
			static::$mEndpoint = $strClass;
		}
		// Return the endpoint class name
		return static::$mEndpoint;
	}

	/**
	 * This method returns the name of the method handling the current request, with the ability to reset it inline
	 * @access public
	 * @name \Crux\Request\Router::endpointMethod()
	 * @package \Crux\Request\Router
	 * @param string $strMethod [null]
	 * @return string
	 * @static
	 */
	public static function endpointMethod(string $strMethod = null) : string
	{
		// Check for a provided method name
		if (!Core\Is::null($strMethod)) {
			// Reset the endpoint method name
			static::$mEndpointMethod = $strMethod;
		}
		// Return the endpoint method name
		return static::$mEndpointMethod;
	}

	/**
	 * This method dispatches the router to determine the route
	 * @access public
	 * @name \Crux\Request\Router::go()
	 * @package \Crux\Request\Router
	 * @param string $strRequestUri ['']
	 * @return void
	 * @static
	 * @throws \Crux\Core\Exception\Request\Router
	 * @uses \Crux\Core\Is::empty()
	 * @uses \Crux\Request\Router::autoRoute()
	 * @uses \Crux\Request\Router::matchRoute()
	 * @uses \Crux\Core\Exception\Request\Router::__construct()
	 * @uses sprintf()
	 * @uses str_ireplace()
	 */
	public static function go(string $strRequestUri = '')
	{
		// Check the request uri
		if (Core\Is::empty($strRequestUri)) {
			// Reset the request uri to the server variable
			$strRequestUri = static::$mContainer->requestUri(false);
		}
		// Check the routing type and determination
		if (static::$mAutoRouting && static::autoRoute($strRequestUri)) {
			// We're done
			return;
		}
		// Try to match the route
		if (!static::matchRoute($strRequestUri)) {
			// Throw the exception
			throw new Core\Exception\Request\Router(sprintf('No route to [%s] could be determined, nor were any defined', $strRequestUri), 404);
		}
	}

	/**
	 * This method compares patterns to the current request to see if we are on said request
	 * @access public
	 * @name \Crux\Request\Router::ifOn()
	 * @package \Crux\Request\Router
	 * @param array<int, string> $arrComparators
	 * @param callable $funCallback
	 * @return void
	 * @static
	 * @uses \Crux\Core\Is::empty()
	 * @uses substr()
	 * @uses substr_replace()
	 * @uses strtolower()
	 * @uses preg_match()
	 * @uses call_user_func()
	 */
	public static function ifOn(array $arrComparators, callable $funCallback)
	{
		// Make sure we have comparators
		if (Core\Is::empty($arrComparators)) {
			// We're done
			return;
		}
		// Iterate over the comparators
		foreach ($arrComparators as $intComparator => $strComparator) {
			// Check the prefix
			if (strtolower(substr($strComparator, 0, 4)) === 'str:') {
				// Do the comparison
				if (strtolower(substr_replace($strComparator, '', 0, 4)) !== strtolower(static::request()->requestUri(false))) {
					// We're done
					return;
				}
			} else {
				// Do the comparison
				if (!preg_match($strComparator, static::request()->requestUri(false))) {
					// We're done
					return;
				}
			}
		}
		// Execute the callback
		call_user_func($funCallback);
	}

	/**
	 * This method compares patterns to the current request to make sure we are not on said request
	 * @access public
	 * @name \Crux\Request\Router::ifNotOn()
	 * @package \Crux\Request\Router
	 * @param array<int, string> $arrComparators
	 * @param callable $funCallback
	 * @return void
	 * @static
	 * @uses \Crux\Core\Is::empty()
	 * @uses substr()
	 * @uses strtolower()
	 * @uses preg_match()
	 * @uses call_user_func()
	 */
	public static function ifNotOn(array $arrComparators, callable $funCallback)
	{
		// Make sure we have comparators
		if (Core\Is::empty($arrComparators)) {
			// We're done
			return;
		}
		// Iterate over the comparators
		foreach ($arrComparators as $intComparator => $strComparator) {
			// Check the prefix
			if (strtolower(substr($strComparator, 0, 4)) === 'str:') {
				// Do the comparison
				if (strtolower(substr_replace($strComparator, '', 0, 4)) === strtolower(static::request()->requestUri(false))) {
					// We're done
					return;
				}
			} else {
				// Do the comparison
				if (preg_match($strComparator, static::request()->requestUri(false))) {
					// We're done
					return;
				}
			}
		}
		// Execute the callback
		call_user_func($funCallback);
	}

	/**
	 * This method returns the routing namespace from the instance with the ability to reset it inline
	 * @access public
	 * @name \Crux\Request\Router::nameSpace()
	 * @package \Crux\Request\Router
	 * @param string $strNameSpace
	 * @return string
	 * @static
	 * @uses \Crux\Core\Is::empty()
	 * @uses trim()
	 * @uses sprintf()
	 */
	public static function nameSpace(string $strNameSpace = '') : string
	{
		// Check for a provided namespace
		if (!Core\Is::empty($strNameSpace)) {
			// Reset the namespace into the instance
			static::$mNamespace = sprintf('\\%s', trim(str_replace('.', '\\', $strNameSpace), '\\'));
		}
		// Return the namespace from the instance
		return static::$mNamespace;
	}

	/**
	 * This method processes post-route hooks
	 * @access public
	 * @name \Crux\Request\Router::processAfterHooks()
	 * @package \Crux\Request\Router
	 * @param bool $blnSuccess
	 * @param string $strResponseContent
	 * @return void
	 * @static
	 * @uses call_user_func_array()
	 */
	public static function processAfterHooks(bool $blnSuccess, string $strResponseContent)
	{
		// Iterate over the post-route hooks
		foreach (static::$mPostHooks as $intHook => $funHook) {
			// Execute the hook
			call_user_func_array($funHook, [static::$mContainer, static::$mResponse, $blnSuccess, $strResponseContent, $intHook]);
		}
	}

	/**
	 * This method processes the pre-route hooks
	 * @access public
	 * @name \Crux\Request\Router::processBeforeHooks()
	 * @package \Crux\Request\Router
	 * @param string $strEndpoint
	 * @param string $strMethod
	 * @return void
	 * @static
	 * @uses call_user_func_array()
	 */
	public static function processBeforeHooks(string $strEndpoint, string $strMethod)
	{
		// Iterate over the pre-route hooks
		foreach (static::$mPreHooks as $intHook => $funHook) {
			// Execute the hook
			call_user_func_array($funHook, [static::$mContainer, static::$mResponse, $strEndpoint, $strMethod, $intHook]);
		}
	}

	/**
	 * This method returns the request container from storage
	 * @access public
	 * @name \Crux\Request\Router::request()
	 * @package \Crux\Request\Router
	 * @return \Crux\Request\Container
	 * @static
	 */
	public static function request() : Container
	{
		// Return the request container from the instance
		return static::$mContainer;
	}

	/**
	 * This method returns the response container from storage
	 * @access public
	 * @name \Crux\Request\Router::response()
	 * @package \Crux\Request\Router
	 * @return \Crux\Request\Response
	 * @static
	 */
	public static function response() : Response
	{
		// Return the response container from the instance
		return static::$mResponse;
	}

	/**
	 * This method adds a route hook to storage
	 * @access public
	 * @name \Crux\Request\Router::use()
	 * @package \Crux\Request\Router
	 * @param int $intPlacement
	 * @param callable $funCallback
	 * @return void
	 * @static
	 * @uses array_push()
	 */
	public static function use(int $intPlacement, callable $funCallback)
	{
		// Check the placement
		if ($intPlacement === static::HookAfter) {
			// Add the hook to the post-route container
			array_push(static::$mPostHooks, $funCallback);
		} else {
			// Add the hook to the pre-route container
			array_push(static::$mPreHooks, $funCallback);
		}
	}

	/**
	 * This method returns the root node name for XML responses with the ability to reset it inline
	 * @access public
	 * @name \Crux\Request\Router::xmlRootNode()
	 * @package \Crux\Request\Router
	 * @param string $strNode ['']
	 * @return string
	 * @static
	 * @uses \Crux\Core\Is::empty()
	 */
	public static function xmlRootNode(string $strNode = '') : string
	{
		// Check for a provided node
		if (!Core\Is::empty($strNode)) {
			// Reset the XML root node
			static::$mXmlRootNode = $strNode;
		}
		// Return the xml root node name
		return static::$mXmlRootNode;
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Setters //////////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method sets an existing Route object into storage
	 * @access public
	 * @name \Crux\Request\Router::setRoute()
	 * @package \Crux\Request\Router
	 * @param string $strName
	 * @param \Crux\Request\Route $rteInstance
	 * @return void
	 * @static
	 */
	public static function setRoute(string $strName, Route $rteInstance)
	{
		// Set the route into the instance
		static::$mDefinedRoutes[$strName] = $rteInstance;
	}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
} /// End Crux\Request\Router Class Definition ///////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


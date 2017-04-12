<?php

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Crux\Transport\Http Namespace ////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

namespace Crux\Transport\Http;

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Imports //////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

use Crux\Core;
use Crux\Serialize;

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Crux\Transport\Http\Request Class Defintion ///////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class Request
{
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Data Type Constants //////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This constant defines an application/octet-stream encoding
	 * @name \Crux\Transport\Http\Request::Binary
	 * @package \Crux\Transport\Http\Request
	 * @var int
	 */
	const Binary = 0x01;

	/**
	 * This constant defines a text/html encoding
	 * @name \Crux\Transport\Http\Request::HTML
	 * @package \Crux\Transport\Http\Request
	 * @var int
	 */
	const HTML = 0x03;

	/**
	 * This constant defines an application/json encoding
	 * @name \Crux\Transport\Http\Request::JSON
	 * @package \Crux\Transport\Http\Request
	 * @var int
	 */
	const JSON = 0x04;

	/**
	 * This constant defines a URL encoding
	 * @name \Crux\Transport\Http\Request::Query
	 * @package \Crux\Transport\Http\Request
	 * @var int
	 */
	const Query = 0x05;

	/**
	 * This constant defines no encoding
	 * @name \Crux\Transport\Http\Request::Raw
	 * @package \Crux\Transport\Http\Request
	 * @var int
	 */
	const Raw = 0x06;

	/**
	 * This constant defines a text/javascript encoding
	 * @name \Crux\Transport\Http\Request::Script
	 * @package \Crux\Transport\Http\Request
	 * @var int
	 */
	const Script = 0x07;

	/**
	 * This constant defines a text/plain encoding
	 * @name \Crux\Transport\Http\Request::Text
	 * @package \Crux\Transport\Http\Request
	 * @var int
	 */
	const Text = 0x08;

	/**
	 * This constant defines a text/xml encoding
	 * @name \Crux\Transport\Http\Request::Xml
	 * @package \Crux\Transport\Http\Request
	 * @var int
	 */
	const XML = 0x09;

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Request Method Constants /////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This constant defines an HTTP DELETE request method
	 * @name \Crux\Transport\Http\Request::Delete
	 * @package \Crux\Transport\Http\Request
	 * @var int
	 */
	const Delete = 0x01;

	/**
	 * This constant defines an HTTP GET request method
	 * @name \Crux\Transport\Http\Request::Get
	 * @package \Crux\Transport\Http\Request
	 * @var int
	 */
	const Get = 0x02;

	/**
	 * This constant defines an HTTP PATCH request method
	 * @name \Crux\Transport\Http\Request::Patch
	 * @package \Crux\Transport\Http\Request
	 * @var int
	 */
	const Patch = 0x03;

	/**
	 * This constant defines an HTTP POST request method
	 * @name \Crux\Transport\Http\Request::Post
	 * @package \Crux\Transport\Http\Request
	 * @var int
	 */
	const Post = 0x04;

	/**
	 * This constant defines an HTTP PUT request method
	 * @name \Crux\Transport\Http\Request::Put
	 * @package \Crux\Transport\Http\Request
	 * @var int
	 */
	const Put = 0x05;

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Parameter Constants //////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This constant defines the Body as the destination for a parameter
	 * @name \Crux\Transport\Http\Request::Body
	 * @package \Crux\Transport\Http\Request
	 * @var int
	 */
	const Body = 0x01;

	/**
	 * This constant defines the URL as the destination for a parameter
	 * @name \Crux\Transport\Http\Request::URL
	 * @package \Crux\Transport\Http\Request
	 * @var int
	 */
	const URL = 0x02;

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Protected Properties /////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This property contains the data for the body of the request
	 * @access protected
	 * @name \Crux\Transport\Http\Request::$mBodyData
	 * @package \Crux\Transport\Http\Request
	 * @var array<string, mixed>|string
	 */
	protected $mBodyData;

	/**
	 * This property tells the instance whether or not to follow locations
	 * @access protected
	 * @name \Crux\Transport\Http\Request::$mFollowLocation
	 * @package \Crux\Transport\Http\Request
	 * @var bool
	 */
	protected $mFollowLocation = true;

	/**
	 * This property contains the headers for the request
	 * @access protected
	 * @name \Crux\Transport\Http\Request::$mHeaders
	 * @package \Crux\Transport\Http\Request
	 * @var array<string, string>
	 */
	protected $mHeaders = [];

	/**
	 * This property tells the instance whether or not HTTP authentication is required
	 * @access protected
	 * @name \Crux\Transport\Http\Request::$mHttpAuthentication
	 * @package \Crux\Transport\Http\Request
	 * @var bool
	 */
	protected $mHttpAuthentication = false;

	/**
	 * This property contains the request information from cURL
	 * @access protected
	 * @name \Crux\Transport\Http\Request::$mInformation
	 * @package \Crux\Transport\Http\Request
	 * @var array<string, mixed>
	 */
	protected $mInformation = [];

	/**
	 * This property contains the maximum number of redirects to follow
	 * @access protected
	 * @name \Crux\Transport\Http\Request::$mMaxRedirects
	 * @package \Crux\Transport\Http\Request
	 * @var int
	 */
	protected $mMaxRedirects = -1;

	/**
	 * This property contains the data for the request URL query string
	 * @access protected
	 * @name \Crux\Transport\Http\Request::$mQueryData
	 * @package \Crux\Transport\Http\Request
	 * @var array<string, mixed>
	 */
	protected $mQueryData = [];

	/**
	 * This property contains the password for HTTP authentication
	 * @access protected
	 * @name \Crux\Transport\Http\Request::$mPassword
	 * @package \Crux\Transport\Http\Request
	 * @var string
	 */
	protected $mPassword = '';

	/**
	 * This property contains the desired request encoding
	 * @access protected
	 * @name \Crux\Transport\Http\Request::$mRequestFormat
	 * @package \Crux\Transport\Http\Request
	 * @var int
	 */
	protected $mRequestFormat = self::Query;

	/**
	 * This property contains the number of seconds to wait for the URL to respond
	 * @access protected
	 * @name \Crux\Transport\Http\Request::$mTimeOut
	 * @package \Crux\Transport\Http\Request
	 * @var int
	 */
	protected $mTimeOut = 30;

	/**
	 * This property contains the URL to request
	 * @access protected
	 * @name \Crux\Transport\Http\Request::$mUrl
	 * @package \Crux\Transport\Http\Request
	 * @var string
	 */
	protected $mUrl = '';

	/**
	 * This property contains the username for HTTP authentication
	 * @access protected
	 * @name \Crux\Transport\Http\Request::$mUsername
	 * @package \Crux\Transport\Http\Request
	 * @var string
	 */
	protected $mUsername = '';

	/**
	 * This property contains the root XML node for sending XML datagrams
	 * @access protected
	 * @name \Crux\Transport\Http\Request::$mXmlRootNode
	 * @package \Crux\Transport\Http\Request
	 * @var string
	 */
	protected $mXmlRootNode = 'payload';

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Static Constructor ///////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method instantiates a new Request object into a fluid interface
	 * @access public
	 * @name \Crux\Transport\Http\Request::Factory()
	 * @package \Crux\Transport\Http\Request
	 * @param string $strUrl ['']
	 * @return \Crux\Transport\Http\Request
	 */
	public static function Factory(string $strUrl = '') : Request
	{
		// Return the new instance
		return new self($strUrl);
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Constructor //////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////


	public function __construct(string $strUrl = '')
	{
		// Check for a URL
		if (!Core\Is::empty($strUrl)) {

			// Set the URL into the instance
			$this->mUrl = $strUrl;
		}
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Protected Methods ////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method processes a URL into manageable parts
	 * @access protected
	 * @name \Crux\Transport\Http\Request::parseUrl()
	 * @package \Crux\Transport\Http\Request
	 * @param string $strUrl
	 * @return array<string, mixed>
	 */
	protected function parseUrl(string $strUrl) : array
	{
		// Define our elements container
		$arrElements = [];
		// Parse the URL
		$arrParts = parse_url($strUrl);
		// check for a query
		if (array_key_exists('query', $arrParts)) {
			// Parse the query
			parse_str($arrParts['query'], $arrElements);
		}
		// Reset the query
		$arrParts['query'] = $arrElements;
		// We're done, return the URL data
		return $arrParts;
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Setters //////////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////


	public function setUrl(string $strUrl) : Request
	{
		// Make sure we have a valid URL
		if (filter_var($strUrl, FILTER_VALIDATE_URL) === false) {
			// Throw the exception
			throw new Core\Exception\Transport\Http\Request(sprintf('Invalid URL [%s]', $strUrl));
		}
		// Process the URL
		$arrUrl = $this->processUrl($strUrl);

	}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
} /// End Crux\Transport\Http\Request Class Definition ////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

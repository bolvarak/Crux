<?php

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Crux\Storage\S3 Namespace ////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

namespace Crux\Storage\S3;

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Imports //////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

use Crux\Core;
use Crux\Serialize;

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Crux\Storage\S3\Response Class Definition /////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class Response
{
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Properties ///////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This property contains the body of the response
	 * @access protected
	 * @name \Crux\Storage\S3\Response::$mBody
	 * @package \Crux\Storage\S3\Response
	 * @var \SimpleXMLElement
	 */
	protected $mBody;

	/**
	 * This property contains the HTTP response code
	 * @access protected
	 * @name \Crux\Storage\S3\Response::$mCode
	 * @package \Crux\Storage\S3\Response
	 * @var int
	 */
	protected $mCode = 200;

	/**
	 * This property contains any errors that occurred
	 * @access protected
	 * @name \Crux\Storage\S3\Response::$mError
	 * @package \Crux\Storage\S3\Response
	 * @var array<string, mixed>
	 */
	protected $mError = [];

	/**
	 * This property contains the response headers
	 * @access protected
	 * @name \Crux\Storage\S3\Response::$mHeaders
	 * @package \Crux\Storage\S3\Response
	 * @var array<string, mixed>
	 */
	protected $mHeaders = [];

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Constructor //////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method instantiates a new S3 Response object
	 * @access public
	 * @name \Crux\Storage\S3\Request::__construct()
	 * @package \Crux\Storage\S3\Request
	 */
	public function __construct()
	{
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Public Methods ///////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method sets the body into the instance with the ability to reset it inline
	 * @access public
	 * @name \Crux\Storage\S3\Response::body()
	 * @package \Crux\Storage\S3\Response
	 * @param \SimpleXMLElement $xmlBody [null]
	 * @return \SimpleXMLElement
	 * @uses \Crux\Core\Is::null()
	 */
	public function body(\SimpleXMLElement $xmlBody = null) : \SimpleXMLElement
	{
		// Check for a provided body
		if (!Core\Is::null($xmlBody)) {
			// Reset the body into the instance
			$this->mBody = $xmlBody;
		}
		// Return the body from the instance
		return $this->mBody;
	}

	/**
	 * This method returns the code from the instance with the ability to reset it inline
	 * @access public
	 * @name \Crux\Storage\S3\Response::code()
	 * @package \Crux\Storage\S3\Response
	 * @param int $intCode [-1]
	 * @return int
	 */
	public function code(int $intCode = -1) : int
	{
		// Check for a provided code
		if ($intCode > 0) {
			// Reset the code into the instance
			$this->mCode = $intCode;
		}
		// Return the code from the instance
		return $this->mCode;
	}

	/**
	 * This method returns the error from the instance with the ability to reset it inline
	 * @access public
	 * @name \Crux\Storage\S3\Response::error()
	 * @package \Crux\Storage\S3\Response
	 * @param array<string, mixed> $arrError [array()]
	 * @return array<string, mixed>
	 * @uses \Crux\Core\Is::empty()
	 */
	public function error(array $arrError = []) : array
	{
		// Check for a provided array
		if (!Core\Is::empty($arrError)) {
			// Reset the error into the instance
			$this->mError = $arrError;
		}
		// Return the error from the instance
		return $this->mError;
	}

	/**
	 * This method returns a specific header  from the instance with the ability to reset it inline
	 * @access public
	 * @name \Crux\Storage\S3\Response::header()
	 * @package \Crux\Storage\S3\Response
	 * @param string $strName
	 * @param mixed $mixValue [null]
	 * @return mixed
	 * @uses \Crux\Core\Is::null()
	 */
	public function header(string $strName, $mixValue = null)
	{
		// Check for a provided value
		if (!Core\Is::null($mixValue)) {
			// Reset the header into the instance
			$this->mHeaders[$strName] = $mixValue;
		}
		// Return the header from the instance
		return $this->mHeaders[$strName];
	}

	/**
	 * This method returns the headers from the instance with the ability to reset them inline
	 * @access public
	 * @name \Crux\Storage\S3\Response::headers()
	 * @package \Crux\Storage\S3\Response
	 * @param array<string, mixed> $arrHeaders [array()]
	 * @return array<string, mixed>
	 * @uses \Crux\Core\Is::empty()
	 */
	public function headers(array $arrHeaders = []) : array
	{
		// Check for provided headers
		if (!Core\Is::empty($arrHeaders)) {
			// Reset the headers into the instance
			$this->mHeaders = $arrHeaders;
		}
		// Return the headers from the instance
		return $this->mHeaders;
	}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
} /// End Crux\Storage\S3\Response Class Definition //////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

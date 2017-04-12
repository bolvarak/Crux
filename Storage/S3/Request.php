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
/// Crux\Storage\S3\Request Class Definition //////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class Request
{
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Constants ////////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This constant defines a DELETE verb
	 * @name \Crux\Storage\S3\Request::Delete
	 * @package \PHieworks\Storage\S3\Request
	 * @var string
	 */
	const Delete = 'DELETE';

	/**
	 * This constant defines a GET verb
	 * @name \Crux\Storage\S3\Request::Get
	 * @package \PHieworks\Storage\S3\Request
	 * @var string
	 */
	const Get = 'GET';

	/**
	 * This constant defines a HEAD verb
	 * @name \Crux\Storage\S3\Request::Head
	 * @package \PHieworks\Storage\S3\Request
	 * @var string
	 */
	const Head = 'HEAD';

	/**
	 * This constant defines a POST verb
	 * @name \Crux\Storage\S3\Request::Post
	 * @package \PHieworks\Storage\S3\Request
	 * @var string
	 */
	const Post = 'POST';

	/**
	 * This constant defines a PUT verb
	 * @name \Crux\Storage\S3\Request::Put
	 * @package \PHieworks\Storage\S3\Request
	 * @var string
	 */
	const Put = 'PUT';

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Properties ///////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This property contains Amazon specific headers
	 * @access protected
	 * @name \Crux\Storage\S3\Request::$mAmazonHeaders
	 * @package \Crux\Storage\S3\Request
	 * @var array<string, mixed>
	 */
	protected $mAmazonHeaders = [];

	/**
	 * This property contains the S3 bucket name
	 * @access protected
	 * @name \Crux\Storage\S3\Request::$mBucket
	 * @package \Crux\Storage\S3\Request
	 * @var string
	 */
	protected $mBucket = '';

	/**
	 * This property is a placeholder for the raw body
	 * @access protected
	 * @name \Crux\Storage\S3\Request::$mBody
	 * @package \Crux\Storage\S3\Request
	 * @var string
	 */
	protected $mBody = '';

	/**
	 * This property contains the AWS URL
	 * @access protected
	 * @name \Crux\Storage\S3\Request::$mEndpoint
	 * @package \Crux\Storage\S3\Request
	 * @var string
	 */
	protected $mEndpoint = Engine::S3;

	/**
	 * This property contains the file pointer for a PUT request
	 * @access protected
	 * @name \Crux\Storage\S3\Request::$mFilePointer
	 * @package \Crux\Storage\S3\Request
	 * @var resource
	 */
	protected $mFilePointer = false;

	/**
	 * This property contains the PUT file size
	 * @access protected
	 * @name \Crux\Storage\S3\Request::$mFileSize
	 * @package \Crux\Storage\S3\Request
	 * @var int
	 */
	protected $mFileSize = 0;

	/**
	 * This property contains our HTTP request headers
	 * @access protected
	 * @name \Crux\Storage\S3\Request::$mHeaders
	 * @package \Crux\Storage\S3\Request
	 * @var array<string, string>
	 */
	protected $mHeaders = [];

	/**
	 * This property contains additional request parameters
	 * @access protected
	 * @name \Crux\Storage\S3\Request::$mParameters
	 * @packge \Crux\Storage\S3\Request
	 * @var array<string, mixed>
	 */
	protected $mParameters = [];

	/**
	 * This property contains the PUT/POST fields
	 * @access protected
	 * @name \Crux\Storage\S3\Request::$mPutPostFields
	 * @package \Crux\Storage\S3\Request
	 * @var array<string, mixed>
	 */
	protected $mPutPostFields = [];

	/**
	 * This property contains the absolute URI to the object
	 * @access protected
	 * @name \Crux\Storage\S3\Request::$mResource
	 * @package \Crux\Storage\S3\Request
	 * @var string
	 */
	protected $mResource = '';

	/**
	 * This property contains the S3 response
	 * @access protected
	 * @name \Crux\Storage\S3\Request::$mResponse
	 * @package \Crux\Storage\S3\Request
	 * @var \Crux\Storage\S3\Response
	 */
	protected $mResponse;

	/**
	 * This property contains the object URI
	 * @access protected
	 * @name \Crux\Storage\S3\Request::$mUri
	 * @package \Crux\Storage\S3\Request
	 * @var string
	 */
	protected $mUri = '';

	/**
	 * This property tells the instance whether or not to use an HTTP PUT request
	 * @access protected
	 * @name \Crux\Storage\S3\Request::$mUsePut
	 * @package \Crux\Storage\S3\Request
	 * @var bool
	 */
	protected $mUsePut = false;

	/**
	 * This property contains the request verb to use
	 * @access protected
	 * @name \Crux\Storage\S3\Request::$mVerb
	 * @package \Crux\Storage\S3\Request
	 * @var string
	 */
	protected $mVerb = '';

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Constructor //////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method instantiates a new S3 Request object
	 * @access public
	 * @name \Crux\Storage\S3\Request::__construct()
	 * @package \Crux\Storage\S3\Request
	 * @param string $strVerb
	 * @param string $strBucket ['']
	 * @param string $strUri ['']
	 * @param string $strEndpoint [\Crux\Storage\S3\Engine::S3]
	 * @uses \Crux\Storage\S3\Request::response()
	 * @uses \Crux\Storage\S3\Request::header()
	 * @uses \Crux\Storage\S3\Request::endpoint()
	 * @uses \Crux\Storage\S3\Request::verb()
	 * @uses \Crux\Storage\S3\Request::bucket()
	 * @uses \Crux\Storage\S3\Request::uri()
	 * @uses \Crux\Storage\S3\Request::validateBucketName()
	 * @uses \Crux\Storage\S3\Request::resource()
	 * @uses \Crux\Storage\S3\Response::__construct()
	 * @uses \Crux\Core\Is::empty()
	 * @uses sprintf()
	 * @uses str_replace()
	 * @uses rawurlencode()
	 */
	public function __construct(string $strVerb, string $strBucket = '', $strUri = '', string $strEndpoint = Engine::S3)
	{
		// Initialize the response
		$this->response(new Response());
		// Default the host header
		$this->header('Host', '');
		// Default the date header
		$this->header('Date', gmdate('D, d M Y H:i:s T'));
		// Default the MD5 content header
		$this->header('Content-MD5', '');
		// Default the content type header
		$this->header('Content-Type', '');
		// Set the endpoint into the instance
		$this->endpoint($strEndpoint);
		// Set the verb into the instance
		$this->verb($strVerb);
		// Set the bucket into the instance
		$this->bucket($strBucket);
		// Set the URI into the instance
		$this->uri(Core\Is::empty($strUri) ? '/' : sprintf('/%s', str_replace('%2F', '/', rawurlencode($strUri))));
		// Check the bucket name
		if (!Core\Is::empty($this->mBucket)) {
			// Validate the bucket name
			if ($this->validateBucketName($this->mBucket)) {
				// Reset the host header
				$this->header('Host', sprintf('%s.%s', $this->mBucket, $this->mEndpoint));
				// Reset the resource
				$this->resource(sprintf('/%s.%s', $this->mBucket, $this->mUri));
			} else {
				// Set the host header
				$this->header('Host', $this->mEndpoint);
				// Reset the URI
				$this->uri($this->mUri);
				// Check the bucket name
				if (!Core\Is::empty($this->mBucket)) {
					// Reset the URI
					$this->uri(sprintf('/%s.%s', $this->mBucket, $this->mUri));
				}
				// Reset the bucket
				$this->mBucket = '';
				// Reset the resource
				$this->resource($this->mUri);
			}
		} else {
			// Reset the host header
			$this->header('Host', $this->mEndpoint);
			// Reset the resource
			$this->resource($this->mUri);
		}
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Request Setup Methods ////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method sets up a DELETE request
	 * @access protected
	 * @name \Crux\Storage\S3\Request::deleteRequest()
	 * @package \Crux\Storage\S3\Request
	 * @param resource $rscCurl
	 * @return void
	 * @uses curl_setopt()
	 */
	protected function deleteRequest(resource &$rscCurl)
	{
		// Set the request type
		curl_setopt($rscCurl, CURLOPT_CUSTOMREQUEST, 'DELETE');
	}

	/**
	 * This method sets up a HEAD request
	 * @access protected
	 * @name \Crux\Storage\S3\Request::headRequest()
	 * @package \Crux\Storage\S3\Request
	 * @param resource $rscCurl
	 * @return void
	 * @uses curl_setopt()
	 */
	protected function headRequest(resource &$rscCurl)
	{
		// Set the request type
		curl_setopt($rscCurl, CURLOPT_CUSTOMREQUEST, 'HEAD');
		// This request has no body
		curl_setopt($rscCurl, CURLOPT_NOBODY, true);
	}

	/**
	 * This method sets up POST and PUT requests
	 * @access protected
	 * @name \Crux\Storage\S3\Request::postAndPutRequest()
	 * @package \Crux\Storage\S3\Request
	 * @param resource $rscCurl
	 * @return void
	 * @uses \Crux\Storage\S3\Request::putRequest()
	 * @uses \Crux\Storage\S3\Request::putPostFields()
	 * @uses \Crux\Storage\S3\Request::postRequest()
	 * @uses \Crux\Storage\S3\Request::verb()
	 * @uses \Crux\Core\Is::empty()
	 * @uses curl_setopt()
	 */
	protected function postAndPutRequest(resource &$rscCurl)
	{
		// Check the input file
		if ($this->mFilePointer !== false) {
			// Setup the PUT request
			$this->putRequest($rscCurl);
		} elseif (!Core\Is::empty($this->putPostFields())) {
			// Setup the POST request
			$this->postRequest($rscCurl);
		} else {
			// Set the request type
			curl_setopt($rscCurl, CURLOPT_CUSTOMREQUEST, $this->verb());
		}
	}

	/**
	 * This method sets up a POST request
	 * @access protected
	 * @name \Crux\Storage\S3\Request::postRequest()
	 * @package \Crux\Storage\S3\Request
	 * @param resource $rscCurl
	 * @return void
	 * @uses \Crux\Storage\S3\Request::verb()
	 * @uses \Crux\Storage\S3\Request::putPostFields()
	 * @uses curl_setopt()
	 */
	protected function postRequest(resource &$rscCurl)
	{
		// Set the request type
		curl_setopt($rscCurl, CURLOPT_CUSTOMREQUEST, $this->verb());
		// Set the data into the handle
		curl_setopt($rscCurl, CURLOPT_POSTFIELDS, $this->putPostFields());
	}

	/**
	 * This method sets up a PUT request
	 * @access protected
	 * @name \Crux\Storage\S3\Request::postRequest()
	 * @package \PHreworks\Storage\S3\Request
	 * @param resource $rscCurl
	 * @return void
	 * @uses \Crux\Storage\S3\Request::fileSize()
	 * @uses curl_setopt()
	 */
	protected function putRequest(resource &$rscCurl)
	{
		// Set the request type
		curl_setopt($rscCurl, CURLOPT_PUT, true);
		// Set the file handle
		curl_setopt($rscCurl, CURLOPT_INFILE, $this->mFilePointer);
		// Check the file size
		if ($this->fileSize() > 0) {
			// Set the input file size
			curl_setopt($rscCurl, CURLOPT_INFILESIZE, $this->fileSize());
		}
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Protected Methods ////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method processes the cURL response headers into the response object
	 * @access protected
	 * @name \Crux\Storage\S3\Request::parseResponseHeaders()
	 * @package \Crux\Storage\S3\Request
	 * @param resource $rscCurl
	 * @param string $strData
	 * @return int
	 * @uses \Crux\Storage\S3\Response::code()
	 * @uses \Crux\Storage\S3\Response::header()
	 * @uses strlen()
	 * @uses substr()
	 * @uses explode()
	 * @uses strtolower()
	 * @uses intval()
	 * @uses strpos()
	 * @uses strtotime()
	 */
	protected function parseResponseHeaders(resource $rscCurl, string $strData) : int
	{
		// Grab the length of the data
		$intDataLength = strlen($strData);
		// Check the length of the data
		if ($intDataLength <= 2) {
			// We're done, just return the data length
			return $intDataLength;
		}
		// Check for an HTTP status header
		if (strtolower(substr($strData, 0, 4)) === 'http') {
			// Set the response code into the response
			$this->response()->code(intval(substr($strData, 9, 3)));
		} else {
			// Trim the data
			$strData = trim($strData);
			// Check for actual headers
			if (strpos($strData, ': ') === false) {
				// We're done, no headers to process
				return $intDataLength;
			}
			// Split the header and value
			list($strHeader, $strValue) = explode(': ', $strData, 2);
			// Convert the header name
			$strLowerHeader = strtolower($strHeader);
			// Check the header
			if ($strLowerHeader === 'last-modified') {
				// Set the timestamp into the response headers
				$this->response()->header('time', strtotime($strValue));
			} elseif ($strLowerHeader === 'date') {
				// Set the date into the response headers
				$this->response()->header('date', strtotime($strValue));
			} elseif ($strLowerHeader === 'content-length') {
				// Set the size into the response headers
				$this->response()->header('size', intval($strValue));
			} elseif ($strLowerHeader === 'content-type') {
				// Set the type into the response headers
				$this->response()->header('type', $strValue);
			} elseif ($strLowerHeader === 'etag') {
				// Set the hash into the response headers
				$this->response()->header('hash', (($strValue{0} === '""') ? substr($strValue, 0, -1) : $strValue));
			} else {
				// Set the header into the response headers
				$this->response()->header($strHeader, $strValue);
			}
		}
		// We're done, just return the data length
		return $intDataLength;
	}

	/**
	 * This method sets up the headers for the request
	 * @access protected
	 * @name \Crux\Storage\S3\Request::setupHeaders()
	 * @package \Crux\Storage\S3\Request
	 * @param resource $rscCurl
	 * @return void
	 * @uses \Crux\Storage\S3\Request::amazonHeaders()
	 * @uses \Crux\Storage\S3\Request::headers()
	 * @uses \Crux\Storage\S3\Request::header()
	 * @uses \Crux\Storage\S3\Request::verb()
	 * @uses \Crux\Storage\S3\Request::resource()
	 * @uses \Crux\Storage\S3\Engine::hasAuth()
	 * @uses \Crux\Storage\S3\Engine::sign()
	 * @uses \Crux\Core\Is::empty()
	 * @uses \Crux\Core\Is::number()
	 * @uses array_push()
	 * @uses sprintf()
	 * @uses strtolower()
	 * @uses usort()
	 * @uses implode()
	 * @uses curl_setopt()
	 */
	protected function setupHeaders(resource &$rscCurl)
	{
		// Define our headers array
		$arrHeaders = [];
		// Define our Amazon headers array
		$arrAmazonHeaders = [];
		// Iterate over the Amazon headers in the instance
		foreach ($this->amazonHeaders() as $strHeader => $mixValue) {
			// Make sure the header has data
			if (!Core\Is::empty($mixValue) || Core\Is::number($mixValue)) {
				// Add the header
				array_push($arrHeaders, sprintf('%s: %s', $strHeader, $mixValue));
				array_push($arrAmazonHeaders, sprintf('%s:%s', strtolower($strHeader), $mixValue));
			}
		}
		// Iterate over the standard headers
		foreach ($this->headers() as $strHeader => $mixValue) {
			// Make sure the header has data
			if (!Core\Is::empty($mixValue) || Core\Is::number($mixValue)) {
				// Add the header
				array_push($arrHeaders, sprintf('%s: %s', $strHeader, $mixValue));
			}
		}
		// Make sure we have Amazon headers
		if (!Core\Is::empty($arrAmazonHeaders)) {
			// Sort the headers
			usort($arrAmazonHeaders, [&$this, 'sortAmazonHeaders']);
			// Implode the Amazon headers
			$strAmazonHeaders = sprintf('%s%s', PHP_EOL, implode(PHP_EOL, $arrAmazonHeaders));
		} else {
			// Reset the amazon headers
			$strAmazonHeaders = '';
		}
		// Check for authentication
		if (Engine::hasAuth()) {
			// Check the host for CloudFront
			if ($this->header('Host') === Engine::CloudFront) {
				// Sign and add the authorization header
				array_push($arrHeaders, sprintf('Authorization: %s', Engine::sign($this->header('Date'))));
			} else {
				// Sign and add the authorization header
				array_push($arrHeaders, sprintf('Authorization: %s', Engine::sign(implode(PHP_EOL, [
					$this->verb(),
					$this->header('Content-MD5'),
					$this->header('Content-Type'),
					sprintf('%s%s', $this->header('Date'), $strAmazonHeaders),
					$this->resource()
				]))));
			}
		}
		// Set the HTTP headers into the handle
		curl_setopt($rscCurl, CURLOPT_HTTPHEADER, $arrHeaders);
		// Do not pass the headers to the data stream
		curl_setopt($rscCurl, CURLOPT_HEADER, false);
	}

	/**
	 * This method sets up the parameters for the request
	 * @access protected
	 * @name \Crux\Storage\S3\Request::setupParameters()
	 * @package \Crux\Storage\S3\Request
	 * @return void
	 * @uses \Crux\Storage\S3\Request::uri()
	 * @uses \Crux\Core\Is::empty()
	 * @uses \Crux\Core\Is::null()
	 * @uses sizeof()
	 * @uses substr()
	 * @uses sprintf()
	 * @uses rawurlencode()
	 * @uses array_key_exists()
	 */
	protected function setupParameters()
	{
		// Check the parameters
		if (sizeof($this->mParameters) > 0) {
			// Check for an existing query string in the URI
			$strQuery = ((substr($this->mUri, -1) !== '?') ? '?' : '&');
			// Iterate over the parameters
			foreach ($this->mParameters as $strName => $mixValue) {
				// Check the value
				if (Core\Is::null($mixValue) || Core\Is::empty($mixValue)) {
					// Append to the query
					$strQuery .= sprintf('%s&', $strName);
				} else {
					// Append to the query
					$strQuery .= sprintf('%s=%s&', $strName, rawurlencode($mixValue));
				}
			}
			// Reset the query
			$strQuery = substr($strQuery, 0, -1);
			// Reset the URI
			$this->uri($strQuery);
			// Check the parameter
			if (
				array_key_exists('acl', $this->mParameters) ||
				array_key_exists('location', $this->mParameters) ||
				array_key_exists('torrent', $this->mParameters) ||
				array_key_exists('website', $this->mParameters) ||
				array_key_exists('logging', $this->mParameters)
			) {
				// Append to the resource
				$this->mResource .= $strQuery;
			}
		}
	}

	/**
	 * This method sets up a proxy in the cURL handle
	 * @access protected
	 * @name \Crux\Storage\S3\Request::setupProxy()
	 * @package \Crux\Storage\S3\Request
	 * @param resource $rscCurl
	 * @return void
	 * @uses \Crux\Storage\S3\Engine::proxyHost()
	 * @uses \Crux\Storage\S3\Engine::proxyType()
	 * @uses \Crux\Storage\S3\Engine::proxyUser()
	 * @uses \Crux\Storage\S3\Engine::proxyPass()
	 * @uses \Crux\Core\Is::empty()
	 * @uses curl_setopt()
	 * @uses sprintf()
	 */
	protected function setupProxy(resource &$rscCurl)
	{
		// Check for a proxy host
		if (!Core\Is::empty(Engine::proxyHost())) {
			// Set the proxy host
			curl_setopt($rscCurl, CURLOPT_PROXY, Engine::proxyHost());
		}
		// Check for a proxy type
		if (!Core\Is::empty(Engine::proxyType())) {
			// Set the proxy type
			curl_setopt($rscCurl, CURLOPT_PROXYTYPE, Engine::proxyType());
		}
		// Make sure we have a username and password
		if (!Core\Is::empty(Engine::proxyUser()) && !Core\Is::empty(Engine::proxyPass())) {
			// Set the proxy authentication
			curl_setopt($rscCurl, CURLOPT_PROXYUSERPWD, sprintf('%s:%s', Engine::proxyUser(), Engine::proxyPass()));
		}
	}

	/**
	 * This method sets up SSL on the cURL handle
	 * @access protected
	 * @name \Crux\Storage\S3\Request::setupSsl()
	 * @package \Crux\Storage\S3\Request
	 * @param resource $rscCurl
	 * @return void
	 * @uses \Crux\Storage\S3\Engine::sslVerifyHost()
	 * @uses \Crux\Storage\S3\Engine::sslVerifyPeer()
	 * @uses \Crux\Storage\S3\Engine::sslKey()
	 * @uses \Crux\Storage\S3\Engine::sslKeyPasswd()
	 * @uses \Crux\Storage\S3\Engine::sslCert()
	 * @uses \Crux\Storage\S3\Engine::sslCertPasswd()
	 * @uses \Crux\Storage\S3\Engine::sslCaCert()
	 * @uses \Crux\Core\Is::empty()
	 * @uses curl_setopt()
	 */
	protected function setupSsl(resource &$rscCurl)
	{
		// Set the SSL version
		curl_setopt($rscCurl, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
		// Set the host verification flag
		curl_setopt($rscCurl, CURLOPT_SSL_VERIFYHOST, Engine::sslVerifyHost());
		// Set the peer verification flag
		curl_setopt($rscCurl, CURLOPT_SSL_VERIFYPEER, Engine::sslVerifyPeer());
		// Check for an SSL key
		if (!Core\Is::empty(Engine::sslKey())) {
			// Set the SSL key
			curl_setopt($rscCurl, CURLOPT_SSLKEY, Engine::sslKey());
		}
		// Check for an SSL key passphrase
		if (!Core\Is::empty(Engine::sslKeyPasswd())) {
			// Set the SSL key passphrase
			curl_setopt($rscCurl, CURLOPT_SSLKEYPASSWD, Engine::sslKeyPasswd());
		}
		// Check for an SSL certificate
		if (!Core\Is::empty(Engine::sslCert())) {
			// Set the SSL certificate
			curl_setopt($rscCurl, CURLOPT_SSLCERT, Engine::sslCert());
		}
		// Check for a certificate password
		if (!Core\Is::empty(Engine::sslCertPasswd())) {
			// Set the SSL certificate passphrase
			curl_setopt($rscCurl, CURLOPT_SSLCERTPASSWD, Engine::sslCertPasswd());
		}
		// Check for certificate authority information
		if (!Core\Is::empty(Engine::sslCaCert())) {
			// Set the SSL certificate authority information
			curl_setopt($rscCurl, CURLOPT_CAINFO, Engine::sslCaCert());
		}
	}

	/**
	 * This method sorts and compares the amazon headers
	 * @access protected
	 * @name \Crux\Storage\S3\Request::sortAmazonHeaders()
	 * @package \Crux\Storage\S3\Request
	 * @param string $strAlpha
	 * @param string $strBravo
	 * @return int
	 * @uses strlen()
	 * @uses min()
	 * @uses strncmp()
	 */
	protected function sortAmazonHeaders(string $strAlpha, string $strBravo) : int
	{
		// Grab the alpha string length
		$intAlphaLength = strlen($strAlpha);
		// Grab the bravo string length
		$intBravoLength = strlen($strBravo);
		// Grab the minimum length
		$intMinimumLength = min($intAlphaLength, $intBravoLength);
		// Compare the strings
		$intComparison = strncmp($strAlpha, $strBravo, $intMinimumLength);
		// Check the lengths
		if ($intAlphaLength === $intBravoLength) {
			// We're done, return the comparison
			return $intComparison;
		}
		// Check the comparison
		if ($intComparison === 0) {
			// We're done, return a new comparison
			return (($intAlphaLength < $intBravoLength) ? -1 : 1);
		}
		// We're done, return the original comparison
		return $intComparison;
	}

	/**
	 * This method validates the bucket name
	 * @access protected
	 * @name \Crux\Storage\S3\Request::validateBucketName()
	 * @package \Crux\Storage\S3\Request
	 * @param $strBucket
	 * @return bool
	 * @uses \Crux\Storage\S3\Engine::sslEnabled()
	 * @uses strlen()
	 * @uses preg_match()
	 * @uses strstr()
	 */
	protected function validateBucketName($strBucket) : bool
	{
		// Check the bucket
		if ((strlen($strBucket) > 63) || (preg_match('/[^a-z0-9\.-]/', $strBucket) > 0)) {
			// We're done, not a valid bucket name
			return false;
		}
		// Check SSL flag and bucket name
		if (Engine::sslEnabled() && (strstr($strBucket, '.') !== false)) {
			// We're done, not a valid bucket name
			return false;
		}
		// Check the bucket name
		if (strstr($strBucket, '-.') !== false) {
			// We're done, not a valid bucket name
			return false;
		}
		// Check the bucket name
		if (strstr($strBucket, '..') !== false) {
			// We're done, not a valid bucket name
			return false;
		}
		// Make sure the bucket name begins with alphanumeric characters
		if (!preg_match('/^[0-9a-z]/', $strBucket)) {
			// We're done, not a valid bucket name
			return false;
		}
		// Make sure the bucket name ends with alphanumeric characters
		if (!preg_match('/[0-9a-z]$/', $strBucket)) {
			// We're done, not a valid bucket name
			return false;
		}
		// We're done, looks like we have a valid bucket name
		return true;
	}

	/**
	 * This method writes a response from the cURL handler to their respective containers
	 * @access protected
	 * @name \Crux\Storage\S3\Request::writeResponse()
	 * @package \Crux\Storage\S3\Request
	 * @param resource $rscCurl
	 * @param string $strData
	 * @return int
	 * @uses \Crux\Storage\S3\Response::code()
	 * @uses \Crux\Storage\S3\Response::body()
	 * @uses in_array()
	 * @uses fwrite()
	 * @uses strlen()
	 */
	protected function writeResponse($rscCurl, $strData) : int
	{
		// Check the response code and file handle
		if (in_array($this->response()->code(), [200, 206]) && ($this->mFilePointer !== false)) {
			// Write the response
			return fwrite($this->mFilePointer, $strData);
		} else {
			// Append the data to the response body
			$this->mBody .= $strData;
			// We're done, return the length of the data
			return strlen($strData);
		}
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Public Methods ///////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method sends a request to S3 or CloudFront
	 * @access public
	 * @name \Crux\Storage\S2\Request::send()
	 * @return Response
	 */
	public function send() : Response
	{
		// Define our query
		$strQuery = '';
		// Setup the parameters
		$this->setupParameters();
		// Define the URL
		$strUrl = sprintf('%s://%s%s', (Engine::sslEnabled() ? 'https' : 'http'), (Core\Is::empty($this->mHeaders['Host']) ? $this->endpoint() : $this->header('Host')), $this->uri());
		// Initialize the cURL handle
		$rscCurl = curl_init();
		// Set the user agent
		curl_setopt($rscCurl, CURLOPT_USERAGENT, 'PHireworks/S3SE');
		// Check the SSL flag
		if (Engine::sslEnabled()) {
			// Setup the SSL connection
			$this->setupSsl($rscCurl);
		}
		// Set the URL into the handle
		curl_setopt($rscCurl, CURLOPT_URL, $strUrl);
		// Check for a proxy
		if (Engine::proxyEnabled()) {
			// Setup the proxy
			$this->setupProxy($rscCurl);
		}
		// Setup the headers
		$this->setupHeaders($rscCurl);
		// We don't want to return the response directly to the handle
		curl_setopt($rscCurl, CURLOPT_RETURNTRANSFER, false);
		// Attach the write callback
		curl_setopt($rscCurl, CURLOPT_WRITEFUNCTION, [&$this, 'writeResponse']);
		// Attach the header callback
		curl_setopt($rscCurl, CURLOPT_HEADERFUNCTION, [&$this, 'parseResponseHeaders']);
		// We need to follow redirects
		curl_setopt($rscCurl, CURLOPT_FOLLOWLOCATION, true);
		// Check the verb
		if (in_array($this->verb(), [self::Post, self::Put])) {
			// Setup the POST or PUT request
			$this->postAndPutRequest($rscCurl);
		} elseif ($this->verb() === self::Head) {
			// Setup the HEAD request
			$this->headRequest($rscCurl);
		} else if ($this->verb() === self::Delete) {
			// Setup the DELETE request
			$this->deleteRequest($rscCurl);
		} else {
			// TODO - GET Request (Placeholder)
		}
		// Execute the cURL handle
		if (curl_exec($rscCurl)) {
			// Set the response code
			$this->response()->code(curl_getinfo($rscCurl, CURLINFO_HTTP_CODE));
		} else {
			// Set the error into the response
			$this->response()->error([
				'code' => curl_errno($rscCurl),
				'message' => curl_error($rscCurl),
				'resource' => $this->resource()
			]);
		}
		// Close the handle
		@curl_close($rscCurl);
		// Check the response
		if (Core\Is::empty($this->response()->error()) && Core\Is::xml($this->mBody)) {
			// Deserialize the response
			$objResponse = simplexml_load_string($this->mBody);
			// Check the code and the message
			if (
				!in_array(($objResponse->code ?? 0), [200, 204, 206]) &&
				!Core\Is::empty($objResponse->body->Code ?? '') &&
				!Core\Is::empty($objResponse->body->Message ?? '')
			) {
				// Define the error
				$arrError = [];
				// Set the code into the error
				$arrError['code'] = $objResponse->body->Code;
				// Set the message into the error
				$arrError['message'] = $objResponse->body->Message;
				// Check for a resource
				if (!Core\Is::empty($objResponse->body->Resource ?? '')) {
					// Set the resource into the response error
					$arrError['resource'] = $objResponse->body->Resource;
				}
				// Set the error into the response
				$this->response()->error($arrError);
			} else {
				// Set the response body
				$this->response()->body($objResponse->body);
			}
		}
		// Check the file pointer
		if (($this->mFilePointer !== false) && Core\Is::resource($this->mFilePointer)) {
			// Close the file pointer
			fclose($this->mFilePointer);
		}
		// We're done, return the response
		return $this->response();
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Inline Methods ///////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method returns a single Amazon specific header from the instance with the ability to reset it inline
	 * @access public
	 * @name \Crux\Storage\S3\Request::amazonHeader()
	 * @package \Crux\Storage\S3\Request
	 * @param string $strName
	 * @param mixed $mixValue [null]
	 * @return mixed
	 * @uses \Crux\Core\Is::null()
	 */
	public function amazonHeader(string $strName, $mixValue = null)
	{
		// Check for a provided value
		if (!Core\Is::null($mixValue)) {
			// Reset the Amazon header into the instance
			$this->mAmazonHeaders[$strName] = $mixValue;
		}
		// Return the Amazon header from the instance
		return $this->mAmazonHeaders[$strName];
	}

	/**
	 * This method returns the Amazon specific headers from the instance with the ability to reset it inline
	 * @access public
	 * @name \Crux\Storage\S3\Request::amazonHeaders()
	 * @package \Crux\Storage\S3\Request
	 * @param array<string, mixed> $arrHeaders [array()]
	 * @return array<string, mixed>
	 * @uses \Crux\Core\Is::empty()
	 */
	public function amazonHeaders(array $arrHeaders = []) : array
	{
		// Check for provided headers
		if (!Core\Is::empty($arrHeaders)) {
			// Reset the Amazon headers into the instance
			$this->mAmazonHeaders = $arrHeaders;
		}
		// Return the Amazon headers from the instance
		return $this->mAmazonHeaders;
	}

	/**
	 * This method returns the request body from the instance with the ability to reset it inline
	 * @access public
	 * @name \Crux\Storage\S3\Engine::body()
	 * @package \Crux\Storage\S3\Engine
	 * @param string $strBody ['']
	 * @return string
	 * @uses \Crux\Core\Is::empty()
	 */
	public function body(string $strBody = '') : string
	{
		// Check for a provided body
		if (!Core\Is::empty($strBody)) {
			// Reset the body into the instance
			$this->mBody = $strBody;
		}
		// Return the body from the instance
		return $this->mBody;
	}

	/**
	 * This method returns the bucket from the instance with the ability to reset it inline
	 * @access public
	 * @name \Crux\Storage\S3\Request::bucket()
	 * @package \Crux\Storage\S3\Request
	 * @param string $strBucket ['']
	 * @return string
	 * @uses \Crux\Core\Is::empty()
	 */
	public function bucket(string $strBucket = '') : string
	{
		// Check for a provided bucket
		if (!Core\Is::empty($strBucket)) {
			// Reset the bucket into the instance
			$this->mBucket = $strBucket;
		}
		// Return the bucket from the instance
		return $this->mBucket;
	}

	/**
	 * This method tells the instance not to use an HTTP PUT request
	 * @access public
	 * @name \Crux\Storage\S3\Request::doNotUsePut()
	 * @package \Crux\Storage\S3\Request
	 * @return \Crux\Storage\S3\Request $this
	 */
	public function doNotUsePut() : Request
	{
		// Reset the flag into the instance
		$this->mUsePut = false;
		// We're done, return the instance
		return $this;
	}

	/**
	 * This method returns the endpoint from the instance with the ability to reset it inline
	 * @access public
	 * @name \Crux\Storage\S3\Request::endpoint()
	 * @package \Crux\Storage\S3\Request
	 * @param string $strEndpoint ['']
	 * @return string
	 * @uses \Crux\Core\Is::empty()
	 */
	public function endpoint(string $strEndpoint = '') : string
	{
		// Check for a provided endpoint
		if (!Core\Is::empty($strEndpoint)) {
			// Reset the endpoint into the instance
			$this->mEndpoint = $strEndpoint;
		}
		// Return the endpoint from the instance
		return $this->mEndpoint;
	}

	/**
	 * This method returns the PUT file size from the instance with the ability to reset it inline
	 * @access public
	 * @name \Crux\Storage\S3\Request::fileSize()
	 * @package \Crux\Storage\S3\Request
	 * @param int $intBytes [-1]
	 * @return int
	 */
	public function fileSize(int $intBytes = -1) : int
	{
		// Check for a provided file size
		if ($intBytes > -1) {
			// Reset the file size into the instance
			$this->mFileSize = $intBytes;
		}
		// Return the file size from the instance
		return $this->mFileSize;
	}

	/**
	 * This method returns single HTTP header from the instance with the ability to reset it inline
	 * @access public
	 * @name \Crux\Storage\S3\Request::header()
	 * @package \Crux\Storage\S3\Request
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
	 * This method returns the HTTP headers from the instance with the ability to reset it inline
	 * @access public
	 * @name \Crux\Storage\S3\Request::headers()
	 * @package \Crux\Storage\S3\Request
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

	/**
	 * This method returns a single parameter from the instance with the ability to reset it inline
	 * @access public
	 * @name \Crux\Storage\S3\Request::parameter()
	 * @package \Crux\Storage\S3\Request
	 * @param string $strName
	 * @param mixed $mixValue [null]
	 * @return mixed
	 * @uses \Crux\Core\Is::null()
	 */
	public function parameter(string $strName, $mixValue = null)
	{
		// Check for a provided value
		if (!Core\Is::null($mixValue)) {
			// Reset the parameter into the instance
			$this->mParameters[$strName] = $mixValue;
		}
		// Return the parameter from the instance
		return $this->mParameters[$strName];
	}

	/**
	 * This method returns the parameters from the instance with the ability to reset it inline
	 * @access public
	 * @name \Crux\Storage\S3\Request::parameters()
	 * @package \Crux\Storage\S3\Request
	 * @param array<string, mixed> $arrParameters [array()]
	 * @return array<string, mixed>
	 * @uses \Crux\Core\Is::empty()
	 */
	public function parameters(array $arrParameters = []) : array
	{
		// Check for provided parameters
		if (!Core\Is::empty($arrParameters)) {
			// Reset the parameters into the instance
			$this->mParameters = $arrParameters;
		}
		// Return the parameters from the instance
		return $this->mParameters;
	}

	/**
	 * This method returns a single PUT/POST field from the instance with the ability to reset it inline
	 * @access public
	 * @name \Crux\Storage\S3\Request::putPostField()
	 * @package \Crux\Storage\S3\Request
	 * @param string $strName
	 * @param mixed $mixValue [null]
	 * @return mixed
	 * @uses \Crux\Core\Is::null()
	 */
	public function putPostField(string $strName, $mixValue = null)
	{
		// Check for a provided field
		if (!Core\Is::null($mixValue)) {
			// Reset the field into the instance
			$this->mPutPostFields[$strName] = $mixValue;
		}
		// Return the field from the instance
		return $this->mPutPostFields[$strName];
	}

	/**
	 * This method returns the PUT/POST fields from the instance with the ability to reset it inline
	 * @access public
	 * @name \Crux\Storage\S3\Request::putPostFields()
	 * @package \Crux\Storage\S3\Request
	 * @param array<string, mixed> $arrFields [array()]
	 * @return array<string, mixed>
	 * @uses \Crux\Core\Is::empty()
	 */
	public function putPostFields(array $arrFields = []) : array
	{
		// Check for provided fields
		if (!Core\Is::empty($arrFields)) {
			// Reset the fields into the instance
			$this->mPutPostFields = $arrFields;
		}
		// Return the fields from the instance
		return $this->mPutPostFields;
	}

	/**
	 * This method returns the resource URI from the instance with the ability to reset it inline
	 * @access public
	 * @name \Crux\Storage\S3\Request::resource()
	 * @package \Crux\Storage\S3\Request
	 * @param string $strResource ['']
	 * @return string
	 * @uses \Crux\Core\Is::empty()
	 */
	public function resource(string $strResource = '') : string
	{
		// Check for a provided resource
		if (!Core\Is::empty($strResource)) {
			// Reset the resource into the instance
			$this->mResource = $strResource;
		}
		// Return the resource from the instance
		return $this->mResource;
	}

	/**
	 * This method returns the response from the instance with the ability to reset it inline
	 * @access public
	 * @name \Crux\Storage\S3\Request::verb()
	 * @package \Crux\Storage\S3\Request
	 * @param \Crux\Storage\S3\Response $s3Response [null]
	 * @return \Crux\Storage\S3\Response
	 * @uses \Crux\Core\Is::null()
	 */
	public function response(Response $s3Response = null) : Response
	{
		// Check for a provided response
		if (!Core\Is::null($s3Response)) {
			// Reset the response into the instance
			$this->mResponse = $s3Response;
		}
		// Return the response from the instance
		return $this->mResponse;
	}

	/**
	 * This method returns the URI from the instance with the ability to reset it inline
	 * @access public
	 * @name \Crux\Storage\S3\Request::uri()
	 * @package \Crux\Storage\S3\Request
	 * @param string $strUri ['']
	 * @return string
	 * @uses \Crux\Core\Is::empty()
	 */
	public function uri(string $strUri = '') : string
	{
		// Check for a provided URI
		if (!Core\Is::empty($strUri)) {
			// Reset the URI into the instance
			$this->mUri = $strUri;
		}
		// Return the URI from the instance
		return $this->mUri;
	}

	/**
	 * This method tells the instance to use a PUT request
	 * @access public
	 * @name \Crux\Storage\S3\Request::usePut()
	 * @package \Crux\Storage\S3\Request
	 * @return \Crux\Storage\S3\Request $this
	 */
	public function usePut() : Request
	{
		// Return the put flag into the instance
		$this->mUsePut = true;
		// We're done, return the instance
		return $this;
	}

	/**
	 * This method returns the put flag from the instance with the ability to reset it inline
	 * @access public
	 * @name \Crux\Storage\S3\Request::usesPut()
	 * @package \Crux\Storage\S3\Request
	 * @param bool $blnFlag [null]
	 * @return bool
	 * @uses \Crux\Core\Is::null()
	 */
	public function usesPut(bool $blnFlag = null) : bool
	{
		// Check for a provided flag
		if (!Core\Is::null($blnFlag)) {
			// Reset the flag into the instance
			$this->mUsePut = $blnFlag;
		}
		// Return the flag from the instance
		return $this->mUsePut;
	}

	/**
	 * This method returns the verb from the instance with the ability to reset it inline
	 * @access public
	 * @name \Crux\Storage\S3\Request::verb()
	 * @package \Crux\Storage\S3\Request
	 * @param string $strVerb ['']
	 * @return string
	 * @uses \Crux\Core\Is::empty()
	 */
	public function verb(string $strVerb = '') : string
	{
		// Check for a provided verb
		if (!Core\Is::empty($strVerb)) {
			// Reset the verb into the instance
			$this->mVerb = $strVerb;
		}
		// Return the verb from the instance
		return $this->mVerb;
	}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
} /// End Crux\Storage\S3\Request Class Definition ///////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

<?php

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Crux\Storage\S3 Namespace ////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

namespace Crux\Storage\S3;

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Imports //////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

use Crux\Core;
use Crux\Crypto;

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Crux\Storage\S3\Engine Class Definition //////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class Engine
{
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Endpoint Constants ///////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This constant contains the default CloudFront endpoint
	 * @name \Crux\Storage\S3\Engine::CloudFront
	 * @package \Crux\Storage\S3\Engine
	 * @var string
	 */
	const CloudFront = 'cloudfront.amazonaws.com';

	/**
	 * This constant contains the default Amazon S3 endpoint
	 * @name \Crux\Storage\S3\Engine::S3
	 * @package \Crux\Storage\S3\Engine
	 * @var string
	 */
	const S3 = 's3.amazonaws.com';

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Access Control Constants /////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This constant defines the Authenticated Read ACL
	 * @name \Crux\Storage\S3\Engine::AuthenticatedRead
	 * @package \Crux\Storage\S3\Engine
	 * @var string
	 */
	const AuthenticatedRead = 'authenticated-read';

	/**
	 * This constant defines the Private ACL
	 * @name \Crux\Storage\S3\Engine::Private
	 * @package \Crux\Storage\S3\Engine
	 * @var string
	 */
	const Private = 'private';

	/**
	 * This constant defines the Public Read ACL
	 * @name \Crux\Storage\S3\Engine::PublicRead
	 * @package \Crux\Storage\S3\Engine
	 * @var string
	 */
	const PublicRead = 'public-read';

	/**
	 * This constant defines the Public Read & Write ACL
	 * @name \Crux\Storage\S3\Engine::PublicReadWrite
	 * @package \Crux\Storage\S3\Engine
	 * @var string
	 */
	const PublicReadWrite = 'public-read-write';

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Storage Class Constants //////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This constant defines the Standard storage class
	 * @name \Crux\Storage\S3\Engine::Standard
	 * @package \Crux\Storage\S3\Engine
	 * @var string
	 */
	const Standard = 'STANDARD';

	/**
	 * This constant defines the Reduced Redundancy storage class
	 * @name \Crux\Storage\S3\Engine::ReducedRedundancy
	 * @package \Crux\Storage\S3\Engine
	 * @var string
	 */
	const ReducedRedundancy = 'REDUCED_REDUNDANCY';

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// SSE Constants ////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This constant defines the AES-256 SSE
	 * @name \Crux\Storage\S3\Engine::AES256
	 * @package \Crux\Storage\S3\Engine
	 * @var string
	 */
	const AES256 = 'AES256';

	/**
	 * This constant defines no SSE
	 * @name \Crux\Storage\S3\Engine::NoSSE
	 * @package \Crux\Storage\S3\Engine
	 * @var string
	 */
	const NoSSE = '';

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Properties ///////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This property contains the Amazon S3 Access Key
	 * @access protected
	 * @name \Crux\Storage\S3\Engine::$mAccessKey
	 * @package \Crux\Storage\S3\Engine
	 * @static
	 * @var string
	 */
	protected static $mAccessKey = '';

	/**
	 * This property contains the default bucket delimiter
	 * @access protected
	 * @name \Crux\Storage\S3\Engine::$mDefaultDelimiter
	 * @package \Crux\Storage\S3\Engine
	 * @static
	 * @var string
	 */
	protected static $mDefaultDelimiter = '';

	/**
	 * This property contains the endpoint to send requests to
	 * @access protected
	 * @name \Crux\Storage\S3\Engine::$mEndpoint
	 * @package \Crux\Storage\S3\Engine
	 * @static
	 * @var string
	 */
	protected static $mEndpoint = self::S3;

	/**
	 * This property contains the signing key pair
	 * @access protected
	 * @name \Crux\Storage\S3\Engine::$mKeyPair
	 * @package \Crux\Storage\S3\Engine
	 * @static
	 * @var string
	 */
	protected static $mKeyPair = '';

	/**
	 * This property contains the signing key pair resource
	 * @access protected
	 * @name \Crux\Storage\S3\Engine::$mKeyResource
	 * @package \Crux\Storage\S3\Engine
	 * @static
	 * @var resource
	 */
	protected static $mKeyResource;

	/**
	 * This property contains the proxy flag
	 * @access protected
	 * @name \Crux\Storage\S3\Engine::$mProxyEnabled
	 * @package \Crux\Storage\S3\Engine
	 * @static
	 * @var bool
	 */
	protected static $mProxyEnabled = false;

	/**
	 * This property contains the proxy host address
	 * @access protected
	 * @name \Crux\Storage\S3\Engine::$mProxyHost
	 * @package \Crux\Storage\S3\Engine
	 * @static
	 * @var string
	 */
	protected static $mProxyHost = '';

	/**
	 * This property contains the proxy authentication password
	 * @access protected
	 * @name \Crux\Storage\S3\Engine::$mProxyPass
	 * @package \Crux\Storage\S3\Engine
	 * @static
	 * @var string
	 */
	protected static $mProxyPass = '';

	/**
	 * This property contains the proxy type
	 * @access protected
	 * @name \Crux\Storage\S3\Engine::$mProxyType
	 * @package \Crux\Storage\S3\Engine
	 * @static
	 * @var int
	 */
	protected static $mProxyType = CURLPROXY_SOCKS5;

	/**
	 * This property contains the proxy authentication username
	 * @access protected
	 * @name \Crux\Storage\S3\Engine::$mProxyUser
	 * @package \Crux\Storage\S3\Engine
	 * @static
	 * @var string
	 */
	protected static $mProxyUser = '';

	/**
	 * This property contains the Amazon S3 Secret Key
	 * @access protected
	 * @name \Crux\Storage\S3\Engine::$mSecretKey
	 * @package \Crux\Storage\S3\Engine
	 * @static
	 * @var string
	 */
	protected static $mSecretKey = '';

	/**
	 * This property contains the Client Certificate Authority
	 * @access protected
	 * @name \Crux\Storage\S3\Engine::$mSslCaCert
	 * @package \Crux\Storage\S3\Engine
	 * @static
	 * @var string
	 */
	protected static $mSslCaCert = '';

	/**
	 * This property contains the Client SSL Certificate
	 * @access protected
	 * @name \Crux\Storage\S3\Engine::$mSslCert
	 * @package \Crux\Storage\S3\Engine
	 * @static
	 * @var string
	 */
	protected static $mSslCert = '';

	/**
	 * This property contains the Client SSL Certificate password
	 * @access protected
	 * @name \Crux\Storage\S3\Engine::$mSslCertPasswd
	 * @package \Crux\Storage\S3\Engine
	 * @static
	 * @var string
	 */
	protected static $mSslCertPasswd = '';

	/**
	 * This property contains the SSL flag
	 * @access protected
	 * @name \Crux\Storage\S3\Engine::$mSslEnabled
	 * @package \Crux\Storage\S3\Engine
	 * @static
	 * @var bool
	 */
	protected static $mSslEnabled = true;

	/**
	 * This property contains the Client SSL Certificate Key
	 * @access protected
	 * @name \Crux\Storage\S3\Engine::$mSslKey
	 * @package \Crux\Storage\S3\Engine
	 * @static
	 * @var string
	 */
	protected static $mSslKey = '';

	/**
	 * This property contains the Client SSL Certificate Key password
	 * @access protected
	 * @name \Crux\Storage\S3\Engine::$mSslKeyPasswd
	 * @package \Crux\Storage\S3\Engine
	 * @static
	 * @var string
	 */
	protected static $mSslKeyPasswd = '';

	/**
	 * This property tells the system whether or not to verify the client with SSL
	 * @access protected
	 * @name \Crux\Storage\S3\Engine::$mSslValidationEnabled
	 * @package \Crux\Storage\S3\Engine
	 * @static
	 * @var bool
	 */
	protected static $mSslValidationEnabled = false;

	/**
	 * This property contains the the SSL/TLS version to be used (BEWARE OF SSL, USE TLS)
	 * @access protected
	 * @name \Crux\Storage\S3\Engine::$mSslVersion
	 * @package \Crux\Storage\S3\Engine
	 * @static
	 * @var int
	 */
	protected static $mSslVersion = CURL_SSLVERSION_TLSv1_2;

	/**
	 * This property tells the instance whether or not to throw exceptions
	 * @access protected
	 * @name \Crux\Storage\S3\Engine::$mThrowExceptions
	 * @package \Crux\Storage\S3\Engine
	 * @static
	 * @var string
	 */
	protected static $mThrowExceptions = true;

	/**
	 * This property contains the number of seconds to offset the time
	 * @access protected
	 * @name \Crux\Storage\S3\Engine::$mTimeOffset
	 * @package \Crux\Storage\S3\Engine
	 * @static
	 * @var int
	 */
	protected static $mTimeOffset = 0;

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Static Constructor ///////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method initializes the Amazon S3 Storage Engine
	 * @access public
	 * @name \Crux\Storage\S3\Engine::Initialize()
	 * @package \Crux\Storage\S3\Engine
	 * @param string $strAccessKey ['']
	 * @param string $strSecretKey ['']
	 * @param bool $blnSslEnabled [true]
	 * @param int $intSslVersion [CURL_SSLVERSION_TLSv1_2]
	 * @return void
	 * @static
	 * @uses \Crux\Storage\S3\Engine::accessKey()
	 * @uses \Crux\Storage\S3\Engine::secretKey()
	 * @uses \Crux\Storage\S3\Engine::sslEnabled()
	 * @uses \Crux\Storage\S3\Engine::sslVersion()
	 * @uses \Crux\Core\Is::empty()
	 */
	public static function Initialize(string $strAccessKey = '', string $strSecretKey = '', bool $blnSslEnabled = true, int $intSslVersion = CURL_SSLVERSION_TLSv1_2)
	{
		// Check for an access key
		if (!Core\Is::empty($strAccessKey)) {
			// Set the access key
			self::accessKey($strAccessKey);
		}
		// Check for a secret key
		if (!Core\Is::empty($strSecretKey)) {
			// Set the secret key
			self::secretKey($strSecretKey);
		}
		// Set the SSL flag
		self::sslEnabled($blnSslEnabled);
		// Set the SSL version
		self::sslVersion($intSslVersion);
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Protected Methods ////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method looks for errors in the response
	 * @access protected
	 * @name \Crux\Storage\S3\Engine::hasErrors()
	 * @package \Crux\Storage\S3\Engine
	 * @param \Crux\Storage\S3\Response $resS3
	 * @return bool
	 * @static
	 * @uses \Crux\Storage\S3\Response::error()
	 * @uses \Crux\Storage\S3\Response::code()
	 * @uses \Crux\Core\Is::empty()
	 * @uses \Crux\Storage\S3\Engine::throw()
	 */
	protected static function hasErrors(Response $resS3) : bool
	{
		// Check for errors
		if (!Core\Is::empty($resS3->error()) || ($resS3->code() !== 200)) {
			// Check the code
			if ($resS3->code() !== 200) {
				// Trigger the error
				self::throw('Unexpected HTTP Status from Amazon', $resS3->code());
			} else {
				// Trigger the error
				self::throw($resS3->error()['message'], $resS3->error()['code']);
			}
			// We're done
			return true;
		}
		// We're done
		return false;
	}

	/**
	 * This method generates a hash for signing the Amazon S3 request
	 * @access protected
	 * @name \Crux\Storage\S3\Engine::hash()
	 * @package \Crux\Storage\S3\Engine
	 * @param string $strData
	 * @return string
	 * @static
	 * @uses \Crux\Storage\S3\Engine::secretKey()
	 * @uses extension_loaded()
	 * @uses hash_hmac()
	 * @uses chr()
	 * @uses str_repeat()
	 * @uses str_pad()
	 * @uses sha1()
	 * @uses pack()
	 * @uses base64_encode()
	 */
	protected static function hash(string $strData) : string
	{
		// Check for the hashing extension
		if (extension_loaded('hash')) {
			// Generate the hash
			$strHash = hash_hmac('sha1', $strData, self::secretKey(), true);
		} else {
			// Generate the hash
			$strHash = pack('H*', sha1(
				sprintf('%s%s', (str_pad(self::secretKey(), 64, chr(0x00)) ^ (str_repeat(chr(0x5c), 64))), pack('H*', sha1(
					sprintf('%s%s', (str_pad(self::secretKey(), 64, chr(0x00)) ^ (str_repeat(chr(0x36), 64))), $strData)
				)))
			));
		}
		// We're done, encode and return the hash
		return base64_encode($strHash);
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Request Methods //////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////


	public static function bucket() : array
	{

	}

	/**
	 * This method lists all buckets on an account
	 * @access public
	 * @name \Crux\Storage\S3\Engine::buckets()
	 * @package \Crux\Storage\S3\Engine
	 * @return array<string, mixed>
	 * @static
	 * @uses \Crux\Storage\S3\Request::__construct()
	 * @uses \Crux\Storage\S3\Request::send()
	 * @uses \Crux\Storage\S3\Engine::hasErrors()
	 * @uses \Crux\Storage\S3\Response::body()
	 * @uses \Crux\Core\Is::empty()
	 * @uses \Crux\Core\Is::null()
	 * @uses array_push()
	 * @uses strtotime()
	 */
	public static function buckets() : array
	{
		// Define the results container
		$arrResults = [];
		// Instantiate the request
		$reqBuckets = new Request(Request::Get);
		// Send the request
		$resBuckets = $reqBuckets->send();
		// Check for errors
		if (self::hasErrors($resBuckets)) {
			// We're done, return the empty results
			return $arrResults;
		}
		// Check for buckets
		if (Core\Is::null($resBuckets->body()->Buckets ?? null)) {
			// We're done
			return $arrResults;
		}
		// Check for an owner
		if (!Core\Is::null($resBuckets->body()->Owner ?? null) && !Core\Is::null($resBuckets->body()->Owner->ID ?? null) && !Core\Is::null($resBuckets->body()->Owner->DisplayName ?? null)) {
			// Set the owner data into the results
			$arrResults['owner'] = [
				'id' => $resBuckets->body()->Owner->ID,
				'name' => $resBuckets->body()->Owner->DisplayName
			];
		}
		// Create the buckets key
		$arrResults['buckets'] = [];
		// Iterate over the buckets
		foreach ($resBuckets->body()->Buckets->Bucket as $objBucket) {
			// Add the bucket to the results
			array_push($arrResults['buckets'], [
				'name' => $objBucket->Name,
				'created' => strtotime($objBucket->CreationDate)
			]);
		}
		// We're done
		return $arrResults;
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Public Methods ///////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method sets the authentication details into the engine
	 * @access public
	 * @name \Crux\Storage\S3\Engine::auth()
	 * @package \Crux\Storage\S3\Engine
	 * @param string $strAccessKey
	 * @param string $strSecretKey
	 * @return void
	 * @static
	 * @uses \Crux\Storage\S3\Engine::accessKey()
	 * @uses \Crux\Storage\S3\Engine::secretKey()
	 */
	public static function auth(string $strAccessKey, string $strSecretKey)
	{
		// Set the access key
		self::accessKey($strAccessKey);
		// Set the secret key
		self::secretKey($strSecretKey);
	}

	/**
	 * This method returns the authentication status of the engine
	 * @access public
	 * @name \Crux\Storage\S3\Engine::hasAuth()
	 * @package \Crux\Storage\S3\Engine
	 * @return bool
	 * @static
	 * @uses \Crux\Storage\S3\Engine::accessKey()
	 * @uses \Crux\Storage\S3\Engine::secretKey()
	 * @uses \Crux\Core\Is::empty()
	 */
	public static function hasAuth() : bool
	{
		// Return the authentication status
		return (!Core\Is::empty(self::accessKey()) && !Core\Is::empty(self::secretKey()));
	}

	/**
	 * This method sets up the engine's proxy
	 * @access public
	 * @name \Crux\Storage\S3\Engine::proxy()
	 * @package \Crux\Storage\S3\Engine
	 * @param string $strHost
	 * @param string $strUsername ['']
	 * @param string $strPassword ['']
	 * @param int $intType [CURLPROXY_SOCKS5]
	 * @return void
	 * @static
	 * @uses \Crux\Storage\S3\Engine::proxyHost()
	 * @uses \Crux\Storage\S3\Engine::proxyUser()
	 * @uses \Crux\Storage\S3\Engine::proxyPass()
	 * @uses \Crux\Storage\S3\Engine::proxyType()
	 * @uses \Crux\Storage\S3\Engine::proxyEnabled()
	 */
	public static function proxy(string $strHost, string $strUsername = '', string $strPassword = '', int $intType = CURLPROXY_SOCKS5)
	{
		// Set the proxy host
		self::proxyHost($strHost);
		// Set the proxy username
		self::proxyUser($strUsername);
		// Set the proxy password
		self::proxyPass($strPassword);
		// Set the proxy type
		self::proxyType($intType);
		// Enable the proxy
		self::proxyEnabled(true);
	}

	/**
	 * This method releases the signing key from memeory
	 * @access public
	 * @name \Crux\Storage\S3\Engine::releaseSigningKey()
	 * @package \Crux\Storage\S3\Engine
	 * @return void
	 * @static
	 * @uses \Crux\Core\Is::resource()
	 * @uses openssl_pkey_free()
	 */
	public static function releaseSigningKey()
	{
		// Check the resource
		if (Core\Is::resource(self::$mKeyResource)) {
			// Free the private key
			openssl_pkey_free(self::$mKeyResource);
		}
	}

	/**
	 * This method signs the Amazon S3 request
	 * @access public
	 * @name \Crux\Storage\S3\Engine::sign()
	 * @package \Crux\Storage\S3\Engine
	 * @param string $strData
	 * @return string
	 * @static
	 * @uses \Crux\Storage\S3\Engine::hash()
	 * @uses sprintf()
	 */
	public static function sign(string $strData) : string
	{
		// Return the signature
		return sprintf('AWS%s:%s', self::accessKey(), self::hash($strData));
	}

	/**
	 * This method sets the signing key pair into the engine
	 * @access public
	 * @name \Crux\Storage\S3\Engine::signingKey()
	 * @package \Crux\Storage\S3\Engine
	 * @param string $strPair
	 * @param string $strKey ['']
	 * @return bool
	 * @static
	 * @uses \Crux\Storage\S3\Engine::keyPair()
	 * @uses \Crux\Storage\S3\Engine::keyResource()
	 * @uses \Crux\Storage\S3\Engine::throw()
	 * @uses \Crux\Crypto\OpenSSL::getKeyResource()
	 * @uses \Crux\Core\Exception\Crypto\OpenSSL::getMessage()
	 * @uses \Crux\Core\Exception\Crypto\OpenSSL::getCode()
	 */
	public static function signingKey(string $strPair, string $strKey = '') : bool
	{
		// Set the key pair
		self::keyPair($strPair);
		// Try to grab the key
		try {
			// Grab the key
			self::keyResource(Crypto\OpenSSL::getKeyResource($strKey));
			// We're done
			return true;
		} catch (Core\Exception\Crypto\OpenSSL $sslException) {
			// Handle the exception
			self::throw($sslException->getMessage(), $sslException->getCode(), $sslException);
			// We're done
			return false;
		}
	}

	/**
	 * This method provides a wrapper for turning SSL and SSL Client validation on and off
	 * @access public
	 * @name \Crux\Storage\S3\Engine::ssl()
	 * @package \Crux\Storage\S3\Engine
	 * @param bool $blnEnable [true]
	 * @param bool $blnEnableClientValidation [false]
	 * @return void
	 * @static
	 * @uses \Crux\Storage\S3\Engine::sslEnabled()
	 * @uses \Crux\Storage\S3\Engine::sslValidationEnabled()
	 */
	public static function ssl(bool $blnEnable = true, bool $blnEnableClientValidation = false)
	{
		// Set the SSL flag
		self::sslEnabled($blnEnable);
		// Set the SSL peer validation flag
		self::sslValidationEnabled($blnEnableClientValidation);
	}

	/**
	 * This method populates the SSL authentication properties in the engine
	 * @access public
	 * @name \Crux\Storage\S3\Engine::sslAuth()
	 * @package \Crux\Storage\S3\Engine
	 * @param string $strCertificate ['']
	 * @param string $strKey ['']
	 * @param string $strCaCertificate ['']
	 * @return void
	 * @static
	 * @uses \Crux\Storage\S3\Engine::sslCert()
	 * @uses \Crux\Storage\S3\Engine::sslKey()
	 * @uses \Crux\Storage\S3\Engine::sslCaCert()
	 * @uses \Crux\Storage\S3\Engine::sslEnabled()
	 * @uses \Crux\Storage\S3\Engine::sslValidationEnabled()
	 */
	public static function sslAuth(string $strCertificate = '', string $strKey = '', string $strCaCertificate = '')
	{
		// Set the SSL certificate
		self::sslCert($strCertificate);
		// Set the SSL key
		self::sslKey($strKey);
		// Set the SSL certificate authority
		self::sslCaCert($strCaCertificate);
		// Enable SSL
		self::sslEnabled(true);
		// Enable SSL client validation
		self::sslValidationEnabled(true);
	}

	/**
	 * This method handles errors that occur while interacting with Amazon S3 and CloudFront
	 * @access public
	 * @name \Crux\Storage\S3\Engine::throw()
	 * @package \Crux\Storage\S3\Engine
	 * @param string $strMessage
	 * @param int $intCode [0]
	 * @param \Exception $excError [null]
	 * @return void
	 * @static
	 * @throws \Crux\Core\Exception\Storage\S3\Engine
	 * @uses \Crux\Storage\S3\Engine::throwExceptions()
	 * @uses \Crux\Core\Exception\Storage\S3\Engine::__construct()
	 * @uses trigger_error()
	 */
	public static function throw(string $strMessage, int $intCode = 0, \Exception $excError = null)
	{
		// Check to see if we can throw exceptions
		if (self::throwExceptions()) {
			// Throw the exception
			throw new Core\Exception\Storage\S3\Engine($strMessage, $intCode, $excError);
		} else {
			// Trigger the error
			trigger_error($strMessage, E_USER_WARNING);
		}
	}

	/**
	 * This method returns the time with the offset accounted for
	 * @access public
	 * @name \Crux\Storage\S3\Engine::time()
	 * @package \Crux\Storage\S3\Engine
	 * @return int
	 * @static
	 * @uses \Crux\Storage\S3\Engine::timeOffset()
	 * @uses time()
	 */
	public static function time() : int
	{
		// Return the time with the offset accounted for
		return (time() + self::timeOffset());
	}

	/**
	 * This method determines the correct system time offset from Amazon S3
	 * @access public
	 * @name \Crux\Storage\S3\Engine::timeCorrectionOffset()
	 * @package \Crux\Storage\S3\Engine
	 * @param int $intOffset [0]
	 * @return void
	 * @static
	 * @uses \Crux\Storage\S3\Engine::timeOffset()
	 * @uses \Crux\Storage\S3\Request::__construct()
	 * @uses \Crux\Storage\S3\Request::send()
	 * @uses \Crux\Storage\S3\Response::header()
	 * @uses time()
	 */
	public static function timeCorrectionOffset(int $intOffset = 0)
	{
		// Check the offset
		if ($intOffset === 0) {
			// Instantiate the request
			$reqS3 = new Request(Request::Head);
			// Send the request
			$resS3 = $reqS3->send();
			// Grab AWS' time
			$intAwsTime = $resS3->header('date');
			// Set the system time
			$intSystemTime = time();
			// Reset the offset
			$intOffset = (($intSystemTime > $intAwsTime) ? (-($intSystemTime - $intAwsTime)) : ($intAwsTime - $intSystemTime));
		}
		// Reset the offset
		self::timeOffset($intOffset);
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Inline Methods ///////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method returns the access key with the ability to reset it inline
	 * @access public
	 * @name \Crux\Storage\S3\Engine::accessKey()
	 * @package \Crux\Storage\S3\Engine
	 * @param string $strAccessKey ['']
	 * @return string
	 * @static
	 * @uses \Crux\Core\Is::empty()
	 */
	public static function accessKey(string $strAccessKey = '') : string
	{
		// Check for a provided access key
		if (!Core\Is::empty($strAccessKey)) {
			// Reset the access key
			self::$mAccessKey = $strAccessKey;
		}
		// Return the access key
		return self::$mAccessKey;
	}

	/**
	 * This method returns the default delimiter with the ability to reset it inline
	 * @access public
	 * @name \Crux\Storage\S3\Engine::defaultDelimiter()
	 * @package \Crux\Storage\S3\Engine
	 * @param string $strDelimiter ['']
	 * @return string
	 * @static
	 * @uses \Crux\Core\Is::empty()
	 */
	public static function defaultDelimiter(string $strDelimiter = '') : string
	{
		// Check for a provided delimiter
		if (!Core\Is::empty($strDelimiter)) {
			// Reset the delimiter
			self::$mDefaultDelimiter = $strDelimiter;
		}
		// Return the default delimiter
		return self::$mDefaultDelimiter;
	}

	/**
	 * This method returns the Amazon S3 Endpoint with the ability to reset it inline
	 * @access public
	 * @name \Crux\Storage\S3\Engine::endpoint()
	 * @package \Crux\Storage\S3\Engine
	 * @param string $strEndpoint ['']
	 * @return string
	 * @static
	 * @uses \Crux\Core\Is::empty()
	 */
	public static function endpoint(string $strEndpoint = '') : string
	{
		// Check for a provided endpoint
		if (!Core\Is::empty($strEndpoint)) {
			// Reset the endpoint
			self::$mEndpoint = $strEndpoint;
		}
		// Return the endpoint
		return self::$mEndpoint;
	}

	/**
	 * This method returns the key pair with the ability to reset it inline
	 * @access public
	 * @name \Crux\Storage\S3\Engine::keyPair()
	 * @package \Crux\Storage\S3\Engine
	 * @param string $strPair ['']
	 * @return string
	 * @static
	 * @uses \Crux\Core\Is::empty()
	 */
	public static function keyPair(string $strPair = '') : string
	{
		// Check for a provided key pair
		if (!Core\Is::empty($strPair)) {
			// Reset the key pair
			self::$mKeyPair = $strPair;
		}
		// Return the key pair
		return self::$mKeyPair;
	}

	/**
	 * This method returns the key pair resource with the ability to reset it inline
	 * @access public
	 * @name \Crux\Storage\S3\Engine::keyResource()
	 * @package \Crux\Storage\S3\Engine
	 * @param resource $rscKey [null]
	 * @return resource
	 * @static
	 * @uses \Crux\Core\Is::null()
	 */
	public static function keyResource(&$rscKey = null)
	{
		// Check for a provided resource
		if (!Core\Is::null($rscKey)) {
			// Reset the key resource
			self::$mKeyResource = $rscKey;
		}
		// Return the key resource
		return self::$mKeyResource;
	}

	/**
	 * This method returns the proxy flag with the ability to reset it inline
	 * @access public
	 * @name \Crux\Storage\S3\Engine::proxyEnabled()
	 * @package \Crux\Storage\S3\Engine
	 * @param bool $blnFlag [null]
	 * @return bool
	 * @static
	 * @uses \Crux\Core\Is::null()
	 */
	public static function proxyEnabled(bool $blnFlag = null) : bool
	{
		// Check for a provided flag
		if (!Core\Is::null($blnFlag)) {
			// Reset the flag
			self::$mProxyEnabled = $blnFlag;
		}
		// Return the flag
		return self::$mProxyEnabled;
	}

	/**
	 * This method returns the proxy host with the ability to reset it inline
	 * @access public
	 * @name \Crux\Storage\S3\Engine::proxyHost()
	 * @package \Crux\Storage\S3\Engine
	 * @param string $strHost ['']
	 * @return string
	 * @static
	 * @uses \Crux\Core\Is::empty()
	 */
	public static function proxyHost(string $strHost = '') : string
	{
		// Check for a provided host
		if (!Core\Is::empty($strHost)) {
			// Reset the proxy host
			self::$mProxyHost = $strHost;
		}
		// Return the proxy host
		return self::$mProxyHost;
	}

	/**
	 * This method returns the proxy password with the ability to reset it inline
	 * @access public
	 * @name \Crux\Storage\S3\Engine::proxyPass()
	 * @package \Crux\Storage\S3\Engine
	 * @param string $strPassword ['']
	 * @return string
	 * @static
	 * @uses \Crux\Core\Is::empty()
	 */
	public static function proxyPass(string $strPassword = '') : string
	{
		// Check for a provided proxy password
		if (!Core\Is::empty($strPassword)) {
			// Reset the proxy password
			self::$mProxyPass = $strPassword;
		}
		// Return the proxy password
		return self::$mProxyPass;
	}

	/**
	 * This method returns the proxy type with the ability to reset it inline
	 * @access public
	 * @name \Crux\Storage\S3\Engine::proxyType()
	 * @package \Crux\Storage\S3\Engine
	 * @param int $intProxyType [null]
	 * @return int
	 * @static
	 * @uses \Crux\Core\Is::null()
	 */
	public static function proxyType(int $intProxyType = null) : int
	{
		// Check for a provided proxy type
		if (!Core\Is::null($intProxyType)) {
			// Reset the proxy type
			self::$mProxyType = $intProxyType;
		}
		// Return the proxy type
		return self::$mProxyType;
	}

	/**
	 * This method returns the proxy username with the ability to reset it inline
	 * @access public
	 * @name \Crux\Storage\S3\Engine::proxyUser()
	 * @package \Crux\Storage\S3\Engine
	 * @param string $strUsername ['']
	 * @return string
	 * @static
	 * @uses \Crux\Core\Is::empty()
	 */
	public static function proxyUser(string $strUsername = '') : string
	{
		// Check fro a provided proxy username
		if (!Core\Is::empty($strUsername)) {
			// Reset the proxy username
			self::$mProxyUser = $strUsername;
		}
		// Return the proxy username
		return self::$mProxyUser;
	}

	/**
	 * This method returns the Amazon S3 secret key with the ability to reset it inline
	 * @access public
	 * @name \Crux\Storage\S3\Engine::secretKey()
	 * @package \Crux\Storage\S3\Engine
	 * @param string $strSecretKey ['']
	 * @return string
	 * @static
	 * @uses \Crux\Core\Is::empty()
	 */
	public static function secretKey(string $strSecretKey = '') : string
	{
		// Check for a provided secret key
		if (!Core\Is::empty($strSecretKey)) {
			// Reset the secret key
			self::$mSecretKey = $strSecretKey;
		}
		// Return the secret key
		return self::$mSecretKey;
	}

	/**
	 * This method returns the SSL Certificate Authority with the ability to reset it inline
	 * @access public
	 * @name \Crux\Storage\S3\Engine::sslCaCert()
	 * @package \Crux\Storage\S3\Engine
	 * @param string $strSslCaCert ['']
	 * @return string
	 * @static
	 * @uses \Crux\Core\Is::empty()
	 */
	public static function sslCaCert(string $strSslCaCert = '') : string
	{
		// Check for a provided certificate authority
		if (!Core\Is::empty($strSslCaCert)) {
			// Reset the certificate authority
			self::$mSslCaCert = $strSslCaCert;
		}
		// Return the certificate authority
		return self::$mSslCaCert;
	}

	/**
	 * This method returns the SSL certificate with the ability to reset it inline
	 * @access public
	 * @name \Crux\Storage\S3\Engine::sslCert()
	 * @package \Crux\Storage\S3\Engine
	 * @param string $strSslCert ['']
	 * @return string
	 * @static
	 * @uses \Crux\Core\Is::empty()
	 */
	public static function sslCert(string $strSslCert = '') : string
	{
		// Check for a provided SSL certificate
		if (!Core\Is::empty($strSslCert)) {
			// Reset the SSL certificate
			self::$mSslCert = $strSslCert;
		}
		// Return the SSL certificate
		return self::$mSslCert;
	}

	/**
	 * This method returns the SSL certificate password with the ability to reset it inline
	 * @access public
	 * @name \Crux\Storage\S3\Engine::sslCertPasswd()
	 * @package \Crux\Storage\S3\Engine
	 * @param string $strPassword ['']
	 * @return string
	 * @static
	 * @uses \Crux\Core\Is::empty()
	 */
	public static function sslCertPasswd(string $strPassword = '') : string
	{
		// Check for a provided SSL certificate password
		if (!Core\Is::empty($strPassword)) {
			// Reset the SSL certificate password
			self::$mSslCertPasswd = $strPassword;
		}
		// Return the SSL certificate password
		return self::$mSslCertPasswd;
	}

	/**
	 * This method returns the SSL flag with the ability to reset it inline
	 * @access public
	 * @name \Crux\Storage\S3\Engine::sslEnabled()
	 * @package \Crux\Storage\S3\Engine
	 * @param bool $blnFlag [null]
	 * @return bool
	 * @static
	 * @uses \Crux\Core\Is::null()
	 */
	public static function sslEnabled(bool $blnFlag = null) : bool
	{
		// Check for a provided flag
		if (!Core\Is::null($blnFlag)) {
			// Reset the flag
			self::$mSslEnabled = $blnFlag;
		}
		// Return the flag
		return self::$mSslEnabled;
	}

	/**
	 * This method returns the SSL key with the ability to reset it inline
	 * @access public
	 * @name \Crux\Storage\S3\Engine::sslKey()
	 * @package \Crux\Storage\S3\Engine
	 * @param string $strKey ['']
	 * @return string
	 * @static
	 * @uses \Crux\Core\Is::empty()
	 */
	public static function sslKey(string $strKey = '') : string
	{
		// Check for a provided SSL key
		if (!Core\Is::empty($strKey)) {
			// Reset the SSL key
			self::$mSslKey = $strKey;
		}
		// Return the SSL key
		return self::$mSslKey;
	}

	/**
	 * This method returns the SSL key password with the ability to reset it inline
	 * @access public
	 * @name \Crux\Storage\S3\Engine::sslKeyPasswd()
	 * @package \Crux\Storage\S3\Engine
	 * @param string $strPassword ['']
	 * @return string
	 * @static
	 * @uses \Crux\Core\Is::empty()
	 */
	public static function sslKeyPasswd(string $strPassword = '') : string
	{
		// Check for a provided SSL key password
		if (!Core\Is::empty($strPassword)) {
			// Reset the SSL key password
			self::$mSslKeyPasswd = $strPassword;
		}
		// Return the SSL key password
		return self::$mSslKeyPasswd;
	}

	/**
	 * This method returns the SSL peer validation flag with the ability to reset it inline
	 * @access public
	 * @name \Crux\Storage\S3\Engine::sslValidation()
	 * @package \Crux\Storage\S3\Engine
	 * @param bool $blnFlag [null]
	 * @return bool
	 * @static
	 * @uses \Crux\Core\Is::null()
	 */
	public static function sslValidationEnabled(bool $blnFlag = null) : bool
	{
		// Check for a provided flag
		if (!Core\Is::null($blnFlag)) {
			// Reset the flag
			self::$mSslValidationEnabled = $blnFlag;
		}
		// Return the flag
		return self::$mSslValidationEnabled;
	}

	/**
	 * This method returns the SSL version with the ability to reset it inline
	 * @access public
	 * @name \Crux\Storage\S3\Engine::sslVersion()
	 * @package \Crux\Storage\S3\Engine
	 * @param int $intVersion [null]
	 * @return int
	 * @static
	 * @uses \Crux\Core\Is::null()
	 */
	public static function sslVersion(int $intVersion = null) : int
	{
		// Check for a provided SSL version
		if (!Core\Is::null($intVersion)) {
			// Reset the SSL version
			self::$mSslVersion = $intVersion;
		}
		// Return the SSL version
		return self::$mSslVersion;
	}

	/**
	 * This method returns the exceptions flag with the ability to reset it inline
	 * @access public
	 * @name \Crux\Storage\S3\Engine::throwExceptions()
	 * @package \Crux\Storage\S3\Engine
	 * @param bool $blnFlag [null]
	 * @return bool
	 * @static
	 * @uses \Crux\Core\Is::null()
	 */
	public static function throwExceptions(bool $blnFlag = null) : bool
	{
		// Check for a provided flag
		if (!Core\Is::null($blnFlag)) {
			// Reset the flag
			self::$mThrowExceptions = $blnFlag;
		}
		// Return the flag
		return self::$mThrowExceptions;
	}

	/**
	 * This method returns the time offset with the ability to reset it inline
	 * @access public
	 * @name \Crux\Storage\S3\Engine::timeOffset()
	 * @package \Crux\Storage\S3\Engine
	 * @param int $intOffset [null]
	 * @return int
	 * @static
	 * @uses \Crux\Core\Is::null()
	 */
	public static function timeOffset(int $intOffset = null) : int
	{
		// Check for a provided time offset
		if (!Core\Is::null($intOffset)) {
			// Reset the time offset
			self::$mTimeOffset = $intOffset;
		}
		// Return the time offset
		return self::$mTimeOffset;
	}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
} /// End Crux\Storage\S3\Engine Class Definition ////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

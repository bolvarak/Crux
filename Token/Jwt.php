<?php

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Crux\Token Namespace /////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

namespace Crux\Token;

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Imports //////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

use Crux\Collection;
use Crux\Core;
use Crux\Crypto;
use Crux\Serialize;

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Crux\Token\Jwt Class Definition //////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class Jwt
{
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Constant Time Increments /////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This constant defines 1 day in seconds
	 * @name \Crux\Token\Jwt::D1
	 * @package \Crux\Token\Jwt
	 * @var int
	 */
	const D1 = (self::H1 * 24);

	/**
	 * This constant defines 15 minutes in seconds
	 * @name \Crux\Token\Jwt::M15
	 * @package \Crux\Token\Jwt
	 * @var int
	 */
	const M15 = (60 * 15);

	/**
	 * This constant defines 30 minutes in seconds
	 * @name \Crux\Token\Jwt::M30
	 * @package \Crux\Token\Jwt
	 * @var int
	 */
	const M30 = (60 * 30);

	/**
	 * This constant defines 5 minutes in seconds
	 * @name \Crux\Token\Jwt::M5
	 * @package \Crux\Token\Jwt
	 * @var int
	 */
	const M5 = (60 * 5);

	/**
	 * This constant defines 1 hour in seconds
	 * @name \Crux\Token\Jwt::H1
	 * @package \Crux\Token\Jwt
	 * @var int
	 */
	const H1 = (60 * 60);

	/**
	 * This constant defines 6 hours in seconds
	 * @name \Crux\Token\Jwt::H6
	 * @package \Crux\Token\Jwt
	 * @var int
	 */
	const H6 = (self::H1 * 6);

	/**
	 * This constant defines 12 hours in seconds
	 * @name \Crux\Token\Jwt::H12
	 * @package \Crux\Token\Jwt
	 * @var int
	 */
	const H12 = (self::H1 * 12);

	/**
	 * This constant defines 1 week in seconds
	 * @name \Crux\Token\Jwt::W1
	 * @package \Crux\Token\Jwt
	 * @var int
	 */
	const W1 = (self::D1 * 7);

	/**
	 * This constant defines 1 year in seconds
	 * @name \Crux\Token\Jwt::Y1
	 * @package \Crux\Token\Jwt
	 * @var int
	 */
	const Y1 = (self::W1 * 52);

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Constant Hashing Algorithms //////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This constant defines an HMAC SHA-256 hashing algorithm
	 * @name \Crux\Token\Jwt::HS256
	 * @package \Crux\Token\Jwt
	 * @var string
	 */
	const HS256 = ['HS256', 'hash_hmac', 'SHA256'];

	/**
	 * This constant defines an HMAC SHA-256 hashing algorithm
	 * @name \Crux\Token\Jwt::HS384
	 * @package \Crux\Token\Jwt
	 * @var string
	 */
	const HS384 = ['HS384', 'hash_hmac', 'SHA384'];

	/**
	 * This constant defines an HMAC SHA-512 hashing algorithm
	 * @name \Crux\Token\Jwt::HS512
	 * @package \Crux\Token\Jwt
	 * @var string
	 */
	const HS512 = ['HS512', 'hash_hmac', 'SHA512'];

	/**
	 * This constant defines an OpenSSL SHA-256 hashing algorithm
	 * @name \Crux\Token\Jwt::RS256
	 * @package \Crux\Token\Jwt
	 * @var string
	 */
	const RS256 = ['RS256', 'openssl_sign', 'SHA256'];

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Properties ///////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This property contains the headers for the JWT
	 * @access private
	 * @name \Crux\Token\Jwt::$mHeaders
	 * @package \Crux\Token\Jwt
	 * @var array
	 */
	private $mHeaders = [];

	/**
	 * This property contains the payload for the JWT
	 * @access private
	 * @name \Crux\Token\Jwt::$mPayload
	 * @package \Crux\Token\Jwt
	 * @var array
	 */
	private $mPayload = [];

	/**
	 * This property contains the algorithm to use when signing and verifying the token
	 * @access protected
	 * @name \Crux\Token\Jwt::$mAlgorithm
	 * @package \Crux\Token\Jwt
	 * @var string
	 */
	protected $mAlgorithm = self::HS256;

	/**
	 * This property contains the valid signing algorithms
	 * @access protected
	 * @name \Crux\Token\Jwt::$mAlgorithms
	 * @package \Crux\Token\Jwt
	 * @var array<int, array<int, string>>
	 */
	protected $mAlgorithms = [
		self::HS256, self::HS384, self::HS512, self::RS256
	];

	/**
	 * This property contains the "aud" or audience for the token
	 * @access protected
	 * @name \Crux\Token\Jwt::$mAudience
	 * @package \Crux\Token\Jwt
	 * @var string
	 */
	protected $mAudience = '';

	/**
	 * This property contains the timestamp at which the token expires
	 * @access protected
	 * @name \Crux\Token\Jwt::$mExpiresAt
	 * @package \Crux\Token\Jwt
	 * @var int
	 */
	protected $mExpiresAt = 0;

	/**
	 * This property contains the timestamp at which the token was issued
	 * @access protected
	 * @name \Crux\Token\Jwt::$mIssuedAt
	 * @package \Crux\Token\Jwt
	 * @var int
	 */
	protected $mIssuedAt = 0;

	/**
	 * This property contains the issuer of the token
	 * @access protected
	 * @name \Crux\Token\Jwt::$mIssuer
	 * @package \Crux\Token
	 * @var string
	 */
	protected $mIssuer = '';

	/**
	 * This property contains the number of seconds to buffer the expiration of the token
	 * @access protected
	 * @name \Crux\Token\Jwt::$mLeeWay
	 * @package \Crux\Token\Jwt
	 * @var int
	 */
	protected $mLeeWay = 0;

	/**
	 * This property contains the timestamp before which the token is invalid
	 * @access protected
	 * @name \Crux\Token\Jwt::$mNotBefore
	 * @package \Crux\Token\Jwt
	 * @var int
	 */
	protected $mNotBefore = 0;

	/**
	 * This property contains the cipher key for hashing the token
	 * @access protected
	 * @name \Crux\Token\Jwt::$mSecret
	 * @package \Crux\Token\Jwt
	 * @var string
	 */
	protected $mSecret = '';

	/**
	 * This property contains the sub header for the token
	 * @access protected
	 * @name \Crux\Token\Jwt::$mSubject
	 * @package \Crux\Token\Jwt
	 * @var string
	 */
	protected $mSubject = '';

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Static Constructors //////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method instantiates a new JWT token object into a fluid interface
	 * @access public
	 * @name \Crux\Token\Jwt::Factory()
	 * @package \Crux\Token\Jwt
	 * @return \Crux\Token\Jwt
	 * @static
	 * @uses \Crux\Token\Jwt::__construct()
	 */
	public static function Factory() : Jwt
	{
		// Return the new instance
		return new self();
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Constructor //////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method instaniates a new JWT token object
	 * @access public
	 * @name \Crux\Token\Jwt::__construct()
	 * @package \Crux\Token\Jwt
	 * @uses \Crux\Core\Config::get()
	 * @uses \Crux\Crypto\OpenSSL::getPublicKey()
	 * @uses \Crux\Core\Is::empty()
	 * @uses \Crux\Token\Jwt::algorithmFromName()
	 * @uses \Crux\Token\Jwt::audience()
	 * @uses \Crux\Token\Jwt::expiresIn()
	 * @uses \Crux\Token\Jwt::issuer()
	 * @uses \Crux\Token\Jwt::leeWay()
	 * @uses \Crux\Token\Jwt::notBefore()
	 * @uses \Crux\Token\Jwt::secret()
	 */
	public function __construct()
	{
		// Set our defaults
		$this->algorithmByName(Core\Config::get('jwt.algorithm'));
		$this->audience(Core\Config::get('jwt.audience'));
		$this->expiresIn(Core\Config::get('jwt.expiresIn'));
		$this->issuer(Core\Config::get('jwt.issuer'));
		$this->leeWay(Core\Config::get('jwt.leeWay'));
		$this->notBefore(Core\Config::get('jwt.notBefore'));
		// Check the algorithm
		$this->secret(Crypto\OpenSSL::getSecret());
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Protected Methods ////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method deconstructs a token into its three parts (Header, Payload, Signature)
	 * @access protected
	 * @name \Crux\Token\Jwt::deconstructToken()
	 * @package \Crux\Token\Jwt
	 * @param string $strToken
	 * @return array<int, array|string>
	 * @throws \Crux\Core\Exception\Token\Jwt
	 * @uses \Crux\Core\Exception\Token\Jwt::__construct()
	 * @uses \Crux\Core\Exception\Serialize\Json::getMessage()
	 * @uses \Crux\Serialize\Url::safeDecode()
	 * @uses \Crux\Serialize\Json::__construct()
	 * @uses \Crux\Serialize\Json::deserialize()
	 * @uses explode()
	 * @uses count()
	 * @uses list()
	 * @uses sprintf()
	 */
	protected function deconstructToken(string $strToken) : array
	{
		// Explode the token
		$arrParts = explode('.', $strToken);
		// Make sure we have the proper number of parts
		if (count($arrParts) !== 3) {
			// We're done, throw the exception
			throw new Core\Exception\Token\Jwt('Invalid JWT:  Wrong number of segments');
		}
		// Instantiate our serializer
		$jsonSerializer = new Serialize\Json();
		// Grab the individual parts
		list($strHeader, $strBody, $strSignature) = $arrParts;
		// Decode the header
		$strDecodedHeader = Serialize\Url::safeDecode($strHeader);
		// Decode the body
		$strDecodedBody = Serialize\Url::safeDecode($strBody);
		// Try to decode the parts
		try {
			// Decode the header
			$arrHeader = $jsonSerializer->deserialize($strDecodedHeader)->toArray();
			// Decode the payload
			$arrPayload = $jsonSerializer->deserialize($strDecodedBody)->toArray();
			// Decode the signature
			$strSignature = Serialize\Url::safeDecode($strSignature);
			// We're done, send the parts
			return [$arrHeader, $arrPayload, $strSignature, $strHeader, $strBody];
		} catch (Core\Exception\Serialize\Json $jsonException) {
			// Throw the JWT exception
			throw new Core\Exception\Token\Jwt(sprintf('Unable to deserialize token:  ', $jsonException->getMessage()));
		}
	}

	/**
	 * This method prefers mb_strlen over strlen if it is available
	 * @access protected
	 * @name \Crux\Token\Jwt::stringLength()
	 * @package \Crux\Token\Jwt
	 * @param string $strSource
	 * @return int
	 * @uses function_exists()
	 * @uses mb_strlen()
	 * @uses strlen()
	 */
	protected function stringLength(string $strSource) : int
	{
		// Check for mb_string
		if (function_exists('mb_strlen')) {
			// Return the mb_strlen value
			return mb_strlen($strSource, '8bit');
		} else {
			// Return the strlen value
			return strlen($strSource);
		}
	}

	/**
	 * This method generates a new header
	 * @access protected
	 * @name \Crux\Token\Jwt::generateHeader()
	 * @package \Crux\Token\Jwt
	 * @param string $strAlgorithm
	 * @return array<string, mixed>
	 */
	protected function generateHeader(string $strAlgorithm) : array
	{
		// Define our header
		$arrHeader = [
			'typ' => 'JWT',
			'alg' => $strAlgorithm
		];
		// We're done, return the header
		return $arrHeader;
	}

	/**
	 * This method generates the payload with reserved claims
	 * @access protected
	 * @name \Crux\Token\Jwt::generatePayload()
	 * @package \Crux\Token\Jwt
	 * @param mixed $mixPayload
	 * @param mixed $mixKeyId
	 * @return array<string, mixed>
	 * @uses \Crux\Core\Is::associativeArray()
	 * @uses \Crux\Core\Is::object()
	 * @uses \Crux\Core\Is::map()
	 * @uses \Crux\Core\Is::sequentialArray()
	 * @uses \Crux\Core\Is::vector()
	 * @uses \Crux\Core\Util::mapFactory()
	 * @uses \Crux\Collection\Map::set()
	 * @uses \Crux\Collection\Map::toArray()
	 * @uses \Crux\Core\Is::empty()
	 * @uses \Crux\Core\Is::null()
	 * @uses \Crux\Token\Jwt::audience()
	 * @uses \Crux\Token\Jwt::expiresAt()
	 * @uses \Crux\Token\Jwt::issuedAt()
	 * @uses \Crux\Token\Jwt::issuer()
	 * @uses \Crux\Token\Jwt::notBefore()
	 * @uses \Crux\Token\Jwt::subject()
	 * @uses time()
	 */
	protected function generatePayload($mixPayload, $mixKeyId) : array
	{
		// Check the payload
		if (Core\Is::associativeArray($mixPayload) || Core\Is::object($mixPayload) && !Core\Is::map($mixPayload)) {
			// Reset the payload
			$mixPayload = Core\Util::mapFactory($mixPayload);
		} elseif(Core\Is::sequentialArray($mixPayload) || Core\Is::vector($mixPayload)) {
			// Reset the payload
			$mixPayload = Core\Util::mapFactory()->set('claims', $mixPayload);
		} else {
			// Check for a map
			if (!Core\Is::map($mixPayload)) {
				// Reset the payload
				$mixPayload = Core\Util::mapFactory()->set('claims', $mixPayload);
			}
		}
		// Set the issuedAt claim
		$mixPayload->set('iat', (($this->issuedAt() === 0) ? time() : $this->issuedAt()));
		// Check for an expiration
		if (!Core\Is::empty($this->mExpiresAt)) {
			// Set the expiration into the header
			$mixPayload->set('exp', $this->expiresAt());
		}
		// Check for a key ID
		if (!Core\Is::null($mixKeyId)) {
			// Set the key ID into the header
			$mixPayload->set('kid', $mixKeyId);
		}
		// Check for an audience
		if (!Core\Is::empty($this->mAudience)) {
			// Set the audience into the header
			$mixPayload->set('aud', $this->audience());
		}
		// Check for an issuer
		if (!Core\Is::empty($this->mIssuer)) {
			// Set the issuer into the header
			$mixPayload->set('iss', $this->issuer());
		}
		// Check for an nbf header
		if (!Core\Is::empty($this->mNotBefore)) {
			// Set the nbf header
			$mixPayload->set('nbf', $this->notBefore());
		}
		// Check for a subject
		if (!Core\Is::empty($this->mSubject)) {
			// Set the sub header
			$mixPayload->set('sub', $this->subject());
		}
		// We're done, return the payload
		return $mixPayload->toArray();
	}

	/**
	 * This method validates an incoming JWT header
	 * @access protected
	 * @name \Crux\Token\Jwt::validateHeader()
	 * @package \Crux\Token\Jwt
	 * @param string $strAlgorithm
	 * @param array $arrHeader
	 * @return void
	 * @throws \Crux\Core\Exception\Token\Jwt
	 * @uses \Crux\Core\Exception\Token\Jwt::__construct()
	 * @uses array_key_exists()
	 * @uses strtolower()
	 *
	 */
	protected function validateHeader(string $strAlgorithm, array $arrHeader)
	{
		// Check the type
		if (!array_key_exists('typ', $arrHeader) || (array_key_exists('typ', $arrHeader) && (strtolower($arrHeader['typ']) !== 'jwt'))) {
			// We're done, throw the exception
			throw new Core\Exception\Token\Jwt('Invalid token type', 401);
		}
		// Check the algorithm
		if (!array_key_exists('alg', $arrHeader) || (array_key_exists('alg', $arrHeader) && (strtolower($arrHeader['alg']) !== strtolower($strAlgorithm)))) {
			// We're done, throw the exception
			throw new Core\Exception\Token\Jwt('Token algorithm mismatch', 401);
		}
	}

	/**
	 * This method validates the requirements for signing and verifying JWTs
	 * @access protected
	 * @name \Crux\Token\Jwt::validateRequirements()
	 * @param string $strKey
	 * @param string $strAlgorithm
	 * @throws \Crux\Core\Exception\Token\Jwt
	 * @return void
	 * @uses \Crux\Core\Is::empty()
	 * @uses \Crux\Core\Exception\Token\Jwt::__construct()
	 * @uses in_array()
	 * @uses sprintf()
	 */
	protected function validateRequirements(string $strKey, string $strAlgorithm)
	{
		// Check the name
		if (!in_array($strAlgorithm, $this->getAlgorithms())) {
			// Throw the exception
			throw new Core\Exception\Token\Jwt(sprintf('%s is not a valid signing algorithm', $strAlgorithm));
		}
		// Make sure we have a signing key
		if (Core\Is::empty(trim($strKey))) {
			// Throw the exception
			throw new Core\Exception\Token\Jwt('Secret key cannot be empty');
		}
	}

	/**
	 * This method validates reserved claims on the JWT
	 * @access protected
	 * @name \Crux\Token\Jwt::validateReservedClaims()
	 * @package \Crux\Token\Jwt
	 * @param array<string, mixed> $arrPayload
	 * @param mixed $mixKeyId
	 * @param bool $blnVerifySubject
	 * @return void
	 * @throws \Crux\Core\Exception\Token\Jwt
	 * @uses \Crux\Core\Exception\Token\Jwt::__construct()
	 * @uses \Crux\Token\Jwt::leeWay()
	 * @uses \Crux\Token\Jwt::audience()
	 * @uses \Crux\Token\Jwt::issuedAt()
	 * @uses \Crux\Token\Jwt::subject()
	 * @uses array_key_exists()
	 * @uses time()
	 * @uses sprintf()
	 * @uses date()
	 */
	protected function validateReservedClaims($arrPayload, $mixKeyId, $blnVerifySubject)
	{
		// Check the issue timestamp
		if (!array_key_exists('iat', $arrPayload) || (array_key_exists('iat', $arrPayload) && ($arrPayload['iat'] > (time() + $this->leeWay())))) {
			// We're done, throw the exception
			throw new Core\Exception\Token\Jwt('Token not yet valid', 401);
		}
		// Check the key ID
		if (array_key_exists('kid', $arrPayload) && ($arrPayload['kid'] !== $mixKeyId)) {
			// We're done, throw the exception
			throw new Core\Exception\Token\Jwt('Invalid token key ID', 401);
		}
		// Check the expiration
		if (array_key_exists('exp', $arrPayload) && ($arrPayload['exp'] <= (time() - $this->leeWay()))) {
			// We're done, throw the exception
			throw new Core\Exception\Token\Jwt('Token has expired', 401);
		}
		// Check for an audience
		if (array_key_exists('aud', $arrPayload) && ($arrPayload['aud'] !== $this->audience())) {
			// We're done, throw the exception
			throw new Core\Exception\Token\Jwt('Invalid token audience', 401);
		}
		// Check the issuer
		if (array_key_exists('iss', $arrPayload) && ($arrPayload['iss'] !== $this->issuer())) {
			// We're done, throw the exception
			throw new Core\Exception\Token\Jwt('Invalid token issuer', 401);
		}
		// Check the not-before
		if (array_key_exists('nbf', $arrPayload) && ($arrPayload['nbf'] > (time() + $this->leeWay()))) {
			// We're done, throw the exception
			throw new Core\Exception\Token\Jwt(sprintf('Token not valid until %s', date(\DateTime::ISO8601, $arrPayload['nbf'])), 401);
		}
		// Check the subject verification flag and the subject
		if ($blnVerifySubject && array_key_exists('sub', $arrPayload) && ($arrPayload['sub'] !== $this->subject())) {
			// We're done, throw the exception
			throw new Core\Exception\Token\Jwt('Invalid toke subject', 401);
		}
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Signing & Verification Methods ///////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method generates and signs a JWT
	 * @access public
	 * @name \Crux\Token\Jwt::sign()
	 * @package \Crux\Token\Jwt
	 * @param mixed $mixPayload
	 * @param mixed $mixKeyId [null]
	 * @return string
	 * @throws \Crux\Core\Exception\Token\Jwt
	 * @throws \Crux\Core\Exception\Token\OpenSSL
	 * @uses \Crux\Core\Exception\Token\Jwt::__construct()
	 * @uses \Crux\Core\Exception\Token\OpenSSL::__construct()
	 * @uses \Crux\Core\Is::empty()
	 * @uses \Crux\Core\Is::null()
	 * @uses \Crux\Serialize\Json::__construct()
	 * @uses \Crux\Serialize\Json::serialize()
	 * @uses \Crux\Serialize\Url::safeEncode()
	 * @uses \Crux\Token\Jwt::algorithm()
	 * @uses \Crux\Token\Jwt::generateHeader()
	 * @uses \Crux\Token\Jwt::getAlgorithms()
	 * @uses \Crux\Token\Jwt::validateRequirements()
	 * @uses \Crux\Core\Exception\Serialize\Json::getMessage()
	 * @uses \Crux\Crypto\OpenSSL::sign()
	 * @uses list()
	 * @uses array_push()
	 * @uses sprintf()
	 * @uses implode()
	 * @uses strtolower()
	 * @uses trim()
	 * @uses hash_hmac()
	 * @uses openssl_sign()
	 * @uses array_push()
	 */
	public function sign($mixPayload, $mixKeyId = null) : string
	{
		// Localize the algorithm
		list ($strName, $strFunction, $strHash) = $this->algorithm();
		// Validate the requirements
		$this->validateRequirements($this->mSecret, $strName);
		// Generate the header
		$arrHeader = $this->generateHeader($strName);
		// Generate the payload
		$mixPayload = $this->generatePayload($mixPayload, $mixKeyId);
		// Set the headers into the instance
		$this->headers($arrHeader);
		// Set the payload into the instance
		$this->payload($mixPayload);
		// Instantiate our serializer
		$jsonSerializer = new Serialize\Json();
		// Define our parts container
		$arrParts = [];
		// Try to serialize the header
		try {
			// Serialize the header
			array_push($arrParts, Serialize\Url::safeEncode($jsonSerializer->serialize($arrHeader)));
		} catch (Core\Exception\Serialize\Json $jsonException) {
			// We're done, throw the JWT exception
			throw new Core\Exception\Token\Jwt(sprintf('Unable to serialize token header:  %s', $jsonException->getMessage()));
		}
		// Try to serialize the payload
		try {
			// Serialize the payload
			array_push($arrParts, Serialize\Url::safeEncode($jsonSerializer->serialize($mixPayload)));
		} catch (Core\Exception\Serialize\Json $jsonException) {
			// We're done, throw the JWT exception
			throw new Core\Exception\Token\Jwt(sprintf('Unable to serialize token payload:  %s', $jsonException->getMessage()));
		}
		// Define our signature input
		$strInput = implode('.', $arrParts);
		// Check the hashing function
		if (strtolower($strFunction) === 'hash_hmac') {
			// Generate the signature
			$strSignature = hash_hmac($strHash, $strInput, trim($this->mSecret), true);
		} else {
			// Try to sign the payload with OpenSSL
			try {
				// Grab the signature
				$strSignature = Crypto\OpenSSL::sign($strInput, $strHash);
			} catch (Core\Exception\Crypto\OpenSSL $sslError) {
				// Throw the relative exception
				throw new Core\Exception\Token\OpenSSL('Unable to sign the JWT with OpenSSL');
			}
		}
		// Add the signature to the parts
		array_push($arrParts, Serialize\Url::safeEncode($strSignature));
		// We're done, return the token
		return implode('.', $arrParts);
	}

	/**
	 * This method verifies a token
	 * @access public
	 * @name \Crux\Token\Jwt::verify()
	 * @package \Crux\Token\Jwt
	 * @param string $strJwt
	 * @param mixed $mixKeyId [null]
	 * @param bool $blnVerifySubject [false]
	 * @return array<string, array<string, mixed>>
	 * @throws \Crux\Core\Exception\Token\Jwt
	 * @uses \Crux\Token\Jwt::algorithm()
	 * @uses \Crux\Token\Jwt::validateRequirements()
	 * @uses \Crux\Token\Jwt::deconstructToken()
	 * @uses \Crux\Token\Jwt::validateHeader()
	 * @uses \Crux\Token\Jwt::stringLength()
	 * @uses \Crux\Core\Exception\Token\Jwt::__construct()
	 * @uses \Crux\Crypto\OpenSSL::verify()
	 * @uses list()
	 * @uses strtolower()
	 * @uses openssl_verify()
	 * @uses hash_hmac()
	 * @uses sprintf()
	 * @uses hash_equals()
	 * @uses min()
	 * @uses ord()
	 */
	public function verify($strJwt, $mixKeyId = null, bool $blnVerifySubject = false) : array
	{
		// Localize the algorithm
		list ($strName, $strFunction, $strHash) = $this->algorithm();
		// Make sure we have the basics
		$this->validateRequirements($this->mSecret, $strName);
		// Deconstruct the token
		list($arrHeader, $arrPayload, $strSignature, $strHeader, $strBody) = $this->deconstructToken($strJwt);
		// Validate the header
		$this->validateHeader($strName, $arrHeader);
		// Validate the reserved claims
		$this->validateReservedClaims($arrPayload, $mixKeyId, $blnVerifySubject);
		// Check the function for the algorithm
		if (strtolower($strFunction) === 'openssl_sign') {
			// Verify the signature
			if (!Crypto\OpenSSL::verify(sprintf('%s.%s', $strHeader, $strBody), $strSignature, $strHash)) {
				// We're done, throw the exception
				throw new Core\Exception\Token\Jwt('Invalid token signature', 401);
			}
			// Set the headers into the instance
			$this->headers($arrHeader);
			// Set the payload into the instance
			$this->payload($arrPayload);
			// We're done, return the data
			return ['header' => $arrHeader, 'payload' => $arrPayload];
		} else {
			// Generate a new hash
			$strNewHash = hash_hmac($strHash, sprintf('%s.%s', $strHeader, $strBody), $this->secret(), true);
			// Check for the hash_equals function
			if (function_exists('hash_equals')) {
				// Check the signature
				if  (!hash_equals($strSignature, $strNewHash)) {
					// We're done, throw the exception
					throw new Core\Exception\Token\Jwt('Invalid token signature', 401);
				} else {
					// Set the headers into the instance
					$this->headers($arrHeader);
					// Set the payload into the instance
					$this->payload($arrPayload);
					// Return the data
					return ['header' => $arrHeader, 'payload' => $arrPayload];
				}
			} else {
				// Grab the minimum value
				$intLength = min($this->stringLength($strSignature), $this->stringLength($strNewHash));
				// Set our status counter
				$intStatus = 0;
				// Iterate to the length
				for ($intCharacter = 0; $intCharacter < $intLength; ++$intCharacter) {
					// Verify the character
					$intStatus |= (ord($strSignature[$intCharacter] ^ ord($strNewHash[$intCharacter])));
				}
				// Pipe in the full comparison
				$intStatus |= ($this->stringLength($strSignature) ^ $this->stringLength($strNewHash));
				// Check the status
				if ($intStatus !== 0) {
					// We're done, throw the exception
					throw new Core\Exception\Token\Jwt('Invalid token signature', 401);
				} else {
					// Set the headers into the instance
					$this->headers($arrHeader);
					// Set the payload into the instance
					$this->payload($arrPayload);
					// Return the data
					return ['header' => $arrHeader, 'payload' => $arrPayload];
				}
			}
		}
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Public Methods ///////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method returns the signing algorithm from the instance with the ability to reset it inline
	 * @access public
	 * @name \Crux\Token\Jwt::algorithm()
	 * @package \Crux\Token\Jwt
	 * @param array<int, string> $strAlgorithm [array()]
	 * @return array<int, string>
	 * @uses \Crux\Core\Is::empty()
	 */
	public function algorithm(array $strAlgorithm = []) : array
	{
		// Check for a provided algorithm
		if (!Core\Is::empty($strAlgorithm)) {
			// Reset the algorithm into the instance
			$this->mAlgorithm = $strAlgorithm;
		}
		// Return the algorithm from the instance
		return $this->mAlgorithm;
	}

	/**
	 * This method sets the algorithm into the instance by its name
	 * @access public
	 * @name \Crux\Token\Jwt::algorithmByName()
	 * @package \Crux\Token\Jwt
	 * @param string $strName
	 * @return array<int, array<string>>
	 * @throws \Crux\Core\Exception\Token\Jwt
	 * @uses \Crux\Token\Jwt::algorithm()
	 * @uses \Crux\Core\Exception\Token\Jwt::__construct()
	 * @uses strtolower()
	 */
	public function algorithmByName(string $strName) : array
	{
		// Iterate over the algorithms
		foreach ($this->mAlgorithms as $intIndex => $arrAlgorithm) {
			// Check the algorithm name
			if (strtolower($strName) === strtolower($arrAlgorithm[0])) {
				// Set the algorithm
				return $this->algorithm($arrAlgorithm);
			}
		}
		// Throw the exception
		throw new Core\Exception\Token\Jwt(sprintf('%s is not a valid algorithm', $strName));
	}

	/**
	 * This method returns the aud header from the instance with the ability to set it inline
	 * @access public
	 * @name \Crux\Token\Jwt::audience()
	 * @package \Crux\Token\Jwt
	 * @param string $aud ['']
	 * @return string
	 * @uses \Crux\Core\Is::empty()
	 */
	public function audience(string $aud = '') : string
	{
		// Check for a provided audience
		if (!Core\Is::empty($aud)) {
			// Reset the audience into the instance
			$this->mAudience = $aud;
		}
		// Return the audience from the instance
		return $this->mAudience;
	}

	/**
	 * This method returns the timestamp in which the token expires with the ability to set it inline
	 * @access public
	 * @name \Crux\Token\Jwt::expiresAt()
	 * @package \Crux\Token\Jwt
	 * @param int $intTimeStamp [-1]
	 * @return int
	 */
	public function expiresAt(int $intTimeStamp = -1) : int
	{
		// Check for a provided timestamp
		if ($intTimeStamp > -1) {
			// Reset the expiration timestamp into the instance
			$this->mExpiresAt = ($intTimeStamp + $this->mLeeWay);
		}
		// Return the expiration timestamp from the instance
		return ($this->mExpiresAt - $this->mLeeWay);
	}

	/**
	 * This method returns the number of seconds until the token expires with the ability to set the expiration timestamp inline
	 * @access public
	 * @name \Crux\Token\Jwt::expiresIn()
	 * @package \Crux\Token\Jwt
	 * @param int $intSeconds [-1]
	 * @return int
	 * @uses time()
	 */
	public function expiresIn(int $intSeconds = -1) : int
	{
		// check for a provided number of seconds
		if ($intSeconds > -1) {
			// Reset the expires at timestamp
			$this->mExpiresAt = ((time() + $intSeconds) + $this->mLeeWay);
		}
		// Return the number of seconds until expiration
		return (($this->mExpiresAt - time()) - $this->mLeeWay);
	}

	/**
	 * This method returns the headers from the instance with the ability to reset them inline
	 * @access public
	 * @name \Crux\Token\Jwt::headers()
	 * @package \Crux\Token\Jwt
	 * @param array<string, mixed> $arrHeaders []
	 * @return \Crux\Collection\Map
	 * @uses \Crux\Core\Is::empty()
	 * @uses \Crux\Collection\Map::fromArray()
	 */
	public function headers(array $arrHeaders = []) : Collection\Map
	{
		// Check for provided headers
		if (!Core\Is::empty($arrHeaders)) {
			// Set the headers into the instance
			$this->mHeaders = $arrHeaders;
		}
		// We're done, return the collection of headers from the instance
		return Collection\Map::fromArray($this->mHeaders);
	}

	/**
	 * This method returns the iat header from the instance with the ability to set it inline
	 * @access public
	 * @name \Crux\Token\Jwt::issuedAt()
	 * @package \Crux\Token\Jwt
	 * @param int $iat [-1]
	 * @return int
	 */
	public function issuedAt(int $iat = -1) : int
	{
		// Check for a provided issue timestamp
		if ($iat > -1) {
			// Reset the issuance timestamp into the instance
			$this->mIssuedAt = $iat;
		}
		// Return the issued timestamp
		return $this->mIssuedAt;
	}

	/**
	 * This method returns the issuer from the instance with the ability to set it inline
	 * @access public
	 * @name \Crux\Token\Jwt::issuer()
	 * @package \Crux\Token\Jwt
	 * @param string $iss ['']
	 * @return string
	 * @uses \Crux\Core\Is::empty()
	 */
	public function issuer(string $iss = '') : string
	{
		// Check for a provided issuer
		if (!Core\Is::empty($iss)) {
			// Reset the issuer into the instance
			$this->mIssuer = $iss;
		}
		// Return the issuer from the instance
		return $this->mIssuer;
	}

	/**
	 * This method returns the buffer time from the instance with the ability to set it inline
	 * @access public
	 * @name \Crux\Token\Jwt::leeWay()
	 * @package \Crux\Token\Jwt
	 * @param int $intSeconds [-1]
	 * @return int
	 */
	public function leeWay(int $intSeconds = -1) : int
	{
		// Check for a provided number of leeway seconds
		if ($intSeconds > -1) {
			// Set the leeway into the instance
			$this->mLeeWay = $intSeconds;
		}
		// Return the leeway seconds from the instance
		return $this->mLeeWay;
	}

	/**
	 * This method returns the nbf timestamp from the instance with the ability to set it inline
	 * @access public
	 * @name \Crux\Token\Jwt::notBefore()
	 * @package \Crux\Token\Jwt
	 * @param int $nbf [-1]
	 * @return int
	 */
	public function notBefore(int $nbf = -1) : int
	{
		// Check for a provided not before timestamp
		if ($nbf > -1) {
			// Set the not before timestamp into the instance
			$this->mNotBefore = $nbf;
		}
		// Return the not before timestamp from the instance
		return $this->mNotBefore;
	}

	/**
	 * This method returns the payload collection from the instance with the ability to reset it inline
	 * @access public
	 * @name \Crux\Token\Jwt::payload()
	 * @package \Crux\Token\Jwt
	 * @param array<string, mixed> $arrPayload [null]
	 * @return \Crux\Collection\Map
	 * @uses \Crux\Core\Is::empty()
	 * @uses \Crux\Collection\Map::fromArray()
	 */
	public function payload(array $arrPayload = []) : Collection\Map
	{
		// Check for a provided payload
		if (!Core\Is::empty($arrPayload)) {
			// Reset the payload into the instance
			$this->mPayload = $arrPayload;
		}
		// We're done, return the payload collection from the instance
		return Collection\Map::fromArray($this->mPayload);
	}

	/**
	 * This method returns the secret from the instance with the ability to set it inline
	 * @access public
	 * @name \Crux\Token\Jwt::secret()
	 * @package \Crux\Token\Jwt
	 * @param string $strSecret ['']
	 * @return string
	 * @uses \Crux\Core\Is::empty()
	 * @uses substr()
	 * @uses strtolower()
	 * @uses substr_replace()
	 * @uses file_get_contents()
	 */
	public function secret(string $strSecret = '') : string
	{
		// Check for a provided secret
		if (!Core\Is::empty($strSecret)) {
			// Set the secret into the instance
			$this->mSecret = ((strtolower(substr($strSecret, 0, 7)) === 'file://') ? file_get_contents(substr_replace($strSecret, '', 0, 7)) : $strSecret);
		}
		// Return the secret from the instance
		return $this->mSecret;
	}

	/**
	 * This method returns the sub header from the instance with the ability to set it inline
	 * @access public
	 * @name \Crux\Token\Jwt::subject()
	 * @package \Crux\Token\Jwt
	 * @param string $sub ['']
	 * @return string
	 * @uses \Crux\Core\Is::empty()
	 */
	public function subject(string $sub = '') : string
	{
		// Check for a provided subject
		if (!Core\Is::empty($sub)) {
			// Set the subject into the instance
			$this->mSubject = $sub;
		}
		// Return the subject from the instance
		return $this->mSubject;
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Getters //////////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method returns the list of valid signing algorithm constant names
	 * @access public
	 * @name \Crux\Token\Jwt::getAlgorithms()
	 * @package \Crux\Token\Jwt
	 * @return array<int, string>
	 * @uses array_push()
	 */
	public function getAlgorithms() : array
	{
		// Create our container
		$arrAlgorithms = [];
		// Iterate over the instance algorithms
		foreach ($this->mAlgorithms as $arrAlgorithm) {
			// Add the name to the array
			array_push($arrAlgorithms, $arrAlgorithm[0]);
		}
		// Return the algorithms
		return $arrAlgorithms;
	}


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
} /// End Crux\Token\Jwt Class Definition ////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

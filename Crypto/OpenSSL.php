<?php

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Crux\Crypto Namespace ////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

namespace Crux\Crypto;

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Imports //////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

use Crux\Core;

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Crux\Crypto\OpenSSL Class Definition /////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class OpenSSL
{
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Constants ////////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This constant defines a 128-bit AES CBC algorithm
	 * @name \Crux\Crypto\OpenSSL::CBC_128
	 * @package \Crux\Crypto\OpenSSL
	 * @var string
	 */
	const CBC_128 = 'aes-128-cbc';

	/**
	 * This constant defines a 256-bit AES CBC algorithm
	 * @name \Crux\Crypto\OpenSSL::CBC_256
	 * @package \Crux\Crypto\OpenSSL
	 * @var string
	 */
	const CBC_256 = 'aes-256-cbc';

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Arbitrary Encryption Methods /////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method decrypts a passphrase encrypted hash
	 * @access public
	 * @name \Crux\Crypto\OpenSSL::decryptWithPassphrase()
	 * @package \Crux\Crypto\OpenSSL
	 * @param string $strHash
	 * @param string $strPassphrase ['']
	 * @return mixed
	 * @static
	 * @throws \Crux\Core\Exception\Crypto\OpenSSL
	 * @uses \Crux\Core\Is::empty()
	 * @uses \Crux\Core\Is::serialized()
	 * @uses \Crux\Core\Config::get()
	 * @uses \Crux\Core\Exception\Crypto\OpenSSL::__construct()
	 * @uses \Crux\Crypto\OpenSSL::getSecret()
	 * @uses preg_match()
	 * @uses base64_decode()
	 * @uses substr()
	 * @uses substr_replace()
	 * @uses intval()
	 * @uses openssl_decrypt()
	 * @uses strtolower()
	 * @uses unserialize()
	 */
	public static function decryptWithPassphrase(string $strHash, string $strPassphrase = '')
	{
		// Grab the parts
		if (!preg_match('/^{(.*?)}\$([0-9]+)\$([0-9]+)\$(.*?)$/i', $strHash, $arrMatches)) {
			// Throw the exception
			throw new Core\Exception\Crypto\OpenSSL('Hash is not valid');
		}
		// Check for a passphrase
		if (Core\Is::empty($strPassphrase)) {
			// Reset the passphrase
			$strPassphrase = self::getSecret();
		}
		// Define the plain-text container
		$strPlainText = base64_decode($arrMatches[4]);
		// Iterate to the number of passes
		for ($intPass = 0; $intPass < intval($arrMatches[2]); ++$intPass) {
			// Grab the initialization vector
			$strInitializationVector = substr($strPlainText, 0, $arrMatches[3]);
			// Reset the plain text
			$strPlainText = substr_replace($strPlainText, '', 0, $arrMatches[3]);
			// Decrypt the hash
			$strPlainText = openssl_decrypt($strPlainText, strtolower($arrMatches[1]), $strPassphrase, OPENSSL_RAW_DATA, $strInitializationVector);
			// Check for success
			if ($strPlainText === false) {
				// Throw the exception
				throw new Core\Exception\Crypto\OpenSSL(sprintf('Unable to decrypt pass %d/%d using passphrase', ($intPass + 1), $arrMatches[2]));
			}
		}
		return (Core\Is::serialized($strPlainText) ? unserialize($strPlainText) : $strPlainText);
	}

	/**
	 * This method encrypts data into a hash using a passphrase
	 * @access public
	 * @name \Crux\Crypto\OpenSSL::encryptWithPassphrase()
	 * @package \Crux\Crypto\OpenSSL
	 * @param mixed $mixData
	 * @param string $strPassphrase ['']
	 * @param int $intPasses [-1]
	 * @return string
	 * @static
	 * @throws \Crux\Core\Exception\Crypto\OpenSSL
	 * @uses \Crux\Core\Is::empty()
	 * @uses \Crux\Core\Is::scalar()
	 * @uses \Crux\Core\Config::get()
	 * @uses \Crux\Crypto\OpenSSL::getSecret()
	 * @uses \Crux\Core\Exception\Crypto\OpenSSL::__construct()
	 * @uses serialize()
	 * @uses openssl_cipher_iv_length()
	 * @uses openssl_random_pseudo_bytes()
	 * @uses base64_decode()
	 * @uses base64_encode()
	 * @uses openssl_encrypt()
	 * @uses strtolower()
	 * @uses strtoupper()
	 * @uses sprintf()
	 */
	public static function encryptWithPassphrase($mixData, string $strPassphrase = '', int $intPasses = -1) : string
	{
		// Make sure we have an algorithm
		if (Core\Is::empty(Core\Config::get('crypto.openssl.algorithm'))) {
			// Set the algorithm
			$strAlgorithm = 'aes-128-cbc-hmac-sha256';
		} else {
			// Set the algorithm
			$strAlgorithm = strtolower(Core\Config::get('crypto.openssl.algorithm'));
		}
		// Check for a passphrase
		if (Core\Is::empty($strPassphrase)) {
			// Reset the passphrase
			$strPassphrase = self::getSecret();
		}
		// Check the passes
		if ($intPasses < 1) {
			// Reset the passes
			$intPasses = Core\Config::get('crypto.openssl.passes');
		}
		// Check for scalar data
		if (!Core\Is::scalar($mixData)) {
			// Serialize the data
			$mixData = serialize($mixData);
		}
		// Define the hash container
		$binHash = $mixData;
		// Set the IV length
		$intIvLength = openssl_cipher_iv_length($strAlgorithm);
		// Iterate to the passes
		for ($intPass = 0; $intPass < $intPasses; ++$intPass) {
			// Create a new initialization vector
			$binInitializationVector = openssl_random_pseudo_bytes($intIvLength);
			// Update the hash
			$binHash = openssl_encrypt($binHash, $strAlgorithm, $strPassphrase, OPENSSL_RAW_DATA, $binInitializationVector);
			// Check the hash
			if ($binHash === false) {
				// Throw the exception
				throw new Core\Exception\Crypto\OpenSSL(sprintf('Unable to encrypt pass %d/%d using passphrase', ($intPass + 1), $intPasses));
			}
			// Add the initialization vector to the beginning of the hash
			$binHash = $binInitializationVector.$binHash;
		}
		// Return the hash
		return sprintf('{%s}$%d$%d$%s', strtoupper($strAlgorithm), $intPasses, $intIvLength, base64_encode($binHash));
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Signing & Verification Methods ///////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method signs a string with OpenSSL
	 * @access public
	 * @name \Crux\Crypto\OpenSSL::sign()
	 * @package \Crux\Crypto\OpenSSL
	 * @param string $strInput
	 * @param int|string $mixAlgorithm [OPENSSL_ALGO_SHA256]
	 * @return string
	 * @throws \Crux\Core\Exception\Crypto\OpenSSL
	 * @uses \Crux\Crypto\OpenSSL::getKey()
	 * @uses \Crux\Core\Exception\Crypto\OpenSSL::__construct()
	 * @uses openssl_sign()
	 */
	public static function sign(string $strInput, $mixAlgorithm = OPENSSL_ALGO_SHA256) : string
	{
		// Define the signature container
		$strSignature = '';
		// Sign the data
		if (!openssl_sign($strInput, $strSignature, self::getKey(), $mixAlgorithm)) {
			// We're done, throw the exception
			throw new Core\Exception\Crypto\OpenSSL('There was a problem signing the input string');
		}
		// We're done, return the signature
		return $strSignature;
	}

	/**
	 * This method verifies an OpenSSL signature against the public key
	 * @access public
	 * @name \Crux\Crypto\OpenSSL::verify()
	 * @package \Crux\Crypto\OpenSSL
	 * @param string $strInput
	 * @param string $strSignature
	 * @param int|string $mixAlgorithm
	 * @return bool
	 * @static
	 * @throws \Crux\Core\Exception\Crypto\OpenSSL
	 * @uses \Crux\Crypto\OpenSSL::getPublicKey()
	 * @uses \Crux\Core\Exception\Crypto\OpenSSL::__construct()
	 * @uses openssl_verify()
	 */
	public static function verify(string $strInput, string $strSignature, $mixAlgorithm = OPENSSL_ALGO_SHA256) : bool
	{
		// Verify the signature
		$intResult = openssl_verify($strInput, $strSignature, self::getPublicKey(), $mixAlgorithm);
		// Check the result
		if ($intResult === 1) {
			// We're done, the signature is valid
			return true;
		} elseif ($intResult === 0) {
			// We're done, the signature is not valid
			return false;
		} else {
			// We're done, throw an exception
			throw new Core\Exception\Crypto\OpenSSL('There was a problem verifying the signature');
		}
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Key Based Encryption Methods /////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method decrypts a hash with a private key
	 * @access public
	 * @name \Crux\Crypto\OpenSSL::decryptWithPrivateKey()
	 * @param string $strHash
	 * @return mixed|string
	 * @param string $strKey ['']
	 * @throws \Crux\Core\Exception\Crypto\OpenSSL
	 * @uses \Crux\Core\Is::empty()
	 * @uses \Crux\Core\Is::serialized()
	 * @uses \Crux\Core\Exception\Crypto\OpenSSL::__construct()
	 * @uses base64_decode()
	 * @uses openssl_private_decrypt()
	 * @uses unserialize()
	 */
	public static function decryptWithPrivateKey(string $strHash, string $strKey = '')
	{
		// Check the key
		if (Core\Is::empty($strKey)) {
			// Reset the key
			$strKey = self::getKey();
		}
		// Decode the data
		$strHash = base64_decode($strHash);
		// Set a temporary placeholder for the decrypted text
		$strPlain = '';
		// Try to decrypt the data
		if (!openssl_private_decrypt($strHash, $strPlain, $strKey)) {
			// Throw the exception
			throw new Core\Exception\Crypto\OpenSSL('Unable to decrypt message using private key');
		}
		// Return the data
		return (Core\Is::serialized($strHash) ? unserialize($strPlain) : $strPlain);
	}

	/**
	 * This method decrypts a hash with a public key
	 * @access public
	 * @name \Crux\Crypto\OpenSSL::decryptWithPublicKey()
	 * @param string $strHash
	 * @return mixed|string
	 * @param string $strKey ['']
	 * @throws \Crux\Core\Exception\Crypto\OpenSSL
	 * @uses \Crux\Core\Is::empty()
	 * @uses \Crux\Core\Is::serialized()
	 * @uses \Crux\Core\Exception\Crypto\OpenSSL::__construct()
	 * @uses base64_decode()
	 * @uses openssl_public_decrypt()
	 * @uses unserialize()
	 */
	public static function decryptWithPublicKey(string $strHash, string $strKey = '')
	{
		// Check the key
		if (Core\Is::empty($strKey)) {
			// Reset the key
			$strKey = self::getPublicKey();
		}
		// Decode the data
		$strHash = base64_decode($strHash);
		// Set a temporary placeholder for the decrypted text
		$strPlain = '';
		// Try to decrypt the data
		if (!openssl_public_decrypt($strHash, $strPlain, $strKey)) {
			// Throw the exception
			throw new Core\Exception\Crypto\OpenSSL('Unable to decrypt message using public key');
		}
		// Return the data
		return (Core\Is::serialized($strHash) ? unserialize($strPlain) : $strPlain);
	}

	/**
	 * This method encrypts data into a hash using a private key
	 * @access public
	 * @name \Crux\Crypto\OpenSSL::encryptWitPrivateKey()
	 * @package \Crux\Crypto\OpenSSL
	 * @param mixed $mixData
	 * @param string $strKey ['']
	 * @return string
	 * @static
	 * @throws \Crux\Core\Exception\Crypto\OpenSSL
	 * @uses \Crux\Core\Is::empty()
	 * @uses \Crux\Core\Is::scalar()
	 * @uses \Crux\Crypto\OpenSSL::getKey()
	 * @uses \Crux\Core\Exception\Crypto\OpenSSL::__construct()
	 * @uses openssl_private_encrypt()
	 * @uses base64_encode()
	 */
	public static function encryptWithPrivateKey($mixData, string $strKey = '') : string
	{
		// Check the key
		if (Core\Is::empty($strKey)) {
			// Reset the key
			$strKey = self::getKey();
		}
		// Check for scalar data
		if (!Core\Is::scalar($mixData)) {
			// Serialize the data
			$mixData = serialize($mixData);
		}
		// Set a temporary placeholder for the encrypted text
		$strCryptex = '';
		// Try to encrypt the data
		if (!openssl_private_encrypt($mixData, $strCryptex, self::getKey())) {
			// Throw the exception
			throw new Core\Exception\Crypto\OpenSSL('Unable to encrypt message using public key');
		}
		// Return the hash
		return base64_encode($strCryptex);
	}

	/**
	 * This method encrypts data into a hash using a public key
	 * @access public
	 * @name \Crux\Crypto\OpenSSL::encryptWitPublicKey()
	 * @package \Crux\Crypto\OpenSSL
	 * @param mixed $mixData
	 * @param string $strKey ['']
	 * @return string
	 * @static
	 * @throws \Crux\Core\Exception\Crypto\OpenSSL
	 * @uses \Crux\Core\Is::empty()
	 * @uses \Crux\Core\Is::scalar()
	 * @uses \Crux\Crypto\OpenSSL::getKey()
	 * @uses \Crux\Core\Exception\Crypto\OpenSSL::__construct()
	 * @uses openssl_public_encrypt()
	 * @uses base64_encode()
	 */
	public static function encryptWithPublicKey($mixData, string $strKey = '') : string
	{
		// Check the key
		if (Core\Is::empty($strKey)) {
			// Reset the key
			$strKey = self::getPublicKey();
		}
		// Check for scalar data
		if (!Core\Is::scalar($mixData)) {
			// Serialize the data
			$mixData = serialize($mixData);
		}
		// Set a temporary placeholder for the encrypted text
		$strCryptex = '';
		// Try to encrypt the data
		if (!openssl_public_encrypt($mixData, $strCryptex, $strKey)) {
			// Throw the exception
			throw new Core\Exception\Crypto\OpenSSL('Unable to encrypt message using public key');
		}
		// Return the hash
		return base64_encode($strCryptex);
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Getters //////////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method returns the cryptographic key from the configuration
	 * @access public
	 * @name \Crux\Crypto\OpenSSL::getKey()
	 * @package \Crux\Crypto\OpenSSL
	 * @param bool $blnTrim [false]
	 * @param string $strKey ['']
	 * @param string $strPassword ['']
	 * @return string
	 * @static
	 * @throws \Crux\Core\Exception\Crypto\OpenSSL
	 * @uses \Crux\Crypto\OpenSSL::getKeyResource()
	 * @uses \Crux\Core\Is::empty()
	 * @uses \Crux\Core\Config::get()
	 * @uses \Crux\Core\Exception\Crypto\OpenSSL::__construct()
	 * @uses substr()
	 * @uses strtolower()
	 * @uses openssl_pkey_export()
	 * @uses openssl_pkey_free()
	 * @uses preg_replace()
	 * @uses trim()
	 */
	public static function getKey(bool $blnTrim = false, string $strKey = '', string $strPassword = '') : string
	{
		// Open the key
		$rscKey = self::getKeyResource($strKey, $strPassword);
		// Define the key container
		$strPrivateKey = '';
		// Export the key
		openssl_pkey_export($rscKey, $strPrivateKey);
		// Free the key
		openssl_pkey_free($rscKey);
		// We're done, return the private key
		return ($blnTrim ? trim(preg_replace('/(-----(BEGIN|END)(.*?)KEY-----)/i', '', $strPrivateKey)) : $strPrivateKey);
	}

	/**
	 * This method loads a private key into a resource
	 * @access public
	 * @name \Crux\Crypto\OpenSSL::getKeyResource()
	 * @package \Crux\Crypto\OpenSSL
	 * @param string $strKey ['']
	 * @param string $strPassword ['']
	 * @return resource
	 * @throws \Crux\Core\Exception\Crypto\OpenSSL
	 * @uses \Crux\Core\Is::empty()
	 * @uses \Crux\Core\Exception\Crypto\OpenSSL::__construct()
	 * @uses openssl_pkey_get_private()
	 */
	public static function getKeyResource(string $strKey = '', string $strPassword = '')
	{
		// Check for a key
		if (Core\Is::empty($strKey)) {
			// Reset the key
			$strKey = Core\Config::get('crypto.privateKey');
		}
		// Check the password
		if (Core\Is::empty($strPassword)) {
			// Reset the password
			$strPassword = Core\Config::get('crypto.keyPass');
		}
		// Make sure we don't have an empty key
		if (Core\Is::empty($strKey)) {
			// Throw the exception
			throw new Core\Exception\Crypto\OpenSSL('Private Key cannot be empty');
		}
		// Open the key
		$rscKey = openssl_pkey_get_private($strKey, $strPassword);
		// Check the resource
		if ($rscKey === false) {
			// Throw the exception
			throw new Core\Exception\Crypto\OpenSSL('Unable to load Private Key');
		}
		// We're done, return the resource
		return $rscKey;
	}

	/**
	 * This method returns the cryptographic public key from the configuration
	 * @access public
	 * @name \Crux\Crypto\OpenSSL::getPublicKey()
	 * @package \Crux\Crypto\OpenSSL
	 * @param bool $blnTrim [false]
	 * @param string $strKey ['']
	 * @return string
	 * @static
	 * @throws \Crux\Core\Exception\Crypto\OpenSSL
	 * @uses \Crux\Core\Is::empty()
	 * @uses \Crux\Core\Config::get()
	 * @uses \Crux\Core\Exception\Crypto\OpenSSL::__construct()
	 * @uses substr()
	 * @uses strtolower()
	 * @uses substr_replace()
	 * @uses file_get_contents()
	 * @uses preg_replace()
	 * @uses trim()
	 */
	public static function getPublicKey(bool $blnTrim = false, string $strKey = '') : string
	{
		// Check the key
		if (Core\Is::empty($strKey)) {
			// Reset the key
			$strKey = Core\Config::get('crypto.publicKey');
		}
		// Make sure we have a cipher
		if (Core\Is::empty($strKey)) {
			// Throw the exception
			throw new Core\Exception\Crypto\OpenSSL('Public Key cannot be empty');
		}
		// Check for a file
		if (strtolower(substr($strKey, 0, 7)) === 'file://') {
			// Load the key from the file system
			$strKey = file_get_contents(substr_replace($strKey, '', 0, 7));
		}
		// We're done, return the private key
		return ($blnTrim ? trim(preg_replace('/(-----(BEGIN|END)(.*?)KEY-----)/i', '', $strKey)) : $strKey);
	}

	/**
	 * This method returns the cryptographic secret from the configuration used for arbitrary encryption and decryption
	 * @access public
	 * @name \Crux\Crypto\OpenSSL::getSecret()
	 * @package \Crux\Crypto\OpenSSL
	 * @return string
	 * @throws \Crux\Core\Exception\Crypto\OpenSSL
	 * @uses \Crux\Core\Config::get()
	 * @uses \Crux\Core\IS::empty()
	 * @uses \Crux\Core\Exception\Crypto\OpenSSL::__construct()
	 */
	public static function getSecret() : string
	{
		// Load the passphrase
		$strSecret = Core\Config::get('crypto.secret');
		// Check the secret
		if (Core\Is::empty($strSecret)) {
			// Throw the exception
			throw new Core\Exception\Crypto\OpenSSL('Secret cannot be empty');
		}
		// We're done, return the secret
		return $strSecret;
	}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
} /// End Crux\Crypto\OpenSSL Class Definition ///////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

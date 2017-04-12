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
/// Crux\Crypto\MCrypt Class Definition //////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class MCrypt
{
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Arbitrary Encryption Methods /////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method decrypts a passphrase encrypted hash
	 * @access public
	 * @name \Crux\Crypto\MCrypt::decryptWithPassphrase()
	 * @package \Crux\Crypto\MCrypt
	 * @param string $strHash
	 * @param string $strPassphrase ['']
	 * @return mixed
	 * @static
	 * @throws \Crux\Core\Exception\Crypto\MCrypt
	 * @uses \Crux\Core\Is::empty()
	 * @uses \Crux\Core\Is::serialized()
	 * @uses \Crux\Core\Config::get()
	 * @uses \Crux\Core\Exception\Crypto\MCrypt::__construct()
	 * @uses \Crux\Crypto\MCrypt::getSecret()
	 * @uses preg_match()
	 * @uses base64_decode()
	 * @uses substr()
	 * @uses substr_replace()
	 * @uses intval()
	 * @uses mcrypt_decrypt()
	 * @uses strtolower()
	 * @uses substr()
	 * @uses unserialize()
	 */
	public static function decryptWithPassphrase(string $strHash, string $strPassphrase = '')
	{
		// Grab the parts
		if (!preg_match('/^{(.*?)-([A-Z]{3,6})}\$([0-9]+)\$([0-9]+)\$(.*?)$/i', $strHash, $arrMatches)) {
			// Throw the exception
			throw new Core\Exception\Crypto\MCrypt('Hash is not valid');
		}
		// Check for a passphrase
		if (Core\Is::empty($strPassphrase)) {
			// Reset the passphrase
			$strPassphrase = self::getSecret();
		}
		// Define the plain-text container
		$strPlainText = base64_decode($arrMatches[5]);
		// Iterate to the number of passes
		for ($intPass = 0; $intPass < intval($arrMatches[3]); ++$intPass) {
			// Grab the initialization vector
			$strInitializationVector = substr($strPlainText, 0, $arrMatches[4]);
			// Reset the plain text
			$strPlainText = substr_replace($strPlainText, '', 0, $arrMatches[4]);
			// Decrypt the hash
			$strPlainText = mcrypt_decrypt(strtolower($arrMatches[1]), substr($strPassphrase, 0, 32), $strPlainText, strtolower($arrMatches[2]), $strInitializationVector);
			// Check for success
			if ($strPlainText === false) {
				// Throw the exception
				throw new Core\Exception\Crypto\MCrypt(sprintf('Unable to decrypt pass %d/%d using passphrase', ($intPass + 1), $arrMatches[3]));
			}
		}
		return (Core\Is::serialized($strPlainText) ? unserialize(trim($strPlainText)) : trim($strPlainText));
	}

	/**
	 * This method encrypts data into a hash using a passphrase
	 * @access public
	 * @name \Crux\Crypto\MCrypt::encryptWithPassphrase()
	 * @package \Crux\Crypto\MCrypt
	 * @param mixed $mixData
	 * @param string $strPassphrase ['']
	 * @param int $intPasses [-1]
	 * @return string
	 * @static
	 * @throws \Crux\Core\Exception\Crypto\MCrypt
	 * @uses \Crux\Core\Is::empty()
	 * @uses \Crux\Core\Is::scalar()
	 * @uses \Crux\Core\Config::get()
	 * @uses \Crux\Crypto\MCrypt::getSecret()
	 * @uses \Crux\Core\Exception\Crypto\MCrypt::__construct()
	 * @uses constant()
	 * @uses serialize()
	 * @uses pack()
	 * @uses mcrypt_get_iv_size()
	 * @uses mcrypt_create_iv()
	 * @uses substr()
	 * @uses mcrypt_encrypt()
	 * @uses sprintf()
	 * @uses strtoupper()
	 * @uses base64_encode()
	 */
	public static function encryptWithPassphrase($mixData, string $strPassphrase = '', int $intPasses = -1) : string
	{
		// Make sure we have an algorithm
		if (Core\Is::empty(Core\Config::get('crypto.mcrypt.algorithm'))) {
			// Set the algorithm
			$strAlgorithm = MCRYPT_RIJNDAEL_256;
		} else {
			// Set the algorithm
			$strAlgorithm = constant(Core\Config::get('crypto.mcrypt.algorithm'));
		}
		// Make sure we have a mode
		if (Core\Is::empty(Core\Config::get('crypto.mcrypt.mode'))) {
			// Set the mode
			$strMode = 'ctr';
		} else {
			// Set the mode
			$strMode = constant(Core\Config::get('crypto.mcrypt.mode'));
		}
		// Check for a passphrase
		if (Core\Is::empty($strPassphrase)) {
			// Reset the passphrase
			$strPassphrase = self::getSecret();
		}
		// Check the passes
		if ($intPasses < 1) {
			// Reset the passes
			$intPasses = Core\Config::get('crypto.mcrypt.passes');
		}
		// Check for scalar data
		if (!Core\Is::scalar($mixData)) {
			// Serialize the data
			$mixData = serialize($mixData);
		}
		// Define the hash container
		$binHash = $mixData;
		// Set the IV length
		$intIvLength = mcrypt_get_iv_size($strAlgorithm, $strMode);
		// Iterate to the passes
		for ($intPass = 0; $intPass < $intPasses; ++$intPass) {
			// Create a new initialization vector
			$binInitializationVector = mcrypt_create_iv($intIvLength, MCRYPT_RAND);
			// Update the hash
			$binHash = mcrypt_encrypt($strAlgorithm, substr($strPassphrase, 0, 32), $binHash, $strMode, $binInitializationVector);
			// Check the hash
			if ($binHash === false) {
				// Throw the exception
				throw new Core\Exception\Crypto\MCrypt(sprintf('Unable to encrypt pass %d/%d using passphrase', ($intPass + 1), $intPasses));
			}
			// Add the initialization vector to the beginning of the hash
			$binHash = $binInitializationVector.$binHash;
		}
		// Return the hash
		return sprintf('{%s-%s}$%d$%d$%s', strtoupper($strAlgorithm), strtoupper($strMode), $intPasses, $intIvLength, base64_encode($binHash));
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Getters //////////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method returns the cryptographic secret from the configuration used for arbitrary encryption and decryption
	 * @access public
	 * @name \Crux\Crypto\MCrypt::getSecret()
	 * @package \Crux\Crypto\MCrypt
	 * @return string
	 * @throws \Crux\Core\Exception\Crypto\MCrypt
	 * @uses \Crux\Core\Config::get()
	 * @uses \Crux\Core\IS::empty()
	 * @uses \Crux\Core\Exception\Crypto\MCrypt::__construct()
	 */
	public static function getSecret() : string
	{
		// Load the passphrase
		$strSecret = Core\Config::get('crypto.secret');
		// Check the secret
		if (Core\Is::empty($strSecret)) {
			// Throw the exception
			throw new Core\Exception\Crypto\MCrypt('Cryptographic Secret cannot be empty');
		}
		// We're done, return the secret
		return $strSecret;
	}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
} /// End Crux\Crypto\MCrypt Class Definition ////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

<?php

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Crux\Token Namespace /////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

namespace Crux\Token;

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Imports //////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

use Crux\Core;

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Crux\Token\Uuid Class Definition /////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class Uuid
{
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Public Static Methods ////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method generates a version 3 compliant Universal Unique Identifier (UUID)
	 * @access public
	 * @name \Crux\Token\Uuid::v3()
	 * @package \Crux\Token\Uuid
	 * @param string $strNameSpace
	 * @param string $strName
	 * @return string
	 * @static
	 * @throws \Crux\Core\Exception\Token\Uuid
	 * @uses \Crux\Core\Exception\Token\Uuid::__construct()
	 * @uses preg_match()
	 * @uses sprintf()
	 * @uses str_replace()
	 * @uses strlen()
	 * @uses hexdec()
	 * @uses chr()
	 * @uses md5()
	 * @uses substr()
	 */
	public static function v3(string $strNameSpace, string $strName) : string
	{
		$fnValid = function (string $strUUID) : bool {
			// Return the match
			return (preg_match('/^\{?[0-9a-f]{8}\-?[0-9a-f]{4}\-?[0-9a-f]{4}\-?' . '[0-9a-f]{4}\-?[0-9a-f]{12}\}?$/i', $strUUID) === 1);
		};
		// Check the validity of the Namespace
		if ($fnValid($strNameSpace) === false) {
			// We'e done
			throw new Core\Exception\Token\Uuid(sprintf('%s is not a valid namespace', $strNameSpace));
		}
		// Get hexadecimal components of namespace
		$hexNameSpace = str_replace(['-', '{', '}'], '', $strNameSpace);
		// Binary Value
		$binNameSpace = '';
		// Convert Namespace UUID to bits
		for ($intIndex = 0; $intIndex < strlen($hexNameSpace); $intIndex += 2) {
			// Append to the binary
			$binNameSpace .= chr(hexdec([$intIndex] . $hexNameSpace[($intIndex + 1)]));
		}
		// Calculate hash value
		$strHash = md5($binNameSpace . $strName);
		// Return the UUID
		return sprintf('%08s-%04s-%04x-%04x-%12s',
			substr($strHash, 0, 8),
			substr($strHash, 8, 4),
			(hexdec(substr($strHash, 12, 4)) & 0x0fff) | 0x3000,
			(hexdec(substr($strHash, 16, 4)) & 0x3fff) | 0x8000,
			substr($strHash, 20, 12)
		);
	}

	/**
	 * This method generates a version 4 compliant Universal Unique Identifier (UUID)
	 * @access public
	 * @name \Crux\Token\Uuid::v4()
	 * @package \Crux\Token\Uuid
	 * @param bool $blnTrim [true]
	 * @return string
	 * @static
	 * @uses function_exists()
	 * @uses com_create_guid()
	 * @uses trim()
	 * @uses openssl_random_pseudo_bytes()
	 * @uses chr()
	 * @uses ord()
	 * @uses vsprintf()
	 * @uses str_split()
	 * @uses bin2hex()
	 * @uses mt_srand()
	 * @uses microtime()
	 * @uses rand()
	 * @uses uniqid()
	 * @uses md5()
	 * @uses strtolower()
	 * @uses substr()
	 */
	public static function v4(bool $blnTrim = true) : string
	{
		// Windows
		if (function_exists('com_create_guid') === true) {
			if ($blnTrim === true)
				return trim(com_create_guid(), '{}');
			else
				return com_create_guid();
		}
		// OSX/Linux
		if (function_exists('openssl_random_pseudo_bytes') === true) {
			// Generate the random bytes
			$bitData = openssl_random_pseudo_bytes(16);
			// Set the version to 0100
			$bitData[6] = chr(ord($bitData[6]) & 0x0f | 0x40);
			// Set bits 6-7 to 10
			$bitData[8] = chr(ord($bitData[8]) & 0x3f | 0x80);
			// Return the new UUID
			return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($bitData), 4));
		}
		// Fallback (PHP 4.2+)
		mt_srand((double) microtime() * 10000);
		// Generate our ID
		$chrId = strtolower(md5(uniqid(rand(), true)));
		// Set our hyphen character
		$chrHyphen = chr(45);                  // "-"
		// Set our left brace character
		$chrLeftBrace = ($blnTrim ? '' : chr(123));
		// Set our right brace character
		$chrRightBrace = ($blnTrim ? '' : chr(125));
		// Piece together our UUID
		$uuidV4 = $chrLeftBrace.
			substr($chrId,  0,  8).$chrHyphen.
			substr($chrId,  8,  4).$chrHyphen.
			substr($chrId, 12,  4).$chrHyphen.
			substr($chrId, 16,  4).$chrHyphen.
			substr($chrId, 20, 12).
			$chrRightBrace;
		// We're done, return the UUID
		return $uuidV4;
	}

	/**
	 * This method generates a version 5 compliant Universal Unique Identifier (UUID)
	 * @access public
	 * @name \Crux\Token\Uuid::v5()
	 * @package \Crux\Token\Uuid
	 * @param string $strNameSpace
	 * @param string $strName
	 * @return string
	 * @static
	 * @throws \Crux\Core\Exception\Token\Uuid
	 * @uses \Crux\Core\Exception\Token\Uuid::__construct()
	 * @uses preg_match()
	 * @uses sprintf()
	 * @uses str_replace()
	 * @uses strlen()
	 * @uses hexdec()
	 * @uses chr()
	 * @uses sha1()
	 * @uses substr()
	 */
	public static function v5(string $strNameSpace, string $strName) : string
	{
		$fnValid = function (string $strUUID) : bool {
			// Return the match
			return (preg_match('/^\{?[0-9a-f]{8}\-?[0-9a-f]{4}\-?[0-9a-f]{4}\-?' . '[0-9a-f]{4}\-?[0-9a-f]{12}\}?$/i', $strUUID) === 1);
		};
		// Check the validity of the Namespace
		if ($fnValid($strNameSpace) === false) {
			// We'e done
			throw new Core\Exception\Token\Uuid(sprintf('%s is not a valid namespace', $strNameSpace));
		}
		// Get hexadecimal components of namespace
		$hexNameSpace = str_replace(['-', '{', '}'], '', $strNameSpace);
		// Binary Value
		$binNameSpace = '';
		// Convert Namespace UUID to bits
		for ($intIndex = 0; $intIndex < strlen($hexNameSpace); $intIndex += 2) {
			// Append to the binary
			$binNameSpace .= chr(hexdec([$intIndex] . $hexNameSpace[($intIndex + 1)]));
		}
		// Calculate hash value
		$strHash = sha1($binNameSpace . $strName);
		// Return the UUID
		return sprintf('%08s-%04s-%04x-%04x-%12s',
			substr($strHash, 0, 8),
			substr($strHash, 8, 4),
			(hexdec(substr($strHash, 12, 4)) & 0x0fff) | 0x5000,
			(hexdec(substr($strHash, 16, 4)) & 0x3fff) | 0x8000,
			substr($strHash, 20, 12)
		);
	}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
} /// End Crux\Token\Uuid Class Definition ///////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

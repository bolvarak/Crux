<?php

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Crux\Core Namespace //////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

namespace Crux\Core;

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Imports //////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

use Crux\Collection;

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Crux\Core\Util Class Definition //////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class Util
{
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Public Methods ///////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method returns the alphabet as an array
	 * @access public
	 * @name \Crux\Core\Util::alphabetArray()
	 * @package \Crux\Core\Util
	 * @param bool $blnAllCaps [false]
	 * @return array
	 * @static
	 */
	public static function alphabetArray(bool $blnAllCaps = false) : array
	{
		// Check for all caps
		if ($blnAllCaps) {
			// Return the alphabet array
			return ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];
		} else {
			// Return the alphabet array
			return ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z'];
		}
	}

	/**
	 * This method searches an associative array for a key with case insensitivity
	 * @access public
	 * @name \Crux\Core\Util::arrayKeySearch()
	 * @package \Crux\Core\Util
	 * @param string $strNeedle
	 * @param array $arrHaystack
	 * @return bool
	 * @static
	 */
	public static function arrayKeySearch(string $strNeedle, array $arrHaystack) : bool
	{
		// Make sure we have an associative array
		if (!Is::associativeArray($arrHaystack)) {
			// We're done, we need an associative array
			return false;
		}
		// Iterate over the array
		foreach ($arrHaystack as $strKey => $anyValue) {
			// Compare the name and key
			if (strtolower($strNeedle) === strtolower($strKey)) {
				// We've found the key
				return $strKey;
			}
		}
		// No matches found
		return false;
	}

	/**
	 * This method generates a seed from microseconds
	 * @access public
	 * @name \Crux\Core\Util::makeSeed()
	 * @package \Crux\Core\Util
	 * @return int
	 * @static
	 * @uses microtime()
	 * @uses explode()
	 * @uses floatval()
	 */
	public static function makeSeed() : int
	{
		// Create parts
		$arrParts = explode(' ', microtime());
		// Return the seed
		return (floatval($arrParts[1]) + (floatval($arrParts[0]) * 100000));
	}

	/**
	 * This method provides a factory method for a Map
	 * @access public
	 * @name \Crux\Core\Util::mapFactory()
	 * @package Crux\Core\Util
	 * @param mixed $mixSource [array()]
	 * @return \Crux\Collection\Map
	 * @static
	 * @uses \Crux\Collection\Map::__construct()
	 */
	public static function mapFactory($mixSource = []) : Collection\Map
	{
		// Check for an array
		if (Is::associativeArray($mixSource)) {
			// Return the new map
			return Collection\Map::fromArray($mixSource);
		} elseif (Is::map($mixSource)) {
			// Return the original source
			return $mixSource;
		} elseif (Is::variantMap($mixSource)) {
			// Return the original data from the source
			return Collection\Map::fromArray($mixSource->toArray());
		} elseif (Is::object($mixSource)) {
			// Return the new map
			return Collection\Map::fromObject($mixSource);
		} else {
			// Return the new map
			return new Collection\Map();
		}
	}

	/**
	 * This method searches a map for a desired key and returns it or the value
	 * @access public
	 * @name \Crux\Core\Util::mapSearch()
	 * @param string $strNeedle
	 * @param \Crux\Collection\Map $mapHaystack
	 * @param bool $blnReturnValue
	 * @return int|mixed|null|string
	 * @static
	 * @uses \Crux\Collection\Map::get()
	 * @uses \Crux\Collection\Map::key()
	 */
	public static function mapSearch(string $strNeedle, Collection\Map $mapHaystack, bool $blnReturnValue = true)
	{
		// Check the map
		return ($blnReturnValue ? $mapHaystack->get($strNeedle) : $mapHaystack->key($strNeedle));
	}

	/**
	 * This method returns a MySQL compliant DATE
	 * @access public
	 * @name \Crux\Core\Util::mysqlDate()
	 * @package Crux\Core\Util
	 * @param int|string $mixTimeStamp [null]
	 * @return string
	 * @static
	 * @uses is_null()
	 * @uses strtotime()
	 * @uses is_string()
	 * @uses time()
	 * @uses date()
	 */
	public static function mysqlDate($mixTimeStamp = null) : string
	{
		// Check for a timestamp
		if (is_null($mixTimeStamp) === false) {
			// Set the time
			$intTime = (is_string($mixTimeStamp) ? strtotime($mixTimeStamp) : $mixTimeStamp);
		} else {
			// Set the time
			$intTime = time();
		}
		// Return the timestamp
		return date('Y-m-d', $intTime);
	}

	/**
	 * This method returns a MySQL compliant DATETIME/TIMESTAMP
	 * @access public
	 * @name \Crux\Core\Util::mysqlTimeStamp()
	 * @package Crux\Core\Util
	 * @param int|string $mixTimeStamp [null]
	 * @return string
	 * @static
	 * @uses is_null()
	 * @uses strtotime()
	 * @uses is_string()
	 * @uses time()
	 * @uses date()
	 */
	public static function mysqlTimeStamp($mixTimeStamp = null) : string
	{
		// Check for a timestamp
		if (is_null($mixTimeStamp) === false) {
			// Set the time
			$intTime = (is_string($mixTimeStamp) ? strtotime($mixTimeStamp) : $mixTimeStamp);
		} else {
			// Set the time
			$intTime = time();
		}
		// Return the timestamp
		return date('Y-m-d H:i:s', $intTime);
	}

	/**
	 * This method returns a PostgreSQL compliant DATETIME/TIMESTAMP WITHOUT TZ
	 * @access public
	 * @name \Crux\Core\Util::pgsqlTimeStamp()
	 * @package Crux\Core\Util
	 * @param int|string $mixTimeStamp [null]
	 * @return string
	 * @static
	 * @uses is_null()
	 * @uses strtotime()
	 * @uses is_string()
	 * @uses microtime()
	 * @uses \DateTime::__construct()
	 * @uses \DateTime::setTimestamp()
	 * @uses \DateTime::format()
	 */
	public static function pgsqlTimeStamp($mixTimeStamp = null) : string
	{
		// Check for a timestamp
		if (is_null($mixTimeStamp) === false) {
			// Set the time
			$intTime = (is_string($mixTimeStamp) ? strtotime($mixTimeStamp) : $mixTimeStamp);
		} else {
			// Set the time
			$intTime = microtime(true);
		}
		// Create the datetime object
		$dteTimeStamp = new \DateTime();
		// Set the time
		$dteTimeStamp->setTimestamp($intTime);
		// Return the timestamp
		return $dteTimeStamp->format('Y-m-d H:i:s');
	}

	/**
	 * This method returns a random number between 0 and the maximum integer of PHP
	 * @access public
	 * @name \Crux\Core\Util::randomInteger()
	 * @package \Crux\Core\Util
	 * @param int $intMinimum [0]
	 * @return int
	 * @static
	 * @uses \Crux\Core\Util::randomSeeder()
	 * @uses mt_rand()
	 * @uses mt_getrandmax()
	 */
	public static function randomInteger(int $intMinimum = 0) : int
	{
		// Seed the random number generator
		self::randomSeeder();
		// Return a random number
		return mt_rand($intMinimum, mt_getrandmax());
	}

	/**
	 * This method seeds the random number generator
	 * @access public
	 * @name \Crux\Core\Util::randomSeeder()
	 * @package \Crux\Core\Util
	 * @param int $intSeed [0]
	 * @static
	 * @uses \Crux\Core\Util::makeSeed()
	 * @uses mt_srand()
	 */
	public static function randomSeeder(int $intSeed = 0)
	{
		// Check for a seed
		if ($intSeed === 0) {
			// Create a seed
			$intSeed = self::makeSeed();
		}
		// Seed
		mt_srand($intSeed);
	}

	/**
	 * This method converts a string into Title Case
	 * @access public
	 * @name \Crux\Core\Util::titleCase()
	 * @package Crux\Core\Util
	 * @param string $strSource
	 * @return string
	 * @static
	 * @uses mb_convert_case()
	 */
	public static function titleCase(string $strSource) : string
	{
		// Return the title case version of the string
		return mb_convert_case($strSource, MB_CASE_TITLE);
	}

	/**
	 * This method provides a factory method for a Vector
	 * @access public
	 * @name \Crux\Core\Util::vectorFactory()
	 * @package Crux\Core\Util
	 * @param mixed $mixSource [array()]
	 * @return \Crux\Collection\Vector
	 * @static
	 * @uses \Crux\Collection\Vector::__construct()
	 */
	public static function vectorFactory($mixSource = null) : Collection\Vector
	{
		// Check the source type
		if (Is::sequentialArray($mixSource)) {
			// Return the new vector
			return Collection\Vector::fromArray($mixSource);
		} elseif (Is::vector($mixSource)) {
			// Return the source
			return $mixSource;
		} elseif (Is::variantList($mixSource)) {
			// Return the new vector
			return Collection\Vector::fromArray($mixSource->toArray());
		} else {
			// Return the new vector
			return new Collection\Vector();
		}
	}

	/**
	 * This method implodes a vector of maps
	 * @access public
	 * @name \Crux\Core\Util::vectorImplode()
	 * @param \Crux\Collection\Vector $vecHaystack
	 * @param string $strNeedle
	 * @param string|null $strDelimiter
	 * @return \Crux\Collection\Vector|string
	 * @static
	 * @uses \Crux\Core\Util::vectorFactory()
	 * @uses \Crux\Collection\Vector::getIterator()
	 * @uses \Crux\Collection\Map::contains()
	 * @uses \Crux\Collection\Map::get()
	 * @uses \Crux\Collection\Vector::add()
	 */
	public static function vectorImplode(Collection\Vector $vecHaystack, string $strNeedle, string $strDelimiter = null)
	{
		// Create the needles vector
		$vecNeedles = self::vectorFactory();
		// Iterate over the haystack
		foreach ($vecHaystack->getIterator() as $intIndex => $mapValue) {
			// Check for the needle in the map
			if ($mapValue->contains($strNeedle)) {
				// Append the needle
				$vecNeedles->add($mapValue->get($strNeedle));
			}
		}
		// Return the data
		return (Is::null($strDelimiter) ? $vecNeedles : implode($strDelimiter, $vecNeedles->toArray()));
	}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
} /// End Crux\Core\Util Class Definition ////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

<?php

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Crux\Core Namespace //////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

namespace Crux\Core;

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Imports //////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

use Crux\Collection;
use Crux\Type\Variant;
use Crux\Type\VariantList;
use Crux\Type\Map;

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Crux\Core\Is Class Definition ////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class Is
{
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Public Static Determinants ///////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method determines whether or not a variable is an instance of a class
	 * @access public
	 * @name \Crux\Core\Is::a()
	 * @package \Crux\Core\Is
	 * @param string $strClass
	 * @param $mixTest
	 * @return bool
	 * @static
	 * @uses is_a()
	 */
	public static function a(string $strClass, $mixTest) : bool
	{
		// Return the test
		return (@is_a($mixTest, $strClass) || ($mixTest instanceof $strClass));
	}

	/**
	 * This method determines whether or not a variable is an array
	 * @access public
	 * @name \Crux\Core\Is::array()
	 * @package \Crux\Core\Is
	 * @param $mixTest
	 * @return bool
	 * @static
	 * @uses is_array()
	 */
	public static function array($mixTest) : bool
	{
		// Return the test
		return @is_array($mixTest);
	}

	/**
	 * This method determines whether or not a variable is an associative array
	 * @access public
	 * @name \Crux\Core\Is::associativeArray()
	 * @package \Crux\Core\Is
	 * @param $mixTest
	 * @return bool
	 * @static
	 * @uses \Crux\Core\Is::array()
	 * @uses array_keys()
	 * @uses array_filter()
	 * @uses is_string()
	 * @uses count()
	 * @uses boolval()
	 */
	public static function associativeArray($mixTest) : bool
	{
		// Make sure we have an array first
		if (!self::array($mixTest)) {
			// We're done, this is not an array
			return false;
		}
		// Return the test
		return boolval(count(array_filter(array_keys($mixTest), 'is_string')));
	}

	/**
	 * This method determines whether or not a variable is Base64 encoded
	 * @access public
	 * @name \Crux\Core\Is::base64()
	 * @package \Crux\Core\Is
	 * @param $mixTest
	 * @return bool
	 * @static
	 * @uses \Crux\Core\Is::string()
	 * @uses trim()
	 * @uses base64_decode()
	 */
	public static function base64($mixTest) : bool
	{
		// Make sure we have a string
		if (!self::string($mixTest)) {
			// We're done, we don't have a string
			return false;
		}
		// Return the comparison
		return (@base64_decode(trim($mixTest)) !== false);
	}

	/**
	 * This method determines whether or not a variable is a boolean
	 * @access public
	 * @name \Crux\Core\Is::boolean()
	 * @package \Crux\Core\Is
	 * @param $mixTest
	 * @return bool
	 * @static
	 * @uses is_bool()
	 */
	public static function boolean($mixTest) : bool
	{
		// Return the test
		return @is_bool($mixTest);
	}

	/**
	 * This method determines whether or not a variable is a closure
	 * @access public
	 * @name \Crux\Core\Is::closure()
	 * @package \Crux\Core\Is
	 * @param $mixTest
	 * @return bool
	 * @static
	 */
	public static function closure($mixTest) : bool
	{
		// Return the comparison
		return ($mixTest instanceof \Closure);
	}

	/**
	 * This method determines whether or not a variable is a collection class
	 * @access public
	 * @name \Crux\Core\Is::collection()
	 * @package \Crux\Core\Is
	 * @param $mixTest
	 * @return bool
	 * @static
	 * @uses \Crux\Core\Is::map()
	 * @uses \Crux\Core\Is::vector()
	 */
	public static function collection($mixTest) : bool
	{
		// Return the comparison
		return (self::map($mixTest) || self::vector($mixTest));
	}

	/**
	 * This method determines whether or not a variable is a valid directory path
	 * @access public
	 * @name \Crux\Core\Is::directory()
	 * @package \Crux\Core\Is
	 * @param $mixTest
	 * @return bool
	 * @static
	 * @uses \Crux\Core\Is::string()
	 * @uses is_dir()
	 */
	public static function directory($mixTest) : bool
	{
		// Make sure we have a string
		if (!self::string($mixTest)) {
			// We're done, we don't have a string
			return false;
		}
		// Return the test
		return @is_dir($mixTest);
	}

	/**
	 * This method determines whether or not a variable is empty
	 * @access public
	 * @name \Crux\Core\Is::empty()
	 * @package \Crux\Core\Is
	 * @param $mixTest
	 * @return bool
	 * @static
	 * @uses empty()
	 */
	public static function empty($mixTest) : bool
	{
		// Return the test
		return empty($mixTest);
	}

	/**
	 * This method determines whether or not a variable is encoded HTML
	 * @access public
	 * @name \Crux\Core\Is::encodedHtml()
	 * @package \Crux\Core\Is
	 * @param mixed $mixTest
	 * @return bool
	 * @static
	 * @uses \Crux\Core\Is::string()
	 * @uses htmlspecialchars_decode()
	 */
	public static function encodedHtml($mixTest) : bool
	{
		// Check for a string
		if (!self::string($mixTest)) {
			// We're done, not a string
			return false;
		}
		// Check for encoded HTML
		return ((htmlspecialchars_decode($mixTest) === $mixTest) ? false : true);
	}

	/**
	 * This method determines whether or not a variable is an MCrypt or OpenSSL encrypted hash
	 * @access public
	 * @name \Crux\Core\Is::encrypted()
	 * @package \Crux\Core\Is
	 * @param mixed $mixTest
	 * @return bool
	 * @uses \Crux\Core\Is::string()
	 * @uses preg_match()
	 */
	public static function encrypted($mixTest) : bool
	{
		// Make sure we have a string
		if (!self::string($mixTest)) {
			// We're done, not a string
			return false;
		}
		// Return the test
		return (preg_match('/^{(.*?)}\$([0-9]+)\$([0-9]+)\$(.*?)$/i', $mixTest) || preg_match('/^{(.*?)-([A-Z]{3,6})}\$([0-9]+)\$([0-9]+)\$(.*?)$/i', $mixTest));
	}

	/**
	 * This method determines whether or not a variable is a valid file path
	 * @access public
	 * @name \Crux\Core\Is::file()
	 * @package \Crux\Core\Is
	 * @param $mixTest
	 * @return bool
	 * @static
	 * @uses \Crux\Core\Is::string()
	 * @uses is_file()
	 */
	public static function file($mixTest) : bool
	{
		// Make sure we have a string
		if (!self::string($mixTest)) {
			// We're done, we don't have a string
			return false;
		}
		// Return the test
		return (@file_exists($mixTest) && @is_file($mixTest));
	}

	/**
	 * This method determines whether or not a variable is a finite value
	 * @access public
	 * @name \Crux\Core\Is::finite()
	 * @package \Crux\Core\Is
	 * @param $mixTest
	 * @return bool
	 * @static
	 * @uses \Crux\Core\Is::float()
	 * @uses is_finite()
	 */
	public static function finite($mixTest) : bool
	{
		// Make sure we have a float
		if (!self::float($mixTest)) {
			// We're done, we don't have a floating point
			return false;
		}
		// Return the test
		return @is_finite($mixTest);
	}

	/**
	 * This method determines whether or not a variable is a floating point
	 * @access public
	 * @name \Crux\Core\Is::float()
	 * @package \Crux\Core\Is
	 * @param $mixTest
	 * @return bool
	 * @static
	 * @uses is_float()
	 */
	public static function float($mixTest) : bool
	{
		// Return the test
		return @is_float($mixTest);
	}

	/**
	 * This method determines whether or not a variable is an HTML string
	 * @access public
	 * @name \Crux\Core\Is::html()
	 * @package \Crux\Core\Is
	 * @param mixed $mixTest
	 * @return bool
	 * @static
	 * @uses \Crux\Core\Is::string()
	 * @uses \Crux\Core\Is::xml()
	 * @uses strip_tags()
	 */
	public static function html($mixTest) : bool
	{
		// Make sure we have a string
		if (!self::string($mixTest)) {
			// We're done, not a string
			return false;
		}
		// Make sure we don't have XML
		if (self::xml($mixTest)) {
			// We're done, no need to alter XML
			return false;
		}
		// Run the test
		return ((strip_tags($mixTest) === $mixTest) ? false : true);
	}

	/**
	 * This method determines whether or not a variable is in a list of values
	 * @access public
	 * @name \Crux\Core\Is::in()
	 * @package \Crux\Core\Is
	 * @param mixed $mixTest
	 * @param array $arrHaystack
	 * @param bool $blnStrict [true]
	 * @return bool
	 * @static
	 * @uses in_array()
	 */
	public static function in($mixTest, array $arrHaystack, bool $blnStrict = true) : bool
	{
		// Return the test
		return @in_array($mixTest, $arrHaystack, $blnStrict);
	}

	/**
	 * This method determines whether or not a variable is an infinite value
	 * @access public
	 * @name \Crux\Core\Is::infinite()
	 * @package \Crux\Core\Is
	 * @param $mixTest
	 * @return bool
	 * @static
	 * @uses \Crux\Core\Is::float()
	 * @uses is_infinite()
	 */
	public static function infinite($mixTest) : bool
	{
		// Make sure we have a float
		if (!self::float($mixTest)) {
			// We're done, we don't have a floating point
			return false;
		}
		// Return the test
		return @is_infinite($mixTest);
	}

	/**
	 * This method determines whether or not a variable is an integer
	 * @access public
	 * @name \Crux\Core\Is::integer()
	 * @package \Crux\Core\Is
	 * @param $mixTest
	 * @return bool
	 * @static
	 * @uses is_integer()
	 */
	public static function integer($mixTest) : bool
	{
		// Return the test
		return @is_integer($mixTest);
	}

	/**
	 * This method determines whether or not a variable is JSON serialized
	 * @access public
	 * @name \Crux\Core\Is::json()
	 * @package \Crux\Core\Is
	 * @param $mixTest
	 * @return bool
	 * @static
	 * @uses \Crux\Core\Is::string()
	 * @uses trim()
	 * @uses json_decode()
	 * @uses \Crux\Core\Is::object()
	 */
	public static function json($mixTest) : bool
	{
		// Make sure we have a string
		if (!self::string($mixTest)) {
			// We're done, we don't have a string
			return false;
		}
		// Return the comparison
		return self::object(@json_decode(trim($mixTest)));
	}

	/**
	 * This method determines whether or not a variable is a Map
	 * @access public
	 * @name \Crux\Core\Is::map()
	 * @package \Crux\Core\Is
	 * @param $mixTest
	 * @return bool
	 * @static
	 */
	public static function map($mixTest) : bool
	{
		// Return the test
		return ($mixTest instanceof Collection\Map);
	}

	/**
	 * This method determines whether or not a variable is NaN
	 * @access public
	 * @name \Crux\Core\Is::nan()
	 * @package \Crux\Core\Is
	 * @param $mixTest
	 * @return bool
	 * @static
	 * @uses \Crux\Core\Is::float()
	 * @uses is_nan()
	 */
	public static function nan($mixTest) : bool
	{
		// Check for a floating point
		if (!self::float($mixTest)) {
			// We're done, we don't have a floating point
			return false;
		}
		// Return the test
		return @is_nan($mixTest);
	}

	/**
	 * This method determines whether or not a variable is null
	 * @access public
	 * @name \Crux\Core\Is::null()
	 * @package \Crux\Core\Is
	 * @param $mixTest
	 * @return bool
	 * @static
	 * @uses is_null()
	 */
	public static function null($mixTest) : bool
	{
		// Return the test
		return @is_null($mixTest);
	}

	/**
	 * This method determines whether or not a variable is numeric
	 * @access public
	 * @name \Crux\Core\Is::number()
	 * @package \Crux\Core\Is
	 * @param $mixTest
	 * @return bool
	 * @static
	 * @uses is_numeric()
	 */
	public static function number($mixTest) : bool
	{
		// Return the test
		return @is_numeric($mixTest);
	}

	/**
	 * This method determines whether or not a variable is an object
	 * @access public
	 * @name \Crux\Core\Is::object()
	 * @package \Crux\Core\Is
	 * @param $mixTest
	 * @return bool
	 * @static
	 * @uses is_object()
	 */
	public static function object($mixTest) : bool
	{
		// Return the test
		return @is_object($mixTest);
	}

	/**
	 * This method determines whether or not a variable is a file and if it is readable or not
	 * @access public
	 * @name \Crux\Core\Is::readable()
	 * @package \Crux\Core\Is
	 * @param mixed $mixTest
	 * @return bool
	 * @static
	 * @uses \Crux\Core\Is::file()
	 * @uses is_readable()
	 */
	public static function readable($mixTest) : bool
	{
		// Make sure we have a file
		if (!self::file($mixTest)) {
			// We're done, not a file
			return false;
		}
		// Return the test
		return @is_readable($mixTest);
	}

	/**
	 * This method determines whether or not a variable is a scalar
	 * @access public
	 * @name \Crux\Core\Is::resource()
	 * @package \Crux\Core\Is
	 * @param mixed $mixTest
	 * @return bool
	 * @static
	 * @uses is_resource()
	 */
	public static function resource($mixTest) : bool
	{
		// Return the test
		return @is_resource($mixTest);
	}

	/**
	 * This method determines whether or not a variable is a scalar
	 * @access public
	 * @name \Crux\Core\Is::scalar()
	 * @package \Crux\Core\Is
	 * @param $mixTest
	 * @return bool
	 * @static
	 * @uses is_scalar()
	 */
	public static function scalar($mixTest) : bool
	{
		// Return the test
		return @is_scalar($mixTest);
	}

	/**
	 * This method determines whether or not a variable is a numerically indexed (sequential) array
	 * @access public
	 * @name \Crux\Core\Is::sequentialArray()
	 * @package \Crux\Core\Is
	 * @param $mixTest
	 * @return bool
	 * @static
	 * @uses \Crux\Core\Is::array()
	 * @uses array_keys()
	 * @uses is_int()
	 * @uses count()
	 */
	public static function sequentialArray($mixTest) : bool
	{
		// Make sure we have an array first
		if (!self::array($mixTest)) {
			// We're done, this is not an array
			return false;
		}
		// Create a counter
		$intCounter = 0;
		// Iterate over the array keys
		foreach (array_keys($mixTest) as $mixKey) {
			// Check for an integer
			if (is_int($mixKey)) {
				// Increment the counter
				++$intCounter;
			}
		}
		// Return the test
		return (count(array_keys($mixTest)) === $intCounter);
	}

	/**
	 * This method determines whether or not a variable is a serialized string
	 * @access public
	 * @name \Crux\Core\Is::serialized()
	 * @package \Crux\Core\Is
	 * @param $mixTest
	 * @return bool
	 * @static
	 * @uses \Crux\Core\Is::string()
	 * @uses trim()
	 * @uses preg_match()
	 * @uses in_array()
	 */
	public static function serialized($mixTest) : bool
	{
		// Make sure we have a string
		if (!self::string($mixTest)) {
			// We're done, we don't have a string
			return false;
		}
		// Trim the data
		$mixData = trim($mixTest);
		// Check for a prefix
		if ('N;' === $mixData) {
			// We're done
			return true;
		}
		// Check for common serialized entitiy descriptors
		if (!preg_match( '/^([adObis]):/', $mixData, $arrBadIons)) {
			// We're done
			return false;
		}
		// Check the bad prefixes
		if (in_array($arrBadIons[1], ['a', 'O', 's']) && preg_match("/^{$arrBadIons[1]}:[0-9]+:.*[;}]\$/s", $mixData)) {
			// We're done, this is serialized
			return true;
		} elseif (in_array($arrBadIons[1], ['b', 'i', 'd']) && preg_match("/^{$arrBadIons[1]}:[0-9.E-]+;\$/", $mixData)) {
			// We're done, this is serialized
			return true;
		} else {
			// We're done, the variable is not serialized
			return false;
		}
	}

	/**
	 * This method determines whether or not a variable is part of the Fluent\Sql workspace
	 * @access public
	 * @name \Crux\Core\Is::sqlColumn()
	 * @package \Crux\Core\Is
	 * @param $mixTest
	 * @return bool
	 * @uses \Crux\Core\Is::a()
	 */
	public static function sqlColumn($mixTest) : bool
	{
		// Return the test
		return self::a('\\Crux\\Fluent\\Sql\\Column', $mixTest);
	}

	/**
	 * This method determines whether or not a variable is an instance of stdClass
	 * @access public
	 * @name \Crux\Core\Is::standardClass()
	 * @package \Crux\Core\Is
	 * @param $mixTest
	 * @return bool
	 * @static
	 * @uses \Crux\Core\Is::object()
	 */
	public static function standardClass($mixTest) : bool
	{
		// Make sure we have an object
		if (!self::object($mixTest)) {
			// We're done, we don't have an object
			return false;
		}
		// Return the test
		return ($mixTest instanceof \stdClass);
	}

	/**
	 * This method determines whether or not a variable is a string
	 * @access public
	 * @name \Crux\Core\Is::string()
	 * @package \Crux\Core\Is
	 * @param $mixTest
	 * @return bool
	 * @static
	 * @uses is_string()
	 */
	public static function string($mixTest) : bool
	{
		// Return the test
		return @is_string($mixTest);
	}

	/**
	 * This method determines whether or not a variable is a symbolic path link
	 * @access public
	 * @name \Crux\Core\Is::symbolicLink()
	 * @package \Crux\Core\Is
	 * @param $mixTest
	 * @return bool
	 * @static
	 * @uses \Crux\Core\Is::string()
	 * @uses is_link()
	 */
	public static function symbolicLink($mixTest) : bool
	{
		// Make sure we have a string
		if (!self::string($mixTest)) {
			// We're done, we don't have a string
			return false;
		}
		// Return the test
		return @is_link($mixTest);
	}

	/**
	 * This method determines whether or not a variable is a path to an uploaded file
	 * @access public
	 * @name \Crux\Core\Is::uploadedFile()
	 * @package \Crux\Core\Is
	 * @param $mixTest
	 * @return bool
	 * @static
	 * @uses \Crux\Core\Is::string()
	 * @uses is_uploaded_file()
	 */
	public static function uploadedFile($mixTest) : bool
	{
		// Make sure we have a string
		if (!self::string($mixTest)) {
			// We're done, we don't have a string
			return false;
		}
		// Return the test
		return @is_uploaded_file($mixTest);
	}

	/**
	 * This method determines whether or not a variable is a variant
	 * @access public
	 * @name \Crux\Core\Is::variant()
	 * @package \Crux\Core\Is
	 * @param mixed $mixTest
	 * @return bool
	 * @static
	 */
	public static function variant($mixTest) : bool
	{
		// Return the test
		return ($mixTest instanceof Variant);
	}

	/**
	 * This method determines whether or not a variable implements the variant interface
	 * @access public
	 * @name \Crux\Core\Is::variantInterface()
	 * @package \Crux\Core\Is
	 * @param mixed $mixTest
	 * @return bool
	 * @static
	 */
	public static function variantInterface($mixTest) : bool
	{
		// Return the test
		return ($mixTest instanceof Variant);
	}

	/**
	 * This method determines whether or not a variable is a variant scalar value
	 * @access public
	 * @name \Crux\Core\Is::variantScalar()
	 * @package \Crux\Core\Is
	 * @param mixed $mixTest
	 * @return bool
	 * @static
	 */
	public static function variantScalar($mixTest) : bool
	{
		// Return the test
		return ($mixTest instanceof Variant\Scalar);
	}

	/**
	 * This method determines whether or not a variable is a variant list
	 * @access public
	 * @name \Crux\Core\Is::variantVector()
	 * @package \Crux\Core\Is
	 * @param mixed $mixTest
	 * @return bool
	 * @static
	 */
	public static function variantVector($mixTest) : bool
	{
		// Return the test
		return ($mixTest instanceof Variant\Vector);
	}

	/**
	 * This method determines whether or not a variable is a variant map
	 * @access public
	 * @name \Crux\Core\Is::variantMap()
	 * @package \Crux\Core\Is
	 * @param mixed $mixTest
	 * @return bool
	 * @static
	 */
	public static function variantMap($mixTest) : bool
	{
		// Return the test
		return ($mixTest instanceof Variant\Map);
	}

	/**
	 * This method determines whether or not a variable is a Vector
	 * @access public
	 * @name \Crux\Core\Is::vector()
	 * @package \Crux\Core\Is
	 * @param $mixTest
	 * @return bool
	 * @static
	 */
	public static function vector($mixTest) : bool
	{
		// Return the comparison
		return ($mixTest instanceof Collection\Vector);
	}

	/**
	 * This method determines whether or not a variable is a file and if it is writable or not
	 * @access public
	 * @name \Crux\Core\Is::writable()
	 * @package \Crux\Core\Is
	 * @param mixed $mixTest
	 * @return bool
	 * @static
	 * @uses \Crux\Core\Is::file()
	 * @uses is_writable()
	 */
	public static function writable($mixTest) : bool
	{
		// Make sure we have a file
		if (!self::file($mixTest)) {
			// We're done, not a file
			return false;
		}
		// Return the test
		return @is_writable($mixTest);
	}

	/**
	 * This method determines whether or not a variable is XML serialized
	 * @access public
	 * @name \Crux\Core\Is::xml()
	 * @package \Crux\Core\Is
	 * @param $mixTest
	 * @return bool
	 * @static
	 * @uses \Crux\Core\Is::string()
	 * @uses trim()
	 * @uses preg_match()
	 */
	public static function xml($mixTest) : bool
	{
		// Make sure we have a string
		if (!self::string($mixTest)) {
			// We're done, we don't have a string
			return false;
		}
		// Return the comparison
		return (self::string($mixTest) && preg_match('/^<\?xml.*?\?>/', trim($mixTest)));
	}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
} /// End Crux\Core\Is Class Definition //////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

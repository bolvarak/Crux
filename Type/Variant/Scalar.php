<?php

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Crux\Type\Variant Namespace //////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

namespace Crux\Type\Variant;

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Imports //////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

use Crux\Core;
use Crux\Crypto;
use Crux\Collection;
use Crux\Provider;
use Crux\Serialize;
use Crux\Type;

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Crux\Type\Variant\Scalar Class Definition ////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class Scalar implements \JsonSerializable
{
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Traits ///////////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	use Type\Variant;

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Constants ////////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This constant contains the type name for a binary string
	 * @name \Crux\Type\Variant\Scalar::Binary
	 * @package \Crux\Type\Variant\Scalar
	 * @static
	 * @var string
	 */
	const Binary = 'string';

	/**
	 * This constant contains the type name for a boolean
	 * @name \Crux\Type\Variant\Scalar::Boolean
	 * @package \Crux\Type\Variant\Scalar
	 * @static
	 * @var string
	 */
	const Boolean = 'bool';

	/**
	 * This constant contains the type name for a double value
	 * @name \Crux\Type\Variant\Scalar::Double
	 * @package \Crux\Type\Variant\Scalar
	 * @static
	 * @var string
	 */
	const Double = 'float';

	/**
	 * This constant contains the type name for a floating point value
	 * @name \Crux\Type\Variant\Scalar::Float
	 * @package \Crux\Type\Variant\Scalar
	 * @static
	 * @var string
	 */
	const Float = 'float';

	/**
	 * This constant contains the type name for an integer
	 * @name \Crux\Type\Variant\Scalar::Integer
	 * @package \Crux\Type\Variant\Scalar
	 * @static
	 * @var string
	 */
	const Integer = 'int';

	/**
	 * This constant contains the type name for a null value
	 * @name \Crux\Type\Variant\Scalar::Null
	 * @package \Crux\Type\Variant\Scalar
	 * @static
	 * @var string
	 */
	const Null = 'null';

	/**
	 * This constant contains the type name for a numerical value
	 * @name \Crux\Type\Variant\Scalar::Number
	 * @package \Crux\Type\Variant\Scalar
	 * @static
	 * @var string
	 */
	const Number = 'int';

	/**
	 * This constant contains the type name for a string
	 * @name \Crux\Type\Variant\Scalar::String
	 * @package \Crux\Type\Variant\Scalar
	 * @static
	 * @var string
	 */
	const String = 'string';

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Properties ///////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This property contain the original data in its original type
	 * @access private
	 * @name \Crux\Type\Variant\Scalar::$mData
	 * @package Crux\Type\Variant\Scalar
	 * @var mixed
	 */
	private $mData;

	/**
	 * This property contains the original type for the data
	 * @access private
	 * @name \Crux\Type\Variant\Scalar::$mOriginalTypeName
	 * @package \Crux\Type\Variant\Scalar
	 * @var string
	 */
	private $mOriginalTypeName = null;



	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Constructor //////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method instantiates a new variant value
	 * @access public
	 * @name \Crux\Type\Variant\Scalar::__constructor()
	 * @package \Crux\Type\Variant\Scalar
	 * @param mixed|null $mixSource
	 * @uses gettype()
	 */
	public function __construct($mixSource = null)
	{
		// Set the original data type for the source
		$this->mOriginalTypeName = gettype($mixSource);
		// Set the data into the instance
		$this->mData = $mixSource;
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Magic Methods ////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method allows for dynamic calling of conversion extensions
	 * @access public
	 * @name \Crux\Type\Variant\Scalar::__call()
	 * @package \Crux\Type\Variant\Scalar
	 * @param string $strMethod
	 * @param array $arrArguments
	 * @return mixed
	 * @throws Core\Exception\Type\Variant\Scalar
	 * @uses \Crux\Core\Api::$mVariantExtensions
	 * @uses substr()
	 * @uses strtolower()
	 * @uses substr_replace()
	 * @uses call_user_func_array()
	 * @uses sprintf()
	 */
	public function __call(string $strMethod, array $arrArguments)
	{
		// Check the method for an extension call
		if (strtolower(substr($strMethod, 0, 2)) === 'to') {
			// Iterate over the extensions
			foreach (Core\Api::$mVariantScalarExtensions as $strName => $fnCallback) {
				// Check the name
				if (strtolower(substr_replace($strMethod, '', 0, 2)) === strtolower($strName)) {
					// Add the data and instance to the argument list
					array_unshift($arrArguments, $this->mData, $this);
					// Execute the callback
					return call_user_func_array($fnCallback, $arrArguments);
				}
			}
			// No extension available, we're done
			throw new Core\Exception\Type\Variant\Scalar(sprintf('Extension [%s] does not exist.', substr_replace($strMethod, '', 0, 2)));
		} else {
			// No method or extension available, we're done
			throw new Core\Exception\Type\Variant\Scalar(sprintf('Method or Extension [%s] does not exist.', $strMethod));
		}
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Implementations //////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method converts the variant to a string when the instance is referenced as a string
	 * @access public
	 * @name \Crux\Type\Variant\Scalar::__toString()
	 * @package \Crux\Type\Variant\Scalar
	 * @return string
	 * @uses \Crux\Type\Variant\Scalar::toString()
	 */
	public function __toString() : string
	{
		// Return the string value of the variant
		return $this->toString();
	}

	/**
	 * This method returns a JSON encode-able value
	 * @access public
	 * @name \Crux\Type\Variant\Scalar::jsonSerialize()
	 * @package \Crux\Type\Variant\Scalar
	 * @return mixed
	 * @uses \Crux\Type\Variant\Scalar::getData()
	 */
	public function jsonSerialize()
	{
		// Return the original data
		return $this->getData();
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Public Static Methods ////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method adds a conversion extension to the construct
	 * @access public
	 * @name \Crux\Type\Variant\Scalar::addExtension()
	 * @package \Crux\Type\Variant\Scalar
	 * @param $strName
	 * @param callable $fnCallback
	 * @return void
	 * @static
	 * @uses \Crux\Core\Api::addVariantExtension()
	 */
	public static function addExtension($strName, callable $fnCallback)
	{
		// Add the extension
		Core\Api::addVariantExtension($strName, $fnCallback);
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Public Methods ///////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method determines whether or not the variant can be converted to a specific type
	 * @access public
	 * @name \Crux\Type\Variant\Scalar::can()
	 * @package \Crux\Type\Variant\Scalar
	 * @param int|string $mixType
	 * @return bool
	 * @uses settype()
	 */
	public function can($mixType) : bool
	{
		// Check the type
		if ($mixType === Core\Api::Array) {
			// Reset the type
			$mixType = 'array';
		}
		// Localize the data
		$mixData = $this->mData;
		// Return the type casting
		return @settype($mixData, $mixType);
	}

	/**
	 * This method searches the data for a specific value
	 * @access public
	 * @name \Crux\Type\Variant\Scalar::contains()
	 * @package \Crux\Type\Variant\Scalar
	 * @param string $strNeedle
	 * @param bool $blnCaseSensitive
	 * @return bool
	 * @uses \Crux\Type\Variant\Scalar::can()
	 * @uses \Crux\Type\Variant\Scalar::toString()
	 * @uses strpos()
	 * @uses stripos()
	 */
	public function contains(string $strNeedle, bool $blnCaseSensitive = false) : bool
	{
		// Check to see if we can convert to a string
		if (!$this->can('string')) {
			// We're done, we can't convert to a string
			return false;
		} elseif ($blnCaseSensitive && (strpos($this->toString(), $strNeedle) !== false)) {
			// We're done, we have a match
			return true;
		} elseif (!$blnCaseSensitive && (stripos($this->toString(), $strNeedle) !== false)) {
			// We're done, we have a match
			return true;
		} else {
			// We're done, there are no matches
			return false;
		}
	}

	/**
	 * This method provides type casting for the data
	 * @access public
	 * @name \Crux\Type\Variant\Scalar::convert()
	 * @package \Crux\Type\Variant\Scalar
	 * @param int|string $mixType
	 * @return mixed|null
	 * @throws Core\Exception\Type\Variant\Scalar
	 * @uses \Crux\Type\Variant\Scalar::can()
	 * @uses \Crux\Core\Is::boolean()
	 * @uses settype()
	 * @uses gettype()
	 */
	public function convert($mixType)
	{
		// Localize the data
		$mixData = $this->mData;
		// Check the type
		if ($mixType === Core\Api::Array) {
			// Reset the type
			$mixType = 'array';
		}
		// Make sure we can convert
		if (!$this->can($mixType)) {
			// We're done, send the error
			throw new Core\Exception\Type\Variant\Scalar(sprintf('Unable to convert source type [%s] to target type [%s]', gettype($this->mData), $mixType));
		}
		// Check the conversion request
		if ($mixType === self::Null) {
			// We're done, it's null
			return null;
		} elseif (($mixType === self::String) && Core\Is::boolean($this->mData)) {
			// Return the new string
			return ($this->mData ? 'true' : 'false');
		} else {
			settype($mixData, $mixType);
			// We're done, return the data
			return $mixData;
		}
	}

	/**
	 * This method performs a trim on the data and returns a variant copy, this is an alias to \Crux\Type\Variant\Scalar::trim()
	 * @access public
	 * @name \Crux\Type\Variant\Scalar::fullTrim()
	 * @package \Crux\Type\Variant\Scalar
	 * @param string $strCharacters [null]
	 * @return \Crux\Type\Variant\Scalar $this
	 * @throws Core\Exception\Type\Variant\Scalar
	 * @uses \Crux\Core\Is::null()
	 * @uses \Crux\Type\Variant\Scalar::toString()
	 * @uses \Crux\Type\Variant\Scalar::Factory()
	 * @uses trim()
	 */
	public function fullTrim(string $strCharacters = null) : Scalar
	{
		// Check for characters
		if (Core\Is::null($strCharacters)) {
			// Return the right trim
			return new self(trim($this->toString()));
		} else {
			// Return the right trim
			return new self(trim($this->toString(), $strCharacters));
		}
	}

	/**
	 * This method determines if the data is greater than an integer
	 * @access public
	 * @name \Crux\Type\Variant\Scalar::gt()
	 * @package \Crux\Type\Variant\Scalar
	 * @param int $intComparator
	 * @return bool
	 * @uses \Crux\Type\Variant\Scalar::toInt()
	 */
	public function gt(int $intComparator) : bool
	{
		// Return the comparison
		return ($this->toInt() > $intComparator);
	}

	/**
	 * This method determines if the data is greater than or equal to an integer
	 * @access public
	 * @name \Crux\Type\Variant\Scalar::gte()
	 * @package \Crux\Type\Variant\Scalar
	 * @param int $intComparator
	 * @return bool
	 * @uses \Crux\Type\Variant\Scalar::toInt()
	 */
	public function gte(int $intComparator) : bool
	{
		// Return the comparison
		return ($this->toInt() >= $intComparator);
	}

	/**
	 * This method determines if the data is greater than or equal to a floating point
	 * @access public
	 * @name \Crux\Type\Variant\Scalar::gteFloat()
	 * @package \Crux\Type\Variant\Scalar
	 * @param float $fltComparator
	 * @return bool
	 * @uses \Crux\Type\Variant\Scalar::toFloat()
	 */
	public function gteFloat(float $fltComparator) : bool
	{
		// Return the comparison
		return ($this->toFloat() >= $fltComparator);
	}

	/**
	 * This method determines if the data is greater than a floating point
	 * @access public
	 * @name \Crux\Type\Variant\Scalar::gtFloat()
	 * @package \Crux\Type\Variant\Scalar
	 * @param float $fltComparator
	 * @return bool
	 * @uses \Crux\Type\Variant\Scalar::toFloat()
	 */
	public function gtFloat(float $fltComparator) : bool
	{
		// Return the comparison
		return ($this->toFloat() > $fltComparator);
	}

	/**
	 * This method returns the boolean state of the data
	 * @access public
	 * @name \Crux\Type\Variant\Scalar::isBoolean()
	 * @package \Crux\Type\Variant\Scalar
	 * @return bool
	 * @uses \Crux\Core\Is::boolean()
	 */
	public function isBoolean() : bool
	{
		// Return the boolean state of the data
		return Core\Is::boolean($this->mData);
	}

	/**
	 * This method returns the empty state of the data
	 * @access public
	 * @name \Crux\Type\Variant\Scalar::isEmpty()
	 * @package \Crux\Type\Variant\Scalar
	 * @return bool
	 * @uses \Crux\Core\Is::empty()
	 */
	public function isEmpty() : bool
	{
		// Return the empty state of the data
		return Core\Is::empty($this->mData);
	}

	/**
	 * This method returns the encrypted state of the data
	 * @access public
	 * @name \Crux\Type\Variant\Scalar::isEncrypted()
	 * @package \Crux\Type\Variant\Scalar
	 * @return bool
	 * @uses \Crux\Core\Is::encrypted()
	 */
	public function isEncrypted() : bool
	{
		// Return the encrypted state
		return Core\Is::encrypted($this->mData);
	}

	/**
	 * This method returns the finite value state of the data
	 * @access public
	 * @name \Crux\Type\Variant\Scalar::isFinite()
	 * @package \Crux\Type\Variant\Scalar
	 * @return bool
	 * @uses \Crux\Type\Variant\Scalar::toFloat()
	 * @uses \Crux\Core\Is::finite()
	 */
	public function isFinite() : bool
	{
		// Return the finite state of the data
		return Core\Is::finite($this->toFloat());
	}

	/**
	 * This method returns the infinite value state of the data
	 * @access public
	 * @name \Crux\Type\Variant\Scalar::isInfinite()
	 * @package \Crux\Type\Variant\Scalar
	 * @return bool
	 * @uses \Crux\Type\Variant\Scalar::toFloat()
	 * @uses \Crux\Core\Is::infinite()
	 */
	public function isInfinite() : bool
	{
		// REturn the infinite state of the data
		return Core\Is::infinite($this->toFloat());
	}

	/**
	 * This method determines whether or not the original data value is in an array
	 * @access public
	 * @name \Crux\Type\Variant\Scalar::isIn()
	 * @package \Crux\Type\Variant\Scalar
	 * @param array<int, mixed> $arrHaystack
	 * @param bool $blnStrict [true]
	 * @return bool
	 * @uses \Crux\Type\Variant\Scalar::getData()
	 * @uses \Crux\Core\Is::in()
	 */
	public function isIn(array $arrHaystack, bool $blnStrict = true) : bool
	{
		// Return the test
		return Core\Is::in($this->getData(), $arrHaystack, $blnStrict);
	}

	/**
	 * This method returns the NaN state of the data
	 * @access public
	 * @name \Crux\Type\Variant\Scalar::isNaN()
	 * @package \Crux\Type\Variant\Scalar
	 * @return bool
	 * @uses \Crux\Type\Variant\Scalar::toFloat()
	 * @uses \Crux\Core\Is::nan()
	 */
	public function isNaN() : bool
	{
		// Return the numerical state
		return Core\Is::nan($this->toFloat());
	}

	/**
	 * This method returns the null state of the data
	 * @access public
	 * @name \Crux\Type\Variant\Scalar::isNull()
	 * @package \Crux\Type\Variant\Scalar
	 * @return bool
	 * @uses Core\Is::null()
	 */
	public function isNull() : bool
	{
		// Return the null state of the data
		return Core\Is::null($this->mData);
	}

	/**
	 * This method returns the numeric state of the data
	 * @access public
	 * @name \Crux\Type\Variant\Scalar::isNumeric()
	 * @package \Crux\Type\Variant\Scalar
	 * @return bool
	 * @uses \Crux\Core\Is::number()
	 */
	public function isNumeric() : bool
	{
		// Return the numeric state of the data
		return Core\Is::number($this->mData);
	}

	/**
	 * This method performs a left trim on the data and returns a variant copy
	 * @access public
	 * @name \Crux\Type\Variant\Scalar::leftTrim()
	 * @package \Crux\Type\Variant\Scalar
	 * @param string $strCharacters [null]
	 * @return \Crux\Type\Variant\Scalar $this
	 * @throws Core\Exception\Type\Variant\Scalar
	 * @uses \Crux\Core\Is::null()
	 * @uses \Crux\Type\Variant\Scalar::toString()
	 * @uses \Crux\Type\Variant\Scalar::Factory()
	 * @uses ltrim()
	 */
	public function leftTrim(string $strCharacters = null) : Scalar
	{
		// Check for characters
		if (Core\Is::null($strCharacters)) {
			// Return the right trim
			return new self(ltrim($this->toString()));
		} else {
			// Return the right trim
			return new self(ltrim($this->toString(), $strCharacters));
		}
	}

	/**
	 * This method returns the length of the string value of the data
	 * @access public
	 * @name \Crux\Type\Variant\Scalar::length()
	 * @package \Crux\Type\Variant\Scalar
	 * @return int
	 * @uses \Crux\Type\Variant\Scalar::toString()
	 * @uses strlen()
	 */
	public function length() : int{
		// Return the length of the string value
		return strlen($this->toString());
	}

	/**
	 * This method determines if the data is less than an integer
	 * @access public
	 * @name \Crux\Type\Variant\Scalar::lt()
	 * @package \Crux\Type\Variant\Scalar
	 * @param int $intComparator
	 * @return bool
	 * @uses \Crux\Type\Variant\Scalar::toInt()
	 */
	public function lt(int $intComparator) : bool
	{
		// Return the comparison
		return ($this->toInt() < $intComparator);
	}

	/**
	 * This method determines if the data is less than or equal to an integer
	 * @access public
	 * @name \Crux\Type\Variant\Scalar::lte()
	 * @package \Crux\Type\Variant\Scalar
	 * @param int $intComparator
	 * @return bool
	 * @uses \Crux\Type\Variant\Scalar::toInt()
	 */
	public function lte(int $intComparator) : bool
	{
		// Return the comparison
		return ($this->toInt() <= $intComparator);
	}

	/**
	 * This method determines if the data is less than a floating point
	 * @access public
	 * @name \Crux\Type\Variant\Scalar::lteFloat()
	 * @package \Crux\Type\Variant\Scalar
	 * @param float $fltComparator
	 * @return bool
	 * @uses \Crux\Type\Variant\Scalar::toFloat()
	 */
	public function lteFloat(float $fltComparator) : bool
	{
		// Return the comparison
		return ($this->toFloat() <= $fltComparator);
	}

	/**
	 * This method determines if the data is less than a floating point
	 * @access public
	 * @name \Crux\Type\Variant\Scalar::ltFloat()
	 * @package \Crux\Type\Variant\Scalar
	 * @param float $fltComparator
	 * @return bool
	 * @uses \Crux\Type\Variant\Scalar::toFloat()
	 */
	public function ltFloat(float $fltComparator) : bool
	{
		// Return the comparison
		return ($this->toFloat() < $fltComparator);
	}

	/**
	 * This method compares the data against a needle with optional strict comparison
	 * @access public
	 * @name \Crux\Type\Variant\Scalar::matches()
	 * @package \Crux\Type\Variant\Scalar
	 * @param mixed $mixNeedle
	 * @param bool $blnStrict [false]
	 * @return bool
	 * @uses \Crux\Core\Is::string()
	 * @uses stripos()
	 * @uses in_array()
	 * @uses \Crux\Type\Variant\Scalar::toString()
	 * @uses explode()
	 */
	public function matches($mixNeedle, bool $blnStrict = false) : bool
	{
		// Check for a string needle
		if (Core\Is::string($mixNeedle) && (stripos($mixNeedle, '||') !== false)) {
			// Check the data
			return in_array(($blnStrict ? $this->mData : $this->toString()), explode('||', $mixNeedle), $blnStrict);
		} else {
			// Check the data
			return ($blnStrict ? ($this->mData === $mixNeedle) : ($this->toString() == $mixNeedle));
		}
	}

	/**
	 * This method executes a modifier function on the data
	 * @access public
	 * @name \Crux\Type\Variant\Scalar::modify()
	 * @package \Crux\Type\Variant\Scalar
	 * @param callable $fnCallback
	 * @param array|\Crux\Collection\Vector $mixArguments [array()]
	 * @return \Crux\Type\Variant
	 * @throws Core\Exception\Type\Variant\Scalar
	 * @uses \Crux\Core\Is::vector()
	 * @uses \Crux\Collection\Vector::toArray()
	 * @uses array_unshift()
	 * @uses call_user_func_array()
	 * @uses \Crux\Type\Variant\Scalar::Factory()
	 */
	public function modify(callable $fnCallback, $mixArguments = []) : Type\Variant
	{
		// Check for arguments
		if (Core\Is::vector($mixArguments)) {
			// Reset the arguments
			$mixArguments = $mixArguments->toArray();
		}
		// Add the data to the arguments
		array_unshift($mixArguments, $this->mData);
		// Return the modifier
		return Type\Variant::Factory(call_user_func_array($fnCallback, $mixArguments));
	}

	/**
	 * This method makes a replacement on the string value of the data and returns a new Variant copy
	 * @access public
	 * @name \Crux\Type\Variant\Scalar::replace()
	 * @package \Crux\Type\Variant\Scalar
	 * @param string $strNeedle
	 * @param string $strReplacement
	 * @param bool $blnCaseSensitive [false]
	 * @return \Crux\Type\Variant\Scalar $this
	 * @throws Core\Exception\Type\Variant\Scalar
	 * @uses str_replace()
	 * @uses str_ireplace()
	 * @uses \Crux\Type\Variant\Scalar::toString()
	 * @uses \Crux\Type\Variant\Scalar::Factory()
	 */
	public function replace(string $strNeedle, string $strReplacement, bool $blnCaseSensitive = false) : Scalar
	{
		// Check for case sensitivity
		if ($blnCaseSensitive) {
			// Return the replacement
			return new self(str_replace($strNeedle, $strReplacement, $this->toString()));
		} else {
			// Return the replacement
			return new self(str_ireplace($strNeedle, $strReplacement, $this->toString()));
		}
	}

	/**
	 * This method makes a replacement on the string value of the data and returns a string copy
	 * @access public
	 * @name \Crux\Type\Variant\Scalar::replaceNonVolatile()
	 * @package \Crux\Type\Variant\Scalar
	 * @param string $strNeedle
	 * @param string $strReplacement
	 * @param bool $blnCaseSensitive [false]
	 * @return string
	 * @uses str_replace()
	 * @uses str_ireplace()
	 * @uses \Crux\Type\Variant\Scalar::toString()
	 */
	public function replaceNonVolatile(string $strNeedle, string $strReplacement, bool $blnCaseSensitive = false) : string
	{
		// Check for case sensitivity
		if ($blnCaseSensitive) {
			// Return the replacement
			return str_replace($strNeedle, $strReplacement, $this->toString());
		} else {
			// Return the replacement
			return str_ireplace($strNeedle, $strReplacement, $this->toString());
		}
	}

	/**
	 * This method performs a right trim on the data and returns a variant copy
	 * @access public
	 * @name \Crux\Type\Variant\Scalar::rightTrim()
	 * @package \Crux\Type\Variant\Scalar
	 * @param string $strCharacters [null]
	 * @return \Crux\Type\Variant\Scalar $this
	 * @throws Core\Exception\Type\Variant\Scalar
	 * @uses \Crux\Core\Is::null()
	 * @uses \Crux\Type\Variant\Scalar::toString()
	 * @uses \Crux\Type\Variant\Scalar::Factory()
	 * @uses rtrim()
	 */
	public function rightTrim(string $strCharacters = null) : Scalar
	{
		// Check for characters
		if (Core\Is::null($strCharacters)) {
			// Return the right trim
			return new self(rtrim($this->toString()));
		} else {
			// Return the right trim
			return new self(rtrim($this->toString(), $strCharacters));
		}
	}

	/**
	 * This method extracts a substring from the data and returns a Variant copy of the substring
	 * @access public
	 * @name \Crux\Type\Variant\Scalar::subString()
	 * @package \Crux\Type\Variant\Scalar
	 * @param int $intStart
	 * @param int $intLength [0]
	 * @return \Crux\Type\Variant\Scalar $this
	 * @throws Core\Exception\Type\Variant\Scalar
	 * @uses \Crux\Type\Variant\Scalar::toString()
	 * @uses substr()
	 * @uses \Crux\Type\Variant\Scalar::Factory()
	 */
	public function subString(int $intStart, int $intLength = 0) : Scalar
	{
		// Check for a length
		if ($intLength === 0) {
			// Return the substring
			return new self(substr($this->toString(), $intStart));
		} else {
			// Return the substring
			return new self(substr($this->toString(), $intStart, $intLength));
		}
	}

	/**
	 * This method performs a trim on the data and returns a variant copy
	 * @access public
	 * @name \Crux\Type\Variant\Scalar::trim()
	 * @package \Crux\Type\Variant\Scalar
	 * @param string $strCharacters [null]
	 * @return \Crux\Type\Variant\Scalar $this
	 * @throws Core\Exception\Type\Variant\Scalar
	 * @uses \Crux\Core\Is::null()
	 * @uses \Crux\Type\Variant\Scalar::toString()
	 * @uses \Crux\Type\Variant\Scalar::Factory()
	 * @uses trim()
	 */
	public function trim(string $strCharacters = null) : Scalar
	{
		// Check for characters
		if (Core\Is::null($strCharacters)) {
			// Return the right trim
			return new self(trim($this->toString()));
		} else {
			// Return the right trim
			return new self(trim($this->toString(), $strCharacters));
		}
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Conversion Methods ///////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method reads in the contents of a file, assuming the data is a file path
	 * @access public
	 * @name \Crux\Type\Variant\Scalar::toBinary()
	 * @package \Crux\Type\Variant\Scalar
	 * @return string
	 * @uses \Crux\Core\Is::file()
	 * @uses \Crux\Core\Is::symbolicLink()
	 * @uses file_get_contents()
	 * @uses readlink()
	 */
	public function toBinary() : string
	{
		// Check the data
		if (Core\Is::file($this->mData)) {
			// Return the file contents
			return file_get_contents($this->mData);
		} elseif (Core\Is::symbolicLink($this->mData)) {
			// Return the file contents
			return file_get_contents(readlink($this->mData));
		} else {
			// Nothing to do
			return null;
		}
	}

	/**
	 * This method converts the data to a boolean value
	 * @access public
	 * @name \Crux\Type\Variant\Scalar::toBool()
	 * @package \Crux\Type\Variant\Scalar
	 * @return bool
	 * @throws Core\Exception\Type\Variant\Scalar
	 * @uses \Crux\Type\Variant\Scalar::convert()
	 */
	public function toBool() : bool
	{
		// Return the boolean value
		return $this->convert(self::Boolean);
	}

	/**
	 * This method converts the variant to an array of booleans
	 * @access public
	 * @name \Crux\Type\Variant\Scalar::toBoolList()
	 * @package \Crux\Type\Variant\Scalar
	 * @param string $strDelimiter [',']
	 * @return array
	 * @uses \Crux\Type\Variant\Scalar::toString()
	 * @uses explode()
	 * @uses boolval()
	 * @uses array_push()
	 */
	public function toBoolList(string $strDelimiter = ',') : array
	{
		// Create the container
		$arrData = [];
		// Iterate over the explosion
		foreach (explode($strDelimiter, $this->toString()) as $strValue) {
			// Add the integer value to the array
			array_push($arrData, boolval($strValue));
		}
		// We're done, return the data
		return $arrData;
	}

	/**
	 * This method seamlessly encrypts the value in the instance using values from the configuration
	 * @access public
	 * @name \Crux\Type\Variant\Scalar::toCryptoText()
	 * @package \Crux\Type\Variant\Scalar
	 * @return string
	 * @uses \Crux\Core\Is::encrypted()
	 * @uses \Crux\Type\Variant\Scalar::toString()
	 * @uses \Crux\Crypto\OpenSSL::encryptWithPassphrase()
	 */
	public function toCryptoText() : string
	{
		// Check for encryption
		if (!Core\Is::encrypted($this->toString())) {
			// Return the encrypted string
			return Crypto\OpenSSL::encryptWithPassphrase($this->toString());
		}
		// Return the existing test
		return $this->toString();
	}

	/**
	 * This method converts the data to a floating point value
	 * @access public
	 * @name \Crux\Type\Variant\Scalar::toDouble()
	 * @package \Crux\Type\Variant\Scalar
	 * @return float
	 * @throws Core\Exception\Type\Variant\Scalar
	 * @uses \Crux\Type\Variant\Scalar::convert()
	 */
	public function toDouble() : float
	{
		// Return the floating point value
		return $this->convert(self::Double);
	}

	/**
	 * This method converts the variant to an array of double (floating) points
	 * @access public
	 * @name \Crux\Type\Variant\Scalar::toDoubleList()
	 * @package \Crux\Type\Variant\Scalar
	 * @param string $strDelimiter [',']
	 * @return array
	 * @uses \Crux\Type\Variant\Scalar::toString()
	 * @uses explode()
	 * @uses doubleval()
	 * @uses array_push()
	 */
	public function toDoubleList(string $strDelimiter = ',') : array
	{
		// Create the container
		$arrData = [];
		// Iterate over the explosion
		foreach (explode($strDelimiter, $this->toString()) as $strValue) {
			// Add the integer value to the array
			array_push($arrData, doubleval($strValue));
		}
		// We're done, return the data
		return $arrData;
	}

	/**
	 * This method converts the data to a floating point value
	 * @access public
	 * @name \Crux\Type\Variant\Scalar::toFloat()
	 * @package \Crux\Type\Variant\Scalar
	 * @return float
	 * @throws Core\Exception\Type\Variant\Scalar
	 * @uses \Crux\Type\Variant\Scalar::convert()
	 */
	public function toFloat() : float
	{
		// Return the floating point value
		return $this->convert(self::Float);
	}

	/**
	 * This method converts the variant to an array of floating points
	 * @access public
	 * @name \Crux\Type\Variant\Scalar::toFloatList()
	 * @package \Crux\Type\Variant\Scalar
	 * @param string $strDelimiter [',']
	 * @return array
	 * @uses \Crux\Type\Variant\Scalar::toString()
	 * @uses explode()
	 * @uses floatval()
	 * @uses array_push()
	 */
	public function toFloatList(string $strDelimiter = ',') : array
	{
		// Create the container
		$arrData = [];
		// Iterate over the explosion
		foreach (explode($strDelimiter, $this->toString()) as $strValue) {
			// Add the integer value to the array
			array_push($arrData, floatval($strValue));
		}
		// We're done, return the data
		return $arrData;
	}

	/**
	 * This method converts the data to an integer value
	 * @access public
	 * @name \Crux\Type\Variant\Scalar::toInt()
	 * @package \Crux\Type\Variant\Scalar
	 * @return int
	 * @throws Core\Exception\Type\Variant\Scalar
	 * @uses \Crux\Type\Variant\Scalar::convert()
	 */
	public function toInt() : int
	{
		// Return the integer value
		return $this->convert(self::Integer);
	}

	/**
	 * This method converts the variant to an array of integers
	 * @access public
	 * @name \Crux\Type\Variant\Scalar::toIntList()
	 * @package \Crux\Type\Variant\Scalar
	 * @param string $strDelimiter [',']
	 * @return array
	 * @uses \Crux\Type\Variant\Scalar::toString()
	 * @uses explode()
	 * @uses intval()
	 * @uses array_push()
	 */
	public function toIntList(string $strDelimiter = ',') : array
	{
		// Create the container
		$arrData = [];
		// Iterate over the explosion
		foreach (explode($strDelimiter, $this->toString()) as $strValue) {
			// Add the integer value to the array
			array_push($arrData, intval($strValue));
		}
		// We're done, return the data
		return $arrData;
	}

	/**
	 * This method converts the data to a lower case string and returns the Variant copy
	 * @access public
	 * @name \Crux\Type\Variant\Scalar::toLower()
	 * @package \Crux\Type\Variant\Scalar
	 * @return \Crux\Type\Variant\Scalar $this
	 * @throws Core\Exception\Type\Variant\Scalar
	 * @uses \Crux\Type\Variant\Scalar::convert()
	 */
	public function toLower() : Scalar
	{
		// Return the variant copy of lower case
		return new self(strtolower($this->toString()));
	}

	/**
	 * This method converts the variant to lower case and returns the string value
	 * @access public
	 * @name \Crux\Type\Variant\Scalar::toLowerString()
	 * @package \Crux\Type\Variant\Scalar
	 * @return string
	 * @uses \Crux\Type\Variant\Scalar::toLower()
	 * @uses \Crux\Type\Variant\Scalar::toString()
	 */
	public function toLowerString() : string
	{
		// Return the lower case string
		return $this->toLower()->toString();
	}

	/**
	 * This method converts the variant to a MySQL safe scalar value
	 * @access public
	 * @name \Crux\Type\Variant\Scalar::toMySqlString()
	 * @package \Crux\Type\Variant\Scalar
	 * @param bool $blnWildCard
	 * @return string
	 * @uses \Crux\Type\Variant\Scalar::isNull()
	 * @uses \Crux\Type\Variant\Scalar::isNumeric()
	 * @uses \Crux\Type\Variant\Scalar::isBoolean()
	 * @uses \Crux\Type\Variant\Scalar::toString()
	 * @uses \Crux\Type\Variant\Scalar::toInt()
	 * @uses addslashes()
	 * @uses sprintf()
	 */
	public function toMySqlString(bool $blnWildCard = false) : string
	{
		// Check the data
		if ($this->isNull()) {
			// We're done, return the SQL null value
			return 'NULL';
		} elseif ($this->isNumeric()) {
			// We're done, return the bare variable
			return $this->toString();
		} elseif ($this->isBoolean()) {
			// Return the numeric representation of the boolean
			return $this->toInt();
		} elseif ($blnWildCard) {
			// Return the quoted string
			return sprintf('\'%%%s%%\'', addslashes($this->toString()));
		} else {
			// Return the quoted string
			return sprintf('\'%s\'', addslashes($this->toString()));
		}
	}

	/**
	 * This method converts the variant to a PostgreSQL safe scalar value
	 * @access public
	 * @name \Crux\Type\Variant\Scalar::toPgSqlString()
	 * @package \Crux\Type\Variant\Scalar
	 * @param bool $blnWildCard [false]
	 * @return string
	 * @uses \Crux\Type\Variant\Scalar::isNull()
	 * @uses \Crux\Type\Variant\Scalar::isNumeric()
	 * @uses \Crux\Type\Variant\Scalar::isBoolean()
	 * @uses \Crux\Type\Variant\Scalar::toString()
	 * @uses sprintf()
	 */
	public function toPgSqlString(bool $blnWildCard = false) : string
	{
		// Check the data
		if ($this->isNull()) {
			// We're done, return the SQL null value
			return 'NULL';
		} elseif ($this->isNumeric()) {
			// We're done, return the bare variable
			return $this->toString();
		} elseif ($this->isBoolean()) {
			// We're done, return the boolean
			return ($this->toBool() ? 't' : 'f');
		} elseif ($blnWildCard) {
			// We're done, return the quoted string
			return sprintf('\'%%%s%%\'', str_replace('\'', '\'\'', $this->toString()));
		} else {
			// We're done, return the quoted string
			return sprintf('\'%s\'', str_replace('\'', '\'\'', $this->toString()));
		}
	}

	/**
	 * This method assumes the value in the instance is encrypted and seamlessly decrypts it
	 * @access public
	 * @name \Crux\Type\Variant\Scalar::toPlainText()
	 * @package \Crux\Type\Variant\Scalar
	 * @return string
	 * @uses \Crux\Core\Is::encrypted()
	 * @uses \Crux\Crypto\OpenSSL::decryptWithPassphrase()
	 * @uses \Crux\Type\Variant\Scalar::toString()
	 */
	public function toPlainText() : string
	{
		// Check for encryption
		if (Core\Is::encrypted($this->toString())) {
			// Decrypt the value
			return Crypto\OpenSSL::decryptWithPassphrase($this->toString());
		}
		// Return the string value
		return $this->toString();
	}

	/**
	 * This method returns the value as a sanitized string or the original data
	 * @access public
	 * @name \Crux\Type\Variant\Scalar::toSaneValue()
	 * @package \Crux\Type\Variant\Scalar
	 * @return mixed
	 * @uses \Crux\Type\Variant\Scalar::toString()
	 * @uses \Crux\Type\Variant\Scalar::getData()
	 * @uses is_string()
	 * @uses htmlspecialchars()
	 */
	public function toSaneValue()
	{
		// Check for a string on the original data
		if (is_string($this->getData())) {
			// Return the sanitized string
			return htmlspecialchars($this->toString());
		} else {
			// Return the original data
			return $this->getData();
		}
	}

	/**
	 * This method returns a SQL safe version of the variant
	 * @access public
	 * @name \Crux\Type\Variant\Scalar::toSql()
	 * @package \Crux\Type\Variant\Scalar
	 * @param string $strConnection
	 * @return string
	 * @uses \Crux\Provider\Sql\Engine::getConnection()
	 * @uses \Crux\Provider\Sql\Engine::escapeValue()
	 * @uses \Crux\Type\Variant\Scalar::toSaneValue()
	 */
	public function toSql(string $strConnection) : string
	{
		// Return the quoted value
		return Provider\Sql\Engine::getConnection($strConnection)->escapeValue($this->toSaneValue());
	}

	/**
	 * This method converts the data to a string value
	 * @access public
	 * @name \Crux\Type\Variant\Scalar::toString()
	 * @package \Crux\Type\Variant\Scalar
	 * @return string
	 * @throws Core\Exception\Type\Variant\Scalar
	 * @uses \Crux\Type\Variant\Scalar::convert()
	 */
	public function toString() : string
	{
		// Return the string value
		return $this->convert(self::String);
	}

	/**
	 * This method converts the data to an array of strings
	 * @access public
	 * @name \Crux\Type\Variant\Scalar::toStringList()
	 * @package \Crux\Type\Variant\Scalar
	 * @return array
	 * @throws Core\Exception\Type\Variant\Scalar
	 * @uses \Crux\Type\Variant\Scalar::convert()
	 */
	public function toStringList(string $strDelimiter = ',') : array
	{
		// Return the list
		return explode($strDelimiter, $this->toString());
	}

	/**
	 * This method converts the data to a UNIX timestamp
	 * @access public
	 * @name \Crux\Type\Variant\Scalar::toTime()
	 * @package \Crux\Type\Variant\Scalar
	 * @return int
	 * @uses \Crux\Type\Variant\Scalar::toString()
	 * @uses strtotime()
	 */
	public function toTime() : int
	{
		// Convert the data
		$intTime = strtotime($this->toString());
		// We're done
		return (($intTime === false) ? 0 : $intTime);
	}

	/**
	 * This method converts the variant to a title cased string and returns a variant copy
	 * @access public
	 * @name \Crux\Type\Variant\Scalar::toTitleCase()
	 * @package \Crux\Type\Variant\Scalar()
	 * @return \Crux\Type\Variant\Scalar $this
	 * @throws \Crux\Core\Exception\Type\Variant\Scalar
	 * @uses \Crux\Type\Variant\Scalar::toString()
	 * @uses \Crux\Core\Util::titleCase()
	 * @uses \Crux\Type\Variant\Scalar::Factory()
	 */
	public function toTitleCase() : Scalar
	{
		// Return the title cased variant copy
		return new self(Core\Util::titleCase($this->toString()));
	}

	/**
	 * This method converts the variant to a title cased string and returns its value
	 * @access public
	 * @name \Crux\Type\Variant\Scalar::toTitleCaseString()
	 * @package \Crux\Type\Variant\Scalar
	 * @return string
	 * @uses \Crux\Type\Variant\Scalar::toTitleCase()
	 * @uses \Crux\Type\Variant\Scalar::toString()
	 */
	public function toTitleCaseString() : string
	{
		// Return the title cased string
		return $this->toTitleCase()->toString();
	}

	/**
	 * This method converts the variant to upper case and returns a variant copy
	 * @access public
	 * @name \Crux\Type\Variant\Scalar::toUpper()
	 * @package \Crux\Type\Variant\Scalar()
	 * @return \Crux\Type\Variant\Scalar $this
	 * @throws \Crux\Core\Exception\Type\Variant\Scalar
	 * @uses \Crux\Type\Variant\Scalar::toString()
	 * @uses \Crux\Type\Variant\Scalar::Factory()
	 * @uses strtoupper()
	 */
	public function toUpper() : Scalar
	{
		// Return the upper case variant copy
		return new self(strtoupper($this->toString()));
	}

	/**
	 * This method converts the variant to upper case and returns the string value
	 * @access public
	 * @name \Crux\Type\Variant\Scalar::toUpperString()
	 * @package \Crux\Type\Variant\Scalar
	 * @return string
	 * @uses \Crux\Type\Variant\Scalar::toUpper()
	 * @uses \Crux\Type\Variant\Scalar::toString()
	 */
	public function toUpperString() : string
	{
		// Return the upper case string
		return $this->toUpper()->toString();
	}

	/**
	 * This method converts the variant to a variant list
	 * @access public
	 * @name \Crux\Type\Variant\Scalar::toVariantList()
	 * @package \Crux\Type\Variant\Scalar
	 * @param string $strDelimiter [',']
	 * @return \Crux\Type\Variant\Vector
	 * @uses \Crux\Type\Variant\ScalarList::__construct()
	 * @uses \Crux\Type\Variant\Scalar::toString()
	 * @uses \Crux\Type\Variant\ScalarList::add()
	 * @uses explode()
	 */
	public function toVariantList(string $strDelimiter = ',') : Vector
	{
		// Create the new variant list
		$varList = new Vector();
		// Iterate over the explosion
		foreach (explode($strDelimiter, $this->toString()) as $strValue) {
			// Add the  value to the variant
			$varList->add($strValue);
		}
		// We're done, return the variant list
		return $varList;
	}

	/**
	 * This method converts the variant to a vector of strings
	 * @access public
	 * @name \Crux\Type\Variant\Scalar::toVector()
	 * @package \Crux\Type\Variant\Scalar
	 * @param string $strDelimiter [',']
	 * @return \Crux\Collection\Vector
	 * @uses \Crux\Collection\Vector::__construct()
	 * @uses \Crux\Type\Variant\Scalar::toString()
	 * @uses \Crux\Collection\Vector::add()
	 * @uses explode()
	 */
	public function toVector(string $strDelimiter = ',') : Collection\Vector
	{
		// Create our new vector
		$vecList = new Collection\Vector();
		// Iterate over the explosion
		foreach (explode($strDelimiter, $this->toString()) as $strValue) {
			// Add the value to the vector
			$vecList->add($strValue);
		}
		// We're done, return the new vector
		return $vecList;
	}

	/**
	 * This method converts the variant to a vector of booleans
	 * @access public
	 * @name \Crux\Type\Variant\Scalar::toVectorBoolList()
	 * @package \Crux\Type\Variant\Scalar
	 * @param string $strDelimiter [',']
	 * @return \Crux\Collection\Vector
	 * @uses \Crux\Collection\Vector::__construct()
	 * @uses \Crux\Type\Variant\Scalar::toString()
	 * @uses \Crux\Collection\Vector::add()
	 * @uses explode()
	 * @uses boolval()
	 */
	public function toVectorBoolList(string $strDelimiter = ',') : Collection\Vector
	{
		// Create our new vector
		$vecData = new Collection\Vector();
		// Iterate over the explosion
		foreach (explode($strDelimiter, $this->toString()) as $strValue) {
			// Add the value to the vector
			$vecData->add(boolval($strValue));
		}
		// We're done, return the new vector
		return $vecData;
	}

	/**
	 * This method converts the variant to a vector of double (floating) points
	 * @access public
	 * @name \Crux\Type\Variant\Scalar::toVectorDoubleList()
	 * @package \Crux\Type\Variant\Scalar
	 * @param string $strDelimiter [',']
	 * @return \Crux\Collection\Vector
	 * @uses \Crux\Collection\Vector::__construct()
	 * @uses \Crux\Type\Variant\Scalar::toString()
	 * @uses \Crux\Collection\Vector::add()
	 * @uses explode()
	 * @uses doubleval()
	 */
	public function toVectorDoubleList(string $strDelimiter = ',') : Collection\Vector
	{
		// Create our new vector
		$vecData = new Collection\Vector();
		// Iterate over the explosion
		foreach (explode($strDelimiter, $this->toString()) as $strValue) {
			// Add the value to the vector
			$vecData->add(doubleval($strValue));
		}
		// We're done, return the new vector
		return $vecData;
	}

	/**
	 * This method converts the variant to a vector of floating points
	 * @access public
	 * @name \Crux\Type\Variant\Scalar::toVectorFloatList()
	 * @package \Crux\Type\Variant\Scalar
	 * @param string $strDelimiter [',']
	 * @return \Crux\Collection\Vector
	 * @uses \Crux\Collection\Vector::__construct()
	 * @uses \Crux\Type\Variant\Scalar::toString()
	 * @uses \Crux\Collection\Vector::add()
	 * @uses explode()
	 * @uses floatval()
	 */
	public function toVectorFloatList(string $strDelimiter = ',') : Collection\Vector
	{
		// Create our new vector
		$vecData = new Collection\Vector();
		// Iterate over the explosion
		foreach (explode($strDelimiter, $this->toString()) as $strValue) {
			// Add the value to the vector
			$vecData->add(floatval($strValue));
		}
		// We're done, return the new vector
		return $vecData;
	}

	/**
	 * This method converts the variant to a vector of integers
	 * @access public
	 * @name \Crux\Type\Variant\Scalar::toVectorIntList()
	 * @package \Crux\Type\Variant\Scalar
	 * @param string $strDelimiter [',']
	 * @return \Crux\Collection\Vector
	 * @uses \Crux\Collection\Vector::__construct()
	 * @uses \Crux\Type\Variant\Scalar::toString()
	 * @uses \Crux\Collection\Vector::add()
	 * @uses explode()
	 * @uses intval()
	 */
	public function toVectorIntList(string $strDelimiter = ',') : Collection\Vector
	{
		// Create our new vector
		$vecData = new Collection\Vector();
		// Iterate over the explosion
		foreach (explode($strDelimiter, $this->toString()) as $strValue) {
			// Add the value to the vector
			$vecData->add(intval($strValue));
		}
		// We're done, return the new vector
		return $vecData;
	}

	/**
	 * This method converts the variant to a vector of strings
	 * @access public
	 * @name \Crux\Type\Variant\Scalar::toVectorList()
	 * @package \Crux\Type\Variant\Scalar
	 * @param string $strDelimiter [',']
	 * @return \Crux\Collection\Vector
	 * @uses \Crux\Type\Variant\Scalar::toVector()
	 */
	public function toVectorList(string $strDelimiter = ',') : Collection\Vector
	{
		// Return the new vector
		return $this->toVector($strDelimiter);
	}

	/**
	 * This method converts the variant to a vector of strings
	 * @access public
	 * @name \Crux\Type\Variant\Scalar::toVectorStringList()
	 * @package \Crux\Type\Variant\Scalar
	 * @param string $strDelimiter [',']
	 * @return \Crux\Collection\Vector
	 * @uses \Crux\Type\Variant\Scalar::toVector()
	 */
	public function toVectorStringList(string $strDelimiter = ',') : Collection\Vector
	{
		// Return the new vector
		return $this->toVector($strDelimiter);
	}

	/**
	 * This method converts a variant value to an XML node
	 * @access public
	 * @name \Crux\Type\Variant\Scalar::toXml()
	 * @package \Crux\Type\Variant\Scalar
	 * @param string $strNode ['string']
	 * @param bool $blnIncludeHeaders [true]
	 * @return string
	 * @uses \Crux\Serialize\Xml::__construct()
	 * @uses \Crux\Serialize\Xml::rootNode()
	 * @uses \Crux\Serialize\Xml::defineDocument()
	 * @uses \Crux\Serialize\Xml::dontDefineDocument()
	 * @uses \Crux\Serialize\Xml::serialize()
	 * @uses \Crux\Type\Variant\Scalar::getData()
	 */
	public function toXml(string $strNode = 'variant', bool $blnIncludeHeaders = true) : string
	{
		// Instantiate our serialize
		$xmlSerializer = new Serialize\Xml();
		// Set the root node
		$xmlSerializer->rootNode($strNode);
		// Check the header flag
		if ($blnIncludeHeaders) {
			// Include the headers
			$xmlSerializer->defineDocument();
		} else {
			// Don't include the headers
			$xmlSerializer->dontDefineDocument();
		}
		// We're done, return the xml
		return $xmlSerializer->serialize($this->getData());
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Getters //////////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method returns the original data from the instance
	 * @access public
	 * @name \Crux\Type\Variant\Scalar::getData()
	 * @package \Crux\Type\Variant\Scalar
	 * @return mixed
	 */
	public function getData()
	{
		// Return the original data
		return $this->mData;
	}

	/**
	 * This method returns the original data type from the instance
	 * @access public
	 * @name \Crux\Type\Variant\Scalar::getOriginalType()
	 * @package \Crux\Type\Variant\Scalar
	 * @return string
	 */
	public function getOriginalType() : string
	{
		// Return the original data type from the instance
		return $this->mOriginalTypeName;
	}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
} /// End Crux\Type\Variant\Scalar Class Definition //////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

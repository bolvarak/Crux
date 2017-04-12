<?php

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Crux\Type Namespace //////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

namespace Crux\Type;

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Imports //////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

use Crux\Core;
use Crux\Crypto;
use Crux\Collection;
use Crux\Provider;
use Crux\Serialize;

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Crux\Type\Variant Class Definition ///////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class Variant implements IsVariant, \JsonSerializable
{
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Constants ////////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This constant contains the type name for a binary string
	 * @name \Crux\Type\Variant::Binary
	 * @package \Crux\Type\Variant
	 * @static
	 * @var string
	 */
	const Binary = 'string';

	/**
	 * This constant contains the type name for a boolean
	 * @name \Crux\Type\Variant::Boolean
	 * @package \Crux\Type\Variant
	 * @static
	 * @var string
	 */
	const Boolean = 'bool';

	/**
	 * This constant contains the type name for a double value
	 * @name \Crux\Type\Variant::Double
	 * @package \Crux\Type\Variant
	 * @static
	 * @var string
	 */
	const Double = 'float';

	/**
	 * This constant contains the type name for a floating point value
	 * @name \Crux\Type\Variant::Float
	 * @package \Crux\Type\Variant
	 * @static
	 * @var string
	 */
	const Float = 'float';

	/**
	 * This constant contains the type name for an integer
	 * @name \Crux\Type\Variant::Integer
	 * @package \Crux\Type\Variant
	 * @static
	 * @var string
	 */
	const Integer = 'int';

	/**
	 * This constant contains the type name for a null value
	 * @name \Crux\Type\Variant::Null
	 * @package \Crux\Type\Variant
	 * @static
	 * @var string
	 */
	const Null = 'null';

	/**
	 * This constant contains the type name for a numerical value
	 * @name \Crux\Type\Variant::Number
	 * @package \Crux\Type\Variant
	 * @static
	 * @var string
	 */
	const Number = 'int';

	/**
	 * This constant contains the type name for a string
	 * @name \Crux\Type\Variant::String
	 * @package \Crux\Type\Variant
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
	 * @name \Crux\Type\Variant::$mData
	 * @package Crux\Type\Variant
	 * @var mixed
	 */
	private $mData;

	/**
	 * This property contains the original type for the data
	 * @access private
	 * @name \Crux\Type\Variant::$mOriginalTypeName
	 * @package \Crux\Type\Variant
	 * @var string
	 */
	private $mOriginalTypeName = null;

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Factory///////////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method constructs a new variant from an existing variable
	 * @access public
	 * @name \Crux\Type\Variant::Factory()
	 * @package \Crux\Type\Variant
	 * @param mixed|null $mixSource
	 * @return \Crux\Type\Variant|\Crux\Type\VariantList|\Crux\Type\VariantMap
	 * @throws \Crux\Core\Exception\Type\Variant
	 * @uses is_array()
	 * @uses \Crux\Core\Is::associativeArra()
	 * @uses \Crux\Type\VariantMap::Factory()
	 * @uses \Crux\Type\VariantList::Factory()
	 * @uses \Crux\Type\Variant::Factory()
	 * @uses \Crux\Core\Is::string()
	 * @uses preg_match()
	 * @uses \Crux\Core\Is::scalar()
	 * @uses \Crux\Type\Variant::__construct()
	 * @uses \Crux\Core\Is::object()
	 */
	public static function Factory($mixSource = null)
	{
		// Make sure we have a scalar
		if ($mixSource === null) {
			// We're done
			return new Variant();
		} elseif (Core\Is::associativeArray($mixSource)) {
			// Return a new instance of VariantMap
			return VariantMap::fromMap($mixSource);
		} elseif (Core\Is::sequentialArray($mixSource)) {
			// Return a new instance of VariantList
			return VariantList::fromArray($mixSource);
		} elseif (Core\Is::map($mixSource)) {
			// Return a new instance of VariantMap
			return VariantMap::fromMap($mixSource);
		} elseif (Core\Is::vector($mixSource)) {
			// Return a new instance of VariantList
			return VariantList::fromVector($mixSource);
		} elseif (Core\Is::variant($mixSource)) {
			// We're done, we already have a variant
			return $mixSource;
		} elseif (Core\Is::string($mixSource) && preg_match('/^(true|false|on|off)$/', $mixSource)) {
			// Re-run the constructor
			return self::Factory(filter_var($mixSource, FILTER_VALIDATE_BOOLEAN));
		} elseif (Core\Is::scalar($mixSource)) {
			// We're done
			return new Variant($mixSource);
		} elseif (Core\Is::object($mixSource)) {
			// Return a new instance of VariantMap
			return VariantMap::fromObject($mixSource);
		} elseif (Core\Is::resource($mixSource)) {
			// Return the new variant
			return new Variant($mixSource);
		} else {
			// Throw an exception
			throw new Core\Exception\Type\Variant('Unable to convert data type [%s] to Variant, VariantList or VariantMap.', gettype($mixSource));
		}
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Constructor //////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method instantiates a new variant value
	 * @access public
	 * @name \Crux\Type\Variant::__constructor()
	 * @package \Crux\Type\Variant
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
	 * @name \Crux\Type\Variant::__call()
	 * @package \Crux\Type\Variant
	 * @param string $strMethod
	 * @param array $arrArguments
	 * @return mixed
	 * @throws Core\Exception\Type\Variant
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
			foreach (Core\Api::$mVariantExtensions as $strName => $fnCallback) {
				// Check the name
				if (strtolower(substr_replace($strMethod, '', 0, 2)) === strtolower($strName)) {
					// Add the data and instance to the argument list
					array_unshift($arrArguments, $this->mData, $this);
					// Execute the callback
					return call_user_func_array($fnCallback, $arrArguments);
				}
			}
			// No extension available, we're done
			throw new Core\Exception\Type\Variant(sprintf('Extension [%s] does not exist.', substr_replace($strMethod, '', 0, 2)));
		} else {
			// No method or extension available, we're done
			throw new Core\Exception\Type\Variant(sprintf('Method or Extension [%s] does not exist.', $strMethod));
		}
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Implementations //////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method converts the variant to a string when the instance is referenced as a string
	 * @access public
	 * @name \Crux\Type\Variant::__toString()
	 * @package \Crux\Type\Variant
	 * @return string
	 * @uses \Crux\Type\Variant::toString()
	 */
	public function __toString() : string
	{
		// Return the string value of the variant
		return $this->toString();
	}

	/**
	 * This method returns a JSON encodable value
	 * @access public
	 * @name \Crux\Type\Variant::jsonSerialize()
	 * @package \Crux\Type\Variant
	 * @return mixed
	 * @uses \Crux\Type\Variant::getData()
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
	 * @name \Crux\Type\Variant::addExtension()
	 * @package \Crux\Type\Variant
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
	 * @name \Crux\Type\Variant::can()
	 * @package \Crux\Type\Variant
	 * @param int|string $mixType
	 * @return bool
	 * @uses settype()
	 */
	public function can($mixType) : bool
	{
		// Check the type
		if ($mixType === self::Array) {
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
	 * @name \PHireowrks\Type\Variant::contains()
	 * @package \Crux\Type\Variant
	 * @param string $strNeedle
	 * @param bool $blnCaseSensitive
	 * @return bool
	 * @uses \Crux\Type\Variant::can()
	 * @uses \Crux\Type\Variant::toString()
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
	 * @name \Crux\Type\Variant::convert()
	 * @package \Crux\Type\Variant
	 * @param int|string $mixType
	 * @return mixed|null
	 * @throws Core\Exception\Type\Variant
	 * @uses \Crux\Type\Variant::can()
	 * @uses \Crux\Core\Is::boolean()
	 * @uses settype()
	 * @uses gettype()
	 */
	public function convert($mixType)
	{
		// Localize the data
		$mixData = $this->mData;
		// Check the type
		if ($mixType === self::Array) {
			// Reset the type
			$mixType = 'array';
		}
		// Make sure we can convert
		if (!$this->can($mixType)) {
			// We're done, send the error
			throw new Core\Exception\Type\Variant(sprintf('Unable to convert source type [%s] to target type [%s]', gettype($this->mData), $mixType));
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
	 * This method performs a trim on the data and returns a variant copy, this is an alias to \Crux\Type\Variant::trim()
	 * @access public
	 * @name \Crux\Type\Variant::fullTrim()
	 * @package \Crux\Type\Variant
	 * @param string $strCharacters [null]
	 * @return \Crux\Type\Variant
	 * @throws Core\Exception\Type\Variant
	 * @uses \Crux\Core\Is::null()
	 * @uses \Crux\Type\Variant::toString()
	 * @uses \Crux\Type\Variant::Factory()
	 * @uses trim()
	 */
	public function fullTrim(string $strCharacters = null) : Variant
	{
		// Check for characters
		if (Core\Is::null($strCharacters)) {
			// Return the right trim
			return Variant::Factory(trim($this->toString()));
		} else {
			// Return the right trim
			return Variant::Factory(trim($this->toString(), $strCharacters));
		}
	}

	/**
	 * This method determines if the data is greater than an integer
	 * @access public
	 * @name \Crux\Type\Variant::gt()
	 * @package \Crux\Type\Variant
	 * @param int $intComparator
	 * @return bool
	 * @uses \Crux\Type\Variant::toInt()
	 */
	public function gt(int $intComparator) : bool
	{
		// Return the comparison
		return ($this->toInt() > $intComparator);
	}

	/**
	 * This method determines if the data is greater than or equal to an integer
	 * @access public
	 * @name \Crux\Type\Variant::gte()
	 * @package \Crux\Type\Variant
	 * @param int $intComparator
	 * @return bool
	 * @uses \Crux\Type\Variant::toInt()
	 */
	public function gte(int $intComparator) : bool
	{
		// Return the comparison
		return ($this->toInt() >= $intComparator);
	}

	/**
	 * This method determines if the data is greater than or equal to a floating point
	 * @access public
	 * @name \Crux\Type\Variant::gteFloat()
	 * @package \Crux\Type\Variant
	 * @param float $fltComparator
	 * @return bool
	 * @uses \Crux\Type\Variant::toFloat()
	 */
	public function gteFloat(float $fltComparator) : bool
	{
		// Return the comparison
		return ($this->toFloat() >= $fltComparator);
	}

	/**
	 * This method determines if the data is greater than a floating point
	 * @access public
	 * @name \Crux\Type\Variant::gtFloat()
	 * @package \Crux\Type\Variant
	 * @param float $fltComparator
	 * @return bool
	 * @uses \Crux\Type\Variant::toFloat()
	 */
	public function gtFloat(float $fltComparator) : bool
	{
		// Return the comparison
		return ($this->toFloat() > $fltComparator);
	}

	/**
	 * This method returns the boolean state of the data
	 * @access public
	 * @name \Crux\Type\Variant::isBoolean()
	 * @package \Crux\Type\Variant
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
	 * @name \Crux\Type\Variant::isEmpty()
	 * @package \Crux\Type\Variant
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
	 * @name \Crux\Type\Variant::isEncrypted()
	 * @package \Crux\Type\Variant
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
	 * @name \Crux\Type\Variant::isFinite()
	 * @package \Crux\Type\Variant
	 * @return bool
	 * @uses \Crux\Type\Variant::toFloat()
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
	 * @name \Crux\Type\Variant::isInfinite()
	 * @package \Crux\Type\Variant
	 * @return bool
	 * @uses \Crux\Type\Variant::toFloat()
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
	 * @name \Crux\Type\Variant::isIn()
	 * @package \Crux\Type\Variant
	 * @param array<int, mixed> $arrHaystack
	 * @param bool $blnStrict [true]
	 * @return bool
	 * @uses \Crux\Type\Variant::getData()
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
	 * @name \Crux\Type\Variant::isNaN()
	 * @package \Crux\Type\Variant
	 * @return bool
	 * @uses \Crux\Type\Variant::toFloat()
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
	 * @name \Crux\Type\Variant::isNull()
	 * @package \Crux\Type\Variant
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
	 * @name \Crux\Type\Variant::isNumeric()
	 * @package \Crux\Type\Variant
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
	 * @name \Crux\Type\Variant::leftTrim()
	 * @package \Crux\Type\Variant
	 * @param string $strCharacters [null]
	 * @return \Crux\Type\Variant
	 * @throws Core\Exception\Type\Variant
	 * @uses \Crux\Core\Is::null()
	 * @uses \Crux\Type\Variant::toString()
	 * @uses \Crux\Type\Variant::Factory()
	 * @uses ltrim()
	 */
	public function leftTrim(string $strCharacters = null) : Variant
	{
		// Check for characters
		if (Core\Is::null($strCharacters)) {
			// Return the right trim
			return Variant::Factory(ltrim($this->toString()));
		} else {
			// Return the right trim
			return Variant::Factory(ltrim($this->toString(), $strCharacters));
		}
	}

	/**
	 * This method returns the length of the string value of the data
	 * @access public
	 * @name \Crux\Type\Variant::length()
	 * @package \Crux\Type\Variant
	 * @return int
	 * @uses \Crux\Type\Variant::toString()
	 * @uses strlen()
	 */
	public function length() : int{
		// Return the length of the string value
		return strlen($this->toString());
	}

	/**
	 * This method determines if the data is less than an integer
	 * @access public
	 * @name \Crux\Type\Variant::lt()
	 * @package \Crux\Type\Variant
	 * @param int $intComparator
	 * @return bool
	 * @uses \Crux\Type\Variant::toInt()
	 */
	public function lt(int $intComparator) : bool
	{
		// Return the comparison
		return ($this->toInt() < $intComparator);
	}

	/**
	 * This method determines if the data is less than or equal to an integer
	 * @access public
	 * @name \Crux\Type\Variant::lte()
	 * @package \Crux\Type\Variant
	 * @param int $intComparator
	 * @return bool
	 * @uses \Crux\Type\Variant::toInt()
	 */
	public function lte(int $intComparator) : bool
	{
		// Return the comparison
		return ($this->toInt() <= $intComparator);
	}

	/**
	 * This method determines if the data is less than a floating point
	 * @access public
	 * @name \Crux\Type\Variant::lteFloat()
	 * @package \Crux\Type\Variant
	 * @param float $fltComparator
	 * @return bool
	 * @uses \Crux\Type\Variant::toFloat()
	 */
	public function lteFloat(float $fltComparator) : bool
	{
		// Return the comparison
		return ($this->toFloat() <= $fltComparator);
	}

	/**
	 * This method determines if the data is less than a floating point
	 * @access public
	 * @name \Crux\Type\Variant::ltFloat()
	 * @package \Crux\Type\Variant
	 * @param float $fltComparator
	 * @return bool
	 * @uses \Crux\Type\Variant::toFloat()
	 */
	public function ltFloat(float $fltComparator) : bool
	{
		// Return the comparison
		return ($this->toFloat() < $fltComparator);
	}

	/**
	 * This method compares the data against a needle with optional strict comparison
	 * @access public
	 * @name \Crux\Type\Variant::matches()
	 * @package \Crux\Type\Variant
	 * @param mixed $mixNeedle
	 * @param bool $blnStrict [false]
	 * @return bool
	 * @uses \Crux\Core\Is::string()
	 * @uses stripos()
	 * @uses in_array()
	 * @uses \Crux\Type\Variant::toString()
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
	 * @name \Crux\Type\Variant::modify()
	 * @package \Crux\Type\Variant
	 * @param callable $fnCallback
	 * @param array|\Crux\Collection\Vector $mixArguments [array()]
	 * @return \Crux\Type\Variant
	 * @throws Core\Exception\Type\Variant
	 * @uses \Crux\Core\Is::vector()
	 * @uses \Crux\Collection\Vector::toArray()
	 * @uses array_unshift()
	 * @uses call_user_func_array()
	 * @uses \Crux\Type\Variant::Factory()
	 */
	public function modify(callable $fnCallback, $mixArguments = []) : Variant
	{
		// Check for arguments
		if (Core\Is::vector($mixArguments)) {
			// Reset the arguments
			$mixArguments = $mixArguments->toArray();
		}
		// Add the data to the arguments
		array_unshift($mixArguments, $this->mData);
		// Return the modifier
		return Variant::Factory(call_user_func_array($fnCallback, $mixArguments));
	}

	/**
	 * This method makes a replacement on the string value of the data and returns a new Variant copy
	 * @access public
	 * @name \Crux\Type\Variant::replace()
	 * @package \Crux\Type\Variant
	 * @param string $strNeedle
	 * @param string $strReplacement
	 * @param bool $blnCaseSensitive [false]
	 * @return \Crux\Type\Variant
	 * @throws Core\Exception\Type\Variant
	 * @uses str_replace()
	 * @uses str_ireplace()
	 * @uses \Crux\Type\Variant::toString()
	 * @uses \Crux\Type\Variant::Factory()
	 */
	public function replace(string $strNeedle, string $strReplacement, bool $blnCaseSensitive = false) : Variant
	{
		// Check for case sensitivity
		if ($blnCaseSensitive) {
			// Return the replacement
			return Variant::Factory(str_replace($strNeedle, $strReplacement, $this->toString()));
		} else {
			// Return the replacement
			return Variant::Factory(str_ireplace($strNeedle, $strReplacement, $this->toString()));
		}
	}

	/**
	 * This method makes a replacement on the string value of the data and returns a string copy
	 * @access public
	 * @name \Crux\Type\Variant::replaceNonVolatile()
	 * @package \Crux\Type\Variant
	 * @param string $strNeedle
	 * @param string $strReplacement
	 * @param bool $blnCaseSensitive [false]
	 * @return string
	 * @uses str_replace()
	 * @uses str_ireplace()
	 * @uses \Crux\Type\Variant::toString()
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
	 * @name \Crux\Type\Variant::rightTrim()
	 * @package \Crux\Type\Variant
	 * @param string $strCharacters [null]
	 * @return \Crux\Type\Variant
	 * @throws Core\Exception\Type\Variant
	 * @uses \Crux\Core\Is::null()
	 * @uses \Crux\Type\Variant::toString()
	 * @uses \Crux\Type\Variant::Factory()
	 * @uses rtrim()
	 */
	public function rightTrim(string $strCharacters = null) : Variant
	{
		// Check for characters
		if (Core\Is::null($strCharacters)) {
			// Return the right trim
			return Variant::Factory(rtrim($this->toString()));
		} else {
			// Return the right trim
			return Variant::Factory(rtrim($this->toString(), $strCharacters));
		}
	}

	/**
	 * This method extracts a substring from the data and returns a Variant copy of the substring
	 * @access public
	 * @name \Crux\Type\Variant::subString()
	 * @package \Crux\Type\Variant
	 * @param int $intStart
	 * @param int $intLength [0]
	 * @return \Crux\Type\Variant
	 * @throws Core\Exception\Type\Variant
	 * @uses \Crux\Type\Variant::toString()
	 * @uses substr()
	 * @uses \Crux\Type\Variant::Factory()
	 */
	public function subString(int $intStart, int $intLength = 0) : Variant
	{
		// Check for a length
		if ($intLength === 0) {
			// Return the substring
			return Variant::Factory(substr($this->toString(), $intStart));
		} else {
			// Return the substring
			return Variant::Factory(substr($this->toString(), $intStart, $intLength));
		}
	}

	/**
	 * This method performs a trim on the data and returns a variant copy
	 * @access public
	 * @name \Crux\Type\Variant::trim()
	 * @package \Crux\Type\Variant
	 * @param string $strCharacters [null]
	 * @return \Crux\Type\Variant
	 * @throws Core\Exception\Type\Variant
	 * @uses \Crux\Core\Is::null()
	 * @uses \Crux\Type\Variant::toString()
	 * @uses \Crux\Type\Variant::Factory()
	 * @uses trim()
	 */
	public function trim(string $strCharacters = null) : Variant
	{
		// Check for characters
		if (Core\Is::null($strCharacters)) {
			// Return the right trim
			return Variant::Factory(trim($this->toString()));
		} else {
			// Return the right trim
			return Variant::Factory(trim($this->toString(), $strCharacters));
		}
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Conversion Methods ///////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method reads in the contents of a file, assuming the data is a file path
	 * @access public
	 * @name \Crux\Type\Variant::toBinary()
	 * @package \Crux\Type\Variant
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
	 * @name \Crux\Type\Variant::toBool()
	 * @package \Crux\Type\Variant
	 * @return bool
	 * @throws Core\Exception\Type\Variant
	 * @uses \Crux\Type\Variant::convert()
	 */
	public function toBool() : bool
	{
		// Return the boolean value
		return $this->convert(self::Boolean);
	}

	/**
	 * This method converts the variant to an array of booleans
	 * @access public
	 * @name \Crux\Type\Variant::toBoolList()
	 * @package \Crux\Type\Variant
	 * @param string $strDelimiter [',']
	 * @return array
	 * @uses \Crux\Type\Variant::toString()
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
	 * @name \Crux\Type\Variant::toCryptoText()
	 * @package \Crux\Type\Variant
	 * @return string
	 * @uses \Crux\Core\Is::encrypted()
	 * @uses \Crux\Type\Variant::toString()
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
	 * @name \Crux\Type\Variant::toDouble()
	 * @package \Crux\Type\Variant
	 * @return float
	 * @throws Core\Exception\Type\Variant
	 * @uses \Crux\Type\Variant::convert()
	 */
	public function toDouble() : float
	{
		// Return the floating point value
		return $this->convert(self::Double);
	}

	/**
	 * This method converts the variant to an array of double (floating) points
	 * @access public
	 * @name \Crux\Type\Variant::toDoubleList()
	 * @package \Crux\Type\Variant
	 * @param string $strDelimiter [',']
	 * @return array
	 * @uses \Crux\Type\Variant::toString()
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
	 * @name \Crux\Type\Variant::toFloat()
	 * @package \Crux\Type\Variant
	 * @return float
	 * @throws Core\Exception\Type\Variant
	 * @uses \Crux\Type\Variant::convert()
	 */
	public function toFloat() : float
	{
		// Return the floating point value
		return $this->convert(self::Float);
	}

	/**
	 * This method converts the variant to an array of floating points
	 * @access public
	 * @name \Crux\Type\Variant::toFloatList()
	 * @package \Crux\Type\Variant
	 * @param string $strDelimiter [',']
	 * @return array
	 * @uses \Crux\Type\Variant::toString()
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
	 * @name \Crux\Type\Variant::toInt()
	 * @package \Crux\Type\Variant
	 * @return int
	 * @throws Core\Exception\Type\Variant
	 * @uses \Crux\Type\Variant::convert()
	 */
	public function toInt() : int
	{
		// Return the integer value
		return $this->convert(self::Integer);
	}

	/**
	 * This method converts the variant to an array of integers
	 * @access public
	 * @name \Crux\Type\Variant::toIntList()
	 * @package \Crux\Type\Variant
	 * @param string $strDelimiter [',']
	 * @return array
	 * @uses \Crux\Type\Variant::toString()
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
	 * @name \Crux\Type\Variant::toLower()
	 * @package \Crux\Type\Variant
	 * @return \Crux\Type\Variant
	 * @throws Core\Exception\Type\Variant
	 * @uses \Crux\Type\Variant::convert()
	 */
	public function toLower() : Variant
	{
		// Return the variant copy of lower case
		return Variant::Factory(strtolower($this->toString()));
	}

	/**
	 * This method converts the variant to lower case and returns the string value
	 * @access public
	 * @name \Crux\Type\Variant::toLowerString()
	 * @package \Crux\Type\Variant
	 * @return string
	 * @uses \Crux\Type\Variant::toLower()
	 * @uses \Crux\Type\Variant::toString()
	 */
	public function toLowerString() : string
	{
		// Return the lower case string
		return $this->toLower()->toString();
	}

	/**
	 * This method converts the variant to a MySQL safe scalar value
	 * @access public
	 * @name \Crux\Type\Variant::toMySqlString()
	 * @package \Crux\Type\Variant
	 * @param bool $blnWildCard
	 * @return string
	 * @uses \Crux\Type\Variant::isNull()
	 * @uses \Crux\Type\Variant::isNumeric()
	 * @uses \Crux\Type\Variant::isBoolean()
	 * @uses \Crux\Type\Variant::toString()
	 * @uses \Crux\Type\Variant::toInt()
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
	 * @name \Crux\Type\Variant::toPgSqlString()
	 * @package \Crux\Type\Variant
	 * @param bool $blnWildCard [false]
	 * @return string
	 * @uses \Crux\Type\Variant::isNull()
	 * @uses \Crux\Type\Variant::isNumeric()
	 * @uses \Crux\Type\Variant::isBoolean()
	 * @uses \Crux\Type\Variant::toString()
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
	 * @name \Crux\Type\Variant::toPlainText()
	 * @package \Crux\Type\Variant
	 * @return string
	 * @uses \Crux\Core\Is::encrypted()
	 * @uses \Crux\Crypto\OpenSSL::decryptWithPassphrase()
	 * @uses \Crux\Type\Variant::toString()
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
	 * @name \Crux\Type\Variant::toSaneValue()
	 * @package \Crux\Type\Variant
	 * @return mixed
	 * @uses \Crux\Type\Variant::toString()
	 * @uses \Crux\Type\Variant::getData()
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
	 * @name \Crux\Type\Variant::toSql()
	 * @package \Crux\Type\Variant
	 * @param string $strConnection
	 * @return string
	 * @uses \Crux\Provider\Sql\Engine::getConnection()
	 * @uses \Crux\Provider\Sql\Engine::escapeValue()
	 * @uses \Crux\Type\Variant::toSaneValue()
	 */
	public function toSql(string $strConnection) : string
	{
		// Return the quoted value
		return Provider\Sql\Engine::getConnection($strConnection)->escapeValue($this->toSaneValue());
	}

	/**
	 * This method converts the data to a string value
	 * @access public
	 * @name \Crux\Type\Variant::toString()
	 * @package \Crux\Type\Variant
	 * @return string
	 * @throws Core\Exception\Type\Variant
	 * @uses \Crux\Type\Variant::convert()
	 */
	public function toString() : string
	{
		// Return the string value
		return $this->convert(self::String);
	}

	/**
	 * This method converts the data to an array of strings
	 * @access public
	 * @name \Crux\Type\Variant::toStringList()
	 * @package \Crux\Type\Variant
	 * @return array
	 * @throws Core\Exception\Type\Variant
	 * @uses \Crux\Type\Variant::convert()
	 */
	public function toStringList(string $strDelimiter = ',') : array
	{
		// Return the list
		return explode($strDelimiter, $this->toString());
	}

	/**
	 * This method converts the data to a UNIX timestamp
	 * @access public
	 * @name \Crux\Type\Variant::toTime()
	 * @package \Crux\Type\Variant
	 * @return int
	 * @uses \Crux\Type\Variant::toString()
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
	 * @name \Crux\Type\Variant::toTitleCase()
	 * @package \Crux\Type\Variant()
	 * @return \Crux\Type\Variant
	 * @throws \Crux\Core\Exception\Type\Variant
	 * @uses \Crux\Type\Variant::toString()
	 * @uses \Crux\Core\Util::titleCase()
	 * @uses \Crux\Type\Variant::Factory()
	 */
	public function toTitleCase() : Variant
	{
		// Return the title cased variant copy
		return Variant::Factory(Core\Util::titleCase($this->toString()));
	}

	/**
	 * This method converts the variant to a title cased string and returns its value
	 * @access public
	 * @name \Crux\Type\Variant::toTitleCaseString()
	 * @package \Crux\Type\Variant
	 * @return string
	 * @uses \Crux\Type\Variant::toTitleCase()
	 * @uses \Crux\Type\Variant::toString()
	 */
	public function toTitleCaseString() : string
	{
		// Return the title cased string
		return $this->toTitleCase()->toString();
	}

	/**
	 * This method converts the variant to upper case and returns a variant copy
	 * @access public
	 * @name \Crux\Type\Variant::toUpper()
	 * @package \Crux\Type\Variant()
	 * @return \Crux\Type\Variant
	 * @throws \Crux\Core\Exception\Type\Variant
	 * @uses \Crux\Type\Variant::toString()
	 * @uses \Crux\Type\Variant::Factory()
	 * @uses strtoupper()
	 */
	public function toUpper() : Variant
	{
		// Return the upper case variant copy
		return Variant::Factory(strtoupper($this->toString()));
	}

	/**
	 * This method converts the variant to upper case and returns the string value
	 * @access public
	 * @name \Crux\Type\Variant::toUpperString()
	 * @package \Crux\Type\Variant
	 * @return string
	 * @uses \Crux\Type\Variant::toUpper()
	 * @uses \Crux\Type\Variant::toString()
	 */
	public function toUpperString() : string
	{
		// Return the upper case string
		return $this->toUpper()->toString();
	}

	/**
	 * This method converts the variant to a variant list
	 * @access public
	 * @name \Crux\Type\Variant::toVariantList()
	 * @package \Crux\Type\Variant
	 * @param string $strDelimiter [',']
	 * @return \Crux\Type\VariantList
	 * @uses \Crux\Type\VariantList::__construct()
	 * @uses \Crux\Type\Variant::toString()
	 * @uses \Crux\Type\VariantList::add()
	 * @uses explode()
	 */
	public function toVariantList(string $strDelimiter = ',') : VariantList
	{
		// Create the new variant list
		$varList = new VariantList();
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
	 * @name \Crux\Type\Variant::toVector()
	 * @package \Crux\Type\Variant
	 * @param string $strDelimiter [',']
	 * @return \Crux\Collection\Vector
	 * @uses \Crux\Collection\Vector::__construct()
	 * @uses \Crux\Type\Variant::toString()
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
	 * @name \Crux\Type\Variant::toVectorBoolList()
	 * @package \Crux\Type\Variant
	 * @param string $strDelimiter [',']
	 * @return \Crux\Collection\Vector
	 * @uses \Crux\Collection\Vector::__construct()
	 * @uses \Crux\Type\Variant::toString()
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
	 * @name \Crux\Type\Variant::toVectorDoubleList()
	 * @package \Crux\Type\Variant
	 * @param string $strDelimiter [',']
	 * @return \Crux\Collection\Vector
	 * @uses \Crux\Collection\Vector::__construct()
	 * @uses \Crux\Type\Variant::toString()
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
	 * @name \Crux\Type\Variant::toVectorFloatList()
	 * @package \Crux\Type\Variant
	 * @param string $strDelimiter [',']
	 * @return \Crux\Collection\Vector
	 * @uses \Crux\Collection\Vector::__construct()
	 * @uses \Crux\Type\Variant::toString()
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
	 * @name \Crux\Type\Variant::toVectorIntList()
	 * @package \Crux\Type\Variant
	 * @param string $strDelimiter [',']
	 * @return \Crux\Collection\Vector
	 * @uses \Crux\Collection\Vector::__construct()
	 * @uses \Crux\Type\Variant::toString()
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
	 * @name \Crux\Type\Variant::toVectorList()
	 * @package \Crux\Type\Variant
	 * @param string $strDelimiter [',']
	 * @return \Crux\Collection\Vector
	 * @uses \Crux\Type\Variant::toVector()
	 */
	public function toVectorList(string $strDelimiter = ',') : Collection\Vector
	{
		// Return the new vector
		return $this->toVector($strDelimiter);
	}

	/**
	 * This method converts the variant to a vector of strings
	 * @access public
	 * @name \Crux\Type\Variant::toVectorStringList()
	 * @package \Crux\Type\Variant
	 * @param string $strDelimiter [',']
	 * @return \Crux\Collection\Vector
	 * @uses \Crux\Type\Variant::toVector()
	 */
	public function toVectorStringList(string $strDelimiter = ',') : Collection\Vector
	{
		// Return the new vector
		return $this->toVector($strDelimiter);
	}

	/**
	 * This method converts a variant value to an XML node
	 * @access public
	 * @name \Crux\Type\Variant::toXml()
	 * @package \Crux\Type\Variant
	 * @param string $strNode ['string']
	 * @param bool $blnIncludeHeaders [true]
	 * @return string
	 * @uses \Crux\Serialize\Xml::__construct()
	 * @uses \Crux\Serialize\Xml::rootNode()
	 * @uses \Crux\Serialize\Xml::defineDocument()
	 * @uses \Crux\Serialize\Xml::dontDefineDocument()
	 * @uses \Crux\Serialize\Xml::serialize()
	 * @uses \Crux\Type\Variant::getData()
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
	 * @name \Crux\Type\Variant::getData()
	 * @package \Crux\Type\Variant
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
	 * @name \Crux\Type\Variant::getOriginalType()
	 * @package \Crux\Type\Variant
	 * @return string
	 */
	public function getOriginalType() : string
	{
		// Return the original data type from the instance
		return $this->mOriginalTypeName;
	}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
} /// End Crux\Type\Variant Class Definition /////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

<?php

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Crux\Core Namespace //////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

namespace Crux\Core;

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Crux\Core\Api Class Definition ///////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class Api
{
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Constants ////////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This constant defines an original variable type of Array
	 * @name \Crux\Core\Api::Array
	 * @package \Crux\Core\Api
	 * @static
	 * @var int
	 */
	const Array = 0x01;

	/**
	 * This constant defines the original variable type as a custom class
	 * @name \Crux\Core\Api::Custom
	 * @package \Crux\Core\Api
	 * @static
	 * @var int
	 */
	const Custom = 0x02;

	/**
	 * This constant defines an original variable type of \Crux\Collection\Map
	 * @name \Crux\Core\Api::Map
	 * @package \Crux\Core\Api
	 * @static
	 * @var int
	 */
	const Map = 0x03;

	/**
	 * This constant defines an original variable type of \stdClass
	 * @name \Crux\Core\Api::Object
	 * @package \Crux\Core\Api
	 * @static
	 * @var int
	 */
	const Object = 0x04;

	/**
	 * This constant defines an original variable type of boolean, float, integer or string
	 * @name \Crux\Core\Api::Scalar
	 * @package \Crux\Core\Api
	 * @static
	 * @var int
	 */
	const Scalar = 0x05;

	/**
	 * This constant defines an original variable type of \Crux\Collection\Vector
	 * @name \Crux\Core\Api::Vector
	 * @package \Crux\Core\Api
	 * @static
	 * @var int
	 */
	const Vector = 0x07;
	
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Public Static Properties /////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This property contain conversion extensions for a Variant
	 * @access public
	 * @name \Crux\Core\Api::$mVariantScalarExtensions
	 * @package \Crux\Core\Api
	 * @var array
	 * @static
	 */
	public static $mVariantScalarExtensions = [];

	/**
	 * This property contain conversion extensions for a VariantList
	 * @access public
	 * @name \Crux\Core\Api::$mVariantVectorExtensions
	 * @package \Crux\Core\Api
	 * @var array
	 * @static
	 */
	public static $mVariantVectorExtensions = [];

	/**
	 * This property contain conversion extensions for a VariantMap
	 * @access public
	 * @name \Crux\Core\Api::$mVariantMapExtensions
	 * @package \Crux\Core\Api
	 * @var array
	 * @static
	 */
	public static $mVariantMapExtensions = [];

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Public Static Methods ////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method adds a Variant\Scalar conversion extension
	 * @access public
	 * @name \Crux\Core\Api::addVariantExtension()
	 * @package \Crux\Core\Api
	 * @param string $strName
	 * @param callable $fnCallback
	 * @return void
	 * @static
	 * @uses substr()
	 * @uses strtolower()
	 * @uses substr_replace()
	 */
	public static function addVariantExtension(string $strName, callable $fnCallback)
	{
		// Check the name of the extension
		if (strtolower(substr($strName, 0, 2)) === 'to') {
			// Remove the toX call
			$strName = substr_replace($strName, '', 0, 2);
		}
		// Add the extension to the instance
		self::$mVariantScalarExtensions[$strName] = $fnCallback;
	}

	/**
	 * This method adds a Variant\Vector conversion extension
	 * @access public
	 * @name \Crux\Core\Api::addVariantVectorExtension()
	 * @package \Crux\Core\Api
	 * @param string $strName
	 * @param callable $fnCallback
	 * @return void
	 * @static
	 * @uses substr()
	 * @uses strtolower()
	 * @uses substr_replace()
	 */
	public static function addVariantVectorExtension(string $strName, callable $fnCallback)
	{
		// Check the name of the extension
		if (strtolower(substr($strName, 0, 2)) === 'to') {
			// Remove the toX call
			$strName = substr_replace($strName, '', 0, 2);
		}
		// Add the extension to the instance
		self::$mVariantVectorExtensions[$strName] = $fnCallback;
	}

	/**
	 * This method adds a Variant\Map conversion extension
	 * @access public
	 * @name \Crux\Core\Api::addVariantMapExtension()
	 * @package \Crux\Core\Api
	 * @param string $strName
	 * @param callable $fnCallback
	 * @return void
	 * @static
	 * @uses substr()
	 * @uses strtolower()
	 * @uses substr_replace()
	 */
	public static function addVariantMapExtension(string $strName, callable $fnCallback)
	{
		// Check the name of the extension
		if (strtolower(substr($strName, 0, 2)) === 'to') {
			// Remove the toX call
			$strName = substr_replace($strName, '', 0, 2);
		}
		// Add the extension to the instance
		self::$mVariantMapExtensions[$strName] = $fnCallback;
	}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
} /// End Crux\Core\Api Class Definition /////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

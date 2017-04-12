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
	/// Public Static Properties /////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This property contain conversion extensions for a Variant
	 * @access public
	 * @name \Crux\Core\Api::$mVariantExtensions
	 * @package \Crux\Core\Api
	 * @var array
	 * @static
	 */
	public static $mVariantExtensions = [];

	/**
	 * This property contain conversion extensions for a VariantList
	 * @access public
	 * @name \Crux\Core\Api::$mVariantListExtensions
	 * @package \Crux\Core\Api
	 * @var array
	 * @static
	 */
	public static $mVariantListExtensions = [];

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
	 * This method adds a Variant conversion extension
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
		self::$mVariantExtensions[$strName] = $fnCallback;
	}

	/**
	 * This method adds a VariantList conversion extension
	 * @access public
	 * @name \Crux\Core\Api::addVariantListExtension()
	 * @package \Crux\Core\Api
	 * @param string $strName
	 * @param callable $fnCallback
	 * @return void
	 * @static
	 * @uses substr()
	 * @uses strtolower()
	 * @uses substr_replace()
	 */
	public static function addVariantListExtension(string $strName, callable $fnCallback)
	{
		// Check the name of the extension
		if (strtolower(substr($strName, 0, 2)) === 'to') {
			// Remove the toX call
			$strName = substr_replace($strName, '', 0, 2);
		}
		// Add the extension to the instance
		self::$mVariantListExtensions[$strName] = $fnCallback;
	}

	/**
	 * This method adds a VariantMap conversion extension
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

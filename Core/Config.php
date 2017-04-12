<?php

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Crux\Core Namespace //////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

namespace Crux\Core;

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Imports //////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

use Crux\Serialize;

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Crux\Core\Config Class Definition /////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class Config
{
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Constants ////////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This constant defines the INI format
	 * @name \Crux\Core\Config::INI
	 * @package \Crux\Core\Config
	 * @var int
	 */
	const INI = 0x01;

	/**
	 * This constant defines the JSON format
	 * @name \Crux\Core\Config::JSON
	 * @package \Crux\Core\Config
	 * @var int
	 */
	const JSON = 0x02;

	/**
	 * This constant defines the PHP format
	 * @name \Crux\Core\Config::PHP
	 * @package \Crux\Core\Config
	 * @var int
	 */
	const PHP = 0x03;

	/**
	 * This constant defines the XML format
	 * @name \Crux\Core\Config::XML
	 * @package \Crux\Core\Config
	 * @var int
	 */
	const XML = 0x04;

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Private Static Properties ////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This property contains the configuration data
	 * @access private
	 * @name \Crux\Core\Config::$mContainer
	 * @package \Crux\Core\Config
	 * @static
	 * @var array<string, mixed>
	 */
	private static $mContainer = [];

	/**
	 * This property contains the format of the configuration data
	 * @access privatee
	 * @name \Crux\Core\Config::$mFormat
	 * @package \Crux\Core\Config
	 * @static
	 * @var int
	 */
	private static $mFormat = self::JSON;

	/**
	 * This property contains the path to the configuration file
	 * @access private
	 * @name \Crux\Core\Config::$mPath
	 * @package \Crux\Core\Config
	 * @static
	 * @var string
	 */
	private static $mPath = '';

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Static Constructor ///////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method initializes the configuration object
	 * @access public
	 * @name \Crux\Core\Config::Initialize()
	 * @package \Crux\Core\Config
	 * @param string $strPath ['']
	 * @param int $intFormat [\Crux\Core\Config::JSON]
	 * @return void
	 * @static
	 * @uses \Crux\Core\Is::empty()
	 * @uses \Crux\Core\Config::path()
	 * @uses \Crux\Core\Config::format()
	 * @uses \Crux\Core\Config::read()
	 */
	public static function Initialize(string $strPath = '', int $intFormat = self::JSON)
	{
		// Reset the path to the configuration file
		self::path($strPath);
		// Reset the format
		self::format($intFormat);
		// Make sure we have a configuration file
		if (!Is::empty(self::$mPath)) {
			// Read the configuration file
			self::read();
		}
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Protected Static Methods /////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method ensures configuration file readability and sets the configuration into memory
	 * @access protected
	 * @name \Crux\Core\Config::read()
	 * @package \Crux\Core\Config
	 * @return void
	 * @static
	 * @throws \Crux\Core\Exception
	 * @uses \Crux\Core\Config::readIni()
	 * @uses \Crux\Core\Config::readJson()
	 * @uses \Crux\Core\Config::readPhp()
	 * @uses \Crux\Core\Config::readXml()
	 * @uses \Crux\Core\Exception::__construct()
	 * @uses file_exists()
	 * @uses is_readable()
	 * @uses sprintf()
	 */
	protected static function read()
	{
		// Make sure the file exists
		if (!file_exists(self::$mPath)) {
			// Throw the exception
			throw new Exception(sprintf('[Core][Config]:  Configuration File [%s] does not exist', self::$mPath));
		}
		// Make sure the file is readable
		if (!is_readable(self::$mPath)) {
			// Throw the exception
			throw new Exception(sprintf('[Core][Config]:  Configuration File [%s] is not readable', self::$mPath));
		}
		// Determine the type
		if (self::$mFormat === self::INI) {
			// Set the configuration into memory
			self::$mContainer = self::readIni();
		} elseif (self::$mFormat === self::PHP) {
			// Set the configuration into memory
			self::$mContainer = self::readPhp();
		} elseif (self::$mFormat === self::XML) {
			// Set the configuration into memory
			self::$mContainer = self::readXml();
		} else {
			// Set the configuration into memory
			self::$mContainer = self::readJson();
		}
	}

	/**
	 * This method reads an INI configuration file
	 * @access protected
	 * @name \Crux\Core\Config::readIni()
	 * @package \Crux\Core\Config
	 * @return array<string, mixed>
	 * @static
	 * @uses parse_ini_file()
	 */
	protected static function readIni() : array
	{
		// Parse the INI file
		return parse_ini_file(self::$mPath, true);
	}

	/**
	 * This method reads a JSON configuration file
	 * @access protected
	 * @name \Crux\Core\Config::readJson()
	 * @package \Crux\Core\Config
	 * @return array<string, mixed>
	 * @static
	 * @uses \Crux\Serialize\Json::__construct()
	 * @uses \Crux\Serialize\Json::deserialize()
	 * @uses \Crux\Serialize\Json::toArray()
	 * @uses file_get_contents()
	 */
	protected static function readJson() : array
	{
		// Instantiate our serializer
		$jsonSerializer = new Serialize\Json();
		// Deserialize the file content
		$jsonSerializer->deserialize(file_get_contents(self::$mPath));
		// Return the data
		return $jsonSerializer->toArray();
	}

	/**
	 * This method reads a PHP configuration file
	 * @access protected
	 * @name \Crux\Core\Config::readPhp()
	 * @package \Crux\Core\Config
	 * @return array<string, mixed>
	 * @static
	 * @uses file_get_contents()
	 * @uses unserialize()
	 */
	protected static function readPhp() : array
	{
		// Return the data
		return unserialize(file_get_contents(self::$mPath));
	}

	/**
	 * This method reads an XML configuration file
	 * @access protected
	 * @name \Crux\Core\Config::readXml()
	 * @package \Crux\Core\Config
	 * @return array<string, mixed>
	 * @static
	 * @uses \Crux\Serialize\Xml::__construct()
	 * @uses \Crux\Serialize\Xml::deserialize()
	 * @uses \Crux\Serialize\Xml::toArray()
	 * @uses file_get_contents()
	 */
	protected static function readXml() : array
	{
		// Instantiate our serializer
		$xmlSerializer = new Serialize\Xml();
		// Deserialize the file content
		$xmlSerializer->deserialize(file_get_contents(self::$mPath));
		// Reset the data
		return $xmlSerializer->toArray();
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Public Static Methods ////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method returns the format from storage with the ability to reset it inline
	 * @access public
	 * @name \Crux\Core\Config::format()
	 * @package \Crux\Core\Config
	 * @param int $intFormat [-1]
	 * @return int
	 * @static
	 */
	public static function format(int $intFormat = -1) : int
	{
		// Check for a provided format
		if ($intFormat > -1) {
			// Reset the format
			self::$mFormat = $intFormat;
		}
		// Return the format
		return self::$mFormat;
	}

	/**
	 * This method returns configuration properties from storage
	 * @access public
	 * @name \Crux\Core\Config::get()
	 * @package \Crux\Core\Config
	 * @param string $strKey ['']
	 * @return mixed
	 * @static
	 * @uses \Crux\Core\Is::empty()
	 * @uses \Crux\Core\Is::array()
	 * @uses strpos()
	 * @uses array_key_exists()
	 * @uses explode()
	 * @uses isset()
	 */
	public static function get(string $strKey = '')
	{
		// Check the key
		if (Is::empty($strKey)) {
			// Return the entire container
			return self::$mContainer;
		} elseif (strpos($strKey, '.') === false) {
			// Return the property
			return (array_key_exists($strKey, self::$mContainer) ? self::$mContainer[$strKey] : null);
		} else {
			// Localize the configuration
			$arrConfig = self::$mContainer;
			// Explode the key
			$arrKeys = explode('.', $strKey);
			// Iterate over the keys
			for ($intKey = 0; $intKey < count($arrKeys); ++$intKey) {
				// Check for an empty key
				if (Is::empty($arrKeys[$intKey])) {
					// Next iteration please
					continue;
				}
				// Check for the key
				if (Is::array($arrConfig) && (array_key_exists($arrKeys[$intKey], $arrConfig) || isset($arrConfig[$arrKeys[$intKey]]))) {
					// Reset the local configuration
					$arrConfig = $arrConfig[$arrKeys[$intKey]];
				} else {
					// We're done
					return null;
				}
			}
			// We're done, return the local configuration
			return $arrConfig;
		}
	}

	/**
	 * This method returns the path from storage with the ability to reset it inline
	 * @access public
	 * @name \Crux\Core\Config::path()
	 * @package \Crux\Core\Config
	 * @param string $strPath ['']
	 * @return string
	 * @static
	 * @uses \Crux\Core\Is::empty()
	 * @uses \Crux\Core\Config::read()
	 */
	public static function path(string $strPath = '') : string
	{
		// Check for a provided path
		if (!Is::empty($strPath)) {
			// Reset the path
			self::$mPath = $strPath;
			// Re-read the configuration
			self::read();
		}
		// Return the path
		return self::$mPath;
	}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
} /// End Crux\Core\Config Class Definition //////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

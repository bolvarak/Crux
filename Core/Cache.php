<?php

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Crux\Core Namespace //////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

namespace Crux\Core;

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Imports //////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

use Crux\Collection;
use Crux\Crypto;
use Crux\Serialize;

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Crux\Core\Cache Class Definition /////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class Cache
{
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Protected Static Methods /////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method returns the cache directory to use
	 * @access protected
	 * @name \Crux\Core\Cache::directory()
	 * @package \Crux\Core\Cache
	 * @return string
	 * @static
	 * @uses \Crux\Core\Config::get()
	 * @uses sys_get_temp_dir()
	 */
	protected static function directory() : string
	{
		// Return the cache directory
		return (Config::get('system.cache.dir') ?? sys_get_temp_dir());
	}

	/**
	 * This method returns the full path to a cache file
	 * @access protected
	 * @name \Crux\Core\Cache::file()
	 * @package \Crux\Core\Cache
	 * @param string $strIdentifier
	 * @return string
	 * @uses \Crux\Core\Cache::directory()
	 * @uses sprintf()
	 */
	protected static function file(string $strIdentifier) : string
	{
		// Return the file name
		return sprintf('%s%sphw.%s.cache', self::directory(), DIRECTORY_SEPARATOR, $strIdentifier);
	}

	/**
	 * This method loads a cache file from the file system
	 * @access protected
	 * @name \Crux\Core\Cache::load()
	 * @package \Crux\Core\Cache
	 * @param string $strIdentifier
	 * @return \Crux\Collection\Map
	 * @static
	 * @uses \Crux\Core\Cache::file()
	 * @uses \Crux\Crypto\OpenSSL::decryptWithPassphrase()
	 * @uses \Crux\Serialize\Json::__construct()
	 * @uses \Crux\Serialize\Json::deserialize()
	 * @uses \Crux\Serialize\Json::toCollection()
	 * @uses file_get_contents()
	 */
	protected static function load(string $strIdentifier) : Collection\Map
	{
		// Load the contents
		$strContents = file_get_contents(self::file($strIdentifier));
		// Decrypt the contents
		$jsonContent = Crypto\OpenSSL::decryptWithPassphrase($strContents);
		// Instantiate our serializer
		$jsonSerializer = new Serialize\Json();
		// Deserialize the cache data
		$jsonSerializer->deserialize($jsonContent);
		// We're done, return the collection
		return $jsonSerializer->toCollection();
	}

	/**
	 * This method returns the cache expiration timestamp
	 * @access protected
	 * @name \Crux\Core\Cache::expiration()
	 * @package \Crux\Core\Cache
	 * @return int
	 * @static
	 * @uses \Crux\Core\Config::get()
	 * @uses time()
	 */
	protected static function expiration() : int
	{
		// Return the expiration timestamp
		return (time() + (Config::get('system.cache.ttl') ?? (24 * 60 * 60)));
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Public Static Methods ////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method returns the existence status of a cache file
	 * @access public
	 * @name \Crux\Core\Cache::exists()
	 * @package \Crux\Core\Cache
	 * @param string $strIdentifier
	 * @return bool
	 * @uses \Crux\Core\Cache::file()
	 */
	public static function exists(string $strIdentifier) : bool
	{
		// Return the status of the cache file
		return (Is::file(self::file($strIdentifier)) && Is::readable(self::file($strIdentifier)));
	}

	/**
	 * This method returns the data from a cache file
	 * @access public
	 * @name \Crux\Core\Cache::get()
	 * @package \Crux\Core\Cache
	 * @param string $strIdentifier
	 * @return mixed
	 * @static
	 * @uses \Crux\Core\Cache::exists()
	 * @uses \Crux\Core\Cache::load()
	 * @uses \Crux\Collection\Map::get()
	 */
	public static function get(string $strIdentifier)
	{
		// Make sure the cache file exists
		if (!self::exists($strIdentifier)) {
			// We're done, throw an exception

		}
		// Return the data
		return self::load($strIdentifier)->get('data');
	}

	/**
	 * This method determines whether or not a cache file has expired
	 * @access public
	 * @name \Crux\Core\Cache::hasExpired()
	 * @package \Crux\Core\Cache
	 * @param string $strIdentifier
	 * @return bool
	 * @static
	 * @uses \Crux\Core\Cache::exists()
	 * @uses \Crux\Core\Cache::load()
	 * @uses \Crux\Collection\Map::get()
	 * @uses time()
	 */
	public static function hasExpired(string $strIdentifier) : bool
	{
		// Check to see if the file exists
		if (!self::exists($strIdentifier)) {
			// We're done, the file doesn't exist
			return true;
		}
		// Load the file
		$mapCache = self::load($strIdentifier);
		// Check the force expiration flag
		if ($mapCache->get('forceExpiration')) {
			// Return the expiry status
			return ($mapCache->get('expiresAt') < time());
		}
		// We're done, the cache is still good
		return false;
	}

	/**
	 * This method writes a cache file to the filesystem
	 * @access public
	 * @name \Crux\Core\Cache::set()
	 * @package \Crux\Core\Cache
	 * @param string $strIdentifier
	 * @param mixed $mixData
	 * @param bool $blnForceExpiration [true]
	 * @param int $intSeconds [null]
	 * @return void
	 * @static
	 * @uses \Crux\Core\Is::null()
	 * @uses \Crux\Core\Cache::expiration()
	 * @uses \Crux\Core\Cache::file()
	 * @uses \Crux\Serialize\Json::__construct()
	 * @uses \Crux\Serialize\Json::serialize()
	 * @uses \Crux\Crypto\OpenSSL::encryptWithPassphrase()
	 * @uses time()
	 * @uses file_put_contents()
	 */
	public static function set(string $strIdentifier, $mixData, bool $blnForceExpiration = true, int $intSeconds = null)
	{
		// Define the container
		$arrContainer = [
			'forceExpiration' => $blnForceExpiration,
			'expiresAt' => (Is::null($intSeconds) ? self::expiration() : (time() + $intSeconds)),
			'data' => $mixData
		];
		// Instantiate our serializer
		$jsonSerializer = new Serialize\Json();
		// Serialize the container
		$jsonData = $jsonSerializer->serialize($arrContainer);
		// Write the cache file
		file_put_contents(self::file($strIdentifier), Crypto\OpenSSL::encryptWithPassphrase($jsonData));
	}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
} /// End Crux\Core\Cache Class Definition ///////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

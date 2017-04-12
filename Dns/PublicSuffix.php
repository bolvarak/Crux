<?php

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Crux\Dns Namespace ///////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

namespace Crux\Dns;

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Imports //////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

use Crux\Core\Config;

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Crux\Dns\PublicSuffix Class Definition ///////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class PublicSuffix
{
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Constants ////////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This constant contains the URL for the PublicSuffix Database of TLDs
	 * @constant
	 * @name \Crux\Dns\PublicSuffix::PUBLIC_SUFFIX_DB
	 * @package \Crux\Dns\PublicSuffix
	 * @var string
	 */
	const PUBLIC_SUFFIX_DB = 'https://publicsuffix.org/list/public_suffix_list.dat';

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Properties ///////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This property contains the second-level domain name for the hostname
	 * @access protected
	 * @name \Crux\Dns\PublicSuffix::$mDomain
	 * @package \Crux\Dns\PublicSuffix
	 * @var string
	 */
	protected $mDomain = '';

	/**
	 * This property contains the sub-level host for the hostname
	 * @access protected
	 * @name \Crux\Dns\PublicSuffix::$mHost
	 * @package \Crux\Dns\PublicSuffix
	 * @var string
	 */
	protected $mHost = '';

	/**
	 * This property contains the port for the hostname
	 * @access protected
	 * @name \Crux\Dns\PublicSuffix::$mPort
	 * @package \Crux\Dns\PublicSuffix
	 * @var int
	 */
	protected $mPort = 0;

	/**
	 * This property contains the public suffix data in a usable format
	 * @access protected
	 * @name \Crux\Dns\PublicSuffix::$mPublicSuffixData
	 * @package \Crux\Dns\PublicSuffix
	 * @var array
	 */
	protected $mPublicSuffixData = [];

	/**
	 * This property contains the source hostname that was parsed
	 * @access protected
	 * @name \Crux\Dns\PublicSuffix::$mSource
	 * @package \Crux\Dns\PublicSuffix
	 * @var string
	 */
	protected $mSource = '';

	/**
	 * This property contains the top level domain (TLD) of the hostname
	 * @access protected
	 * @name \Crux\Dns\PublicSuffix::$mTopLevelDomain
	 * @package \Crux\Dns\PublicSuffix
	 * @var string
	 */
	protected $mTopLevelDomain = '';

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Static Methods ///////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method determines whether or not a hostname appears to be valid
	 * @access public
	 * @name \Crux\Dns\PublicSuffix::isValue()
	 * @package \Crux\Dns\PublicSuffix
	 * @param string $strHostname
	 * @return bool
	 * @static
	 * @uses preg_match()
	 */
	public static function isValid(string $strHostname) : bool
	{
		// Return the status of our checks
		return (
			preg_match('/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i', $strHostname) && // Character Check
			preg_match('/^.{1,253}$/', $strHostname) &&                                         // Length Check
			preg_match('/^[^\.]{1,63}(\.[^\.]{1,63})*$/', $strHostname)                         // Part Length Check
		);
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Constructor //////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method instantiates the library with a database
	 * @access public
	 * @name \Crux\Dns\PublicSuffix::__construct()
	 * @param string $strHostname ['']
	 * @uses \Crux\Dns\PublicSuffix::openDatabase()
	 * @uses \Crux\Dns\PublicSuffix::parse()
	 */
	public function __construct(string $strHostname = '')
	{
		// Open the database
		$this->openDatabase();
		// Check for a hostname
		if ($strHostname !== '') {
			// Parse the hostname
			$this->parse($strHostname);
		}
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Protected Methods ////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method determines the local host's name from the source hostname
	 * @access protected
	 * @name \Crux\Dns\PublicSuffix::determineHost()
	 * @package \Crux\Dns\PublicSuffix
	 * @return void
	 * @uses sprintf()
	 * @uses str_ireplace()
	 */
	protected function determineHost() : void
	{
		// Set the host into the instance
		$this->mHost = str_ireplace([
			sprintf('.%s', $this->mDomain),
			sprintf(':%d', $this->mPort)
		], '', $this->mSource);
	}

	/**
	 * This method determines the port number of the hostname
	 * @access protected
	 * @name \Crux\Dns\PublicSuffix::determinePort()
	 * @package \Crux\Dns\PublicSuffix
	 * @return void
	 * @uses preg_match()
	 * @uses count()
	 * @uses intval()
	 */
	protected function determinePort() : void
	{
		// Define a match placeholder
		$arrMatches = [];
		// Match the port number
		preg_match('/:([0-9]+?)$/', $this->mSource, $arrMatches);
		// Check the count
		if (count($arrMatches) >= 2) {
			// Set the port into the instance
			$this->mPort = intval($arrMatches[1]);
		}
	}

	/**
	 * This method determines the second-level domain name from the source hostname
	 * @access protected
	 * @name \Crux\Dns\PublicSuffix::determineTopLevelDomain()
	 * @package \Crux\Dns\PublicSuffix
	 * @return void
	 * @uses trim()
	 * @uses sprintf()
	 * @uses str_replace()
	 * @uses explode()
	 * @uses count()
	 * @uses array_pop()
	 * @uses strtolower()
	 */
	protected function determineTopLevelDomain() : void
	{
		// Split the domain parts
		$arrParts = explode('.', str_replace(sprintf(':%d', $this->mPort), '', trim($this->mSource)));
		// Set the found flag
		$blnFound = false;
		// Create a working TLD
		$strWorkingTLD = $arrParts[count($arrParts) - 1];
		// Remove the last key from the array
		array_pop($arrParts);
		// Iterate until found
		while (!$blnFound) {
			// Iterate over the TLDs
			foreach ($this->mPublicSuffixData as $strTLD) {
				// Compare the TLDs
				if (strtolower($strWorkingTLD) === strtolower($strTLD)) {
					// Reset the found flag
					$blnFound = true;
					// We're done
					break;
				}
			}
			// Check the found flag
			if ($blnFound === false) {
				// Prepend the next part from the array
				$strWorkingTLD = sprintf('%s.%s', $arrParts[count($arrParts) - 1], $strWorkingTLD);
				// Remove the last key from the array
				array_pop($arrParts);
			}
		}
		// Set the top level domain
		$this->mTopLevelDomain = $strWorkingTLD;
		// We're done, set the second-level domain into the instance
		$this->mDomain = sprintf('%s.%s', $arrParts[count($arrParts) - 1], $strWorkingTLD);
	}

	/**
	 * This method builds the database from Public Suffix
	 * @access protected
	 * @name \Crux\Dns\PublicSuffix::openDatabase()
	 * @package \Crux\Dns\PublicSuffix
	 * @return void
	 * @uses \Crux\Core\Config::get()
	 * @uses file_exists()
	 * @uses file_get_contents()
	 * @uses file_put_contents()
	 * @uses explode()
	 * @uses substr()
	 * @uses str_replace()
	 * @uses trim()
	 * @uses array_push()
	 */
	protected function openDatabase() : void
	{
		// Check for cache
		if (file_exists(Config::get('system.temp.dir') . 'acx.cache.public.suffix')) {
			// Load the file
			$strPublicSuffixData = file_get_contents(Config::get('system.temp.dir') . 'acx.cache.public.suffix');
		} else {
			// Load the file
			$strPublicSuffixData = file_get_contents(self::PUBLIC_SUFFIX_DB);
			// Write the file to cache
			file_put_contents(Config::get('system.temp.dir') . 'acx.cache.public.suffix', $strPublicSuffixData);
		}
		// Clear any existing data
		$this->mPublicSuffixData = [];
		// Iterate over the lines
		foreach (explode(PHP_EOL, $strPublicSuffixData) as $strLine) {
			// Check the line
			if (empty($strLine) || (substr($strLine, 0, 2) === '//')) {
				// Next iteration
				continue;
			}
			// Add the data to the array
			array_push($this->mPublicSuffixData, trim(str_replace(['!.', '*.', '*', '!'], '', $strLine)));
		}
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Public Methods ///////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method bootstraps the parsing of a hostname
	 * @access public
	 * @name \Crux\Dns\PublicSuffix::parse()
	 * @package \Crux\Dns\PublicSuffix
	 * @param string $strHostname
	 * @return \Crux\Dns\PublicSuffix $this
	 * @uses \Crux\Dns\PublicSuffix::determineHost()
	 * @uses \Crux\Dns\PublicSuffix::determinePort()
	 * @uses \Crux\Dns\PublicSuffix::determineTopLevelDomain()
	 */
	public function parse(string $strHostname) : PublicSuffix
	{
		// Set the hostname into the instance
		$this->mSource = $strHostname;
		// Determine the port number
		$this->determinePort();
		// Determine the second-level domain
		$this->determineTopLevelDomain();
		// Determine the host
		$this->determineHost();
		// We're done, return the instance
		return $this;
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Getters //////////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method returns the hostname's second-level domain from the instance
	 * @access public
	 * @name \Crux\Dns\PublicSuffix::getDomain()
	 * @package \Crux\Dns\PublicSuffix
	 * @return string
	 */
	public function getDomain() : string
	{
		// Return the second-level domain from the instance
		return $this->mDomain;
	}

	/**
	 * This method returns the hostname's local host's name from the instance
	 * @access public
	 * @name \Crux\Dns\PublicSuffix::getHost()
	 * @package \Crux\Dns\PublicSuffix
	 * @return string
	 */
	public function getHost() : string
	{
		// Return the hostname from the instance
		return $this->mHost;
	}

	/**
	 * This method returns the hostname's port number from the instance
	 * @access public
	 * @name \Crux\Dns\PublicSuffix::getPort()
	 * @package \Crux\Dns\PublicSuffix
	 * @return int
	 */
	public function getPort() : int
	{
		// Return the port from the instance
		return $this->mPort;
	}

	/**
	 * This method returns the hostname's top-level domain (TLD) from the instance
	 * @access public
	 * @name \Crux\Dns\PublicSuffix::getTopLevelDomain()
	 * @package \Crux\Dns\PublicSuffix
	 * @return string
	 */
	public function getTopLevelDomain() : string
	{
		// Return the TLD from the instance
		return $this->mTopLevelDomain;
	}

	/**
	 * This method returns the list of top-level domains that are available from Public Suffix
	 * @access public
	 * @name \Crux\Dns\PublicSuffix::getTopLevelDomains()
	 * @package \Crux\Dns\PublicSuffix
	 * @return array
	 */
	public function getTopLevelDomains() : array
	{
		// Return the top-level domains from the instance
		return $this->mPublicSuffixData;
	}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
} /// End Crux\Dns\PublicSuffix Class Definition /////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

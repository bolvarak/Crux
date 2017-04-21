<?php

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Crux\Grammar Namespace ///////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

namespace Crux\Grammar;

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Crux\Grammar\Inflection Class ////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class Inflection
{
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Properties ///////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This property contains irregular words that do not follow the normal pattern
	 * @access protected
	 * @name \Crux\Grammar\Inflection::$mIrregular
	 * @package Crux\Grammar\Inflection
	 * @static
	 * @var array
	 */
	protected static $mIrregular = [
		'move' => 'moves',
		'foot' => 'feet',
		'goose' => 'geese',
		'sex' => 'sexes',
		'child' => 'children',
		'man' => 'men',
		'tooth' => 'teeth',
		'person' => 'people',
		'valve' => 'valves'
	];

	/**
	 * This property contains the plural regular expression matches and replacements for words
	 * @access protected
	 * @name \Crux\Grammar\Inflection::$mPlural
	 * @package Crux\Grammar\Inflection
	 * @static
	 * @var array
	 */
	protected static $mPlural = [
		'/(quiz)$/i' => '$1zes',
		'/^(ox)$/i' => '$1en',
		'/([m|l])ouse$/i' => '$1ice',
		'/(matr|vert|ind)ix|ex$/i' => '$1ices',
		'/(x|ch|ss|sh)$/i' => '$1es',
		'/([^aeiouy]|qu)y$/i' => '$1ies',
		'/(hive)$/i' => '$1s',
		'/(?:([^f])fe|([lr])f)$/i' => '$1$2ves',
		'/(shea|lea|loa|thie)f$/i' => '$1ves',
		'/sis$/i' => 'ses',
		'/([ti])um$/i' => '$1a',
		'/(tomat|potat|ech|her|vet)o$/i' => '$1oes',
		'/(bu)s$/i' => '$1ses',
		'/(alias)$/i' => '$1es',
		'/(octop)us$/i' => '$1i',
		'/(ax|test)is$/i' => '$1es',
		'/(us)$/i' => '$1es',
		'/s$/i' => 's',
		'/$/' => 's'
	];

	/**
	 * This property contains the singular regular expression matches and replacements for words
	 * @access protected
	 * @name \Crux\Grammar\Inflection::$mSingular
	 * @package Crux\Grammar\Inflection
	 * @static
	 * @var array
	 */
	protected static $mSingular = [
		'/(quiz)zes$/i' => '$1',
		'/(matr)ices$/i' => '$1ix',
		'/(vert|ind)ices$/i' => '$1ex',
		'/^(ox)en$/i' => '$1',
		'/(alias)es$/i' => '$1',
		'/(octop|vir)i$/i' => '$1us',
		'/(cris|ax|test)es$/i' => '$1is',
		'/(shoe)s$/i' => '$1',
		'/(o)es$/i' => '$1',
		'/(bus)es$/i' => '$1',
		'/([m|l])ice$/i' => '$1ouse',
		'/(x|ch|ss|sh)es$/i' => '$1',
		'/(m)ovies$/i' => '$1ovie',
		'/(s)eries$/i' => '$1eries',
		'/([^aeiouy]|qu)ies$/i' => '$1y',
		'/([lr])ves$/i' => '$1f',
		'/(tive)s$/i' => '$1',
		'/(hive)s$/i' => '$1',
		'/(li|wi|kni)ves$/i' => '$1fe',
		'/(shea|loa|lea|thie)ves$/i' => '$1f',
		'/(^analy)ses$/i' => '$1sis',
		'/((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/i' => '$1$2sis',
		'/([ti])a$/i' => '$1um',
		'/(n)ews$/i' => '$1ews',
		'/(h|bl)ouses$/i' => '$1ouse',
		'/(corpse)s$/i' => '$1',
		'/(us)es$/i' => '$1',
		'/(business)$/i' => '$1',
		'/s$/i' => ''
	];

	/**
	 * This property contains words that are the same in both singular and plural form
	 * @access protected
	 * @name \Crux\Grammar\Inflection::$mUncountable
	 * @package Crux\Grammar\Inflection
	 * @static
	 * @var array
	 */
	protected static $mUncountable = [
		'sheep',
		'fish',
		'deer',
		'series',
		'species',
		'money',
		'rice',
		'information',
		'equipment'
	];

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Public Methods ///////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method pluralizes a singular word
	 * @access public
	 * @name \Crux\Grammar\Inflection::pluralize()
	 * @package Crux\Grammar\Inflection
	 * @param string $strWord
	 * @return string
	 * @static
	 * @uses strtolower()
	 * @uses in_array()
	 * @uses sprintf()
	 * @uses preg_match()
	 * @uses preg_replace()
	 */
	public static function pluralize(string $strWord): string
	{
		// Check for the word in the uncountable array
		if (in_array(strtolower($strWord), self::$mUncountable)) {
			// We're done, the word is uncountable
			return $strWord;
		}
		// Iterate over the irregular words and check for the word
		foreach (self::$mIrregular as $strPattern => $strResult) {
			// Create the regular expression pattern
			$strPattern = sprintf('/%s$/i', $strPattern);
			// Execute the comparison
			if (preg_match($strPattern, $strWord)) {
				// We're done, we have a match, return the replacement
				return preg_replace($strPattern, $strResult, $strWord);
			}
		}
		// Iterate over the plural words and check for the word
		foreach (self::$mPlural as $strPattern => $strResult) {
			// Check for the word
			if (preg_match($strPattern, $strWord)) {
				// We're done, we have a match, pluralize the word
				return preg_replace($strPattern, $strResult, $strWord);
			}
		}
		// We're done, no match, return the word
		return $strWord;
	}

	/**
	 * This method returns the singular variant of a word
	 * @access public
	 * @name \Crux\Grammar\Inflection::singularize()
	 * @package Crux\Grammar\Inflection
	 * @param string $strWord
	 * @return string
	 * @static
	 * @uses strtolower()
	 * @uses in_array()
	 * @uses sprintf()
	 * @uses preg_match()
	 * @uses preg_replace()
	 */
	public static function singularize(string $strWord): string
	{
		// Check for the word in the uncountable array
		if (in_array(strtolower($strWord), self::$mUncountable)) {
			// We're done, return the word
			return $strWord;
		}
		// Iterate over the irregular words array
		foreach (self::$mIrregular as $strResult => $strPattern) {
			// Create the regular expression pattern
			$strPattern = sprintf('/%s$/i', $strPattern);
			// Check for the word
			if (preg_match($strPattern, $strWord)) {
				// We're done, we have a match, return the replacement
				return preg_replace($strPattern, $strResult, $strWord);
			}
		}
		// Iterate over the singular array
		foreach (self::$mSingular as $strPattern => $strResult) {
			// Check for the word
			if (preg_match($strPattern, $strWord)) {
				// We're done, we have a match, return the replacement
				return preg_replace($strPattern, $strResult, $strWord);
			}
		}
		// We're done, we have no match, return the word
		return $strWord;
	}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
} /// End Crux\Grammar\Inflection Class Definition ///////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

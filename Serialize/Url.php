<?php

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Crux\Serialize Namespace /////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

namespace Crux\Serialize;

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Imports //////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

use Crux\Core;
use Crux\Collection;
use Crux\Type;

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Crux\Serialize\Url Class Definition //////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class Url extends Es
{
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Protected Properties /////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This property contains a deserialized URL query string
	 * @access protected
	 * @name \Crux\Serialize\Url::$mDeserialized
	 * @var array<mixed>
	 */
	protected $mDeserialized = [];

	/**
	 * This property contains a URL query string
	 * @access protected
	 * @name \Crux\Serialize\Url::$mSerialized
	 * @var string
	 */
	protected $mSerialized = '';

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Constructor //////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method instantiates a new URL query string serializer object
	 * @access public
	 * @name \Crux\Serialize\Url::__construct()
	 */
	public function __construct()
	{}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Public Static Methods ////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method decodes a string that was encoded for use in a URL
	 * @access public
	 * @name \Crux\Serialize\Url::decode()
	 * @param string $strSource
	 * @return string
	 * @static
	 * @uses urldecode()
	 */
	public static function decode(string $strSource) : string
	{
		// Return the decoded source
		return urldecode($strSource);
	}

	/**
	 * This method encodes a string for use in a URL
	 * @access public
	 * @name \Crux\Serialize\Url::encode()
	 * @package \Crux\Serialize\Url
	 * @param string $strSource
	 * @return string
	 * @static
	 * @uses urlencode()
	 */
	public static function encode(string $strSource) : string
	{
		// Return the encoded source
		return urlencode($strSource);
	}

	/**
	 * This method decodes a string that was safely encoded
	 * @access public
	 * @name \Crux\Serialize\Url::safeDecode()
	 * @package \Crux\Serialize\Url
	 * @param string $strSource
	 * @return string
	 * @static
	 * @uses strlen()
	 * @uses str_repeat()
	 * @uses strtr()
	 * @uses base64_decode()
	 */
	public static function safeDecode(string $strSource) : string
	{
		// Grab the remainder
		$intRemainder = (strlen($strSource) % 4);
		// Check for a remainder
		if ($intRemainder) {
			// Pad the source
			$strSource .= str_repeat('=', (4 - $intRemainder));
		}
		// Return the decoded string
		return base64_decode(strtr($strSource, '-_', '+/'));
	}

	/**
	 * This method safely encodes a string for use in the URL
	 * @access public
	 * @name \Crux\Serialize\Url::safeEncode()
	 * @package \Crux\Serialize\Url
	 * @param string $strSource
	 * @return string
	 * @static
	 * @uses base64_encode()
	 * @uses strtr()
	 * @uses str_replace()
	 */
	public static function safeEncode(string $strSource) : string
	{
		// Return the safe encoding
		return str_replace('=', '', strtr(base64_encode($strSource), '+/', '-_'));
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Implemented Public Methods ///////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method deserializes a URL query string
	 * @access public
	 * @name \Crux\Serialize\Url::deserialize()
	 * @package \Crux\Serialize\Url
	 * @param string $strSource
	 * @return \Crux\Serialize\Url $this
	 * @throws \Crux\Core\Exception\Serialize\Url
	 */
	public function deserialize(string $strSource) : Url
	{
		// Parse the query string
		parse_str($strSource, $this->mDeserialized);
		// Check the results
		if (!Core\Is::empty($strSource) && Core\Is::empty($this->mDeserialized)) {
			// Throw the exception
			throw new Core\Exception\Serialize\Url('Source string is not a valid query string');
		}
		// We're done, return the instance
		return $this;
	}

	/**
	 * This method serializes a data structure into a query string
	 * @access public
	 * @name \Crux\Serialize\Url::serialize()
	 * @package \Crux\Serialize\Url
	 * @param mixed $mixSource
	 * @return string
	 * @uses \Crux\Core\Is::array()
	 * @uses \Crux\Core\Is::variantList()
	 * @uses \Crux\Core\Is::variantMap()
	 * @uses \Crux\Core\Is::variant()
	 * @uses \Crux\Core\Is::vector()
	 * @uses \Crux\Core\Is::map()
	 * @uses \Crux\Core\Is::scalar()
	 * @uses \Crux\Type\VariantList::toArray()
	 * @uses \Crux\Type\VariantMap::toArray()
	 * @uses \Crux\Type\Variant::getData()
	 * @uses \Crux\Collection\Vector::toArray()
	 * @uses \Crux\Collection\Map::toArray()
	 * @uses http_build_query()
	 */
	public function serialize($mixSource) : string
	{
		// Check the data type
		if (Core\Is::array($mixSource)) {
			// Return the serialization
			return http_build_query($mixSource);
		} elseif (Core\Is::variantList($mixSource)) {
			// Return the serialization
			return http_build_query($mixSource->toArray());
		} elseif (Core\Is::variantMap($mixSource)) {
			// Return the serialization
			return http_build_query($mixSource->toArray());
		} elseif (Core\Is::variant($mixSource)) {
			// Return the serialization
			return http_build_query(['data' => $mixSource->getData()]);
		} elseif (Core\Is::vector($mixSource)) {
			// Return the serialization
			return http_build_query($mixSource->toArray());
		} elseif (Core\Is::map($mixSource)) {
			// Return the serialization
			return http_build_query($mixSource->toArray());
		} elseif (Core\Is::scalar($mixSource)) {
			// Return the serialization
			return http_build_query(['data' => $mixSource]);
		} else {
			// Return the serialization
			return http_build_query($mixSource);
		}
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Implemented Converters ///////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method returns the deserialized query string
	 * @access public
	 * @name \Crux\Serialize\Url::toArray()
	 * @package \Crux\Serialize\Url
	 * @return array<mixed, mixed>
	 */
	public function toArray()
	{
		// Return the array data
		return $this->mDeserialized;
	}

	/**
	 * This method converts the deserialized data to a PHireworks Collection
	 * @access public
	 * @name \Crux\Serialize\Url::toCollection()
	 * @package \Crux\Serialize\Url
	 * @return \Crux\Collection\Map|\Crux\Collection\Vector
	 * @uses \Crux\Core\Is::sequentialArray()
	 * @uses \Crux\Collection\Map::fromArray()
	 * @uses \Crux\Collection\Vector::fromArray()
	 */
	public function toCollection()
	{
		// Check the deserialized data
		if (Core\Is::sequentialArray($this->mDeserialized)) {
			// Return the vector
			return Collection\Vector::fromArray($this->mDeserialized);
		} else {
			// Return the map
			return Collection\Map::fromArray($this->mDeserialized);
		}
	}

	/**
	 * This method converts the deserialized data into JSON
	 * @access public
	 * @name \Crux\Serialize\Url::toJson()
	 * @package \Crux\Serialize\Url
	 * @param bool $blnPrettyPrint [false]
	 * @return string
	 * @uses \Crux\Serialize\Json::__construct()
	 * @uses \Crux\Serialize\Json::prettyOutput()
	 * @uses \Crux\Serialize\Json::trimmedOutput()
	 * @uses \Crux\Serialize\Json::serialize()
	 */
	public function toJson(bool $blnPrettyPrint = false) : string
	{
		// Instantiate our serializer
		$jsonSerializer = new Json();
		// Check the pretty-print flag
		if ($blnPrettyPrint) {
			// Turn pretty-printing on
			$jsonSerializer->prettyOutput();
		} else {
			// Turn pretty-printing off
			$jsonSerializer->trimmedOutput();
		}
		// Serialize the data and return the JSON
		return $jsonSerializer->serialize($this->mDeserialized);
	}

	/**
	 * This method returns the deserialized data as a PHP stdClass
	 * @access public
	 * @name \Crux\Serialize\Url::toObject()
	 * @package \Crux\Serialize\Url
	 * @return \stdClass
	 * @uses json_encode()
	 */
	public function toObject() : \stdClass
	{
		// We're done, return the class object
		return json_encode($this->mDeserialized);
	}

	/**
	 * This method converts the deserialized data into a PHireworks Variant
	 * @access public
	 * @name \Crux\Serialize\Url::toVariant()
	 * @package \Crux\Serialize\Url
	 * @return \Crux\Type\Variant|\Crux\Type\VariantList|\Crux\Type\VariantMap
	 * @uses \Crux\Type\Variant::Factory()
	 */
	public function toVariant()
	{
		// Return the variant value of the deserialize data
		return Type\Variant::Factory($this->mDeserialized);
	}

	/**
	 * This method converts the deserialized data into XML
	 * @access public
	 * @name \Crux\Serialize\Url::toXml()
	 * @package \Crux\Serialize\Url
	 * @param bool $blnPrettyPrint [false]
	 * @return string
	 * @uses \Crux\Serialize\Xml::__construct()
	 * @uses \Crux\Serialize\Xml::prettyOutput()
	 * @uses \Crux\Serialize\Xml::trimmedOutput()
	 * @uses \Crux\Serialize\Xml::serialize()
	 */
	public function toXml(bool $blnPrettyPrint = false) : string
	{
		// Instantiate our serializer
		$xmlSerializer = new Xml();
		// Check the pretty print flag
		if ($blnPrettyPrint) {
			// Turn formatting on
			$xmlSerializer->prettyOutput();
		} else {
			// Turn formatting off
			$xmlSerializer->trimmedOutput();
		}
		// Serialize the data and return the XML
		return $xmlSerializer->serialize($this->mDeserialized);
	}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
} /// End Crux\Serialize\Url Class Definition ////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

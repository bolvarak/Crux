<?php

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Crux\Serialize Namespace /////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

namespace Crux\Serialize;

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Crux\Serialize\Es Abstract Class Definition //////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

abstract class Es
{
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Implementation Methods ///////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method is responsible for deserializing the entity into PHP data
	 * @access public
	 * @name \Crux\Serialize\Es::deserialize()
	 * @package \Crux\Serialize\Es
	 * @param string $strSource
	 * @return mixed
	 */
	abstract public function deserialize(string $strSource);

	/**
	 * This method is responsible for serializing PHP data into the string
	 * @access public
	 * @name \Crux\Serialize\Es::serialize()
	 * @package \Crux\Serialize\Es
	 * @param mixed $mixSource
	 * @return string
	 */
	abstract public function serialize($mixSource) : string;

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Implementation Converters ////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method returns the deserialized output as a PHP array
	 * @access public
	 * @name \Crux\Serialize\Es::toArray()
	 * @package \Crux\Serialize\Es
	 * @return array<string, mixed>|array<int, mixed>
	 */
	abstract public function toArray();

	/**
	 * This method returns the deserialized output as a PHireworks Collection
	 * @access public
	 * @name \Crux\Serialize\Es::toCollection()
	 * @package \Crux\Serialize\Es
	 * @return \Crux\Collection\Map|\Crux\Collection\Vector
	 */
	abstract public function toCollection();

	/**
	 * This method converts the deserialized output as a JSON string
	 * @access public
	 * @name \Crux\Serialize\Es::toJson()
	 * @package \Crux\Serialize\Es
	 * @param bool $blnPrettyPrint [false]
	 * @return string
	 */
	abstract public function toJson(bool $blnPrettyPrint = false) : string;

	/**
	 * This method returns the deserialized output as a standard PHP class
	 * @access public
	 * @name \Crux\Serialize\Es::toObject()
	 * @package \Crux\Serialize\Es
	 * @return \stdClass
	 */
	abstract public function toObject() : \stdClass;

	/**
	 * This method converts the deserialized output as a PHireworks Variant
	 * @access public
	 * @name \Crux\Serialize\Es::toVariant()
	 * @package \Crux\Serialize\Es
	 * @return \Crux\Type\Variant|\Crux\Type\VariantList|\Crux\Type\Map
	 */
	abstract public function toVariant();

	/**
	 * This method converts the deserialized output as an XML string
	 * @access public
	 * @name \Crux\Serialize\Es::toXml()
	 * @package \Crux\Serialize\Es
	 * @param bool $blnPrettyPrint [false]
	 * @return string
	 */
	abstract public function toXml(bool $blnPrettyPrint = false) : string;

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
} /// End Crux\Serialize\Es Abstract Class Definition ////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

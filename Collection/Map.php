<?php

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Crux\Collection Namespace ////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

namespace Crux\Collection;

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Imports //////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

use Crux\Core;
use Crux\Serialize;

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Crux\Collection\Map Class Definition //////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class Map extends \ArrayObject implements \JsonSerializable
{
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Factory //////////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method instantiates the Map into a fluid interface
	 * @access public
	 * @name \Crux\Collection\Map::Factory()
	 * @package Crux\Collection\Map
	 * @return \Crux\Collection\Map
	 * @static
	 * @uses \Crux\Collection\Map::Map()
	 */
	public static function Factory()
	{
		// Return the new instance
		return new self();
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Constructs ///////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method converts an associative array to a map
	 * @access public
	 * @name \Crux\Collection\Map::fromArray()
	 * @package \Crux\Collection\Map
	 * @param $mixSource
	 * @return \Crux\Collection\Map
	 * @static
	 * @throws \Crux\Core\Exception\Collection\Map
	 * @uses \Crux\Core\Is::associativeArray()
	 * @uses \Crux\Collection\Map::fromArray()
	 * @uses \Crux\Core\Is::sequentialArray()
	 * @uses \Crux\Collection\Vector::fromArray()
	 * @uses \Crux\Core\Is::object()
	 * @uses \Crux\Collection\Map::fromObject()
	 */
	public static function fromArray(array $mixSource)
	{
		// Check for an array
		if (!Core\Is::associativeArray($mixSource)) {
			// We're done, this is not an associative array
			throw new Core\Exception\Collection\Map('Source variable must be of type array.');
		}
		// Check for an existing map
		if ($mixSource instanceof Map) {
			// Convert the map back to an associative array
			$mixSource = $mixSource->toArray();
		}
		// Create the new map
		$mapData = new self('string', 'mixed');
		// Iterate over the array
		foreach ($mixSource as $strKey => $mixValue) {
			// Check for an array
			if (Core\Is::associativeArray($mixValue)) {
				// Convert the sub array to a map
				$mapData->set($strKey, self::fromArray($mixValue));
			} elseif (Core\Is::sequentialArray($mixValue)) {
				// Convert the sub array to a vector
				$mapData->set($strKey, Vector::fromArray($mixValue));
			} elseif (Core\Is::object($mixValue)) {
				// Convert the sub object to a map
				$mapData->set($strKey, self::fromObject($mixValue));
			} else {
				// Set the key into the map
				$mapData->set($strKey, $mixValue);
			}
		}
		// We're done, return the new map
		return $mapData;
	}

	/**
	 * This method converts an object to a map
	 * @access public
	 * @name \Crux\Collection\Map::fromObject()
	 * @package \Crux\Collection\Map
	 * @param object $objSource
	 * @return \Crux\Collection\Map|\Crux\Collection\Vector
	 * @static
	 * @throws \Crux\Core\Exception\Collection\Map
	 * @uses \Crux\Core\Is::associativeArray()
	 * @uses \Crux\Collection\Map::fromArray()
	 * @uses \Crux\Core\Is::sequentialArray()
	 * @uses \Crux\Collection\Vector::fromArray()
	 * @uses \Crux\Core\Is::object()
	 * @uses \Crux\Collection\Map::fromObject()
	 */
	public static function fromObject($objSource)
	{
		// Check for a map
		if ($objSource instanceof Map) {
			// We're done, we already have a map
			return $objSource;
		}
		// Check for a vector
		if ($objSource instanceof Vector) {
			// We're done, we have a vector
			return $objSource;
		}
		// Create the new map container
		$mapData = new self();
		// Iterate over the object's properties
		foreach (get_object_vars($objSource) as $strKey => $mixValue) {
			// Check for an array
			if (Core\Is::associativeArray($mixValue)) {
				// Convert the sub array to a map
				$mapData->set($strKey, Map::fromArray($mixValue));
			} elseif (Core\Is::sequentialArray($mixValue)) {
				// Convert the sub array to a vector
				$mapData->set($strKey, Vector::fromArray($mixValue));
			} elseif (Core\Is::standardClass($mixValue)) {
				// Convert the sub object to a map
				$mapData->set($strKey, Map::fromObject($mixValue));
			} else {
				// Set the key into the map
				$mapData->set($strKey, $mixValue);
			}
		}
		// We're done, return the new map
		return $mapData;
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Constructor //////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method instantiates the Map Collection
	 * @access public
	 * @name \Crux\Collection\Map::Map()
	 * @package Crux\Collection\Map
	 * @uses \ArrayObject::ArrayObject()
	 */
	public function __construct()
	{
		// Execute the parent constructor
		parent::__construct([], self::ARRAY_AS_PROPS);
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Implementations //////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method converts the map to a string when the instance is referenced as a string
	 * @access public
	 * @name \Crux\Collection\Map::__toString()
	 * @package \Crux\Collection\Map
	 * @return string
	 * @uses \Crux\Collection\Map::toString()
	 */
	public function __toString() : string
	{
		// Return the string value of the map
		return $this->toString();
	}

	/**
	 * This method converts the map to a JSON serializable data type
	 * @access public
	 * @name \Crux\Collection\Map::jsonSerialize()
	 * @package \Crux\Collection\Map
	 * @return array
	 * @uses \Crux\Collection\Map::toArray()
	 */
	public function jsonSerialize()
	{
		// Return an array
		return $this->toArray();
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Public Methods ///////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method removes all of the elements from the current vector
	 * @access public
	 * @name \Crux\Collection\Map::clear()
	 * @package \Crux\Collection\Map
	 * @return \Crux\Collection\Map $this
	 * @uses \Crux\Collection\Map::keys()
	 * @uses \Crux\Collection\Vector::getIterator()
	 * @uses \Crux\Collection\Map::offsetUnset()
	 */
	public function clear() : Map
	{
		// Iterate over the keys
		foreach ($this->keys()->getIterator() as $strKey) {
			// Unset the index
			$this->offsetUnset($strKey);
		}
		// We're done, return the instance
		return $this;
	}

	/**
	 * This method returns whether or not a key exists in the map
	 * @access public
	 * @name \Crux\Collection\Map::contains()
	 * @package Crux\Collection\Map
	 * @param $mixNeedle
	 * @return bool
	 * @uses \Crux\Collection\Map::searchMapForKey()
	 */
	public function contains($mixNeedle) : bool
	{
		// Search for the key
		if (($mixKey = $this->searchMapForKey($mixNeedle)) === null) {
			// We're done, the key does not exist
			return false;
		} else {
			// We're done, the key exists
			return true;
		}
	}

	/**
	 * This method returns a key's value from the map if it exists
	 * @access public
	 * @name \Crux\Collection\Map::get()
	 * @package Crux\Collection\Map
	 * @param $mixNeedle
	 * @param $mixDefault [null]
	 * @return mixed|null
	 * @throws Core\Exception\Collection\Map
	 * @uses \Crux\Collection\Map::validateType()
	 * @uses \Crux\Collection\Map::searchMapForKey()
	 */
	public function get($mixNeedle, $mixDefault = null)
	{
		// Search the map for the key
		if (($mixKey = $this->searchMapForKey($mixNeedle)) === null) {
			// We're done, the key does not exist
			return $mixDefault;
		} else {
			// We're done, the key exits
			return $this->offsetGet($mixKey);
		}
	}

	/**
	 * This method returns the empty state of the map
	 * @access public
	 * @name \Crux\Collection\Map::isEmpty()
	 * @package Crux\Collection\Map
	 * @return bool
	 * @uses \Crux\Collection\Map::count()
	 */
	public function isEmpty() : bool
	{
		// Check the property count
		if ($this->count() === 0) {
			// We're done, the map is empty
			return true;
		} else {
			// We're done, the map is not empty
			return false;
		}
	}

	/**
	 * This method searches the map for a desired key with insensitivity
	 * @access public
	 * @name \Crux\Collection\Map::key()
	 * @package \Crux\Collection\Map
	 * @param mixed $mixNeedle
	 * @return int|null|string
	 * @uses \Crux\Collection\Map::searchMapForKey()
	 */
	public function key($mixNeedle)
	{
		// Return the key search
		return $this->searchMapForKey($mixNeedle);
	}

	/**
	 * This method returns a vector of keys from the map
	 * @access public
	 * @name \Crux\Collection\Map::keys()
	 * @package Crux\Collection\Map
	 * @return Vector
	 * @uses \Crux\Collection\Map::toKeysArray()
	 * @uses \Crux\Collection\Vector::Vector()
	 */
	public function keys() : Vector
	{
		// Return the new vector of keys
		return new Vector($this->mKeyType, $this->toKeysArray());
	}

	/**
	 * This method removes a key from the map
	 * @access public
	 * @name \Crux\Collection\Map::remove()
	 * @package Crux\Collection\Map
	 * @param $mixNeedle
	 * @return \Crux\Collection\Map $this
	 * @uses \Crux\Collection\Map::searchMapForKey()
	 * @uses \Crux\Collection\Map::offsetUnset()
	 */
	public function remove($mixNeedle) : Map
	{
		// Check for the key
		if (($mixKey = $this->searchMapForKey($mixNeedle)) !== null) {
			// Remove the key
			$this->offsetUnset($mixKey);
		}
		// We're done, return the instance
		return $this;
	}

	/**
	 * This method is an alias of \Crux\Collection\Map::remove()
	 * @access public
	 * @name \Crux\Collection\Map ::removeKey()
	 * @package \Crux\Collection\Map
	 * @param string $strKey
	 * @return \Crux\Collection\Map $this
	 * @see \Crux\Collection\Map::remove()
	 * @uses \Crux\Collection\Map::remove()
	 */
	public function removeKey(string $strKey) : Map
	{
		// Return the removal
		return $this->remove($strKey);
	}

	/**
	 * This method searches the map for a key and returns either it's value or name
	 * @access public
	 * @name \Crux\Collection\Map::search()
	 * @package \Crux\Collection\Map
	 * @param mixed $mixNeedle
	 * @param bool $blnReturnValue [true]
	 * @return int|mixed|null|string
	 * @uses \Crux\Collection\Map::get()
	 * @uses \Crux\Collection\Map::key()
	 */
	public function search($mixNeedle, bool $blnReturnValue = true)
	{
		// Return the search
		return ($blnReturnValue ? $this->get($mixNeedle) : $this->key($mixNeedle));
	}

	/**
	 * This method searches the map for a key using case insensitivity for string keys
	 * @access public
	 * @name \Crux\Collection\Map::searchMapForKey()
	 * @package Crux\Collection\Map
	 * @param $mixNeedle
	 * @return mixed
	 * @uses \Crux\Collection\Map::getIterator()
	 * @uses is_string()
	 * @uses strtolower()
	 */
	protected function searchMapForKey($mixNeedle)
	{
		// Iterate over the map
		foreach ($this->toKeysArray() as $mixKey) {
			// Check the key for a string
			if (Core\Is::string($mixKey) && (strtolower($mixKey) === strtolower($mixNeedle))) {
				// We're done, return the key
				return $mixKey;
			} elseif (Core\Is::number($mixKey) && ((string) $mixKey === strtolower($mixNeedle))) {
				// We're done, return the key
				return $mixKey;
			} elseif ($mixKey === $mixNeedle) {
				// We're done, return the key
				return $mixKey;
			}
		}
		// We're done, return null if the keey is not found
		return null;
	}

	/**
	 * This method sets a key and value into the map
	 * @access public
	 * @name \Crux\Collection\Map::set()
	 * @package Crux\Collection\Map
	 * @param $mixKey
	 * @param $mixValue
	 * @return \Crux\Collection\Map $this
	 * @throws Core\Exception\Collection\Map
	 * @uses \Crux\Collection\Map::validateType()
	 * @uses \Crux\Collection\Map::offsetSet()
	 */
	public function set($mixKey, $mixValue)
	{
		// Set the value into the map
		$this->offsetSet($mixKey, $mixValue);
		// We're done, return the instance
		return $this;
	}

	/**
	 * This method
	 * @param $mixKey
	 * @param $mixValue
	 * @param $fnCallback
	 * @return Map
	 */
	public function setWith($mixKey, $mixValue, callable$fnCallback) : Map
	{
		// Check for a function
		if (Core\Is::closure($fnCallback)) {
			// Call the function directly
			$fnCallback($mixKey, $mixValue, $this);
		} else {
			// Filter the key and value
			call_user_func_array($fnCallback, [&$mixKey, &$mixValue, $this]);
		}
		// Set the key into the instance
		return $this->set($mixKey, $mixValue);
	}

	/**
	 * This method returns the map as a traditional associative array
	 * @access public
	 * @name \Crux\Collection\Map::toArray()
	 * @package Crux\Collection\Map
	 * @return array
	 * @uses \Crux\Collection\Map::toArray()
	 * @uses \Crux\Collection\Vector::toArray()
	 */
	public function toArray() : array
	{
		// Create the container array
		$arrMap = [];
		// Iterate over the instance
		foreach ($this->getIterator() as $strKey => $mixValue) {
			// Check for a nested constructs
			if ($mixValue instanceof Map) {// Map
				// Set the key into the array
				$arrMap[$strKey] = $mixValue->toArray();
			} elseif ($mixValue instanceof Vector) {// Vector
				// Set the key into the array
				$arrMap[$strKey] = $mixValue->toArray();
			} else {
				// Set the key into the array
				$arrMap[$strKey] = $mixValue;
			}
		}
		// Return the array version of the map
		return $this->getArrayCopy();
	}

	/**
	 * This method encodes the map into a JSON string
	 * @access public
	 * @name \Crux\Collection\Map::toJson()
	 * @package \Crux\Collection\Map
	 * @return string
	 * @uses \Crux\Collection\Map::toArray()
	 * @uses json_encode()
	 */
	public function toJson() : string
	{
		// Return the JSON encoding of the map
		return json_encode($this->toArray(), JSON_PRETTY_PRINT);
	}

	/**
	 * This method returns an array of keys from the map
	 * @access public
	 * @name \Crux\Collection\Map::toKeysArray()
	 * @package Crux\Collection\Map
	 * @return array
	 * @uses \Crux\Collection\Map::getIterator()
	 * @uses array_push()
	 */
	public function toKeysArray() : array
	{
		// Create the keys container
		$arrKeys = [];
		// Iterate over the instance
		foreach ($this->getIterator() as $strKey => $mixValue) {
			// Append the key to the container
			array_push($arrKeys, $strKey);
		}
		// Return the keys
		return $arrKeys;
	}

	/**
	 * This method converts a map collection to stdClass
	 * @access public
	 * @name \Crux\Collection\Map::toObject()
	 * @return \stdClass
	 * @uses \stdClass::__constructor()
	 * @uses \Crux\Collection\Map::getIterator()
	 * @uses \Crux\Collection\Map::toObject()
	 */
	public function toObject() : \stdClass
	{
		// Create the new class
		$objMap = new \stdClass();
		// Iterate over the instance
		foreach ($this->getIterator() as $strKey => $mixValue) {
			// Check for a nested map
			if ($mixValue instanceof Map) {
				// Set the property into the object
				$objMap->{$strKey} = $mixValue->toObject();
			} else {
				// Set the property into the object
				$objMap->{$strKey} = $mixValue;
			}
		}
		// We're done, return the new object
		return $objMap;
	}

	/**
	 * This method serializes the map into a byte string
	 * @access public
	 * @name \Crux\Collection\Map::toString()
	 * @package \Crux\Collection\Map
	 * @return string
	 * @uses serialize()
	 */
	public function toString() : string
	{
		// Return the string value of the map
		return serialize($this);
	}

	/**
	 * This method returns an array of values from the map
	 * @access public
	 * @name \Crux\Collection\Map::toValuesArray()
	 * @package Crux\Collection\Map
	 * @return array
	 * @uses \Crux\Collection\Map::getIterator()
	 * @uses array_push()
	 */
	public function toValuesArray() : array
	{
		// Create the values container
		$arrValues = [];
		// Iterate over the instance
		foreach ($this->getIterator() as $strKey => $mixValue) {
			// Append the value to the container
			array_push($arrValues, $mixValue);
		}
		// We're done, return the values
		return $arrValues;
	}

	/**
	 * This method seamlessly converts the map to XML
	 * @access public
	 * @name \Crux\Collection\Map::toXml()
	 * @package \Crux\Colleciton\Map
	 * @param string $strRootNode ['map']
	 * @param bool $blnIncludeHeader [true]
	 * @return string
	 * @uses \Crux\Serialize\Xml::__construct()
	 * @uses \Crux\Serialize\Xml::rootNode()
	 * @uses \Crux\Serialize\Xml::defineDocument()
	 * @uses \Crux\Serialize\Xml::dontDefineDocument()
	 * @uses \Crux\Serialize\Xml::serialize()
	 * @uses \Crux\Collection\Map::toArray()
	 */
	public function toXml(string $strRootNode = 'map', bool $blnIncludeHeader = true) : string
	{
		// Instantiate our serializer
		$xmlSerializer = new Serialize\Xml();
		// Set the root node
		$xmlSerializer->rootNode($strRootNode);
		// Check the header flag
		if ($blnIncludeHeader) {
			// Include the headers
			$xmlSerializer->defineDocument();
		} else {
			// Don't include the header
			$xmlSerializer->dontDefineDocument();
		}
		// We're done, return the XML
		return $xmlSerializer->serialize($this->toArray());
	}

	/**
	 * This method returns a vector of values from the map
	 * @access public
	 * @name \Crux\Collection\Map::values()
	 * @package Crux\Collection\Map
	 * @return Vector
	 * @uses \Crux\Collection\Map::toValuesArray()
	 * @uses \Crux\Collection\Vector::Vector()
	 */
	public function values() : Vector
	{
		// Return the new vector of values
		return new Vector($this->mValueType, $this->toValuesArray());
	}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
} /// End Crux\Collection\Map Class Definition ///////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

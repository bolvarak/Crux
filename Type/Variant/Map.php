<?php

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Crux\Type\Variant Namespace //////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

namespace Crux\Type\Variant;

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Imports //////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

use Crux\Core;
use Crux\Collection;
use Crux\Type;

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Crux\Type\Variant\Map Class Definition ///////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class Map extends Collection\Map implements \JsonSerializable
{
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Traits ///////////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	
	use Type\Variant;
	
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Properties ///////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This property contains the original data type name of the source data
	 * @access private
	 * @name \Crux\Type\Variant\Map::$mOriginalTypeName
	 * @package \Crux\Type\Variant\Map
	 * @var
	 */
	private $mOriginalTypeName = null;

	/**
	 * This property contains the type of the original variable
	 * @access private
	 * @name \Crux\Type\Variant\Map::$mOriginalVariableType
	 * @package \Crux\Type\Variant\Map
	 * @var int
	 */
	private $mOriginalVariableType = Core\Api::Array;

	/**
	 * This property contains the original variable's class instance name
	 * @access private
	 * @name \Crux\Type\Variant\Map::$mOriginalInstanceName
	 * @package \Crux\Type\Variant\Map
	 * @var string
	 */
	private $mOriginalInstanceName = '';

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Constructor //////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method sets up the instance with existing data
	 * @access public
	 * @name \Crux\Type\Variant\Map ::__construct()
	 * @package \Crux\Type\Variant\Map
	 * @param mixed $mixSource [null]
	 * @uses \Crux\Collection\Map::__constructor()
	 * @uses \Crux\Core\Is::associativeArray()
	 * @uses \Crux\Collection\Map::fromArray()
	 * @uses \Crux\Core\Is::map()
	 * @uses \Crux\Core\Is::variantMap()
	 * @uses \Crux\Core\Is::object()
	 * @uses gettype()
	 */
	public function __construct($mixSource = null)
	{
		// Execute the parent constructor
		parent::__construct();
		// Set the original data type
		$this->mOriginalTypeName = gettype($mixSource);
		// Determine the type for data we are getting
		if (Core\Is::associativeArray($mixSource)) {
			// Set the original variable type
			$this->mOriginalVariableType = Core\Api::Array;
			// Construct the base
			$mapSource = Collection\Map::fromArray($mixSource);
		} elseif (Core\Is::map($mixSource)) {
			// Set the original variable type
			$this->mOriginalVariableType = Core\Api::Map;
			// Set the data to the source
			$mapSource = $mixSource;
		} elseif (Core\Is::variantMap($mixSource)) {
			// We're done, return the source
			return $mixSource;
		} elseif (Core\Is::object($mixSource)) {
			// Set the original variable type
			$this->mOriginalVariableType = Core\Api::Object;
			// Check for a class name
			if (($strClassName = @get_class($mixSource)) !== false) {
				// Set the class name
				$this->mOriginalInstanceName = $strClassName;
			}
			// Construct the base
			$mapSource = Collection\Map::fromObject($mixSource);
		} else {
			// Set the original variable type
			$this->mOriginalVariableType = Core\Api::Map;
			// Construct the base
			$mapSource = new Collection\Map();
		}
		// Iterate over the keys
		foreach ($mapSource->getIterator() as $mixKey => $mixValue) {
			// Set the value into the instance
			$this->set($mixKey, $mixValue);
		}
		// Destroy the pointer
		unset($mapSource);
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Magic Methods ////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method allows for dynamic calling of conversion extensions
	 * @access public
	 * @name \Crux\Type\Variant\Map::__call()
	 * @package \Crux\Type\Variant\Map
	 * @param string $strMethod
	 * @param array $arrArguments
	 * @return mixed
	 * @throws \Crux\Core\Exception\Type\Variant\Map
	 * @uses \Crux\Core\Api::$mVariantMapExtensions
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
			foreach (Core\Api::$mVariantMapExtensions as $strName => $fnCallback) {
				// Check the name
				if (strtolower(substr_replace($strMethod, '', 0, 2)) === strtolower($strName)) {
					// Execute the callback
					return call_user_func_array($fnCallback, [$this->mData, $this]);
				}
			}
			// No extension available, we're done
			throw new Core\Exception\Type\Variant\Map(sprintf('Extension [%s] does not exist.', substr_replace($strMethod, '', 0, 2)));
		} else {
			// No method or extension available, we're done
			throw new Core\Exception\Type\Variant\Map(sprintf('Method or Extension [%s] does not exist.', $strMethod));
		}
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Public Static Methods ////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method adds a conversion extension to the construct
	 * @access public
	 * @name \Crux\Type\Variant\Map::addExtension()
	 * @package \Crux\Type\Variant\Map
	 * @param $strName
	 * @param callable $fnCallback
	 * @return void
	 * @static
	 * @uses \Crux\Core\Api::addVariant\MapExtension()
	 */
	public static function addExtension($strName, callable $fnCallback)
	{
		// Add the extension
		Core\Api::addVariantMapExtension($strName, $fnCallback);
	}

	/**
	 * This method constructs a new instance from an associative array
	 * @access public
	 * @name \Crux\Type\Variant\Map ::fromArray()
	 * @package \Crux\Type\Variant\Map
	 * @param array $arrSource
	 * @return \Crux\Type\Variant\Map
	 * @static
	 * @uses \Crux\Type\Map::__constructor()
	 */
	public static function fromArray(array $arrSource)
	{
		// Return the new instance
		return new self($arrSource);
	}

	/**
	 * This method constructs a new instance from a Map
	 * @access public
	 * @name \Crux\Type\Variant\Map ::fromMap()
	 * @package \Crux\Type\Variant\Map
	 * @param \Crux\Collection\Map $mapSource
	 * @return \Crux\Type\Variant\Map
	 * @static
	 * @uses \Crux\Type\Map::__constructor()
	 */
	public static function fromMap(Collection\Map $mapSource) : Map
	{
		// Return the new instance
		return new self($mapSource);
	}

	/**
	 * This method constructs a new instance from an object
	 * @access public
	 * @name \Crux\Type\Variant\Map ::fromObject()
	 * @package \Crux\Type\Variant\Map
	 * @param object|\stdClass $objSource
	 * @return \Crux\Type\Variant\Map
	 * @static
	 * @uses \Crux\Type\Map::__constructor()
	 */
	public static function fromObject($objSource)
	{
		// Return the new instance
		return new self($objSource);
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Implementations //////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method converts the variant map to a string
	 * @access public
	 * @name \Crux\Type\Variant\Map::__toString()
	 * @package \Crux\Type\Variant\Map
	 * @return string
	 * @uses \Crux\Type\Map::toString()
	 */
	public function __toString() : string
	{
		// Return the encoded string value of the class
		return $this->toString();
	}

	/**
	 * This method converts a variant to JSON
	 * @access public
	 * @name \Crux\Type\Variant\Map::jsonSerialize()
	 * @package \Crux\Type\Variant\Map
	 * @return mixed
	 * @uses \Crux\Type\Map::getData()
	 */
	public function jsonSerialize()
	{
		// Return the map data
		return $this->getData();
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Public Methods ///////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method searches the Map for a key with case-insensitivity and returns the data if found, Variant::Factory(null) elsewise
	 * @access public
	 * @name \Crux\Type\Variant\Map ::at()
	 * @package \Crux\Type\Variant\Map
	 * @param mixed $mixKey
	 * @param mixed $mixDefault [null]
	 * @return \Crux\Type\Variant
	 * @uses \PH
	 */
	public function at($mixKey, $mixDefault = null) : Type\Variant
	{
		// Check for the key
		if (($mixRealKey = $this->search($mixKey)) !== null) {
			// Return the data
			return $this->get($mixRealKey);
		}
		// Return an empty variant
		return self::Factory($mixDefault);
	}

	/**
	 * This is an alias of Variant\Map::contains()
	 * @access public
	 * @name Variant\Map ::containsKey()
	 * @param string $strKey
	 * @return bool
	 * @see \Crux\Type\Variant\Map::contains()
	 */
	public function containsKey(string $strKey) : bool
	{
		// Return the comparison
		return $this->contains($strKey);
	}

	/**
	 * This method returns a value from the variant map
	 * @access public
	 * @name \Crux\Type\Variant\Map::get()
	 * @package \Crux\Type\Variant
	 * @param $mixKey
	 * @param $mixDefault [null]
	 * @return mixed|null|Type\Variant
	 * @throws Core\Exception\Type\Variant
	 * @uses \Crux\Core\Is::null()
	 * @uses \Crux\Type\Variant::Factory()
	 * @uses \Crux\Collection\Map::get()
	 */
	public function get($mixKey, $mixDefault = null)
	{
		// Check for a separator
		if (stripos($mixKey, '.') !== false) {
			// Set the data
			$mapData = clone $this;
			// Grab the parts
			$arrParts = explode('.', $mixKey);
			// Iterate over the parts
			foreach ($arrParts as $intIndex => $strKey) {
				// Check for the key
				if ($mapData->contains($strKey)) {
					// Reset the data
					$mapData = $mapData->get($strKey);
				} else {
					// Reset the data
					$mapData = (($intIndex === (count($arrParts) - 1)) ? self::Factory($mixDefault) : new self());
				}
			}
			// We're done, return the data
			return $mapData;
		} elseif (Core\Is::null(parent::get($mixKey))) {
			// We're done, return a null variant
			return self::Factory(null);
		} else {
			// Return the variant
			return parent::get($mixKey);
		}
	}

	/**
	 * This method sets a new key into the instance
	 * @access public
	 * @name \Crux\Type\Variant\Map ::set()
	 * @package \Crux\Type\Variant\Map
	 * @param mixed $mixKey
	 * @param mixed $mixValue
	 * @return \Crux\Type\Variant\Map $this
	 * @uses \Crux\Collection\Map::setWith()
	 * @uses \Crux\Core\Is::associativeArray()
	 * @uses \Crux\Type\Map::fromArray()
	 * @uses \Crux\Core\Is::map()
	 * @uses \Crux\Collection\Map::toArray()
	 * @uses \Crux\Type\Map::fromArray()
	 * @uses \Crux\Core\Is::vector()
	 * @uses \Crux\Collection\Vector::toArray()
	 * @uses \Crux\Type\VariantList::fromArray()
	 * @uses \Crux\Core\Is::object()
	 * @uses \Crux\Type\Map::fromObject()
	 * @uses \Crux\Type\Variant::Factory()
	 */
	public function set($mixKey, $mixValue)
	{
		// Check the data type
		if (Core\Is::associativeArray($mixValue)) {
			// Convert the data to a variant map
			$mixData = Map::fromArray($mixValue);
		} elseif (Core\Is::map($mixValue)) {
			// Convert the data to a variant map
			$mixData = Map::fromArray($mixValue->toArray());
		} elseif (Core\Is::vector($mixValue)) {
			// Convert the data to a variant list
			$mixData = Vector::fromArray($mixValue->toArray());
		} elseif (Core\Is::sequentialArray($mixValue)) {
			// Convert the data to a variant list
			$mixData = Vector::fromArray($mixValue);
		} elseif (Core\Is::variantInterface($mixValue)) {
			// We're done, the data is sane
			$mixData = $mixValue;
		} elseif (Core\Is::object($mixValue)) {
			// Convert the data to a variant map
			$mixData = Map::fromObject($mixValue);
		} else {
			// Convert the data to a variant
			$mixData = self::Factory($mixValue);
		}
		// Call the parent setter
		parent::set($mixKey, $mixData);
		// We're done, return the instance
		return $this;
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Converters ///////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method converts the variant map to a JSON string
	 * @access public
	 * @name \Crux\Type\Variant\Map::toJson()
	 * @package \Crux\Type\Variant\Map
	 * @return string
	 * @uses \Crux\Type\Map::getData()
	 */
	public function toJson() : string
	{
		// Return the json value of the variant map
		return json_encode($this->getData(), JSON_PRETTY_PRINT);
	}

	/**
	 * This method returns the Map's keys as a vector
	 * @access public
	 * @name Variant\Map ::toKeysVector()
	 * @return \Crux\Collection\Vector<string>
	 */
	public function toKeysVector() : Collection\Vector
	{
		// Return the keys vector
		return new Collection\Vector($this->toKeysArray());
	}

	/**
	 * This method converts the Variant\Map to a standard map
	 * @access public
	 * @name \Crux\Type\Variant\Map::toMap(0
	 * @package \Crux\Type\Variant\Map
	 * @return \Crux\Collection\Map
	 * @uses \Crux\Collection\Map::__construct()
	 * @uses \Crux\Core\Is::variant()
	 * @uses \Crux\Core\Is::variantList()
	 * @uses \Crux\Core\Is::variantMap()
	 * @uses \Crux\Type\Variant::getData()
	 * @uses \Crux\Type\VariantList::toVector()
	 * @uses \Crux\Type\Map::toMap()
	 * @uses \Crux\Collection\Map::set()
	 */
	public function toMap() : Collection\Map
	{
		// Create our new map container
		$mapContainer = new Collection\Map();
		// Iterate over the instance
		foreach ($this->getIterator() as $strKey => $mixValue) {
			// Check the data
			if (Core\Is::variant($mixValue)) {
				// Set the value
				$mapContainer->set($strKey, $mixValue->getData());
			} elseif (Core\Is::variantVector($mixValue)) {
				// Set the value
				$mapContainer->set($strKey, $mixValue->toVector());
			} elseif (Core\Is::variantMap($mixValue)) {
				// Set the value
				$mapContainer->set($strKey, $mixValue->toMap());
			} else {
				// Set the value
				$mapContainer->set($strKey, $mixValue);
			}
		}
		// We're done, return the new map
		return $mapContainer;
	}

	/**
	 * This method converts the variant map to a string
	 * @access public
	 * @name \Crux\Type\Variant\Map::toString()
	 * @package \Crux\Variant\Map
	 * @return string
	 * @uses \Crux\Type\Map::getData()
	 * @uses serialize()
	 */
	public function toString() : string
	{
		// Return the variant map as a string
		return serialize($this);
	}

	/**
	 * This method returns the the Map's values as a vector with the values in the original type
	 * @access public
	 * @name Variant\Map ::toValuesVector()
	 * @return Collection\Vector<mixed>
	 */
	public function toValuesVector() : Collection\Vector
	{
		// Return the values vector
		return Collection\Vector::fromArray($this->toKeysArray());
	}

	/**
	 * This method returns the data as an array with the values in Variant form
	 * @access public
	 * @name Variant\Map ::toVariantArray()
	 * @return array
	 */
	public function toVariantArray() : array
	{
		// Return the data
		return $this->mData->toArray();
	}

	/**
	 * This method returns the Map's values as an array of Variants
	 * @access public
	 * @name Variant\Map ::toVariantValuesArray()
	 * @return array
	 */
	public function toVariantValuesArray() : array
	{
		// Return the values array
		return $this->mData->toValuesArray();
	}

	/**
	 * This method returns the Map's values as a Vector of Variants
	 * @access public
	 * @name Variant\Map ::toVariantValuesVector()
	 * @return Collection\Vector<Variant>
	 */
	public function toVariantValuesVector() : Collection\Vector
	{
		// return the values vector
		return Collection\Vector::fromArray($this->toVariantValuesArray());
	}

	/**
	 * This method converts a Variant\Map to XML
	 * @access public
	 * @name \Crux\Type\Variant\Map::toXml()
	 * @package \Crux\Type\Variant\Map
	 * @param string $strRootNode ['variantMap']
	 * @param bool $blnIncludeHeaders [true]
	 * @return string
	 * @uses \Crux\Type\Map::toMap()
	 * @uses \Crux\Collection\Map::toXml()
	 */
	public function toXml(string $strRootNode = 'variantMap', bool $blnIncludeHeaders = true) : string
	{
		// Convert and return the XML
		return $this->toMap()->toXml($strRootNode, $blnIncludeHeaders);
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Getters //////////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method returns the data in its original type
	 * @access public
	 * @name Variant\Map ::getData()
	 * @return mixed
	 */
	public function getData()
	{
		// Create the response map
		$arrData = [];
		// Iterate over the data map
		foreach ($this->getIterator() as $strKey => $varValue) {
			// Reset the data into the new map
			$arrData[$strKey] = $varValue->getData();
		}
		// Check the original variable type
		if ($this->mOriginalVariableType === Core\Api::Array) {
			// We're done, send the data
			return $arrData;
		} elseif ($this->mOriginalVariableType === Core\Api::Map) {
			// Check for data
			if (Core\Is::empty($arrData)) {
				// We're done, return an empty map
				return Collection\Map::Factory();
			} else {
				// We're done, return the converted data
				return Collection\Map::fromArray($arrData);
			}
		} else {
			// Create the new instance
			$instOrigin = $this->toObject();
			// Iterate over the properties
			foreach ($arrData as $strKey => $mixValue) {
				// Set the property into the instance
				$instOrigin->$strKey = $mixValue;
			}
			// Return the instance
			return $instOrigin;
		}
	}

	/**
	 * This method returns the original source's data type name from the instance
	 * @access public
	 * @name \Crux\Type\Variant\Map::getOriginalType()
	 * @package \Crux\Type\Variant\Map
	 * @return string
	 */
	public function getOriginalType() : string
	{
		// Return the original data type name from the instance
		return $this->mOriginalTypeName;
	}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
} /// End Crux\Type\Variant\Map Class Definition //////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

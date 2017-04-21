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
/// Crux\Type\Variant\Vector Class Definition ////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class Vector extends Collection\Vector implements \JsonSerializable
{
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Traits ///////////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	use Type\Variant;

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Properties ///////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This property defines the original type of the variable
	 * @access private
	 * @name \Crux\Type\Variant\Vector::$mOriginalType
	 * @package \Crux\Type\Variant\Vector
	 * @var int
	 */
	private $mOriginalType = Core\Api::Array;

	/**
	 * This property contains the name for the original data type
	 * @access private
	 * @name \Crux\Type\Variant\Vector::$mOriginalTypeName
	 * @package \Crux\Type\Variant\Vector
	 * @var string
	 */
	private $mOriginalTypeName = null;

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Constructor //////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method sets up the instance with existing data
	 * @access public
	 * @name \Crux\Type\Variant\Vector::__constructor()
	 * @package \Crux\Type\Variant\Vector
	 * @param array|null|\Crux\Collection\Vector $mixSource
	 * @uses \Crux\Collection\Vector::__construct()
	 * @uses \Crux\Core\Is::sequentialArray()
	 * @uses \Crux\Collection\Vector::fromArray()
	 * @uses \Crux\Collection\Vector::getIterator();
	 * @uses \Crux\Type\Variant\Vector::set()
	 * @uses gettype()
	 */
	public function __construct($mixSource = null)
	{
		// Execute the parent construct
		parent::__construct();
		// Set the original data type name
		$this->mOriginalTypeName = gettype($mixSource);
		// Check for data
		if (Core\Is::sequentialArray($mixSource)) {
			// Set the original type
			$this->mOriginalType = Core\Api::Array;
			// Create a new vector
			$vecTemp = Collection\Vector::fromArray($mixSource);
		} elseif (Core\Is::vector($mixSource)) {
			// Set the original type
			$this->mOriginalType = Core\Api::Vector;
			// Set the data into the instance
			$vecTemp = $mixSource;
		} elseif (Core\Is::variantVector($mixSource)) {
			// We're done, we already have a variant
			return $mixSource;
		} else {
			// Set the original type
			$this->mOriginalType = Core\Api::Vector;
			// Create an empty vector
			$vecTemp = new Collection\Vector();
		}
		// Iterate over the new vector
		foreach ($vecTemp->getIterator() as $intIndex => $mixValue) {
			// Reset the data
			$this->set($intIndex, $mixValue);
		}
		// Clear the pointer
		$vecTemp = null;
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Magic Methods ////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method allows for dynamic calling of conversion extensions
	 * @access public
	 * @name \Crux\Type\Variant\Vector::__call()
	 * @package \Crux\Type\Variant\Vector
	 * @param string $strMethod
	 * @param array $arrArguments
	 * @return mixed
	 * @throws \Crux\Core\Exception\Type\Variant\Vector
	 * @uses \Crux\Core\Api::$mVariantVectorExtensions
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
			foreach (Core\Api::$mVariantVectorExtensions as $strName => $fnCallback) {
				// Check the name
				if (strtolower(substr_replace($strMethod, '', 0, 2)) === strtolower($strName)) {
					// Execute the callback
					return call_user_func_array($fnCallback, [$this->mData, $this]);
				}
			}
			// No extension available, we're done
			throw new Core\Exception\Type\Variant\Vector(sprintf('Extension [%s] does not exist.', substr_replace($strMethod, '', 0, 2)));
		} else {
			// No method or extension available, we're done
			throw new Core\Exception\Type\Variant\Vector(sprintf('Method or Extension [%s] does not exist.', $strMethod));
		}
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Public Static Methods ////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method constructs a new instance from a sequential array
	 * @access public
	 * @name \Crux\Type\Variant\Vector::fromArray()
	 * @package \Crux\Type\Variant\Vector
	 * @param array $arrSource
	 * @return \Crux\Type\Variant\Vector
	 * @static
	 * @uses \Crux\Type\Variant\Vector::__construct()
	 */
	public static function fromArray(array $arrSource) : Vector
	{
		// Return the new instance
		return new self($arrSource);
	}

	/**
	 * This method constructs a new instance from an existing vector
	 * @access public
	 * @name \Crux\Type\Variant\Vector::fromVector()
	 * @package \Crux\Type\Variant\Vector
	 * @param \Crux\Collection\Vector<mixed> $vecSource
	 * @return \Crux\Type\Variant\Vector
	 * @static
	 * @uses \Crux\Type\Variant\Vector::__construct()
	 */
	public static function fromVector(Collection\Vector $vecSource) : Vector
	{
		// Return the new instance
		return new self($vecSource);
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Implementations //////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method converts the variant list to a string when the instance is referenced as a string
	 * @access public
	 * @name \Crux\Type\Variant\Vector::__toString()
	 * @package \Crux\Type\Variant\Vector
	 * @return string
	 * @uses \Crux\Type\Variant\Vector::toString()
	 */
	public function __toString() : string
	{
		// Return the string copy of this class
		return $this->toString();
	}

	/**
	 * This method returns a json-encode-able construct of a variant
	 * @access public
	 * @name \Crux\Type\Variant\Vector::jsonSerialize()
	 * @package \Crux\Type\Variant\Vector
	 * @return mixed
	 * @uses \Crux\Type\Variant\Vector::getData()
	 */
	public function jsonSerialize()
	{
		// Return the json encode-able data
		return $this->getData();
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Public Static Methods ////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method adds a conversion extension to the construct
	 * @access public
	 * @name \Crux\Type\Variant\Vector::addExtension()
	 * @package \Crux\Type\Variant\Vector
	 * @param $strName
	 * @param callable $fnCallback
	 * @return void
	 * @static
	 * @uses \Crux\Core\Api::addVariantVectorExtension()
	 */
	public static function addExtension($strName, callable $fnCallback)
	{
		// Add the extension
		Core\Api::addVariantVectorExtension($strName, $fnCallback);
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Public Methods ///////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method adds an element to the vector
	 * @access public
	 * @name \Crux\Type\Variant\Vector ::add()
	 * @package \Crux\Type\Variant\Vector
	 * @param mixed $mixValue
	 * @return \Crux\Type\Variant\Vector $this
	 * @uses \Crux\Core\Is::associativeArray()
	 * @uses \Crux\Core\Is::map()
	 * @uses \Crux\Core\Is::vector()
	 * @uses \Crux\Core\Is::sequentialArray()
	 * @uses \Crux\Core\Is::object()
	 * @uses \Crux\Type\Map::fromArray()
	 * @uses \Crux\Type\Variant\Vector::fromArray()
	 * @uses \Crux\Type\Map::fromObject()
	 * @uses \Crux\Type\Variant::Factory()
	 */
	public function add($mixValue)
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
			$mixData = Vector::fromVector($mixValue);
		} elseif (Core\Is::sequentialArray($mixValue)) {
			// Convert the data to a variant list
			$mixData = Vector::fromArray($mixValue);
		} elseif (Core\Is::variant($mixValue)) {
			// We're done, the data is sane
			$mixData = $mixValue;
		} elseif (Core\Is::object($mixValue)) {
			// Convert the data to a variant map
			$mixData = Map::fromObject($mixValue);
		} else {
			// Convert the data to a variant
			$mixData = self::Factory($mixValue);
		}
		// Set the data into the instance
		parent::add($mixData);
		// We're done, return the instance
		return $this;
	}

	/**
	 * This method searches the Vector for a key with case-insensitivity and returns the data if found, Variant::Factory(null) elsewise
	 * @access public
	 * @name \Crux\Type\Variant\Vector ::at()
	 * @package \Crux\Type\Variant\Vector
	 * @param int $intKey
	 * @return \Crux\Type\Variant
	 * @uses \Crux\Collection\Vector::contains()
	 * @uses \Crux\Collection\Vector::at()
	 * @uses \Crux\Type\Variant::Factory()
	 */
	public function at(int $intKey) : Type\Variant
	{
		if (parent::contains($intKey)) {
			// We're done, return the key
			return parent::at($intKey);
		} else {
			// We're done, return an empty variant
			return self::Factory(null);
		}
	}

	/**
	 * This method is an alias of Variant\Vector::at()
	 * @access public
	 * @name \Crux\Type\Variant\Vector ::get()
	 * @package \Crux\Type\Variant\Vector
	 * @param int $intKey
	 * @return \Crux\Type\Variant $this
	 * @see  \Crux\Type\Variant\Vector::at()
	 * @uses \Crux\Type\Variant\Vector::at()
	 */
	public function get(int $intKey) : Type\Variant
	{
		// Return the data
		return $this->at($intKey);
	}

	/**
	 * This method groups a Variant\Vector<Variant\Map> into a Variant\Map<string, Variant\Vector>> indexed by $strMapKey
	 * @access public
	 * @name \Crux\Type\Variant\Vector ::groupedVariantMap()
	 * @package \Crux\Type\Variant\Vector
	 * @param mixed $mixOperand
	 * @return \Crux\Type\Variant\Map
	 * @uses \Crux\Collection\Map::__constructor()
	 * @uses \Crux\Type\Map::get()
	 * @uses \Crux\Type\Map::isEmpty()
	 * @uses \Crux\Type\Variant::toString()
	 * @uses \Crux\Collection\Vector::__constructor()
	 * @uses \Crux\Collection\Map::set()
	 * @uses \Crux\Collection\Vector::add()
	 * @uses \Crux\Type\Variant::getData()
	 * @uses \Crux\Type\Variant\Map::Factory()
	 */
	public function groupedVariantMap($mixOperand) : Map
	{
		// Group the vector
		$mapGrouped = $this->toVector()->mapGroup($mixOperand);
		// Return the variant map
		return Map::fromMap($mapGrouped);
	}

	/**
	 * This method checks the vector data for duplicates
	 * @access public
	 * @name \Crux\Type\Variant\Vector ::hasDuplicates()
	 * @package \Crux\Type\Variant\Vector
	 * @return bool
	 * @uses \Crux\Type\Variant\Vector::getIterator()
	 * @uses \Crux\Type\IsVariant::getData()
	 * @uses in_array()
	 * @uses array_push()
	 */
	public function hasDuplicates() : bool
	{
		// Create a temporary array
		$arrTemp = [];
		// Iterate over the data
		foreach ($this->getIterator() as $varData) {
			// Check for the data
			if (in_array($varData->getData(), $arrTemp)) {
				// We're done
				return true;
			}
			// Add the data to the array
			array_push($arrTemp, $varData->getData());
		}
		// We're done
		return false;
	}

	/**
	 * This method implodes the vector into a string list
	 * @access public
	 * @name \Crux\Type\Variant\Vector ::implode()
	 * @package \Crux\Type\Variant\Vector
	 * @param string $strDelimiter [',']
	 * @param bool $blnForMySQL [false]
	 * @return string
	 * @uses \Crux\Collection\Vector::__constructor()
	 * @uses \Crux\Type\Variant\Vector::getIterator()
	 * @uses \Crux\Type\IsVariant::getData()
	 * @uses \Crux\Collection\Vector::add()
	 * @uses \Crux\Collection\Vector::toArray()
	 * @uses implode()
	 */
	public function implode(string $strDelimiter = ',', bool $blnForMySQL = false) : string
	{
		// Create a temporary vector
		$vecTemp = new Collection\Vector();
		// Iterate over the data
		foreach ($this->getIterator() as $intIndex => $varValue) {
			// Check the flag
			if ($blnForMySQL) {
				// Set the data
				$vecTemp->add($varValue->toMySqlString());
			} else {
				// Set the data
				$vecTemp->add($varValue->toString());
			}
		}
		// Return the imploded vector
		return implode($strDelimiter, $vecTemp->toArray());
	}

	/**
	 * This method implodes the vector with a callback just before data reset
	 * @access public
	 * @name \Crux\Type\Variant\Vector ::implodeCallback()
	 * @package \Crux\Types\Variant\Vector
	 * @param string $strDelimiter [',']
	 * @param callable $fnCallback
	 * @return string
	 * @uses \Crux\Collection\Vector::__constructor()
	 * @uses \Crux\Type\Variant\Vector::getIterator()
	 * @uses \Crux\Collection\Vector::add()
	 * @uses call_user_func_array()
	 * @uses \Crux\Collection\Vector::toArray()
	 * @uses implode()
	 */
	public function implodeCallback(string $strDelimiter = ',', callable $fnCallback) : string
	{
		// Localize the data
		$vecTemp = new Collection\Vector();
		// Iterate over the data
		foreach ($this->getIterator() as $intIndex => $varValue) {
			// Reset the data after executing the callback
			$vecTemp->add(call_user_func_array($fnCallback, [$varValue]));
		}
		// Return the imploded vector
		return implode($strDelimiter, $vecTemp->toArray());
	}

	/**
	 * This method removes the last element in the vector and returns its real value
	 * @access public
	 * @name \Crux\Type\Variant\Vector ::popReal()
	 * @package \Crux\Type\Variant\Vector
	 * @return mixed
	 * @uses \Crux\Type\Variant\Vector::pop()
	 * @uses \Crux\Type\IsVariant::getData()
	 */
	public function popReal()
	{
		// Return the actual value of the popped element
		return $this->pop()->getData();
	}

	/**
	 * This method does the same as Variant\Vector::reserve() as well as set the default value of the reservations
	 * @access public
	 * @name \Crux\Type\Variant\Vector ::reserveDefault()
	 * @package \Crux\Type\Variant\Vector
	 * @param int $intSize
	 * @param mixed $mixDefault [null]
	 * @return \Crux\Type\Variant\Vector $this
	 * @uses \Crux\Type\Variant\Vector::reserve()
	 * @uses \Crux\Type\Variant\Vector::keys()
	 * @uses \Crux\Collection\Vector::getIterator()
	 * @uses \Crux\Type\Variant\Vector::set()
	 */
	public function reserveDefault(int $intSize, $mixDefault = null) : Vector
	{
		// Reserve the space
		$this->reserve($intSize);
		// Iterate over the instance
		foreach ($this->keys()->getIterator() as $intIndex => $intKey) {
			// Reserve the data
			$this->set($intKey, $mixDefault);
		}
		// We're done, return the instance
		return $this;
	}

	/**
	 * This method searches the Vector for a specified term in the values, if found the index will be returned, -1 elsewise
	 * @access public
	 * @name \Crux\Type\Variant\Vector ::search()
	 * @package \Crux\Type\Variant\Vector
	 * @param mixed $mixTerm
	 * @return int
	 * @uses \Crux\Collection\Vector::__constructor()
	 * @uses \Crux\Type\Variant\Vector::getIterator()
	 * @uses \Crux\Type\IsVariant::getData()
	 * @uses \Crux\Collection\Vector::add()
	 * @uses \Crux\Collection\Vector::linearSearch()
	 */
	public function search($mixTerm) : int
	{
		// Create the temporary vector
		$vecTemp = new Collection\Vector();
		// Iterate over the data
		foreach ($this->getIterator() as $intIndex => $varData) {
			// Set the data into the temporary vector
			$vecTemp->add($varData->getData());
		}
		// Return the search
		return $vecTemp->linearSearch($mixTerm);
	}

	/**
	 * This method searches the Vector for a specified term, with case insensitivity, in the values, if found the index will be returned, -1 elsewise
	 * @access public
	 * @name \Crux\Type\Variant\Vector ::search()
	 * @package \Crux\Type\Variant\Vector
	 * @param mixed $mixTerm
	 * @return int
	 * @uses \Crux\Collection\Vector::__constructor()
	 * @uses \Crux\Type\Variant\Vector::getIterator()
	 * @uses \Crux\Type\IsVariant::getData()
	 * @uses \Crux\Collection\Vector::add()
	 * @uses \Crux\Collection\Vector::linearSearch()
	 * @uses \Crux\Core\Is::string()
	 * @uses strtolower()
	 */
	public function searchInsensitive($mixTerm) : int
	{
		// Create the temporary vector
		$vecTemp = new Collection\Vector();
		// Iterate over the data
		foreach ($this->getIterator() as $intIndex => $varData) {
			// Check for a string
			if (Core\Is::string($varData->getData())) {
				// Set the data into the temporary vector
				$vecTemp->add(strtolower($varData->getData()));
			} else {
				// Set the data into the temporary vector
				$vecTemp->add($varData->getData());
			}
		}
		// Return the search
		return $vecTemp->linearSearch(Core\Is::string($mixTerm) ? strtolower($mixTerm) : $mixTerm);
	}

	/**
	 * This method sets a new key into the instance
	 * @access public
	 * @name \Crux\Type\Variant\Vector ::set()
	 * @package \Crux\Type\Variant\Vector
	 * @param int $intKey
	 * @param mixed $mixValue
	 * @return \Crux\Type\Variant\Vector $this
	 * @uses \Crux\Type\Variant\Vector::setWith()
	 * @uses \Crux\Type\Variant\Vector::Factory()
	 */
	public function set(int $intKey, $mixValue)
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
			$mixData = Vector::fromVector($mixValue);
		} elseif (Core\Is::sequentialArray($mixValue)) {
			// Convert the data to a variant list
			$mixData = Vector::fromArray($mixValue);
		} elseif (Core\Is::variant($mixValue)) {
			// We're done, the data is sane
			$mixData = $mixValue;
		} elseif (Core\Is::object($mixValue)) {
			// Convert the data to a variant map
			$mixData = Map::fromObject($mixValue);
		} else {
			// Convert the data to a variant
			$mixData = self::Factory($mixValue);
		}
		// Add the data
		parent::set($intKey, $mixData);
		// We're done, return the instance
		return $this;
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Converters ///////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method converts the Variant\Vector to a Vector of booleans
	 * @access public
	 * @name \Crux\Type\Variant\Vector ::toBoolList()
	 * @package \Crux\Type\Variant\Vector
	 * @return \Crux\Collection\Vector
	 * @uses \Crux\Collection\Vector::__constructor()
	 * @uses \Crux\Type\Variant\Vector::getIterator()
	 * @uses \Crux\Type\Variant::toBool()
	 * @uses \Crux\Collection\Vector::add()
	 */
	public function toBoolList() : Collection\Vector
	{
		// Create the temporary vector
		$vecTemp = new Collection\Vector();
		// Iterate over the current Vector
		foreach ($this->getIterator() as $intIndex => $varItem) {
			// Check for a variant
			if (Core\Is::variant($varItem)) {
				// Add the item to the temporary vector
				$vecTemp->add($varItem->toBool());
			} else {
				// Add false
				$vecTemp->add(false);
			}
		}
		// Return the vector
		return $vecTemp;
	}

	/**
	 * This method converts the Variant\Vector to a Vector of integers
	 * @access public
	 * @name \Crux\Type\Variant\Vector ::toIntList()
	 * @package \Crux\Type\Variant\Vector
	 * @return \Crux\Collection\Vector
	 * @uses \Crux\Collection\Vector::__constructor()
	 * @uses \Crux\Type\Variant\Vector::getIterator()
	 * @uses \Crux\Type\Variant::toInt()
	 * @uses \Crux\Collection\Vector::add()
	 */
	public function toIntList() : Collection\Vector
	{
		// Create the temporary vector
		$vecTemp = new Collection\Vector();
		// Iterate over the current Vector
		foreach ($this->getIterator() as $varItem) {
			// Check for a variant
			if (Core\Is::variant($varItem)) {
				// Add the item to the temporary vector
				$vecTemp->add($varItem->toInt());
			} else {
				// Add the item to the temporary vector
				$vecTemp->add(-1);
			}
		}
		// Return the vector
		return $vecTemp;
	}

	/**
	 * This method converts the variant list to JSON
	 * @access public
	 * @name \Crux\Type\Variant\Vector::toJson()
	 * @package \Crux\Type\Variant\Vector
	 * @return string
	 * @uses \Crux\Type\Variant\Vector::getData()
	 * @uses json_encode()
	 */
	public function toJson() : string
	{
		// Return the JSON
		return json_encode($this->getData(), JSON_PRETTY_PRINT);
	}

	/**
	 * This method returns the Vector's keys as a vector
	 * @access public
	 * @name \Crux\Type\Variant\Vector ::toKeysVector()
	 * @package \Crux\Type\Variant\Vector
	 * @return \Crux\Collection\Vector
	 * @uses \Crux\Type\Variant\Vector::toKeysArray()
	 * @uses \Crux\Collection\Vector::fromArray()
	 */
	public function toKeysVector() : Collection\Vector
	{
		// Return the keys vector
		return Collection\Vector::fromArray($this->toKeysArray());
	}

	/**
	 * This method converts the variant list to a string
	 * @access public
	 * @name \Crux\Type\Variant\Vector::toString()
	 * @package \Crux\Type\Variant\Vector
	 * @return string
	 * @uses serialize()
	 */
	public function toString() : string
	{
		// Return the string value of the class
		return serialize($this);
	}

	/**
	 * This method converts the Variant\Vector to a Vector of strings
	 * @access public
	 * @name \Crux\Type\Variant\Vector ::toStringList()
	 * @package \Crux\Type\Variant\Vector
	 * @return Collection\Vector
	 * @uses \Crux\Collection\Vector::__constructor()
	 * @uses \Crux\Type\Variant\Vector::getIterator()
	 * @uses \Crux\Type\IsVariant::toString()
	 * @uses \Crux\Collection\Vector::add()
	 */
	public function toStringList() : Collection\Vector
	{
		// Create the temporary vector
		$vecTemp = new Collection\Vector();
		// Iterate over the current Vector
		foreach ($this->getIterator() as $varItem) {
			// Check for a variant
			if (Core\Is::variant($varItem)) {
				// Add the item to the temporary vector
				$vecTemp->add($varItem->toString());
			} else {
				// Add an empty string
				$vecTemp->add(null);
			}
		}
		// Return the vector
		return $vecTemp;
	}

	/**
	 * This method converts a Variant\Vector<Collection\Map> to a Variant\Vector<mixed> by combining the values in each map from key $strKey into a Vector<mixed>
	 * @access public
	 * @name \Crux\Type\Variant\Vector ::toTargetKeyList()
	 * @package \Crux\Type\Variant\Vector
	 * @param string $strKey
	 * @return \Crux\Type\Variant\Vector
	 * @uses \Crux\Type\Variant\Vector::Factory()
	 * @uses \Crux\Type\Variant\Vector::implodeCallback()
	 * @uses \Crux\Type\Map::get()
	 * @uses \Crux\Type\IsVariant::getData()
	 * @uses explode()
	 */
	public function toTargetKeyList(string $strKey) : Vector
	{
		// Return the vector
		return new self(explode(',', $this->implodeCallback(',', function (Map $varMapData) use ($strKey) {
			// Return the target key
			return $varMapData->get($strKey)->getData();
		})));
	}

	/**
	 * This method returns a vector of the current vector's values
	 * @access public
	 * @name \Crux\Type\Variant\Vector ::toValuesVector()
	 * @package \Crux\Type\Variant\Vector
	 * @return Collection\Vector
	 * @see \Crux\Collection\Vector::keys()
	 * @uses \Crux\Collection\Vector::keys()
	 */
	public function toValuesVector() : Collection\Vector
	{
		// Return the values vector
		return $this->keys();
	}

	/**
	 * This method returns the data as an array with the values in Variant form
	 * @access public
	 * @name \Crux\Type\Variant\Vector ::toVariantArray()
	 * @package \Crux\Type\Variant\Vector
	 * @return array
	 * @uses \Crux\Type\Variant\Vector::getIterator()
	 * @uses array_push()
	 */
	public function toVariantArray() : array
	{
		// Create an array placeholder
		$arrContainer = [];
		// Iterate over the instance
		foreach ($this->getIterator() as $varValue) {
			// Add the value to the array
			array_push($arrContainer, $varValue);
		}
		// Return the data
		return $arrContainer;
	}

	/**
	 * This method returns the Vector's values as an array of Variants
	 * @access public
	 * @name \Crux\Type\Variant\Vector ::toVariantValuesArray()
	 * @package \Crux\Type\Variant\Vector
	 * @return array
	 * @uses \Crux\Type\Variant\Vector::toValuesArray()
	 */
	public function toVariantValuesArray() : array
	{
		// Return the values array
		return $this->toValuesArray();
	}

	/**
	 * This method returns the Vector's values as a Vector of Variants
	 * @access public
	 * @name \Crux\Type\Variant\Vector ::toVariantValuesVector()
	 * @package \Crux\Type\Variant\Vector
	 * @return \Crux\Collection\Vector
	 * @uses \Crux\Type\Variant\Vector::values()
	 */
	public function toVariantValuesVector() : Collection\Vector
	{
		// return the values vector
		return $this->values();
	}

	/**
	 * This method converts the variant list to a collection
	 * @access public
	 * @name \Crux\Type\Variant\Vector::toVector()
	 * @package \Crux\Type\Variant\Vector
	 * @return \Crux\Collection\Vector
	 * @uses \Crux\Collection\Vector::__construct()
	 * @uses \Crux\Core\Is::variantMap()
	 * @uses \Crux\Core\Is::Variant\Vector()
	 * @uses \Crux\Core\Is::variant()
	 * @uses \Crux\Type\Map::toMap()
	 * @uses \Crux\Type\Variant\Vector::toVector()
	 * @uses \Crux\Type\Variant::getData()
	 * @uses \Crux\Collection\Vector::add()
	 */
	public function toVector() : Collection\Vector
	{
		// Create our vector container
		$vecContainer = new Collection\Vector();
		// Iterate over the instance
		foreach ($this->getIterator() as $varValue) {
			// Check the data type
			if (Core\Is::variantMap($varValue)) {
				// Add the value
				$vecContainer->add($varValue->toMap());
			} elseif (Core\Is::variantVector($varValue)) {
				// Add the value
				$vecContainer->add($varValue->toVector());
			} elseif (Core\Is::variant($varValue)) {
				// Add the value
				$vecContainer->add($varValue->getData());
			} else {
				// Add the value
				$vecContainer->add($varValue);
			}
		}
		// We're done, return the new value
		return $vecContainer;
	}

	/**
	 * This method converts a variant list to XML
	 * @access public
	 * @name \Crux\Type\Variant\Vector::toXml()
	 * @package \Crux\Type\Variant\Vector
	 * @param string $strRootNode ['Variant\Vector']
	 * @param bool $blnIncludeHeaders [true]
	 * @param string $strChildNode ['item']
	 * @return string
	 * @uses \Crux\Type\Variant\Vector::toVector()
	 * @uses \Crux\Collection\Vector::toXml()
	 */
	public function toXml(string $strRootNode = 'Variant\Vector', bool $blnIncludeHeaders = true, string $strChildNode = 'item') : string
	{
		// Convert the variant list to a vector and return the XML
		return $this->toVector()->toXml($strRootNode, $blnIncludeHeaders, $strChildNode);
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Getters //////////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method returns the data in its original type
	 * @access public
	 * @name \Crux\Type\Variant\Vector ::getData()
	 * @package \Crux\Type\Variant\Vector
	 * @return array|\Crux\Collection\Vector
	 * @uses \Crux\Type\Variant\Vector::getIterator()
	 * @uses \Crux\Type\IsVariant::getData()
	 * @uses \Crux\Collection\Vector::fromArray()
	 * @uses array_push()
	 */
	public function getData()
	{
		// Create the data array
		$arrData = [];
		// Create the response map
		// Iterate over the data map
		foreach ($this->getIterator() as $varValue) {
			// Push the data
			array_push($arrData, $varValue->getData());
		}
		// Return the data
		return (($this->mOriginalType === Core\Api::Vector) ? Collection\Vector::fromArray($arrData) : $arrData);
	}

	/**
	 * This method returns the original type name from the instance
	 * @access public
	 * @name \Crux\Type\Variant\Vector::getOriginalType()
	 * @package \Crux\Type\Variant\Vector
	 * @return string
	 */
	public function getOriginalType() : string
	{
		// Return the original type name from the instance
		return $this->mOriginalTypeName;
	}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
} /// End Crux\Type\Variant\Vector Class Definition //////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

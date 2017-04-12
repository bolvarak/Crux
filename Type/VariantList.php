<?php

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Crux\Type Namespace //////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

namespace Crux\Type;

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Imports //////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

use Crux\Core;
use Crux\Collection;

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Crux\Type\VariantList Class Definition ///////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class VariantList extends Collection\Vector implements IsVariant, \JsonSerializable
{
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Properties ///////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This property defines the original type of the variable
	 * @access private
	 * @name \Crux\Type\VariantList::$mOriginalType
	 * @package \Crux\Type\VariantList
	 * @var int
	 */
	private $mOriginalType = self::Array;

	/**
	 * This property contains the name for the original data type
	 * @access private
	 * @name \Crux\Type\VariantList::$mOriginalTypeName
	 * @package \Crux\Type\VariantList
	 * @var string
	 */
	private $mOriginalTypeName = null;

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Constructor //////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method sets up the instance with existing data
	 * @access public
	 * @name \Crux\Type\VariantList::__constructor()
	 * @package \Crux\Type\VariantList
	 * @param array|null|\Crux\Collection\Vector $mixSource
	 * @uses \Crux\Collection\Vector::__construct()
	 * @uses \Crux\Core\Is::sequentialArray()
	 * @uses \Crux\Collection\Vector::fromArray()
	 * @uses \Crux\Collection\Vector::getIterator();
	 * @uses \Crux\Type\VariantList::set()
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
			$this->mOriginalType = self::Array;
			// Create a new vector
			$vecTemp = Collection\Vector::fromArray($mixSource);
		} elseif ($mixSource instanceof Collection\Vector) {
			// Set the original type
			$this->mOriginalType = self::Vector;
			// Set the data into the instance
			$vecTemp = $mixSource;
		} elseif (Core\Is::variantList($mixSource)) {
			// We're done, we already have a variant
			return $mixSource;
		} else {
			// Set the original type
			$this->mOriginalType = self::Vector;
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
	 * @name \Crux\Type\VariantList::__call()
	 * @package \Crux\Type\VariantList
	 * @param string $strMethod
	 * @param array $arrArguments
	 * @return mixed
	 * @throws \Crux\Core\Exception\Type\VariantList
	 * @uses \Crux\Core\Api::$mVarianListtExtensions
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
			foreach (Core\Api::$mVariantListExtensions as $strName => $fnCallback) {
				// Check the name
				if (strtolower(substr_replace($strMethod, '', 0, 2)) === strtolower($strName)) {
					// Execute the callback
					return call_user_func_array($fnCallback, [$this->mData, $this]);
				}
			}
			// No extension available, we're done
			throw new Core\Exception\Type\VariantList(sprintf('Extension [%s] does not exist.', substr_replace($strMethod, '', 0, 2)));
		} else {
			// No method or extension available, we're done
			throw new Core\Exception\Type\VariantList(sprintf('Method or Extension [%s] does not exist.', $strMethod));
		}
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Static Constructor ///////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method constructs a new instance from any traversable data
	 * @access public
	 * @name \Crux\Type\VariantList::Factory()
	 * @package \Crux\Type\VariantList
	 * @return \Crux\Type\VariantList
	 * @static
	 * @uses \Crux\Type\VariantList::__construct()
	 */
	public static function Factory()
	{
		// Return the new instance
		return new self(func_get_args()[0] ?? null);
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Public Static Methods ////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method constructs a new instance from a sequential array
	 * @access public
	 * @name \Crux\Type\VariantList::fromArray()
	 * @package \Crux\Type\VariantList
	 * @param array $arrSource
	 * @return \Crux\Type\VariantList
	 * @static
	 * @uses \Crux\Type\VariantList::__construct()
	 */
	public static function fromArray(array $arrSource)
	{
		// Return the new instance
		return new self($arrSource);
	}

	/**
	 * This method constructs a new instance from an existing vector
	 * @access public
	 * @name \Crux\Type\VariantList::fromVector()
	 * @package \Crux\Type\VariantList
	 * @param \Crux\Collection\Vector<mixed> $vecSource
	 * @return \Crux\Type\VariantList
	 * @static
	 * @uses \Crux\Type\VariantList::__construct()
	 */
	public static function fromVector(Collection\Vector $vecSource) : VariantList
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
	 * @name \Crux\Type\VariantList::__toString()
	 * @package \Crux\Type\VariantList
	 * @return string
	 * @uses \Crux\Type\VariantList::toString()
	 */
	public function __toString() : string
	{
		// Return the string copy of this class
		return $this->toString();
	}

	/**
	 * This method returns a json-encodable construct of a variant
	 * @access public
	 * @name \Crux\Type\VariantList::jsonSerialize()
	 * @package \Crux\Type\VariantList
	 * @return mixed
	 * @uses \Crux\Type\VariantList::getData()
	 */
	public function jsonSerialize()
	{
		// Return the json encodable data
		return $this->getData();
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Public Static Methods ////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method adds a conversion extension to the construct
	 * @access public
	 * @name \Crux\Type\VariantList::addExtension()
	 * @package \Crux\Type\VariantList
	 * @param $strName
	 * @param callable $fnCallback
	 * @return void
	 * @static
	 * @uses \Crux\Core\Api::addVariantListExtension()
	 */
	public static function addExtension($strName, callable $fnCallback)
	{
		// Add the extension
		Core\Api::addVariantListExtension($strName, $fnCallback);
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Public Methods ///////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method adds an element to the vector
	 * @access public
	 * @name \Crux\Type\VariantList ::add()
	 * @package \Crux\Type\VariantList
	 * @param mixed $mixValue
	 * @return \Crux\Type\VariantList $this
	 * @uses \Crux\Core\Is::associativeArray()
	 * @uses \Crux\Core\Is::map()
	 * @uses \Crux\Core\Is::vector()
	 * @uses \Crux\Core\Is::sequentialArray()
	 * @uses \Crux\Core\Is::object()
	 * @uses \Crux\Type\VariantMap::fromArray()
	 * @uses \Crux\Type\VariantList::fromArray()
	 * @uses \Crux\Type\VariantMap::fromObject()
	 * @uses \Crux\Type\Variant::Factory()
	 */
	public function add($mixValue)
	{
		// Check the data type
		if (Core\Is::associativeArray($mixValue)) {
			// Convert the data to a variant map
			$mixData = VariantMap::fromArray($mixValue);
		} elseif (Core\Is::map($mixValue)) {
			// Convert the data to a variant map
			$mixData = VariantMap::fromArray($mixValue->toArray());
		} elseif (Core\Is::vector($mixValue)) {
			// Convert the data to a variant list
			$mixData = VariantList::fromVector($mixValue);
		} elseif (Core\Is::sequentialArray($mixValue)) {
			// Convert the data to a variant list
			$mixData = VariantList::fromArray($mixValue);
		} elseif ($mixValue instanceof IsVariant) {
			// We're done, the data is sane
			$mixData = $mixValue;
		} elseif (Core\Is::object($mixValue)) {
			// Convert the data to a variant map
			$mixData = VariantMap::fromObject($mixValue);
		} else {
			// Convert the data to a variant
			$mixData = Variant::Factory($mixValue);
		}
		// Set the data into the instance
		parent::add($mixData);
		// We're done, return the instance
		return $this;
	}

	/**
	 * This method searches the Vector for a key with case-insensitivity and returns the data if found, Variant::Factory(null) elsewise
	 * @access public
	 * @name \Crux\Type\VariantList ::at()
	 * @package \Crux\Type\VariantList
	 * @param int $intKey
	 * @return \Crux\Type\IsVariant
	 * @uses \Crux\Collection\Vector::contains()
	 * @uses \Crux\Collection\Vector::at()
	 * @uses \Crux\Type\Variant::Factory()
	 */
	public function at(int $intKey) : IsVariant
	{
		if (parent::contains($intKey)) {
			// We're done, return the key
			return parent::at($intKey);
		} else {
			// We're done, return an empty variant
			return Variant::Factory(null);
		}
	}

	/**
	 * This method is an alias of VariantList::at()
	 * @access public
	 * @name \Crux\Type\VariantList ::get()
	 * @package \Crux\Type\VariantList
	 * @param int $intKey
	 * @return \Crux\Type\IsVariant $this
	 * @see  VariantList::at()
	 * @uses \Crux\Type\VariantList::at()
	 */
	public function get(int $intKey) : IsVariant
	{
		// Return the data
		return $this->at($intKey);
	}

	/**
	 * This method groups a VariantList<VariantMap> into a VariantMap<string, VariantList>> indexed by $strMapKey
	 * @access public
	 * @name \Crux\Type\VariantList ::groupedVariantMap()
	 * @package \Crux\Type\VariantList
	 * @param mixed $mixOperand
	 * @return \Crux\Type\VariantMap
	 * @uses \Crux\Collection\Map::__constructor()
	 * @uses \Crux\Type\VariantMap::get()
	 * @uses \Crux\Type\VariantMap::isEmpty()
	 * @uses \Crux\Type\Variant::toString()
	 * @uses \Crux\Collection\Vector::__constructor()
	 * @uses \Crux\Collection\Map::set()
	 * @uses \Crux\Collection\Vector::add()
	 * @uses \Crux\Type\Variant::getData()
	 * @uses \Crux\Type\VariantMap::Factory()
	 */
	public function groupedVariantMap($mixOperand) : VariantMap
	{
		// Group the vector
		$mapGrouped = $this->toVector()->mapGroup($mixOperand);
		// Return the variant map
		return VariantMap::fromMap($mapGrouped);
	}

	/**
	 * This method checks the vector data for duplicates
	 * @access public
	 * @name \Crux\Type\VariantList ::hasDuplicates()
	 * @package \Crux\Type\VariantList
	 * @return bool
	 * @uses \Crux\Type\VariantList::getIterator()
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
	 * @name \Crux\Type\VariantList ::implode()
	 * @package \Crux\Type\VariantList
	 * @param string $strDelimiter [',']
	 * @param bool $blnForMySQL [false]
	 * @return string
	 * @uses \Crux\Collection\Vector::__constructor()
	 * @uses \Crux\Type\VariantList::getIterator()
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
	 * @name \Crux\Type\VariantList ::implodeCallback()
	 * @package \Crux\Types\VariantList
	 * @param string $strDelimiter [',']
	 * @param callable $fnCallback
	 * @return string
	 * @uses \Crux\Collection\Vector::__constructor()
	 * @uses \Crux\Type\VariantList::getIterator()
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
	 * @name \Crux\Type\VariantList ::popReal()
	 * @package \Crux\Type\VariantList
	 * @return mixed
	 * @uses \Crux\Type\VariantList::pop()
	 * @uses \Crux\Type\IsVariant::getData()
	 */
	public function popReal()
	{
		// Return the actual value of the popped element
		return $this->pop()->getData();
	}

	/**
	 * This method does the same as VariantList::reserve() as well as set the default value of the reservations
	 * @access public
	 * @name \Crux\Type\VariantList ::reserveDefault()
	 * @package \Crux\Type\VariantList
	 * @param int $intSize
	 * @param mixed $mixDefault [null]
	 * @return \Crux\Type\VariantList $this
	 * @uses \Crux\Type\VariantList::reserve()
	 * @uses \Crux\Type\VariantList::keys()
	 * @uses \Crux\Collection\Vector::getIterator()
	 * @uses \Crux\Type\VariantList::set()
	 */
	public function reserveDefault(int $intSize, $mixDefault = null) : VariantList
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
	 * @name \Crux\Type\VariantList ::search()
	 * @package \Crux\Type\VariantList
	 * @param mixed $mixTerm
	 * @return int
	 * @uses \Crux\Collection\Vector::__constructor()
	 * @uses \Crux\Type\VariantList::getIterator()
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
	 * @name \Crux\Type\VariantList ::search()
	 * @package \Crux\Type\VariantList
	 * @param mixed $mixTerm
	 * @return int
	 * @uses \Crux\Collection\Vector::__constructor()
	 * @uses \Crux\Type\VariantList::getIterator()
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
	 * @name \Crux\Type\VariantList ::set()
	 * @package \Crux\Type\VariantList
	 * @param int $intKey
	 * @param mixed $mixValue
	 * @return \Crux\Type\VariantList $this
	 * @uses \Crux\Type\VariantList::setWith()
	 * @uses \Crux\Type\VariantList::Factory()
	 */
	public function set(int $intKey, $mixValue)
	{
		// Check the data type
		if (Core\Is::associativeArray($mixValue)) {
			// Convert the data to a variant map
			$mixData = VariantMap::fromArray($mixValue);
		} elseif (Core\Is::map($mixValue)) {
			// Convert the data to a variant map
			$mixData = VariantMap::fromArray($mixValue->toArray());
		} elseif (Core\Is::vector($mixValue)) {
			// Convert the data to a variant list
			$mixData = VariantList::fromVector($mixValue);
		} elseif (Core\Is::sequentialArray($mixValue)) {
			// Convert the data to a variant list
			$mixData = VariantList::fromArray($mixValue);
		} elseif ($mixValue instanceof IsVariant) {
			// We're done, the data is sane
			$mixData = $mixValue;
		} elseif (Core\Is::object($mixValue)) {
			// Convert the data to a variant map
			$mixData = VariantMap::fromObject($mixValue);
		} else {
			// Convert the data to a variant
			$mixData = Variant::Factory($mixValue);
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
	 * This method converts the VariantList to a Vector of booleans
	 * @access public
	 * @name \Crux\Type\VariantList ::toBoolList()
	 * @package \Crux\Type\VariantList
	 * @return \Crux\Collection\Vector
	 * @uses \Crux\Collection\Vector::__constructor()
	 * @uses \Crux\Type\VariantList::getIterator()
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
			if ($varItem instanceof Variant) {
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
	 * This method converts the VariantList to a Vector of integers
	 * @access public
	 * @name \Crux\Type\VariantList ::toIntList()
	 * @package \Crux\Type\VariantList
	 * @return \Crux\Collection\Vector
	 * @uses \Crux\Collection\Vector::__constructor()
	 * @uses \Crux\Type\VariantList::getIterator()
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
			if ($varItem instanceof Variant) {
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
	 * @name \Crux\Type\VariantList::toJson()
	 * @package \Crux\Type\VariantList
	 * @return string
	 * @uses \Crux\Type\VariantList::getData()
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
	 * @name \Crux\Type\VariantList ::toKeysVector()
	 * @package \Crux\Type\VariantList
	 * @return \Crux\Collection\Vector
	 * @uses \Crux\Type\VariantList::toKeysArray()
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
	 * @name \Crux\Type\VariantList::toString()
	 * @package \Crux\Type\VariantList
	 * @return string
	 * @uses serialize()
	 */
	public function toString() : string
	{
		// Return the string value of the class
		return serialize($this);
	}

	/**
	 * This method converts the VariantList to a Vector of strings
	 * @access public
	 * @name \Crux\Type\VariantList ::toStringList()
	 * @package \Crux\Type\VariantList
	 * @return Collection\Vector
	 * @uses \Crux\Collection\Vector::__constructor()
	 * @uses \Crux\Type\VariantList::getIterator()
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
			if ($varItem instanceof Variant) {
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
	 * This method converts a VariantList<Collection\Map> to a VariantList<mixed> by combining the values in each map from key $strKey into a Vector<mixed>
	 * @access public
	 * @name \Crux\Type\VariantList ::toTargetKeyList()
	 * @package \Crux\Type\VariantList
	 * @param string $strKey
	 * @return \Crux\Type\VariantList
	 * @uses \Crux\Type\VariantList::Factory()
	 * @uses \Crux\Type\VariantList::implodeCallback()
	 * @uses \Crux\Type\VariantMap::get()
	 * @uses \Crux\Type\IsVariant::getData()
	 * @uses explode()
	 */
	public function toTargetKeyList(string $strKey) : VariantList
	{
		// Return the vector
		return self::Factory(explode(',', $this->implodeCallback(',', function (VariantMap $varMapData) use ($strKey) {
			// Return the target key
			return $varMapData->get($strKey)->getData();
		})));
	}

	/**
	 * This method returns a vector of the current vector's values
	 * @access public
	 * @name \Crux\Type\VariantList ::toValuesVector()
	 * @package \Crux\Type\VariantList
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
	 * @name \Crux\Type\VariantList ::toVariantArray()
	 * @package \Crux\Type\VariantList
	 * @return array
	 * @uses \Crux\Type\VariantList::getIterator()
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
	 * @name \Crux\Type\VariantList ::toVariantValuesArray()
	 * @package \Crux\Type\VariantList
	 * @return array
	 * @uses \Crux\Type\VariantList::toValuesArray()
	 */
	public function toVariantValuesArray() : array
	{
		// Return the values array
		return $this->toValuesArray();
	}

	/**
	 * This method returns the Vector's values as a Vector of Variants
	 * @access public
	 * @name \Crux\Type\VariantList ::toVariantValuesVector()
	 * @package \Crux\Type\VariantList
	 * @return \Crux\Collection\Vector
	 * @uses \Crux\Type\VariantList::values()
	 */
	public function toVariantValuesVector() : Collection\Vector
	{
		// return the values vector
		return $this->values();
	}

	/**
	 * This method converts the variant list to a collection
	 * @access public
	 * @name \Crux\Type\VariantList::toVector()
	 * @package \Crux\Type\VariantList
	 * @return \Crux\Collection\Vector
	 * @uses \Crux\Collection\Vector::__construct()
	 * @uses \Crux\Core\Is::variantMap()
	 * @uses \Crux\Core\Is::variantList()
	 * @uses \Crux\Core\Is::variant()
	 * @uses \Crux\Type\VariantMap::toMap()
	 * @uses \Crux\Type\VariantList::toVector()
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
			} elseif (Core\Is::variantList($varValue)) {
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
	 * @name \Crux\Type\VariantList::toXml()
	 * @package \Crux\Type\VariantList
	 * @param string $strRootNode ['variantList']
	 * @param bool $blnIncludeHeaders [true]
	 * @param string $strChildNode ['item']
	 * @return string
	 * @uses \Crux\Type\VariantList::toVector()
	 * @uses \Crux\Collection\Vector::toXml()
	 */
	public function toXml(string $strRootNode = 'variantList', bool $blnIncludeHeaders = true, string $strChildNode = 'item') : string
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
	 * @name \Crux\Type\VariantList ::getData()
	 * @package \Crux\Type\VariantList
	 * @return \Crux\Collection\Vector
	 * @uses \Crux\Type\VariantList::getIterator()
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
		return (($this->mOriginalType === self::Vector) ? Collection\Vector::fromArray($arrData) : $arrData);
	}

	/**
	 * This method returns the original type name from the instance
	 * @access public
	 * @name \Crux\Type\VariantList::getOriginalType()
	 * @package \Crux\Type\VariantList
	 * @return string
	 */
	public function getOriginalType() : string
	{
		// Return the original type name from the instance
		return $this->mOriginalTypeName;
	}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
} /// End Crux\Type\VariantList Class Definition /////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

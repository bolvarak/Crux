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
/// Crux\Collection\Vector Class Definition ///////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class Vector extends \ArrayObject implements \JsonSerializable
{
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Factory //////////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method instantiates the Vector into a fluid interface
	 * @access public
	 * @name \Crux\Collection\Vector::Factory()
	 * @package Crux\Collection\Vector
	 * @return \Crux\Collection\Vector
	 * @static
	 * @uses \Crux\Collection\Vector::Vector()
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
	 * This method converts a sequential array into a vector
	 * @access public
	 * @name \Crux\Collection\Vector::fromArray()
	 * @package \Crux\Collection\Vector
	 * @param $mixSource
	 * @return Vector
	 * @throws \Crux\Core\Exception\Collection\Map
	 * @throws Core\Exception\Collection\Vector
	 */
	public static function fromArray(array $mixSource)
	{
		// Make sure we have a sequential array
		if (!Core\Is::sequentialArray($mixSource)) {
			// We're done, we don't have a sequential array
			throw new Core\Exception\Collection\Vector('Source variable must be of type array (Sequential).');
		}
		// Check for a vector
		if ($mixSource instanceof Vector) {
			// We're done, return the vector
			return $mixSource;
		}
		// Create the new container vector
		$vecContainer = new self();
		// Iterate over the array
		foreach ($mixSource as $mixValue) {
			// Check the value
			if (Core\Is::map($mixValue)) {
				// Set the value into the vector
				$vecContainer->add($mixValue);
			} elseif (Core\Is::map($mixValue)) {
				// Set the value into the vector
				$vecContainer->add($mixValue);
			} elseif (Core\Is::associativeArray($mixValue)) {
				// Set a new map into the vector
				$vecContainer->add(Map::fromArray($mixValue));
			} elseif (Core\Is::sequentialArray($mixValue)) {
				// Set the nested vector into the parent vector
				$vecContainer->add(Vector::fromArray($mixValue));
			} elseif ($mixValue instanceof \stdClass) {
				// Set a new map into the vector
				$vecContainer->add(Map::fromObject($mixValue));
			} else {
				// Set the value into the vector
				$vecContainer->add($mixValue);
			}
		}
		// We're done, return the new vector
		return $vecContainer;
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Constructor //////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method instantiates the Vector Collection
	 * @access public
	 * @name \Crux\Collection\Vector::Vector()
	 * @package Crux\Collection\Vector
	 * @throws \Crux\Core\Exception\Collection\Vector
	 * @uses \ArrayObject::ArrayObject()
	 * @uses sprintf()
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
	 * This method returns a string representation of the vector when the instance is referenced as a string
	 * @access public
	 * @name \Crux\Collection\Vector::__toString()
	 * @package \Crux\Collection\Vector
	 * @return string
	 * @uses \Crux\Collection\Vector::toString()
	 */
	public function __toString() : string
	{
		// Return the string representation of the vector
		return $this->toString();
	}

	/**
	 * This method converts the vector to a JSON serialized array
	 * @access public
	 * @name \Crux\Collection\Vector::jsonSerialize()
	 * @package \Crux\Collection\Vector
	 * @return array
	 * @uses \Crux\Collection\Vector::toArray()
	 */
	public function jsonSerialize()
	{
		// Return the array version of this
		return $this->toArray();
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Protected Methods ////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////




	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Public Methods ///////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method adds a copy of $mixValue to the current vector
	 * @access public
	 * @name \Crux\Collection\Vector::add()
	 * @package \Crux\Collection\Vector
	 * @param $mixValue
	 * @return \Crux\Collection\Vector $this
	 * @throws Core\Exception\Collection\Vector
	 * @uses \Crux\Collection\Vector::validateType()
	 * @uses \Crux\Collection\Vector::append()
	 */
	public function add($mixValue)
	{
		// Add the value to the instance
		$this->append($mixValue);
		// We're done, return the instance
		return $this;
	}

	/**
	 * This method adds all indexes from an existing iterator
	 * @access public
	 * @name \Crux\Collection\Vector::addAll()
	 * @package \Crux\Collection\Vector
	 * @param array|\Crux\Collection\Vector $mixSource
	 * @return \Crux\Collection\Vector
	 * @throws Core\Exception\Collection\Vector
	 * @uses \Crux\Core\Is::sequentialArray()
	 * @uses \Crux\Core\Is::vector()
	 * @uses \Crux\Collection\Vector::fromArray()
	 */
	public function addAll($mixSource) : Vector
	{
		// Check for an array or vector
		if (Core\Is::sequentialArray($mixSource) || Core\Is::vector($mixSource)) {
			// Check for a vector
			if (Core\Is::vector($mixSource)) {
				// We're done, return the existing vector
				return $mixSource;
			} else {
				// Return the new vector
				return Vector::fromArray($mixSource);
			}
		}
		// We're done, return the instance
		return $this;
	}

	/**
	 * This method adds a copy of $mixValue to the current vector after it's filtered with $fnCallback
	 * @access public
	 * @name \Crux\Collection\Vector::addWith()
	 * @package \Crux\Collection\Vector
	 * @param mixed $mixValue
	 * @param callable $fnCallback
	 * @return \Crux\Collection\Vector
	 * @uses call_user_func_array()
	 * @uses \Crux\Collection\Vector::add()
	 */
	public function addWith($mixValue, callable $fnCallback) : Vector
	{
		// Filter the value
		call_user_func_array($fnCallback, [$mixValue, $this]);
		// Add the value
		return $this->add($mixValue);
	}

	/**
	 * This method returns the value of $intIndex from the current vector if it exists, if not, an exception is thrown
	 * @access public
	 * @name \Crux\Collection\Vector::at()
	 * @package \Crux\Collection\Vector
	 * @param int $intIndex
	 * @return mixed
	 * @throws Core\Exception\Collection\Vector
	 * @uses \Crux\Collection\Vector::offsetExists()
	 * @uses sprintf()
	 * @uses \Crux\Collection\Vector::offsetGet()
	 */
	public function at(int $intIndex)
	{
		// Check to see if the offset exists
		if (!$this->offsetExists($intIndex)) {
			// We're done, send the error
			throw new Core\Exception\Collection\Vector(sprintf('Offset index [%d] does not exist in the current vector.', $intIndex));
		} else {
			// We're done, return the value
			return $this->offsetGet($intIndex);
		}
	}

	/**
	 * This method removes all of the elements from the current vector
	 * @access public
	 * @name \Crux\Collection\Vector::clear()
	 * @package \Crux\Collection\Vector
	 * @return \Crux\Collection\Vector $this
	 * @uses \Crux\Collection\Vector::toKeysArray()
	 * @uses \Crux\Collection\Vector::offsetUnset()
	 */
	public function clear() : Vector
	{
		// Grab the keys
		$arrKeys = $this->toKeysArray();
		// Iterate over the keys
		foreach ($arrKeys as $intIndex) {
			// Unset the index
			$this->offsetUnset($intIndex);
		}
		// We're done, return the instance
		return $this;
	}

	/**
	 * This method concatenates a vector or array with the current vector
	 * @access public
	 * @name \Crux\Collection\Vector::concat()
	 * @package \Crux\Collection\Vector
	 * @param array|\Crux\Collection\Vector $mixData
	 * @return \Crux\Collection\Vector $this
	 * @throws Core\Exception\Collection\Vector
	 * @uses \Crux\Core\Is::sequentialArray()
	 * @uses \Crux\Collection\Vector::fromArray()
	 * @uses \Crux\Collection\Vector::getIterator()
	 * @uses \Crux\Collection\Vector::append()
	 */
	public function concat($mixData) : Vector
	{
		// Check for an array or vector
		if (($mixData instanceof Vector) === false) {
			// Check for an array
			if (!Core\Is::sequentialArray($mixData)) {
				// We're done, send the error
				throw new Core\Exception\Collection\Vector('Concatenation data variable must be of type Vector or array.');
			} else {
				// Reset the data
				$mixData = self::fromArray($mixData);
			}
		}
		// Iterate over the data
		foreach ($mixData->getIterator() as $mixValue) {
			// Add the value to the instance
			$this->append($mixValue);
		}
		// We're done, return the instance
		return $this;
	}

	/**
	 * This method determines if the current vector contains an element at $intIndex
	 * @access public
	 * @name \Crux\Collection\Vector::contains()
	 * @package \Crux\Collection\Vector
	 * @param int $intIndex
	 * @return bool
	 * @uses \Crux\Collection\Vector::offsetExists()
	 */
	public function contains(int $intIndex) : bool
	{
		// We're done, return the offset's existence
		return $this->offsetExists($intIndex);
	}

	/**
	 * This method determines if the current vector contains an element at $intIndex
	 * @access public
	 * @name \Crux\Collection\Vector::containsKey()
	 * @package \Crux\Collection\Vector
	 * @param int $intIndex
	 * @return bool
	 * @uses \Crux\Collection\Vector::offsetExists()
	 */
	public function containsKey(int $intIndex) : bool
	{
		// We're done, return the offset's existence
		return $this->offsetExists($intIndex);
	}

	/**
	 * This method returns a vector containing the values of the current vector that meets a supplied condition applied to its values
	 * @access public
	 * @name \Crux\Collection\Vector::filter()
	 * @package \Crux\Collection\Vector
	 * @param callable $fnCallback
	 * @return \Crux\Collection\Vector
	 * @uses \Crux\Collection\Vector::getArrayCopy()
	 * @uses array_filter()
	 * @uses \Crux\Collection\Vector::fromArray()
	 */
	public function filter(callable $fnCallback) : Vector
	{
		// Filter the array
		$arrVector = array_filter($this->getArrayCopy(), $fnCallback);
		// Return the new vector
		return self::fromArray($arrVector);
	}

	/**
	 * This method returns a vector containing the values of the current vector that meets a supplied condition applied to its keys and values
	 * @access public
	 * @name \Crux\Collection\Vector::filterWithKey()
	 * @package \Crux\Collection\Vector
	 * @param callable $fnCallback
	 * @return \Crux\Collection\Vector
	 * @uses \Crux\Collection\Vector::getArrayCopy()
	 * @uses array_filter()
	 * @uses \Crux\Collection\Vector::fromArray()
	 */
	public function filterWithKey(callable $fnCallback) : Vector
	{
		// Filter the array
		$arrVector = array_filter($this->getArrayCopy(), $fnCallback, ARRAY_FILTER_USE_BOTH);
		// Return the new vector
		return self::fromArray($arrVector);
	}

	/**
	 * This method returns the first key of the current vector
	 * @access public
	 * @name \Crux\Collection\Vector::firstKey()
	 * @packate \Crux\Collection\Vector
	 * @return int
	 * @uses \Crux\Collection\Vector::keys()
	 * @uses \Crux\Collection\Vector::at()
	 */
	public function firstKey() : int
	{
		// Return the first key
		return $this->keys()->at(0);
	}

	/**
	 * This method returns the first value from the current vector
	 * @access public
	 * @name \Crux\Collection\Vector::firstValue()
	 * @return mixed
	 * @uses \Crux\Collection\Vector::firstKey()
	 * @uses \Crux\Collection\Vector::offsetGet()
	 */
	public function firstValue()
	{
		// Return the first value from the vector
		return $this->offsetGet($this->firstKey());
	}

	/**
	 * This method returns a value from the current vector at $intIndex if it exists
	 * @access public
	 * @name \Crux\Collection\Vector::get()
	 * @package \Crux\Collection\Vector
	 * @param int $intIndex
	 * @return mixed|null
	 * @uses \Crux\Collection\Vector::offsetExists()
	 * @uses \Crux\Collection\Vector::offsetGet()
	 */
	public function get(int $intIndex)
	{
		// Check to see if the offset exists
		if (!$this->offsetExists($intIndex)) {
			// We're done, the index doesn't exist
			return null;
		} else {
			// We're done, return the index
			return $this->offsetGet($intIndex);
		}
	}

	/**
	 * This method implodes a vector's values into a string
	 * @access public
	 * @name \Crux\Collection\Vector::implode()
	 * @package \Crux\Collection\Vector
	 * @param string $strDelimiter [',']
	 * @return string
	 * @uses \Crux\Collection\Vector::toValuesArray()
	 * @uses implode()
	 */
	public function implode(string $strDelimiter = ',') : string
	{
		// Return the imploded vector
		return implode($strDelimiter, $this->toValuesArray());
	}

	/**
	 * This method returns whether or not the current vector is empty
	 * @access public
	 * @name \Crux\Collection\Vector::isEmpty()
	 * @package \Crux\Collection\Vector
	 * @return bool
	 * @uses \Crux\Collection\Vector::getArrayCopy()
	 * @uses empty()
	 */
	public function isEmpty() : bool
	{
		// We're done, return the empty state
		return empty($this->getArrayCopy());
	}

	/**
	 * This method returns an iterable view of the current vector
	 * @access public
	 * @name \Crux\Collection\Vector::items()
	 * @package \Crux\Collection\Vector
	 * @return \ArrayIterator
	 * @uses \Crux\Collection\Vector::getArrayCopy()
	 * @uses \ArrayIterator::__construct()
	 */
	public function items() : \ArrayIterator
	{
		// Return the new iterator
		return new \ArrayIterator($this->getArrayCopy());
	}

	/**
	 * This method returns a vector of keys from the current vector
	 * @access public
	 * @name \Crux\Collection\Vector::keys()
	 * @package \Crux\Collection\Vector
	 * @return \Crux\Collection\Vector
	 * @uses \Crux\Collection\Vector::toKeysArray()
	 * @uses \Crux\Collection\Vectr::fromArray()
	 */
	public function keys() : Vector
	{
		// Return the new vector of keys
		return self::fromArray($this->toKeysArray());
	}

	/**
	 * This method returns the last key in the current vector
	 * @access public
	 * @name \Crux\Collection\Vector::lastKey()
	 * @package \Crux\Collection\Vector
	 * @return int
	 * @uses \Crux\Collection\Vector::getArrayCopy()
	 * @uses array_keys()
	 * @uses count()
	 */
	public function lastKey() : int
	{
		// Localize the instance keys
		$arrVectorKeys = array_keys($this->getArrayCopy());
		// Return the last key
		return $arrVectorKeys[count($arrVectorKeys) - 1];
	}

	/**
	 * This method returns the last value in the current vector
	 * @access public
	 * @name \Crux\Collection\Vector::lastValue()
	 * @package \Crux\Collection\Vector
	 * @return mixed
	 * @uses \Crux\Collection\Vector::getArrayCopy()
	 * @uses count()
	 */
	public function lastValue()
	{
		// Localize the vector
		$arrVector = $this->getArrayCopy();
		// Return the last element
		return $arrVector[count($arrVector) - 1];
	}

	/**
	 * This method returns the index of the first element that matches $mixNeedle
	 * @access public
	 * @name \Crux\Collection\Vector::linearSearch(0
	 * @package \Crux\Collection\Vector
	 * @param $mixNeedle
	 * @param bool $blnStrict [false]
	 * @return int
	 * @uses \Crux\Collection\Vector::getIterator()
	 */
	public function linearSearch($mixNeedle, bool $blnStrict = false) : int
	{
		// Iterate over the instance
		foreach ($this->getIterator() as $intIndex => $mixValue) {
			// Check the value
			if ($blnStrict && ($mixValue === $mixNeedle)) {
				// We're done, the value matches
				return $intIndex;
			} elseif (!$blnStrict && ($mixValue == $mixNeedle)) {
				// We're done, the value matches
				return $intIndex;
			} else {
				// Next iteration
				continue;
			}
		}
		// We're done, no matches found
		return -1;
	}

	/**
	 * This method groups a vector of maps
	 * @access public
	 * @name \Crux\Collection\Vector::mapGroup()
	 * @package \Crux\Collection\Map
	 * @param mixed $mixOperand
	 * @return \Crux\Collection\Map
	 * @throws Core\Exception\Collection\Map
	 * @uses \Crux\Collection\Map::__construct()
	 * @uses \Crux\Collection\Vector::getIterator()
	 * @uses \Crux\Core\Is::map()
	 * @uses \Crux\Core\Is::associativeArray()
	 * @uses \Crux\Collection\Map::fromArray()
	 * @uses \Crux\Core\Is::null()
	 * @uses \Crux\Collection\Map::key()
	 * @uses \Crux\Core\Is::empty()
	 * @uses \Crux\Collection\Map::get()
	 * @uses \Crux\Collection\Vector::__construct()
	 * @uses \Crux\Collection\Map::set()
	 * @uses \Crux\Collection\Vector::add()
	 */
	public function mapGroup($mixOperand) : Map
	{
		// Creat the response map
		$mapResponse = new Map();
		// Iterate over the vector
		foreach ($this->getIterator() as $intIndex => $mixValue) {
			// Make sure we have an associative array or a map
			if (Core\Is::map($mixValue) || Core\Is::associativeArray($mixValue)) {
				// Check for an associative array
				if (Core\Is::associativeArray($mixValue)) {
					// Reset the value
					$mixValue = Map::fromArray($mixValue);
				}
				// Check for the key
				if (!Core\Is::null($mixValue->key($mixOperand))) {
					// Set the new key
					$strNewKey = (Core\Is::null($mixValue->get($mixOperand)) ? 'Unknown' : $mixValue->get($mixOperand));
					// Check for the value in the response map
					if (Core\Is::empty($mapResponse->get($mixValue->get($mixOperand)))) {
						// Set the key into the response
						$mapResponse->set($strNewKey, new Vector());
					}
					// Set the value
					$mapResponse->get($strNewKey)->add($mixValue);
				}
			}
		}
		// We're done, return the map
		return $mapResponse;
	}

	/**
	 * This method implodes a vector of maps
	 * @access public
	 * @name \Crux\Collection\Vector::mapImplode()
	 * @package \Crux\Collection\Vector
	 * @param mixed $mixNeedle
	 * @param string|null $strDelimiter [null]
	 * @return mixed
	 * @uses \Crux\Collection\Vector::__construct()
	 * @uses \Crux\Collection\Vector::getIterator()
	 * @uses \Crux\Collection\Map::contains()
	 * @uses \Crux\Collection\Map::get()
	 * @uses \Crux\Collection\Vector::add()
	 * @uses \Crux\Core\Is::null()
	 * @uses \Crux\Collection\Vector::implode()
	 */
	public function mapImplode($mixNeedle, string $strDelimiter = null)
	{
		// Create the needles vector
		$vecNeedles = new self();
		// Iterate over the haystack
		foreach ($this->getIterator() as $intIndex => $mapValue) {
			// Check for the needle in the map
			if ($mapValue->contains($mixNeedle)) {
				// Append the needle
				$vecNeedles->add($mapValue->get($mixNeedle));
			}
		}
		// Return the data
		return (Core\Is::null($strDelimiter) ? $vecNeedles : $vecNeedles->implode($strDelimiter));
	}

	/**
	 * This method removes the last element of the current vector
	 * @access public
	 * @name \Crux\Collection\Vector::pop()
	 * @return mixed
	 * @uses \Crux\Collection\Vector::getArrayCopy()
	 * @uses count()
	 * @uses \Crux\Collection\Vector::offsetUnset()
	 */
	public function pop()
	{
		// Localize the data
		$mixValue = $this->lastValue();
		// Remove the last element from the instance
		$this->offsetUnset($this->lastKey());
		// We're done, return the instance
		return $mixValue;
	}

	/**
	 * This method prepends a single value to the beginning of the vector
	 * @access public
	 * @name \Crux\Collection\Vector::prepend()
	 * @package \Crux\Collection\Vector
	 * @param mixed $mixValue
	 * @return \Crux\Collection\Vector $this
	 * @uses \Crux\Collection\Vector::unshift()
	 */
	public function prepend($mixValue)
	{
		// Prepend the value
		return $this->unshift($mixValue);
	}

	/**
	 * This method is an alias to Vector::removeKey()
	 * @access public
	 * @name \Crux\Collection\Vector::remove()
	 * @package \Crux\Collection\Vector
	 * @param int $intIndex
	 * @return \Crux\Collection\Vector $this
	 * @see \Crux\Collection\Vector::removeKey()
	 * @uses \Crux\Collection\Vector
	 */
	public function remove(int $intIndex) : Vector
	{
		// Return the instance
		return $this->removeKey($intIndex);
	}

	/**
	 * This method removes the specified $intIndex from the current vector
	 * @access public
	 * @name \Crux\Collection\Vector::removeKey()
	 * @package \Crux\Collection\Vector
	 * @param int $intIndex
	 * @return \Crux\Collection\Vector $this
	 * @uses \Crux\Collection\Vector::offsetUnset()
	 */
	public function removeKey(int $intIndex) : Vector
	{
		// Remove the key
		$this->offsetUnset($intIndex);
		// We're done, return the instance
		return $this;
	}

	/**
	 * This method reserves enough memory to accommodate a given number of elements
	 * @access public
	 * @name \Crux\Collection\Vector::reserve()
	 * @package \Crux\Collection\Vector
	 * @param int $intCount
	 * @return \Crux\Collection\Vector $this
	 * @uses \Crux\Collection\Vector::getArrayCopy()
	 * @uses count()
	 * @uses \Crux\Collection\Vector::offsetSet()
	 */
	public function reserve(int $intCount) : Vector
	{
		// Create an iterator
		$intIterator = count($this->getArrayCopy());
		// Iterate to the count
		while (count($this->getArrayCopy()) <= $intCount) {
			// Push a null value to the array
			$this->offsetSet($intIterator, null);
			// Increment the iterator
			++$intIterator;
		}
		// We're done, return the instance
		return $this;
	}


	public function resize($intCount, $mixDefaultValue) : Vector
	{
		// We're done, return the instance
		return $this;
	}

	/**
	 * This method reverses the elements of the current vector in place
	 * @access public
	 * @name \Crux\Collection\Vector::reverse()
	 * @package \Crux\Collection\Vector
	 * @return \Crux\Collection\Vector $this
	 * @uses \Crux\Collection\Vector::getArrayCopy()
	 * @uses array_reverse()
	 * @uses \Crux\Collection\Vector::offsetSet()
	 */
	public function reverse() : Vector
	{
		// Reverse the instance order
		$arrVector = array_reverse($this->getArrayCopy());
		// Iterate over the vector
		foreach ($arrVector as $intIndex => $mixValue) {
			// Reset the value in the instance
			$this->offsetSet($intIndex, $mixValue);
		}
		// We're done, return the instance
		return $this;
	}

	/**
	 * This method sets a key and value into the Vector
	 * @access public
	 * @name \Crux\Collection\Vector::set()
	 * @package Crux\Collection\Vector
	 * @param int $intIndex
	 * @param $mixValue
	 * @return \Crux\Collection\Vector $this
	 * @throws Core\Exception\Collection\Vector
	 * @uses \Crux\Collection\Vector::validateType()
	 * @uses \Crux\Collection\Vector::offsetSet()
	 */
	public function set(int $intIndex, $mixValue)
	{
		// Set the value into the Vector
		$this->offsetSet($intIndex, $mixValue);
		// We're done, return the instance
		return $this;
	}

	/**
	 * For ever element in the provided $mixSource, this method stores a value into the current vector associated with each key, overwriting the previous value associated with the key
	 * @access publc
	 * @name \Crux\Collection\Vector::setAll()
	 * @package \Crux\Collection\Vector
	 * @param array|\Crux\Collection\Vector $mixSource
	 * @return \Crux\Collection\Vector $this
	 * @throws Core\Exception\Collection\Vector
	 * @uses \Crux\Core\Is::sequentialArray()
	 * @uses \Crux\Collection\Vector::fromArray()
	 * @uses \Crux\Collection\Vector::getArrayCopy()
	 * @uses \Crux\Collection\Vector::toArray()
	 * @uses \Crux\Collection\Vector::getIterator()
	 * @uses \Crux\Collection\Vector::offsetSet()
	 */
	public function setAll($mixSource) : Vector
	{
		// Check for a vector
		if (($mixSource instanceof Vector) === false) {
			// Make sure we have a sequential array
			if (!Core\Is::sequentialArray($mixSource)) {
				// We're done, send the error
				throw new Core\Exception\Collection\Vector('Source variable must be of type Vector or array.');
			}
			// Convert the array to a vector
			$mixSource = self::fromArray($mixSource);

		}
		// Merge the arrays
		$vecNew = self::fromArray(array_merge($this->getArrayCopy(), $mixSource->toArray()));
		// Iterate over the new array
		foreach ($vecNew->getIterator() as $intIndex => $mixValue) {
			// Reset the index
			$this->offsetSet($intIndex, $mixValue);
		}
		// We're done, return the instance
		return $this;
	}

	/**
	 * This method sets $intIndex into the current vector after $mixValue has been filtered by $fnCallback
	 * @access public
	 * @name \Crux\Collection\Vector::setWith()
	 * @package \Crux\Collection\Vector
	 * @param int $intIndex
	 * @param mixed $mixValue
	 * @param callable $fnCallback
	 * @return \Crux\Collection\Vector
	 * @uses call_user_func_array()
	 * @uses \Crux\Collection\Vector::set()
	 */
	public function setWith(int $intIndex, $mixValue, callable $fnCallback) : Vector
	{
		// Filter the index and value
		call_user_func_array($fnCallback, [$intIndex, &$mixValue, $this]);
		// Set the value into the instance
		return $this->set($intIndex, $mixValue);
	}

	/**
	 * This method shuffles the vector in place
	 * @access public
	 * @name \Crux\Collection\Vector::shuffle()
	 * @package \Crux\Collection\Vector
	 * @return \Crux\Collection\Vector $this
	 * @uses \Crux\Collection\Vector::getArrayCopy()
	 * @uses shuffle()
	 * @uses \Crux\Collection\Vector::offsetSet()
	 */
	public function shuffle() : Vector
	{
		// Localize the instance
		$arrSelf = $this->getArrayCopy();
		// Shuffle the instance
		shuffle($arrSelf);
		// Iterate over the shuffled array
		foreach ($arrSelf as $intIndex => $mixValue) {
			// Reset the offset
			$this->offsetSet($intIndex, $mixValue);
		}
		// We're done, return the instance
		return $this;
	}

	/**
	 * This method returns a vector containing the elements after $intOffset
	 * @access public
	 * @name \Crux\Collection\Vector::skip()
	 * @package \Crux\Collection\Vector
	 * @param int $intOffset
	 * @return \Crux\Collection\Vector
	 * @uses \Crux\Collection\Vector::__construct()
	 * @uses \Crux\Collection\Vector::getIterator()
	 * @uses \Crux\Collection\Vector::add()
	 */
	public function skip(int $intOffset) : Vector
	{
		// Create the vector container
		$vecContainer = new self();
		// Iterate over the instance
		foreach ($this->getIterator() as $intIndex => $mixValue) {
			// Check the index against the offset
			if ($intIndex > $intOffset) {
				// Add the value to the container
				$vecContainer->add($mixValue);
			}
		}
		// We're done, return the new vector
		return $vecContainer;
	}

	/**
	 * This method returns a vector containg the values of the current vector starting after and including the first value that produces true when passed to the specified callback
	 * @access public
	 * @name \Crux\Collection\Vector::skipWhile()
	 * @package \Crux\Collection\Vector
	 * @param callable $fnCallback
	 * @return \Crux\Collection\Vector
	 * @uses \Crux\Collection\Vector::getIterator()
	 * @uses call_user_func_array()
	 * @uses \Crux\Collection\Vector::slice()
	 * @uses \Crux\Collection\Vector::__construct()
	 */
	public function skipWhile(callable $fnCallback) : Vector
	{
		// Iterate over the instance
		foreach ($this->getIterator() as $intIndex => $mixValue) {
			// Execute the callback
			if (call_user_func_array($fnCallback, [$mixValue])) {
				// We're done, return the slice
				return $this->slice($intIndex);
			}
		}
		// We're done, return an empty vector
		return new self();
	}

	/**
	 * This method returns a subset of the current vector starting from a given key up to, but not including, the element at the provided length from the starting key
	 * @access public
	 * @name \Crux\Collection\Vector::slice()
	 * @package \Crux\Collection\Vector
	 * @param int $intStart
	 * @param int|null $intLength
	 * @return \Crux\Collection\Vector
	 * @throws Core\Exception\Collection\Vector
	 * @uses array_slice()
	 * @uses \Crux\Collection\Vector::fromArray()
	 */
	public function slice(int $intStart, int $intLength = null) : Vector
	{
		// Create the new array
		$arrVector = array_slice($this->getArrayCopy(), $intStart, $intLength);
		// Return the new vector
		return self::fromArray($arrVector);
	}

	/**
	 * This method splices the current vector in place
	 * @access public
	 * @name \Crux\Collection\Vector::splice()
	 * @package \Crux\Collection\Vector
	 * @param int $intOffset
	 * @param int|null $intLength
	 * @return \Crux\Collection\Vector $this
	 * @uses array_splice()
	 */
	public function splice(int $intOffset, int $intLength = null) : Vector
	{
		// Localize the vector
		$arrVector = $this->getArrayCopy();
		// Clear the vector
		$this->clear();
		// Create the new array
		array_splice($arrVector, $intOffset, $intLength);
		// Iterate over the spliced array
		foreach ($arrVector as $mixValue) {
			// Add the value back into the instance
			$this->add($mixValue);
		}
		// We're done, return the instance
		return $this;
	}

	/**
	 * This method returns a vector containing the first $intCount values from the current vector
	 * @access public
	 * @name \Crux\Collection\Vector::take()
	 * @package \Crux\Collection\Vector
	 * @param int $intCount
	 * @return \Crux\Collection\Vector
	 * @uses \Crux\Collection\Vector::__construct()
	 * @uses \Crux\Collection\Vector::offsetExists()
	 * @uses \Crux\Collection\Vector::offsetGet()
	 * @uses \Crux\Collection\Vector::add()
	 */
	public function take(int $intCount) : Vector
	{
		// Create our container
		$vecContainer = new self();
		// Iterate to the count
		for ($intIndex = 0; $intIndex < $intCount; ++$intIndex) {
			// Check for the index
			if (!$this->offsetExists($intIndex)) {
				// We're done
				break;
			}
			// Add the value to the vector
			$vecContainer->add($this->offsetGet($intIndex));
		}
		// We're done, return the new vector
		return $vecContainer;
	}

	/**
	 * This method returns a vector containing the values of the current vector up to, but not including, the first value that produces false when passed to the specified callback
	 * @access public
	 * @name \Crux\Collection\Vector::takeWhile()
	 * @package \Crux\Collection\Vector
	 * @param callable $fnCallback
	 * @return \Crux\Collection\Vector
	 * @uses \Crux\Collection\Vector::getIterator()
	 * @uses call_user_func_array()
	 * @uses \Crux\Collection\Vector::add()
	 */
	public function takeWhile(callable $fnCallback) : Vector
	{
		// Create our vector container
		$vecContainer = new self();
		// Iterate over the instance
		foreach ($this->getIterator() as $mixValue) {
			// Execute the callback
			if (call_user_func_array($fnCallback, [$mixValue])) {
				// Add the value to the vector
				$vecContainer->add($mixValue);
			} else {
				// We're done
				break;
			}
		}
		// We're done, return the new vector
		return $vecContainer;
	}

	/**
	 * This method returns the Vector as a traditional associative array
	 * @access public
	 * @name \Crux\Collection\Vector::toArray()
	 * @package Crux\Collection\Vector
	 * @return array
	 * @uses \Crux\Core\Is::map()
	 * @uses \Crux\Core\Is::vector()
	 * @uses \Crux\Core\Is::variantScalar()
	 * @uses \Crux\Core\Is::variantMap()
	 * @uses \Crux\Core\Is::variantVector()
	 * @uses \Crux\Collection\Map::toArray()
	 * @uses \Crux\Collection\Vector::toArray()
	 * @uses array_push()
	 */
	public function toArray() : array
	{
		// Create the array container
		$arrVector = [];
		// Iterate over the instance
		foreach ($this->getIterator() as $mixValue) {
			// Check the value
			if (Core\Is::map($mixValue)) {
				// Add the value to the container
				array_push($arrVector, $mixValue->toArray());
			} elseif (Core\Is::vector($mixValue)) {
				// Add the value to the container
				array_push($arrVector, $mixValue->toArray());
			} elseif (Core\Is::variantScalar($mixValue)) {
				// Add the value to the container
				array_push($arrVector, $mixValue->getData());
			} elseif (Core\Is::variantMap($mixValue)) {
				// Add the value to the container
				array_push($arrVector, $mixValue->getData());
			} elseif (Core\Is::variantVector($mixValue)) {
				// Add the value to the container
				array_push($arrVector, $mixValue->getData());
			} else {
				// Add the value to the container
				array_push($arrVector, $mixValue);
			}
		}
		// We're done, return the new array
		return $arrVector;
	}

	/**
	 * This method encodes the vector into a JSON string
	 * @access public
	 * @name \Crux\Collection\Vector::toJson()
	 * @package \Crux\Collection\Vector
	 * @return string
	 * @uses \Crux\Collection\Vector::toArray()
	 * @uses json_encode()
	 */
	public function toJson() : string
	{
		// Encode the vector in JSON
		return json_encode($this->toArray(), JSON_PRETTY_PRINT);
	}

	/**
	 * This method returns an array of keys from the Vector
	 * @access public
	 * @name \Crux\Collection\Vector::toKeysArray()
	 * @package Crux\Collection\Vector
	 * @return array
	 * @uses \Crux\Collection\Vector::getIterator()
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
	 * This method serializes the vector into a string
	 * @access public
	 * @name \Crux\Collection\Vector::toString()
	 * @package \Crux\Collection\Vector
	 * @return string
	 * @uses serialize()
	 */
	public function toString() : string
	{
		// Return the string representation
		return serialize($this);
	}

	/**
	 * This method returns an array of values from the Vector
	 * @access public
	 * @name \Crux\Collection\Vector::toValuesArray()
	 * @package Crux\Collection\Vector
	 * @return array
	 * @uses \Crux\Collection\Vector::getIterator()
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
	 * @name \Crux\Collection\Vector::toXml()
	 * @package \Crux\Colleciton\Vector
	 * @param string $strRootNode ['vector']
	 * @param bool $blnIncludeHeader [true]
	 * @param string $strChildNode ['item']
	 * @return string
	 * @uses \Crux\Serialize\Xml::__construct()
	 * @uses \Crux\Serialize\Xml::rootNode()
	 * @uses \Crux\Serialize\Xml::childListNode()
	 * @uses \Crux\Serialize\Xml::defineDocument()
	 * @uses \Crux\Serialize\Xml::dontDefineDocument()
	 * @uses \Crux\Serialize\Xml::serialize()
	 * @uses \Crux\Collection\Vector::toArray()
	 */
	public function toXml(string $strRootNode = 'vector', bool $blnIncludeHeader = true, string $strChildNode = 'item') : string
	{
		// Instantiate our serializer
		$xmlSerializer = new Serialize\Xml();
		// Set the root node name
		$xmlSerializer->rootNode($strRootNode);
		// Set the child node name
		$xmlSerializer->childListNode($strChildNode);
		// Check the header flag
		if ($blnIncludeHeader) {
			// Include the header
			$xmlSerializer->defineDocument();
		} else {
			// Don't include the header
			$xmlSerializer->dontDefineDocument();
		}
		// We're done, return the XML
		return $xmlSerializer->serialize($this->toArray());
	}

	/**
	 * This method prepends one or more values to the beginning of the vector
	 * @access public
	 * @name \Crux\Collection\Vector::unshift()
	 * @package \Crux\Collection\Vector
	 * @param array ...$arrValues
	 * @return \Crux\Collection\Vector $this
	 * @throws \Crux\Core\Exception\Collection\Vector
	 * @uses \Crux\Collection\Vector::getArrayCopy()
	 * @uses \Crux\Collection\Vector::clear()
	 * @uses \Crux\Collection\Vector::setAll()
	 * @uses array_unshift()
	 * @uses unset()
	 */
	public function unshift(...$arrValues)
	{
		// Grab a copy of the vector
		$arrCopy = $this->getArrayCopy();
		// Iterate over the values
		foreach ($arrValues as $mixValue) {
			// Prepend the value
			array_unshift($arrCopy, $mixValue);
		}
		// Clear the vector
		$this->clear();
		// Reset the data
		$this->setAll($arrCopy);
		// Unset the copy
		unset($arrCopy);
		// We're done, return the instance
		return $this;
	}

	/**
	 * This method returns a vector of values from the Vector
	 * @access public
	 * @name \Crux\Collection\Vector::values()
	 * @package Crux\Collection\Vector
	 * @return Vector
	 * @uses \Crux\Collection\Vector::toValuesArray()
	 * @uses \Crux\Collection\Vector::Vector()
	 */
	public function values() : Vector
	{
		// Return the new vector of values
		return new Vector($this->mValueType, $this->toValuesArray());
	}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
} /// End Crux\Collection\Vector Class Definition ////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

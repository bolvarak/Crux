<?php

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Crux\Provider\Sql Namespace //////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

namespace Crux\Provider\Sql;

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Imports //////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

use Crux\Collection;
use Crux\Core;
use Crux\Serialize;
use Crux\Type;

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Crux\Provider\Sql\Record Class Definition ////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class Record implements Serialize\Able
{
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Magic Methods ////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method provides dynamic method constructs for column setters and getters
	 * @access public
	 * @name \Crux\Provider\Sql\Record::__call()
	 * @package \Crux\Provider\Sql\Record
	 * @param string $strMethod
	 * @param array<int, mixed> $arrArguments
	 * @return mixed
	 * @throws \Crux\Core\Exception\Provider\Sql\Record
	 * @uses \Crux\Provider\Sql\Record::__get()
	 * @uses \Crux\Provider\Sql\Record::__set()
	 * @uses \Crux\Core\Exception\Provider\Sql\Record::__construct()
	 * @uses substr()
	 * @uses strtolower()
	 * @uses substr_replace()
	 * @uses array_unshift()
	 * @uses call_user_func_array()
	 */
	public function __call(string $strMethod, array $arrArguments)
	{
		// Check the method name
		if (strtolower(substr($strMethod, 0, 3)) === 'get') {
			// Call the method
			return $this->__get(substr_replace($strMethod, '', 0, 3));
		} elseif (strtolower(substr($strMethod, 0, 3)) === 'set') {
			// Call the method
			return $this->__set(substr_replace($strMethod, '', 0, 3), $arrArguments[0] ?? null);
		} else {
			// Throw the exception
			throw new Core\Exception\Provider\Sql\Record(sprintf('Method [%s] could not be found in the SQL record.', $strMethod));
		}
	}

	/**
	 * This method returns a column from the instance
	 * @access public
	 * @name \Crux\Provider\Sql\Record::__get()
	 * @package \Crux\Provider\Sql\Record
	 * @param string $strProperty
	 * @return \Crux\Type\Variant
	 * @throws \Crux\Core\Exception\Provider\Sql\Record
	 * @uses \Crux\Type\VariantMap::getIterator()
	 * @uses \Crux\Core\Is::encodedHtml()
	 * @uses \Crux\Core\Exception\Provider\Sql\Record::__construct()
	 * @uses str_replace()
	 * @uses strtolower()
	 * @uses htmlspecialchars_decode()
	 * @uses sprintf()
	 */
	public function __get(string $strProperty)
	{
		// Convert the key
		$strKey = strtolower(str_replace(['-', '_'], ['', ''], $strProperty));
		// Grab the keys
		$arrKeys = $this->mContainer->toKeysArray();
		// Iterate over the keys
		for ($intKey = 0; $intKey < count($arrKeys); ++$intKey) {
			// Check the key
			if ($strKey == strtolower(str_replace(['-', '_'], ['', ''], $arrKeys[$intKey]))) {
				// Localize the value
				$mixValue = $this->mContainer->get($arrKeys[$intKey]);
				// Check the value for encoded HTML
				if (Core\Is::encodedHtml($mixValue)) {
					// Return the decoded value
					return htmlspecialchars_decode($mixValue, ENT_QUOTES);
				} else {
					// Return the value
					return $mixValue;
				}
			}
		}
		// Throw the exception
		throw new Core\Exception\Provider\Sql\Record(sprintf('Column [%s] could not be found in the SQL record.', $strProperty));
	}

	/**
	 * This method sets a column into the instance
	 * @access public
	 * @name \Crux\Provider\Sql\Record::__set()
	 * @package \Crux\Provider\Sql
	 * @param string $strProperty
	 * @param mixed $mixValue
	 * @uses \Crux\Core\Is::html()
	 * @uses \Crux\Type\VariantMap::set()
	 * @uses htmlspecialchars()
	 */
	public function __set(string $strProperty, $mixValue)
	{
		// Set the property into the instance
		$this->mContainer->set($strProperty, (Core\Is::html($mixValue) ? htmlspecialchars($mixValue, ENT_QUOTES) : $mixValue));
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Protected Properties /////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This property contains the data from the database record
	 * @access protected
	 * @name \Crux\Provider\Sql\Record::$mContainer
	 * @package \Crux\Provider\Sql\Record
	 * @var \Crux\Type\VariantMap
	 */
	protected $mContainer;

	/**
	 * This property contains the executed statement
	 * @access protected
	 * @name \Crux\Provider\Sql\Record::$mStatement
	 * @package \Crux\Provider\Sql\Record
	 * @var \Crux\Provider\Sql\Statement
	 */
	protected $mStatement;

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Constructor //////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method instantiates a new SQL record object
	 * @access public
	 * @name \Crux\Provider\Sql\Record::__construct()
	 * @package \Crux\Provider\Sql\Record
	 * @uses \Crux\Type\VariantMap::__construct()
	 */
	public function __construct(Statement $pdoStatement)
	{
		// Set the statement into the instance
		$this->mStatement = $pdoStatement;
		// Initialize the data container
		$this->mContainer = new Type\VariantMap();
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Static Constructs ////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method builds a SQL Record from an associative array
	 * @access public
	 * @name \Crux\Provider\Sql\Record::fromArray()
	 * @package \Crux\Provider\Sql\Record
	 * @param \Crux\Provider\Sql\Statement $pdoStatement
	 * @param array<string, mixed> $arrSource
	 * @return \Crux\Provider\Sql\Record
	 * @static
	 * @uses \Crux\Provider\Sql\Record::__construct()
	 * @uses \Crux\Provider\Sql\Record::__set()
	 */
	public static function fromArray(Statement $pdoStatement, array $arrSource) : Record
	{
		// Instantiate the  class
		$pdoRecord = new self($pdoStatement);
		// Iterate over the array
		foreach ($arrSource as $strColumn => $mixValue) {
			// Set the property
			$pdoRecord->{$strColumn} = $mixValue;
		}
		// We're done, return the record
		return $pdoRecord;
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Public Methods ///////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method returns a column from the record
	 * @access public
	 * @name \Crux\Provider\Sql\Record::column()
	 * @package \Crux\Provider\Sql\Record
	 * @param string $strColumn
	 * @return \Crux\Type\Variant
	 * @throws \Crux\Core\Exception\Provider\Sql\Record
	 * @uses \Crux\Type\VariantMap::containsKey()
	 * @uses \Crux\Type\VariantMap::get()
	 * @uses \Crux\Core\Exception\Provider\Sql\Record::__construct()
	 * @uses sprintf()
	 */
	public function column(string $strColumn) : Type\Variant
	{
		// Check for the key
		if (!$this->mContainer->containsKey($strColumn)) {
			// Throw the exception
			throw new Core\Exception\Provider\Sql\Record(sprintf('Column %s does not exist in the Record', $strColumn));
		}
		// Return the column
		return $this->mContainer->get($strColumn);
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Implemented Converters ///////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method converts the records to a JSON string
	 * @access public
	 * @name \Crux\Provider\Sql\Record::toJson()
	 * @package \Crux\Provider\Sql\Record
	 * @param bool $blnPrettyPrint [false]
	 * @return string
	 * @uses \Crux\Type\VariantMap::toJson()
	 */
	public function toJson(bool $blnPrettyPrint = false) : string
	{
		// Return the JSON
		return $this->mContainer->toJson();
	}

	/**
	 * This method converts the columns to XML
	 * @access public
	 * @name \Crux\Provider\Sql\Record::toXml()
	 * @package \Crux\Provider\Sql\Record
	 * @param bool $blnIncludeHeaders [true]
	 * @param string $strRootNode ['payload']
	 * @param string $strChildListNode ['item']
	 * @param bool $blnPrettyPrint [false]
	 * @return string
	 * @uses \Crux\Type\VariantMap::toXml()
	 */
	public function toXml(bool $blnIncludeHeaders = true, string $strRootNode = 'payload', string $strChildListNode = 'item', bool $blnPrettyPrint = false) : string
	{
		// Return the XML
		return $this->mContainer->toXml($strRootNode, $blnPrettyPrint);
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Converters ///////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method converts the columns to an associative array
	 * @access public
	 * @name \Crux\Provider\Sql\Record::toArray()
	 * @package \Crux\Provider\Sql\Record
	 * @return array<string, mixed>
	 * @uses \Crux\Type\VariantMap::toArray()
	 */
	public function toArray() : array
	{
		// Return the array of columns
		return $this->mContainer->toArray();
	}

	/**
	 * This method converts the columns to a PHireworks Map
	 * @access public
	 * @name \Crux\Provider\Sql\Record::toCollection()
	 * @package \Crux\Provider\Sql\Record
	 * @return \Crux\Collection\Map
	 * @uses \Crux\Type\VariantMap::toMap()
	 */
	public function toCollection() : Collection\Map
	{
		// Return the collection of columns
		return $this->mContainer->toMap();
	}

	/**
	 * This method converts the columns to an instance of \stdClass
	 * @access public
	 * @name \Crux\Provider\Sql\Record::toObject()
	 * @package \Crux\Provider\Sql\Record
	 * @return \stdClass
	 * @uses \Crux\Type\VariantMap::toObject()
	 */
	public function toObject() : \stdClass
	{
		// Return the object
		return $this->mContainer->toObject();
	}

	/**
	 * This method converts the columns to a PHireworks VariantMap
	 * @access public
	 * @name \Crux\Provider\Sql\Record::toVariant()
	 * @package \Crux\Provider\Sql\Record
	 * @return \Crux\Type\VariantMap
	 */
	public function toVariant() : Type\VariantMap
	{
		// Return the column container
		return $this->mContainer;
	}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
} /// End Crux\Provider\Sql\Record Class Definition //////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

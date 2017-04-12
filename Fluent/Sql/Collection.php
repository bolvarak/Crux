<?php

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Crux\Fluent\Sql Namespace ////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

namespace Crux\Fluent\Sql;

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Imports //////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

use Crux\Collection\Vector;
use Crux\Core;
use Crux\Grammar;
use Crux\Provider\Sql;
use Crux\Serialize;
use Crux\Type;

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Crux\Fluent\Sql\Collection Class Definition //////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class Collection implements Serialize\Able, \JsonSerializable
{
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Magic Methods ////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method handles dynamic calls to the collection iterator
	 * @access public
	 * @name \Crux\Fluent\Sql\Collection::__call()
	 * @package \Crux\Fluent\Sql\Collection
	 * @param string $strMethod
	 * @param array $arrArguments
	 * @return \Crux\Fluent\Sql\Model|\Crux\Provider\Sql\Record
	 * @throws \Crux\Core\Exception\Fluent\Sql\Collection
	 * @uses \Crux\Fluent\Sql\Collection::model()
	 * @uses \Crux\Fluent\Sql\Collection::record()
	 * @uses \Crux\Core\Exception\Fluent\Sql\Collection::__construct()
	 * @uses preg_match()
	 * @uses intval()
	 * @uses sprintf()
	 */
	public function __call(string $strMethod, array $arrArguments)
	{
		// Check the method
		if (preg_match('/get([0-9]+)AsModel/i', $strMethod, $arrMatches)) {
			// Return the model
			return $this->model(intval($arrMatches[1]));
		} elseif (preg_match('/get([0-9]+)AsRecord/i', $strMethod, $arrMatches)) {
			// Return the record
			return $this->record(intval($arrMatches[1]));
		} else {
			// Throw the exception
			throw new Core\Exception\Fluent\Sql\Collection(sprintf('Method %s is not a valid collection reference', $strMethod));
		}
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Protected Properties /////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This property contains the connection pool name
	 * @access protected
	 * @name \Crux\Fluent\Sql\Collection::$mConnection
	 * @package \Crux\Fluent\Sql\Collection
	 * @var string
	 */
	protected $mConnection = '';

	/**
	 * This property contains the collection data
	 * @access protected
	 * @name \Crux\Fluent\Sql\Collection::$mData
	 * @var \Crux\Collection\Vector<\Crux\Fluent\Sql\Record>
	 */
	protected $mData;

	/**
	 * This property contains the table's description
	 * @access protected
	 * @name \Crux\Fluent\Sql\Collection::$mDescription
	 * @package \Crux\Fluent\Sql\Collection
	 * @var array<string, array<int, string>>
	 */
	protected $mDescription = [];

	/**
	 * This property tells the instance whether or not results have been fetched
	 * @access protected
	 * @name \Crux\Fluent\Sql\Collection::$mFetched
	 * @package \Crux\Fluent\Sql\Collection
	 * @var bool
	 */
	protected $mFetched = false;

	/**
	 * This property contains the GROUP BY clause
	 * @access protected
	 * @name \Crux\Fluent\Sql\Collection::$mGroupBy
	 * @package \Crux\Fluent\Sql\Collection
	 * @var string
	 */
	protected $mGroupBy = '';

	/**
	 * This property contains the LIMIT clause
	 * @access protected
	 * @name \Crux\Fluent\Sql\Collection::$mLimit
	 * @package \Crux\Fluent\Sql\Collection
	 * @var string
	 */
	protected $mLimit = '';

	/**
	 * This property contains the model instance to use for fetching records into
	 * @access protected
	 * @name \Crux\Fluent\Sql\Collection::$mModel
	 * @package \Crux\Fluent\Sql\Collection
	 * @var string
	 */
	protected $mModel = '';

	/**
	 * This property contains the ORDER BY clause
	 * @access protected
	 * @name \Crux\Fluent\Sql\Collection::$mOrderBy
	 * @package \Crux\Fluent\Sql\Collection
	 * @var string
	 */
	protected $mOrderBy = '';

	/**
	 * This property contains the primary key column name
	 * @access protected
	 * @name \Crux\Fluent\Sql\Collection::$mPrimaryKey
	 * @package \Crux\Fluent\Sql\Collection
	 * @var string
	 */
	protected $mPrimaryKey = '';

	/**
	 * This property contains the schema name in which the table resides
	 * @access protected
	 * @name \Crux\Fluent\Sql\Collection::$mSchema
	 * @package \Crux\Fluent\Sql\Collection
	 * @var string
	 */
	protected $mSchema = '';

	/**
	 * This property contains the table name to query
	 * @access protected
	 * @name \Crux\Fluent\Sql\Collection::$mTable
	 * @package \Crux\Fluent\Sql\Collection
	 * @var string
	 */
	protected $mTable = '';

	/**
	 * This property contains the WHERE clause
	 * @access protected
	 * @name \Crux\Fluent\Sql\Collection::$mWhere
	 * @package \Crux\Fluent\Sql\Collection
	 * @var string
	 */
	protected $mWhere = '';

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Constructor //////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method instantiates a new collection object
	 * @access public
	 * @name \Crux\Fluent\Sql\Collection::__construct()
	 * @package \Crux\Fluent\Sql\Collection
	 * @param \Crux\Fluent\Sql\Record $modContainer
	 * @uses \Crux\Fluent\Sql\Record::connectionName()
	 * @uses \Crux\Fluent\Sql\Record::schemaName()
	 * @uses \Crux\Fluent\Sql\Record::tableName()
	 */
	public function __construct(Record $modContainer)
	{
		// Initialize the data container
		$this->mData = new Vector();
		// Set the connection name into the instance
		$this->mConnection = $modContainer->connectionName();
		// Set the schema name into the instance
		$this->mSchema = $modContainer->schemaName();
		// Set the table name into the instance
		$this->mTable = $modContainer->tableName();
		// Set the primary key column name
		$this->mPrimaryKey = $modContainer->primaryKeyName();
		// Set the table description
		$this->mDescription = $modContainer->tableDescription();
		// Set the container into the instance
		$this->mModel = get_class($modContainer);
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Implemented Converters ///////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method gets called when json_encode is executed on an object of this class
	 * @access public
	 * @name \Crux\Fluent\Sql\Collection::jsonSerialize()
	 * @package \Crux\Fluent\Sql\Collection
	 * @return \Crux\Collection\Vector
	 */
	public function jsonSerialize()
	{
		// Return the records
		return $this->mData;
	}

	/**
	 * This method converts the record set to a collection
	 * @access public
	 * @name \Crux\Fluent\Sql\Collection::toCollection()
	 * @package \Crux\Fluent\Sql\Collection
	 * @return \Crux\Collection\Vector
	 * @uses \Crux\Collection\Vector::__construct()
	 * @uses \Crux\Collection\Vector::add()
	 * @uses \Crux\Collection\Vector::getIterator()
	 * @uses \Crux\Fluent\Sql\Collection::records()
	 * @uses \Crux\Fluent\Sql\Record::toCollection()
	 */
	public function toCollection() : Vector
	{
		// Define our container
		$vecContainer = new Vector();
		// Iterate over the records
		foreach ($this->records()->getIterator() as $modRecord) {
			// Add the record to the container
			$vecContainer->add($modRecord->toCollection());
		}
		// We're done, return the container
		return $vecContainer;
	}

	/**
	 * This method converts the collection results to JSON
	 * @access public
	 * @name \Crux\Fluent\Sql\Collection::toJson()
	 * @package \Crux\Fluent\Sql\Collection()
	 * @param bool $blnPrettyPrint [false]
	 * @return string
	 * @uses \Crux\Collection\Vector::toJson()
	 */
	public function toJson(bool $blnPrettyPrint = false) : string
	{
		// Return the JSON from the vector
		$this->mData->toJson();
	}

	/**
	 * This method converts the collection to an array of objects
	 * @access public
	 * @name \Crux\Fluent\Sql\Collection::toObject()
	 * @package \Crux\Fluent\Sql\Collection
	 * @return array<\stdClass>
	 * @uses \Crux\Fluent\Sql\Collection::records()
	 * @uses \Crux\Fluent\Sql\Record::toObject()
	 * @uses \Crux\Collection\Vector::getIterator()
	 * @uses array_push()
	 */
	public function toObject() : array
	{
		// Define our container
		$arrContainer = [];
		// Iterate over the records
		foreach ($this->records()->getIterator() as $modRecord) {
			// Add the record to the container
			array_push($arrContainer, $modRecord->toObject());
		}
		// We're done, return the container
		return $arrContainer;
	}

	/**
	 * This method converts the record set to a variant list
	 * @access public
	 * @name \Crux\Fluent\Sql\Collection::toVariant()
	 * @package \Crux\Fluent\Sql\Collection
	 * @return \Crux\Type\VariantList
	 * @uses \Crux\Type\VariantList::__construct()
	 * @uses \Crux\Type\VariantList::add()
	 * @uses \Crux\Fluent\Sql\Collection::records()
	 * @uses \Crux\Collection\Vector::getIterator()
	 */
	public function toVariant() : Type\VariantList
	{
		// Define our container
		$lstContainer = new Type\VariantList();
		// Iterate over the records
		foreach ($this->records()->getIterator() as $modRecord) {
			// Add the record to the container
			$lstContainer->add($modRecord->toVariant());
		}
		// We're done, return the container
		return $lstContainer;
	}

	/**
	 * This method converts the collection results to XML
	 * @access public
	 * @name \Crux\Fluent\Sql\Collection::toXml()
	 * @package \Crux\Fluent\Sql\Collecion
	 * @param bool $blnIncludeHeaders [true]
	 * @param string $strRootNode ['']
	 * @param string $strChildListNode ['']
	 * @param bool $blnPrettyPrint [false]
	 * @return string
	 * @uses \Crux\Core\Is::empty()
	 * @uses \Crux\Grammar\Inflection::pluralize()
	 * @uses \Crux\Grammar\Inflection::singularize()
	 * @uses \Crux\Collection\Vector::toXml()
	 */
	public function toXml(bool $blnIncludeHeaders = true, string $strRootNode = '', string $strChildListNode = '', bool $blnPrettyPrint = false) : string
	{
		// Check the root node
		if (Core\Is::empty($strRootNode)) {
			// Reset the root node
			$strRootNode = Grammar\Inflection::pluralize($this->mTable);
		}
		// Check the child node
		if (Core\Is::empty($strChildListNode)) {
			// Reset the child node
			$strChildListNode = Grammar\Inflection::singularize($this->mTable);
		}
		// Return the XML from the vector
		return $this->mData->toXml($strRootNode, $blnIncludeHeaders, $strChildListNode);
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Public Methods ///////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method populates the collection
	 * @access public
	 * @name \Crux\Fluent\Sql\Collection::populate()
	 * @package \Crux\Fluent\Sql\Collection
	 * @return \Crux\Fluent\Sql\Collection $this
	 * @uses \Crux\Core\Is::empty()
	 * @uses \Crux\Provider\Sql\Engine::getConnection()
	 * @uses \Crux\Provider\Sql\Engine::queryf()
	 * @uses \Crux\Collection\Vector::clear()
	 * @uses \Crux\Collection\Vector::add()
	 * @uses sprintf()
	 * @uses trim()
	 */
	public function populate() : Collection
	{
		// Check for a schema
		if (Core\Is::empty($this->mSchema)) {
			// Initialize the SELECT statement
			$strQuery = Sql\Engine::getConnection($this->mConnection)->queryf('SELECT %C FROM %T', $this->mPrimaryKey, $this->mTable);
		} else {
			// Initialize the SELECT statement
			$strQuery = Sql\Engine::getConnection($this->mConnection)->queryf('SELECT %C FROM %T.%T', $this->mPrimaryKey, $this->mSchema, $this->mTable);
		}
		// Check for a WHERE clause
		if (!Core\Is::empty($this->mWhere)) {
			// Add the WHERE clause
			$strQuery = sprintf('%s WHERE %s', $strQuery, $this->mWhere);
		}
		// Check for a GROUP BY clause
		if (!Core\Is::empty($this->mGroupBy)) {
			// Add the GROUP BY clause
			$strQuery = sprintf('%s GROUP BY %s', $strQuery, $this->mGroupBy);
		}
		// Check for an ORDER BY clause
		if (!Core\Is::empty($this->mOrderBy)) {
			// Add the ORDER BY clause
			$strQuery = sprintf('%s ORDER BY %s', $strQuery, $this->mOrderBy);
		}
		// Check for a LIMIT clause
		if (!Core\Is::empty($this->mLimit)) {
			// Add the LIMIT clause
			$strQuery = sprintf('%s LIMIT %s', $strQuery, $this->mLimit);
		}
		// Finalize the query
		$strQuery = sprintf('%s;', trim($strQuery));
		// Execute the query
		$pdoStatement = Sql\Engine::getConnection($this->mConnection)->executeQuery($strQuery);
		// Empty the data container
		$this->mData->clear();
		// Iterate over the results
		while ($pdoRecord = $pdoStatement->fetch()) {
			// Instantiate the model
			$modThisRow = new $this->mModel($pdoRecord->column($this->mPrimaryKey), $this->mPrimaryKey);
			// Set the model into the instance
			$this->mData->add($modThisRow);
		}
		// We're done, return the instance
		return $this;
	}

	/**
	 * This method returns a record from the collection
	 * @access public
	 * @name \Crux\Fluent\Sql\Collection::record()
	 * @package \Crux\Fluent\Sql\Collection
	 * @param int $intRow [0]
	 * @return \Crux\Fluent\Sql\Record
	 * @throws \Crux\Core\Exception\Fluent\Sql\Collection
	 * @uses \Crux\Collection\Vector::contains()
	 * @uses \Crux\Collection\Vector::at()
	 * @uses \Crux\Core\Exception\Fluent\Sql\Collection::__construct()
	 * @uses sprintf()
	 */
	public function record(int $intRow) : Record
	{
		// Check for the record
		if (!$this->mData->contains($intRow)) {
			// Throw the exception
			throw new Core\Exception\Fluent\Sql\Collection(sprintf('Collection record at index [%d] does not exist', $intRow));
		}
		// Return the record
		return $this->mData->at($intRow);
	}

	/**
	 * This method returns the records from the instance
	 * @access public
	 * @name \Crux\Fluent\Sql\Collection::records()
	 * @package \Crux\Fluent\Sql\Collection
	 * @return \Crux\Collection\Vector
	 */
	public function records() : Vector
	{
		// Return the records from the instance
		return $this->mData;
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Clause Methods ///////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method adds a GROUP BY clause to the instance
	 * @access public
	 * @name \Crux\Fluent\Sql\Collection::groupBy()
	 * @package \Crux\Fluent\Sql\Collection
	 * @param string $strClause
	 * @param array ...$arrReplacements
	 * @return \Crux\Fluent\Sql\Collection $this
	 * @uses \Crux\Provider\Sql\Engine::getConnection()
	 * @uses \Crux\Provider\Sql\Engine::queryf()
	 * @uses array_unshift()
	 * @uses call_user_func_array()
	 */
	public function groupBy(string $strClause, ...$arrReplacements) : Collection
	{
		// Add the statement to the beginning of the replacements
		array_unshift($arrReplacements, $strClause);
		// Process the query
		$this->mGroupBy = call_user_func_array([Sql\Engine::getConnection($this->mConnection), 'queryf'], $arrReplacements);
		// We're done, return the instance
		return $this;
	}

	/**
	 * This method adds a LIMIT clause to the instance
	 * @access public
	 * @name \Crux\Fluent\Sql\Collection::limit()
	 * @package \Crux\Fluent\Sql\Collection
	 * @param int $intCount
	 * @param int $intOffset [-1]
	 * @return \Crux\Fluent\Sql\Collection $this
	 * @uses sprintf()
	 */
	public function limit(int $intCount, int $intOffset = -1) : Collection
	{
		// Check for an offset
		if ($intOffset > -1) {
			// Set the limit clause
			$this->mLimit = sprintf('%d, %d', $intOffset, $intCount);
		} else {
			// Set the limit clause
			$this->mLimit = $intCount;
		}
	}

	/**
	 * This method adds an ORDER BY clause to the instance
	 * @access public
	 * @name \Crux\Fluent\Sql\Collection::orderBy()
	 * @package \Crux\Fluent\Sql\Collection
	 * @param string $strClause
	 * @param array ...$arrReplacements
	 * @return \Crux\Fluent\Sql\Collection $this
	 * @uses \Crux\Provider\Sql\Engine::getConnection()
	 * @uses \Crux\Provider\Sql\Engine::queryf()
	 * @uses array_unshift()
	 * @uses call_user_func_array()
	 */
	public function orderBy(string $strClause, ...$arrReplacements) : Collection
	{
		// Add the statement to the beginning of the replacements
		array_unshift($arrReplacements, $strClause);
		// Process the query
		$this->mOrderBy = call_user_func_array([Sql\Engine::getConnection($this->mConnection), 'queryf'], $arrReplacements);
		// We're done, return the instance
		return $this;
	}

	/**
	 * This method adds a custom WHERE clause to the instance
	 * @access public
	 * @name \Crux\Fluent\Sql\Collection::where()
	 * @package \Crux\Fluent\Sql\Collection
	 * @param string $strClause
	 * @param array<int, mixed> ...$arrReplacements
	 * @return \Crux\Fluent\Sql\Collection $this
	 * @uses \Crux\Provider\Sql\Engine::getConnection()
	 * @uses \Crux\Provider\Sql\Engine::queryf()
	 * @uses array_unshift()
	 * @uses call_user_func_array()
	 */
	public function where(string $strClause, ...$arrReplacements) : Collection
	{
		// Add the statement to the beginning of the replacements
		array_unshift($arrReplacements, $strClause);
		// Process the query
		$this->mWhere = call_user_func_array([Sql\Engine::getConnection($this->mConnection), 'queryf'], $arrReplacements);
		// We're done, return the instance
		return $this;
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Inline Methods ///////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method returns the schema name from the instance with the ability to reset it inline
	 * @access public
	 * @name \Crux\Fluent\Sql\Collection::schemaName()
	 * @package \Crux\Fluent\Sql\Collection
	 * @param string $strSchema [\Crux::NoValue]
	 * @return string
	 */
	public function schemaName(string $strSchema = \Crux::NoValue) : string
	{
		// Check for a provided schema name
		if ($strSchema !== \Crux::NoValue) {
			// Reset the schema name
			$this->mSchema = $strSchema;
		}
		// Return the schema name from the instance
		return $this->mSchema;
	}

	/**
	 * This method returns the table name from the instance with the ability to reset it inline
	 * @access public
	 * @name \Crux\Fluent\Sql\Collection::tableName()
	 * @package \Crux\Fluent\Sql\Collection
	 * @param string $strTable [\Crux::NoValue]
	 * @return string
	 */
	public function tableName(string $strTable = \Crux::NoValue) : string
	{
		// Check for a provided table name
		if ($strTable !== \Crux::NoValue) {
			// Reset the table name
			$this->mTable = $strTable;
		}
		// Return the table name from the instance
		return $this->mTable;
	}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
} /// End Crux\Fluent\Sql\Collection Class Definition ////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

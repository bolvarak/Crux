<?php

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Crux\Fluent\Sql Namespace ////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

namespace Crux\Fluent\Sql;

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Imports //////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

use Crux\Collection;
use Crux\Core;
use Crux\Grammar;
use Crux\Provider\Sql;
use Crux\Serialize;
use Crux\Type;

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Crux\Fluent\Sql\Record Class Definition //////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

abstract class Record implements Serialize\Able, \JsonSerializable
{
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Magic Methods ////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method provides dynamic method constructs for column setters and getters
	 * @access public
	 * @name \Crux\Fluent\Sql\Record::__call()
	 * @package \Crux\Fluent\Sql\Record
	 * @param string $strMethod
	 * @param array<int, mixed> $arrArguments
	 * @return \Crux\Type\Variant|\Crux\Fluent\Sql\Record|\Crux\Fluent\Sql\Collection
	 * @throws \Crux\Core\Exception\Fluent\Sql\Record
	 * @uses \Crux\Fluent\Sql\Record::__get()
	 * @uses \Crux\Fluent\Sql\Record::__set()
	 * @uses \Crux\Core\Exception\Fluent\Sql\Record::__construct()
	 * @uses substr()
	 * @uses strtolower()
	 * @uses substr_replace()
	 * @uses array_unshift()
	 * @uses call_user_func_array()
	 */
	public function __call(string $strMethod, array $arrArguments)
	{
		// Check the method name
		if (preg_match('/^getOne([a-zA-Z_]+)([0-9]+)$/i', $strMethod, $arrMatches)) {
			// Return the relationship
			return $this->oneOfMany($arrMatches[1], $arrMatches[2]);
		} elseif (preg_match('/^getOne([a-zA-Z_]+)$/i', $strMethod, $arrMatches)) {
			// Return the one-to-one relationship
			return $this->one($arrMatches[1]);
		} elseif (preg_match('/^getMany([a-zA-Z_]+)$/i', $strMethod, $arrMatches)) {
			// Return the one-to-many relationships
			return $this->many($arrMatches[1]);
		} elseif (strtolower(substr($strMethod, 0, 3)) === 'get') {
			// Prepend the property to the arguments
			array_unshift($arrArguments, substr_replace($strMethod, '', 0, 3));
			// Call the method
			return call_user_func_array([$this, '__get'], $arrArguments);
		} elseif (strtolower(substr($strMethod, 0, 3)) === 'set') {
			// Prepend the property to the arguments
			array_unshift($arrArguments, substr_replace($strMethod, '', 0, 3));
			// Call the method
			return call_user_func_array([$this, '__set'], $arrArguments);
		} else {
			// Throw the exception
			throw new Core\Exception\Fluent\Sql\Record(sprintf('Method [%s] could not be found in %s.', $strMethod, __CLASS__));
		}
	}

	/**
	 * This method returns a column from the instance
	 * @access public
	 * @name \Crux\Fluent\Sql\Record::__get()
	 * @package \Crux\Fluent\Sql\Record
	 * @param string $strProperty
	 * @return \Crux\Type\Variant
	 * @uses \Crux\Fluent\Sql\Record::column()
	 */
	public function __get(string $strProperty)
	{
		// Return the property from the instance
		return $this->column($strProperty);
	}

	/**
	 * This method sets a column into the instance
	 * @access public
	 * @name \Crux\Fluent\Sql\Record::__set()
	 * @package \Crux\Fluent\Sql\Record
	 * @param string $strProperty
	 * @param mixed $mixValue
	 * @return \Crux\Fluent\Sql\Record $this
	 * @uses \Crux\Fluent\Sql\Record::column()
	 */
	public function __set(string $strProperty, $mixValue) : Record
	{
		// Set the property into the instance
		$this->column($strProperty, $mixValue);
		// We're done, return the instance
		return $this;
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Protected Properties /////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This property contains the connection pool name that this model should use
	 * @access protected
	 * @name \Crux\Fluent\Sql\Record::$mConnection
	 * @package \Crux\Fluent\Sql\Record
	 * @var string
	 */
	protected $mConnection = Sql\Engine::DEFAULT_CONNECTION;

	/**
	 * This property contains the data from the table
	 * @access protected
	 * @name \Crux\Fluent\Sql\Record::$mContainer
	 * @package \Crux\Fluent\Sql\Record
	 * @var \Crux\Type\VariantMap
	 */
	protected $mContainer;

	/**
	 * This property contains a flag that tells the instance that the delete method has been called previously on this instance
	 * @access protected
	 * @name \Crux\Fluent\Sql\Record::$mDeleteCalled
	 * @package \Crux\Fluent\Sql\Record
	 * @var bool
	 */
	protected $mDeleteCalled = false;

	/**
	 * This property contains the table's description to which the model represents
	 * @access protected
	 * @name \Crux\Fluent\Sql\Record::$mDescription
	 * @package \Crux\Fluent\Sql\Record
	 * @var array<string, array<int, string>>
	 */
	protected $mDescription = [];

	/**
	 * This property contains the one-to-many (LEFT JOIN) relationships for the model
	 * @access protected
	 * @name \Crux\Fluent\Sql\Record::$mOneToMany
	 * @package \Crux\Fleunt\Sql\Record
	 * @var array<string, \Crux\Fluent\Sql\Collection>
	 */
	protected $mOneToMany = [];

	/**
	 * This property contains the one-to-one (INNER JOIN) relationships for the model
	 * @access protected
	 * @name \Crux\Fluent\Sql\Record::$mOneToOne
	 * @package \Crux\Fluent\Sql\Record
	 * @var array<string, \Crux\Fluent\Sql\Record>
	 */
	protected $mOneToOne = [];

	/**
	 * This property contains the ORDER BY clause for the select query
	 * @access protected
	 * @name \Crux\Fluent\Sql\Record::$mOrderBy
	 * @package \Crux\Fluent\Sql\Record
	 * @var string
	 */
	protected $mOrderBy = '';

	/**
	 * This property contains the primary key column name
	 * @access protected
	 * @name \Crux\Fluent\Sql\Record::$mPrimaryKey
	 * @package \Crux\Fluent\Sql\Record
	 * @var string
	 */
	protected $mPrimaryKey = '';

	/**
	 * This property contains the name of the schema to which the model represents (SQLite and PostgresSQL)
	 * @access protected
	 * @name \Crux\Fluent\Sql\Record::$mSchema
	 * @package \Crux\Fluent\Sql\Record
	 * @var string
	 */
	protected $mSchema = '';

	/**
	 * This property contains the name of the table to which the model represents
	 * @access protected
	 * @name \Crux\Fluent\Sql\Record::$mTable
	 * @package \Crux\Fluent\Sql\Record
	 * @var string
	 */
	protected $mTable = '';

	/**
	 * This property contains a custom WHERE clause for loading the model
	 * @access protected
	 * @name \Crux\Fluent\Sql\Record::$mWhere
	 * @pqckage \Crux\Fluent\Sql\Record
	 * @var string
	 */
	protected $mWhere = '';

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Constructor //////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method instantiates a new SQL Model object
	 * @access public
	 * @name \Crux\Fluent\Sql\Record::__construct()
	 * @package \Crux\Fluent\Sql\Record
	 * @param string $strConnection
	 * @param string $strTable
	 * @param string $strSchema ['']
	 * @param string $strPrimaryKey ['']
	 * @param string $strWhere ['']
	 * @uses \Crux\Type\VariantMap::__construct()
	 */
	public function __construct(string $strConnection, string $strTable, string $strSchema = '', string $strPrimaryKey = '', string $strWhere = '')
	{
		// Initialize the container
		$this->mContainer = new Type\VariantMap();
		// Set the connection name into the instance
		$this->mConnection = $strConnection;
		// Set the schema into the instance
		$this->mSchema = $strSchema;
		// Set the table into the instance
		$this->mTable = $strTable;
		// Set the primary key into the instance
		$this->mPrimaryKey = $strPrimaryKey;
		// Set the custom WHERE clause into the instance
		$this->mWhere = $strWhere;
		// Parse the table description
		$this->parseTableDescription();
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Protected Methods ////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method provides a delete statement specific to the database engine
	 * @access protected
	 * @name \Crux\Fluent\Sql\Record::deleteStatement()
	 * @package \Crux\Fluent\Sql\Record
	 * @param mixed $mixId
	 * @param string $strColumn
	 * @return string
	 * @uses \Crux\Core\Is::empty()
	 * @uses \Crux\Provider\Sql\Engine::getConnection()
	 * @uses \Crux\Provider\Sql\Engine::queryf()
	 */
	protected function deleteStatement($mixId, string $strColumn = '') : string
	{
		// Check for a schema
		if (Core\Is::empty($this->mSchema)) {
			// Return the query
			return Sql\Engine::getConnection($this->mConnection)->queryf('DELETE FROM %T WHERE %T.%C = %s', $this->mTable, $this->mTable, $strColumn, $mixId);
		} else {
			// Return the query
			return Sql\Engine::getConnection($this->mConnection)->queryf('DELETE FROM %T.%T WHERE %T.%T.%C = %s', $this->mSchema, $this->mTable, $this->mSchema, $this->mTable, $strColumn, $mixId);
		}
	}

	/**
	 * This method provides an insert statement specific to the database engine
	 * @access protected
	 * @name \Crux\Fluent\Sql\Record::insertStatement()
	 * @package \Crux\Fluent\Sql\Record
	 * @param bool $blnForceInsert [false]
	 * @return string
	 * @uses \Crux\Core\Is::empty()
	 * @uses \Crux\Type\VariantMap::toKeysArray()
	 * @uses \Crux\Type\VariantMap::get()
	 * @uses \Crux\Type\Variant::getData()
	 * @uses \Crux\Provider\Sql\Engine::getConnection()
	 * @uses \Crux\Provider\Sql\Engine::queryf()
	 * @uses array_push()
	 * @uses array_unshift()
	 * @uses call_user_func_array()
	 * @uses implode()
	 * @uses sprintf()
	 */
	protected function insertStatement(bool $blnForceInsert = false) : string
	{
		// Create the replacements container
		$arrReplacements = [];
		// Create our statement containers
		$arrColumns = [];
		$arrValues = [];
		// Check for a schema
		if (Core\Is::empty($this->mSchema)) {
			// Start the statement
			$strStatement = 'INSERT INTO %T';
			// Add the table to the replacements container
			array_push($arrReplacements, $this->mTable);
		} else {
			// Start the statement
			$strStatement = 'INSERT INTO %T.%T';
			// Add the schema to the replacements container
			array_push($arrReplacements, $this->mSchema);
			// Add the schema to the replacements container
			array_push($arrReplacements, $this->mTable);
		}
		// Iterate over the columns
		foreach ($this->mContainer->toKeysArray() as $strColumn) {
			// Check the column for a primary key
			if ((strtolower($strColumn) === strtolower($this->mPrimaryKey)) && !$blnForceInsert) {
				// Next iteration please
				continue;
			}
			// Add the column placeholder to the columns
			array_push($arrColumns, '%T');
			// Add the column to the replacements container
			array_push($arrReplacements, $strColumn);
		}
		// Run a second iteration over the columns
		foreach ($this->mContainer->toKeysArray() as $strColumn) {
			// Check the column for a primary key
			if ((strtolower($strColumn) === strtolower($this->mPrimaryKey)) && !$blnForceInsert) {
				// Next iteration please
				continue;
			}
			// Add the value placeholder to the container
			array_push($arrValues, '%m');
			// Add the value to the replacements container
			array_push($arrReplacements, $this->mContainer->get($strColumn)->getData());
		}
		// Finalize the statement
		$strStatement = sprintf('%s (%s) VALUES (%s)',$strStatement, implode(', ', $arrColumns), implode(', ', $arrValues));
		// Check for PostgreSQL
		if ($this->engine()->getDriver() === Sql\Engine::DRIVER_PGSQL) {
			// Add the returning clause
			$strStatement .= ' RETURNING %C';
			// Add the primary key to the replacements
			array_push($arrReplacements, $this->primaryKeyName());
		}
		// Add the statement to the beginning of the replacements container
		array_unshift($arrReplacements, $strStatement);
		// Return the statement
		return call_user_func_array([Sql\Engine::getConnection($this->mConnection), 'queryf'], $arrReplacements);
	}

	/**
	 * This method parses the table's description into the instance
	 * @access protected
	 * @name \Crux\Fluent\Sql\Record::parseTableDescription()
	 * @package \Crux\Fluent\Sql\Record
	 * @param bool $blnForce [false]
	 * @return \Crux\Fluent\Sql\Record $this
	 * @uses \Crux::environment()
	 * @uses \Crux\Fluent\Sql\Record::connectionName()
	 * @uses \Crux\Fluent\Sql\Record::schemaName()
	 * @uses \Crux\Fluent\Sql\Record::tableName()
	 * @uses \Crux\Fluent\Sql\Record::engine()
	 * @uses \Crux\Provider\Sql\Engine::describe()
	 * @uses \Crux\Provider\Sql\Engine::timeStamp()
	 * @uses \Crux\Provider\Sql\Engine::date()
	 * @uses \Crux\Core\Is::empty()
	 * @uses \Crux\Core\Config::get()
	 * @uses \Crux\Core\Cache::hasExpired()
	 * @uses \Crux\Core\Cache::get()
	 * @uses \Crux\Core\Cache::set()
	 * @uses \Crux\Collection\Map::toArray()
	 * @uses \Crux\Type\VariantMap::set()
	 * @uses sprintf()
	 * @uses preg_match()
	 */
	protected function parseTableDescription(bool $blnForce = false) : Record
	{
		// Define the cache name
		$strCacheName = sprintf('db.%s.%s.%s.%s', \Crux::environment(), $this->connectionName(), (Core\Is::empty($this->schemaName()) ? 'none' : $this->schemaName()), $this->tableName());
		// Check for cache
		if (Core\Config::get('db.cacheMeta') && !Core\Cache::hasExpired($strCacheName)) {
			// Set the table description from cache
			$this->mDescription = Core\Cache::get($strCacheName)->toArray();
		} elseif (Core\Config::get('db.cacheMeta') && Core\Cache::hasExpired($strCacheName)) {
			// Load the table description
			$this->mDescription = $this->engine()->describe($this->tableName(), $this->schemaName());
			// Save the description to cache
			Core\Cache::set($strCacheName, $this->mDescription);
		} else {
			// Load the table description
			$this->mDescription = $this->engine()->describe($this->tableName(), $this->schemaName());
		}
		// Check for data in the container
		if (!$this->mContainer->isEmpty()) {
			// We're done, return the instance
			return $this;
		}
		// Iterate over the table description
		foreach ($this->mDescription as $strColumn => $arrMetaData) {
			// Set the primary key identifier
			$strPrimaryKeyIdentifier = ($arrMetaData[2] ?? null);
			// Check the type
			if (preg_match('/(timestamp|datetime)/i', $arrMetaData[0])) {
				// Set the property into the container
				$this->mContainer->set($strColumn, $this->engine()->timeStamp());
			} elseif (preg_match('/^date$/i', $arrMetaData[0])) {
				// Set the property into the container
				$this->mContainer->set($strColumn, $this->engine()->date());
			} else {
				// Set the property into the container
				$this->mContainer->set($strColumn, null);
			}
			// Check the primary key identifier
			if ($strPrimaryKeyIdentifier === Sql\Engine::PRIMARY_KEY_IDENTIFIER) {
				// Set the primary key into the instance
				$this->mPrimaryKey = $strColumn;
			}
		}
		// We're done, return the instance
		return $this;
	}

	/**
	 * This method checks to see whether or not the primary key has been defined and populated
	 * @access protected
	 * @name \Crux\Fluent\Sql\Record::primaryKeyIsEmpty()
	 * @package \Crux\Fluent\Sql\Record
	 * @return bool
	 * @uses \Crux\Core\Is::empty()
	 * @uses \Crux\Type\VariantMap::get()
	 * @uses \Crux\Type\Variant::isEmpty()
	 */
	protected function primaryKeyIsEmpty() : bool
	{
		// Return the empty state of the primary key
		return (Core\Is::empty($this->mPrimaryKey) || $this->mContainer->get($this->mPrimaryKey)->isEmpty());
	}

	/**
	 * This method provides a select statement specific to the database engine
	 * @access protected
	 * @name \Crux\Fluent\Sql\Record::selectStatement()
	 * @package \Crux\Fluent\Sql\Record
	 * @param mixed $mixId
	 * @param string $strSourceColumn
	 * @return string
	 * @uses \Crux\Core\Is::empty()
	 * @uses \Crux\Type\VariantMap::toKeysArray()
	 * @uses \Crux\Provider\Sql\Engine::getConnection()
	 * @uses \Crux\Provider\Sql\Engine::queryf()
	 * @uses array_push()
	 * @uses array_unshift()
	 * @uses call_user_func_array()
	 * @uses implode()
	 * @uses sprintf()
	 */
	protected function selectStatement($mixId, string $strSourceColumn) : string
	{
		// Define our replacements container
		$arrReplacements = [];
		// Define our columns container
		$arrColumns = [];
		// Iterate over the columns
		foreach ($this->mContainer->toKeysArray() as $strColumn) {
			// Check for a schema
			if (Core\Is::empty($this->mSchema)) {
				// Add the column placeholder to the columns ontainer
				array_push($arrColumns, '%T.%C');
			} else {
				// Add the column placeholder to the columns container
				array_push($arrColumns, '%T.%T.%C');
				// Add the schema to the replacements
				array_push($arrReplacements, $this->mSchema);
			}
			// Add the table to the replacements container
			array_push($arrReplacements, $this->mTable);
			// Add the column to the replacements container
			array_push($arrReplacements, $strColumn);
		}
		// Check for a schema
		if (Core\Is::empty($this->mSchema)) {
			// Set the FROM placeholder
			$strFrom = '%T';
		} else {
			// Set the FROM placeholder
			$strFrom = '%T.%T';
			// Add the schema to the replacements container
			array_push($arrReplacements, $this->mSchema);
		}
		// Add the table to the replacements container
		array_push($arrReplacements, $this->mTable);
		// Check for a WHERE clause
		if (Core\Is::empty($this->mWhere)) {
			// Check for a schema
			if (Core\Is::empty($this->mSchema)) {
				// Set the WHERE clause
				$strWhere = '%T.%C = %m';
			} else {
				// Set the WHERE clause
				$strWhere = '%T.%T.%C = %m';
				// Add the schema to the replacements container
				array_push($arrReplacements, $this->mSchema);
			}
			// Add the table to the replacements container
			array_push($arrReplacements, $this->mTable);
			// Add the primary key to the the replacements container
			array_push($arrReplacements, $strSourceColumn);
			// Add the identifier value to the replacements container
			array_push($arrReplacements, $mixId);
		} else {
			// Set the WHERE clause
			$strWhere = $this->mWhere;
		}
		// Check for an ORDER BY clause
		if (Core\Is::empty($this->mOrderBy)) {
			// Finalize the statement
			$strStatement = sprintf('SELECT %s FROM %s WHERE %s LIMIT 1', implode(', ', $arrColumns), $strFrom, $strWhere);
		} else {
			// Finalize the statement
			$strStatement = sprintf('SELECT %s FROM %s WHERE %s ORDER BY %s LIMIT 1', implode(', ', $arrColumns), $strFrom, $strWhere, $this->mOrderBy);
		}
		// Prepend the statement to the replacements container
		array_unshift($arrReplacements, $strStatement);
		// We're done, return the statement
		return call_user_func_array([$this->engine(), 'queryf'], $arrReplacements);
	}

	/**
	 * This method provides an update statement specific to the database engine
	 * @access protected
	 * @name \Crux\Fluent\Sql\Record::updateStatement()
	 * @package \Crux\Fluent\Sql\Record
	 * @return string
	 * @uses \Crux\Core\Is::empty()
	 * @uses \Crux\Type\VariantMap::toKeysArray()
	 * @uses \Crux\Type\VariantMap::get()
	 * @uses \Crux\Type\Variant::getData()
	 * @uses \Crux\Provider\Sql\Engine::getConnection()
	 * @uses \Crux\Provider\Sql\Engine::queryf()
	 * @uses array_push()
	 * @uses array_unshift()
	 * @uses call_user_func_array()
	 * @uses implode()
	 * @uses sprintf()
	 */
	protected function updateStatement() : string
	{
		// Define our replacements container
		$arrReplacements = [];
		// Define our pair containers
		$arrPairs = [];
		// Check for a schema
		if (Core\Is::empty($this->mSchema)) {
			// Start the statement
			$strStatement = 'UPDATE %T';
			// Add the table to the replacements container
			array_push($arrReplacements, $this->mTable);
		} else {
			// Start the statement
			$strStatement = 'UPDATE %T.%T';
			// Add the schema to the replacements container
			array_push($arrReplacements, $this->mSchema);
			// Add the table to the replacements container
			array_push($arrReplacements, $this->mTable);
		}
		// Iterate over the columns
		foreach ($this->mContainer->toKeysArray() as $strColumn) {
			// Check for a primary key
			if (strtolower($strColumn) === strtolower($this->mPrimaryKey)) {
				// Next iteration please
				continue;
			}
			// Add the pair
			array_push($arrPairs, '%C = %m');
			// Add the column to the replacements container
			array_push($arrReplacements, $strColumn);
			// Add the value to the replacements container
			array_push($arrReplacements, $this->mContainer->get($strColumn)->getData());
		}
		// Set the WHERE clause
		$strWhere = '%C = %m';
		// Add the primary key to the replacements container
		array_push($arrReplacements, $this->mPrimaryKey);
		// Add the primary key value to the replacements container
		array_push($arrReplacements, $this->mContainer->get($this->mPrimaryKey)->getData());
		// Finalize the statement
		$strStatement = sprintf('%s SET %s WHERE %s', $strStatement, implode(', ', $arrPairs), $strWhere);
		// Prepend the statement to the replacements
		array_unshift($arrReplacements, $strStatement);
		// We're done, return the statement
		return call_user_func_array([Sql\Engine::getConnection($this->mConnection), 'queryf'], $arrReplacements);
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Public Methods ///////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method returns the connection name from the instance with the ability to reset it inline
	 * @access public
	 * @name \Crux\Fluent\Sql\Record::connectionName
	 * @package \Crux\Fluent\Sql\Record
	 * @param string $strConnection ['']
	 * @return string
	 * @uses \Crux\Core\Is::empty()
	 */
	public function connectionName(string $strConnection = '') : string
	{
		// Check for a provided connection
		if (!Core\Is::empty($strConnection)) {
			// Reset the connection name into the instance
			$this->mConnection = $strConnection;
		}
		// Return the connection name from the instance
		return $this->mConnection;
	}

	/**
	 * This method returns a column from the record with the ability to reset it inline
	 * @access public
	 * @name \Crux\Fluent\Sql\Record::column()
	 * @package
	 * @param string $strColumn
	 * @param mixed $mixValue [\Crux::NoValue]
	 * @return \Crux\Type\Variant
	 * @throws \Crux\Core\Exception\Fluent\Sql\Record
	 * @uses \Crux\Type\VariantMap::containsKey()
	 * @uses \Crux\Type\VariantMap::get()
	 * @uses \Crux\Type\VariantMap::set()
	 * @uses \Crux\Core\Exception\Fluent\Sql\Record::__construct()
	 * @uses sprintf()
	 */
	public function column(string $strColumn, $mixValue = \Crux::NoValue) : Type\Variant
	{
		// Make sure the column exists
		if (($strRealColumn = $this->mContainer->key($strColumn)) === null) {
			// We're done, throw an exception
			throw new Core\Exception\Fluent\Sql\Record(sprintf('Column [%s] could not be found in %s.', $strColumn, __CLASS__));
		}
		// Check for a value
		if ($mixValue !== \Crux::NoValue) {
			// Reset the column value
			$this->mContainer->set($strRealColumn, $mixValue);
		}
		// We're done, return the column
		return $this->mContainer->get($strRealColumn);
	}

	/**
	 * This method deletes a record from the database
	 * @access public
	 * @name \Crux\Fluent\Sql\Record::delete()
	 * @package \Crux\Fluent\Sql\Record
	 * @param mixed $mixId [null]
	 * @return \Crux\Fluent\Sql\Record $this
	 * @throws \Crux\Core\Exception\Fluent\Sql\Record
	 * @uses \Crux\Fluent\Sql\Record::primaryKeyIsEmpty()
	 * @uses \Crux\Fluent\Sql\Record::isEmpty()
	 * @uses \Crux\Core\Is::null()
	 * @uses \Crux\Provider\Sql\Engine::getConnection()
	 * @uses \Crux\Provider\Sql\Engine::executeQuery()
	 * @uses \Crux\Fluent\Sql\Record::deleteStatement()
	 * @uses \Crux\Core\Exception\Fluent\Sql\Record::__construct()
	 * @uses \PDOException::getMessage()
	 * @uses \PDOException::getCode()
	 */
	public function delete($mixId = null) : Record
	{
		// Make sure the model is populated
		if ($this->primaryKeyIsEmpty() || $this->isEmpty()) {
			// Throw the exception
			throw new Core\Exception\Fluent\Sql\Record(sprintf('%s must be populated before delete() can be executed', __CLASS__));
		}
		// Check for an ID
		if (Core\Is::null($mixId)) {
			// Reset the ID
			$mixId = $this->mContainer->get($this->mPrimaryKey)->getData();
		}
		// Try to execute the query
		try {
			// Execute the query
			Sql\Engine::getConnection($this->mConnection)->executeQuery($this->deleteStatement($mixId, $this->mPrimaryKey));
			// We're done, return the instance
			return $this;
		} catch (\PDOException $pdoException) {
			// Throw the new exception
			throw new Core\Exception\Fluent\Sql\Record($pdoException->getMessage(), $pdoException->getCode(), $pdoException);
		}
	}

	/**
	 * This method empties out the data in the model instance
	 * @access public
	 * @name \Crux\Fluent\Sql\Record::empty()
	 * @package \Crux\Fluent\Sql\Record
	 * @return \Crux\Fluent\Sql\Record $this
	 * @uses \Crux\Type\VariantMap::toKeysArray()
	 * @uses \Crux\Type\VariantMap::set()
	 */
	public function empty() : Record
	{
		// Iterate over the keys in the container
		foreach ($this->mContainer->toKeysArray() as $strColumn) {
			// Empty the column
			$this->mContainer->set($strColumn, null);
		}
		// We're done, return the instance
		return $this;
	}

	/**
	 * This method returns the engine associated with the record
	 * @access public
	 * @name \Crux\Fluent\Sql\Record::engine()
	 * @package \Crux\Fluent\Sql\Record
	 * @return \Crux\Provider\Sql\Engine
	 * @uses \Crux\Provider\Sql\Engine::getConnection()
	 */
	public function engine() : Sql\Engine
	{
		// Return the engine associated with this record
		return Sql\Engine::getConnection($this->mConnection);
	}

	/**
	 * This method attaches a collection of one-to-may relationship models to the instance
	 * @access public
	 * @name \Crux\Fluent\Sql\Record::hasMany()
	 * @package \Crux\Fluent\Sql\Record
	 * @param \Crux\Fluent\Sql\Record $modContainer
	 * @param string $strTargetColumn ['']
	 * @param string $strSourceColumn ['']
	 * @return \Crux\Fluent\Sql\Record $this
	 * @throws \Crux\Core\Exception\Fluent\Sql\Record
	 * @uses \Crux\Core\Is::empty()
	 * @uses \Crux\Fluent\Sql\Record::primaryKeyIsEmpty()
	 * @uses \Crux\Fluent\Sql\Record::isEmpty()
	 * @uses \Crux\Core\Exception\Fluent\Sql\Record::__construct()
	 * @uses \Crux\Fluent\Sql\Collection::__construct()
	 * @uses \Crux\Fluent\Sql\Collection::where()
	 * @uses \Crux\Fluent\Sql\Collection::fetchIntoModel()
	 * @uses \Crux\Fluent\Sql\Record::schemaName()
	 * @uses \Crux\Fluent\Sql\Record::tableName()
	 * @uses \Crux\Type\VariantMap::get()
	 * @uses \Crux\Type\Variant::getData()
	 */
	public function hasMany(Record $modContainer, string $strTargetColumn = '', string $strSourceColumn = '') : Record
	{
		// Check for a primary key
		if ($this->primaryKeyIsEmpty() || $this->isEmpty()) {
			// Throw the exception
			throw new Core\Exception\Fluent\Sql\Record(sprintf('%s must be loaded before adding relationships.', __CLASS__));
		}
		// Check for a target column
		if (Core\Is::empty($strTargetColumn)) {
			// Reset the target column to the primary key
			$strTargetColumn = $this->mPrimaryKey;
		}
		// Check for a source column
		if (Core\Is::empty($strSourceColumn)) {
			// Reset the source column to the primary key
			$strSourceColumn = $this->mPrimaryKey;
		}
		// Instantiate the collection
		$colMany = new \Crux\Fluent\Sql\Collection($modContainer);
		// Check for a schema in the child model
		if (Core\Is::empty($modContainer->schemaName())) {
			// Set the WHERE clause into the collection
			$colMany->where('%T.%C = %m', $modContainer->tableName(), $strTargetColumn, $this->mContainer->get($strSourceColumn)->getData());
		} else {
			// Set the WHERE clause into the collection
			$colMany->where('%T.%T.%C = %m', $modContainer->schemaName(), $modContainer->tableName(), $strTargetColumn, $this->mContainer->get($strSourceColumn)->getData());
		}
		// Set the collection into the instance
		$this->mOneToMany[Grammar\Inflection::pluralize($modContainer->tableName())] = $colMany->populate();
		// We're done, return the instance
		return $this;
	}

	/**
	 * This method attaches a one-to-one relationship model to the instance
	 * @access public
	 * @name \Crux\Fluent\Sql\Record::hasOne()
	 * @package \Crux\Fluent\Sql\Record
	 * @param \Crux\Fluent\Sql\Record $modContainer
	 * @param string $strTargetColumn ['']
	 * @param string $strSourceColumn ['']
	 * @return \Crux\Fluent\Sql\Record $this
	 * @throws \Crux\Core\Exception\Fluent\Sql\Record
	 * @uses \Crux\Fluent\Sql\Record::primaryKeyIsEmpty()
	 * @uses \Crux\Fluent\Sql\Record::isEmpty()
	 * @uses \Crux\Core\Exception\Fluent\Sql\Record::__construct()
	 * @uses \Crux\Core\Is::empty()
	 * @uses \Crux\Fluent\Sql\Record::tableName()
	 * @uses \Crux\Fluent\Sql\Record::load()
	 * @uses \Crux\Type\VariantMap::get()
	 * @uses \Crux\Type\Variant::getData()
	 */
	public function hasOne(Record $modContainer, string $strTargetColumn = '', string $strSourceColumn = '') : Record
	{
		// Check for a primary key
		if ($this->primaryKeyIsEmpty() || $this->isEmpty()) {
			// Throw the exception
			throw new Core\Exception\Fluent\Sql\Record(sprintf('%s must be loaded before adding relationships.', __CLASS__));
		}
		// Check the target column
		if (Core\Is::empty($strTargetColumn)) {
			// Reset the target column to the primary key
			$strTargetColumn = $this->mPrimaryKey;
		}
		// Check the source column
		if (Core\Is::empty($strSourceColumn)) {
			// Reset the source column to the primary key
			$strSourceColumn = $this->mPrimaryKey;
		}
		// Load the relationship and store it into the instance
		$this->mOneToOne[Grammar\Inflection::singularize($modContainer->tableName())] = $modContainer->load($this->mContainer->get($strSourceColumn)->getData(), $strTargetColumn);
		// We're done, return the instance
		return $this;
	}

	/**
	 * This method returns the empty state of the model
	 * @access public
	 * @name \Crux\Fluent\Sql\Record::isEmpty()
	 * @package \Crux\Fluent\Sql\Record
	 * @return bool
	 * @uses \Crux\Type\VariantMap::isEmpty()
	 * @uses \Crux\Fluent\Sql\Record::primaryKeyIsEmpty()
	 */
	public function isEmpty() : bool
	{
		// Return the empty state of the model
		return ($this->mContainer->isEmpty() || $this->primaryKeyIsEmpty());
	}

	/**
	 * This method populates the model from the database
	 * @access public
	 * @name \Crux\Fluent\Sql\Record::load()
	 * @package \Crux\Fluent\Sql\Record
	 * @param mixed $mixId [null]
	 * @param string $strColumn ['']
	 * @return \Crux\Fluent\Sql\Record $this
	 * @throws \Crux\Core\Exception\Fluent\Sql\Record
	 * @uses \Crux\Core\Is::empty()
	 * @uses \Crux\Fluent\Sql\Record::primaryKeyIsEmpty()
	 * @uses \Crux\Fluent\Sql\Record::parseTableDescription()
	 * @uses \Crux\Fluent\Sql\Record::selectStatement()
	 * @uses \Crux\Provider\Sql\Engine::getConnection()
	 * @uses \Crux\Provider\Sql\Engine::executeQuery()
	 * @uses \Crux\Provider\Sql\Statement::rowCount()
	 * @uses \Crux\Provider\Sql\Statement::toArray()
	 * @uses \Crux\Type\VariantMap::set()
	 * @uses \Crux\Core\Exception\Fluent\Sql\Record::__construct()
	 * @uses \PDOException::getMessage()
	 * @uses \PDOException::getCode()
	 */
	public function load($mixId = null, string $strColumn = '') : Record
	{
		// Parse the table description
		$this->parseTableDescription();
		// Check for a user-defined column
		if (Core\Is::empty($strColumn)) {
			// Reset the column to the primary key
			$strColumn = $this->mPrimaryKey;
		}
		// Try to load the model
		try {
			// Load the model
			$pdoStatement = Sql\Engine::getConnection($this->mConnection)->executeQuery($this->selectStatement($mixId, $strColumn));
			// Make sure we have results
			if ($pdoStatement->rowCount() >= 1) {
				// Fetch the records into an array
				$arrColumns = $pdoStatement->toArray();
				// Iterate over the array
				foreach ($arrColumns[0] as $strFoundColumn => $mixValue) {
					// Check the value
					if (Core\Is::encodedHtml($mixValue)) {
						// Convert the string back to HTML
						$this->mContainer->set($strFoundColumn, htmlspecialchars_decode($mixValue, ENT_QUOTES));
					} else {
						// Set the value into the container
						$this->mContainer->set($strFoundColumn, $mixValue);
					}
				}
			}
			// We're done, return the instance
			return $this;
		} catch (\PDOException $pdoException) {
			// Throw the PHireworks exception
			throw new Core\Exception\Fluent\Sql\Record($pdoException->getMessage(), $pdoException->getCode(), $pdoException);
		}
	}

	/**
	 * This method returns a table collection of one-to-many relationships from the instance
	 * @access public
	 * @name \Crux\Fluent\Sql\Record::many()
	 * @package \Crux\Fluent\Sql\Record
	 * @param string $strTable
	 * @return array<int, \Crux\Fluent\Sql\Collection>
	 * @throws \Crux\Core\Exception\Fluent\Sql\Record
	 * @uses \Crux\Core\Exception\Fluent\Sql\Record::__construct()
	 * @uses array_keys()
	 * @uses preg_replace()
	 * @uses strtolower()
	 */
	public function many(string $strTable) : array
	{
		// Iterate over the keys in the relationships
		foreach (array_keys($this->mOneToMany) as $strCollectionTable) {
			// Check the table
			if (strtolower(preg_replace('/[^a-zA-Z0-9]/i', '', $strTable)) === strtolower(preg_replace('/[^a-zA-Z0-9]/i', '', $strCollectionTable))) {
				// Return the collection
				return $this->mOneToMany[$strCollectionTable];
			}
		}
		// Throw the exception
		throw new Core\Exception\Fluent\Sql\Record(sprintf('%s collection not found in %s', $strTable, __CLASS__));
	}

	/**
	 * This method returns a one-to-one relation model from the instance
	 * @access public
	 * @name \Crux\Fluent\Sql\Record::one()
	 * @package \Crux\Fluent\Sql\Record
	 * @param string $strTable
	 * @return \Crux\Fluent\Sql\Record
	 * @throws \Crux\Core\Exception\Fluent\Sql\Record
	 * @uses \Crux\Grammar\Inflection::singularize()
	 * @uses \Crux\Core\Exception\Fluent\Sql\Record::__construct()
	 * @uses array_keys()
	 * @uses preg_replace()
	 * @uses strtolower()
	 */
	public function one(string $strTable) : Record
	{
		// Iterate over the keys in the relationships
		foreach (array_keys($this->mOneToOne) as $strChildTable) {
			// Check the table
			if (strtolower(preg_replace('/[^a-zA-Z0-9]/i', '', Grammar\Inflection::singularize($strTable))) === strtolower(preg_replace('/[^a-zA-Z0-9]/i', '', $strChildTable))) {
				// Return the collection
				return $this->mOneToOne[$strChildTable];
			}
		}
		// Throw the exception
		throw new Core\Exception\Fluent\Sql\Record(sprintf('%s relation not found in %s', $strTable, __CLASS__));
	}

	/**
	 * This method returns a single record from a one-to-many relationship collection in the instance
	 * @access public
	 * @name \Crux\Fluent\Sql\Record::oneOfMany()
	 * @package \Crux\Fluent\Sql\Record
	 * @param string $strTable
	 * @param int $intRow [0]
	 * @return \Crux\Fluent\Sql\Record
	 * @throws \Crux\Core\Exception\Fluent\Sql\Record
	 * @uses \Crux\Fluent\Sql\Record::many()
	 * @uses \Crux\Fluent\Sql\Collection::size()
	 * @uses \Crux\Core\Exception\Fluent\Sql\Record::__construct()
	 * @uses \Crux\Fluent\Sql\Collection::record()
	 */
	public function oneOfMany(string $strTable, int $intRow = 0) : Record
	{
		// Load the collection
		$colRelation = $this->many($strTable);
		// Check the size
		if ($colRelation->size() < $intRow) {
			// Throw the exception
			throw new Core\Exception\Fluent\Sql\Record(sprintf('%d relationship in %s collection not found in %s', $intRow, $strTable, __CLASS__));
		}
		// Return the relationship
		return $colRelation->record($intRow);
	}

	/**
	 * This method adds an ORDER BY clause to the select statement
	 * @access public
	 * @name \Crux\Fluent\Sql\Record::orderBy()
	 * @package \Crux\Fluent\Sql\Record
	 * @param string $strClause
	 * @param array ...$arrReplacements
	 * @return \Crux\Fluent\Sql\Record $this
	 * @uses \Crux\Provider\Sql\Engine::queryf()
	 * @uses call_user_func_array()
	 */
	public function orderBy(string $strClause, ...$arrReplacements) : Record
	{
		// Add the statement to the beginning of the replacements
		array_unshift($arrReplacements, $strClause);
		// Process the query
		$this->mOrderBy = call_user_func_array([$this->engine(), 'queryf'], $arrReplacements);
		// We're done, return the instance
		return $this;
	}

	/**
	 * This method returns the primary key column name from the instance with the ability to reset it inline
	 * @access public
	 * @name \Crux\Fluent\Sql\Record::primaryKeyName()
	 * @package \Crux\Fluent\Sql\Record
	 * @param string $strColumn ['']
	 * @return string
	 * @uses \Crux\Core\Is::empty()
	 */
	public function primaryKeyName(string $strColumn = '') : string
	{
		// Check for a provided primary key column
		if (!Core\Is::empty($strColumn)) {
			// Reset the primary key column into the instance
			$this->mPrimaryKey = $strColumn;
		}
		// Return the primary key column from the instance
		return $this->mPrimaryKey;
	}

	/**
	 * This method sets an existing record into the instance
	 * @access public
	 * @name \Crux\Fluent\Sql\Record::relationship()
	 * @package \Crux\Fluent\Sql\Record
	 * @param \Crux\Fluent\Sql\Record $modRecord
	 * @param string $strName ['']
	 * @return \Crux\Fluent\Sql\Record $this
	 * @uses \Crux\Core\Is::empty()
	 * @uses \Crux\Fluent\Sql\Record::tableName()
	 * @uses \Crux\Grammar\Inflection::singularize()
	 */
	public function relationship(Record $modRecord, string $strName = '') : Record
	{
		// Check for a name
		if (Core\Is::empty($strName)) {
			// Set the name to the table name
			$strName = Grammar\Inflection::singularize($modRecord->tableName());
		} else {
			// Singularize the name
			$strName = Grammar\Inflection::singularize($strName);
		}
		// Set the relationship into the instance
		$this->mOneToOne[$strName] = $modRecord;
		// We're done, return the instance
		return $this;
	}

	/**
	 * This method sets an existing collection into the instance
	 * @access public
	 * @name \Crux\Fluent\Sql\Record::relationships()
	 * @package \Crux\Fluent\Sql\Record
	 * @param \Crux\Fluent\Sql\Collection $colRecords
	 * @param string $strName ['']
	 * @return \Crux\Fluent\Sql\Record $this
	 * @uses \Crux\Core\Is::empty()
	 * @uses \Crux\Fluent\Sql\Collection::tableName()
	 * @uses \Crux\Fluent\Sql\Collection::populate()
	 * @uses \Crux\Grammar\Inflection::pluralize()
	 */
	public function relationships(\Crux\Fluent\Sql\Collection $colRecords, string $strName = '') : Record
	{
		// Check for a name
		if (Core\Is::empty($strName)) {
			// Set the name to the table name
			$strName = Grammar\Inflection::pluralize($colRecords->tableName());
		} else {
			// Pluralize the name
			$strName = Grammar\Inflection::pluralize($strName);
		}
		// Set the relationships into the instance
		$this->mOneToMany[$strName] = $colRecords->populate();
		// We're done, return the instance
		return $this;
	}

	/**
	 * This method saves the model instance to the database
	 * @access public
	 * @name \Crux\Fluent\Sql\Record::save()
	 * @package \Crux\Fluent\Sql\Record
	 * @param bool $blnForceInsert [false]
	 * @return \Crux\Fluent\Sql\Record $this
	 * @throws \Crux\Core\Exception\Fluent\Sql\Record
	 * @uses \Crux\Fluent\Sql\Record::parseTableDescription()
	 * @uses \Crux\Fluent\Sql\Record::primaryKeyIsEmpty()
	 * @uses \Crux\Fluent\Sql\Record::insertStatement()
	 * @uses \Crux\Fluent\Sql\Record::updateStatement()
	 * @uses \Crux\Provider\Sql\Engine::getConnection()
	 * @uses \Crux\Provider\Sql\Engine::executeQuery()
	 * @uses \Crux\Provider\Sql\Engine::lastId()
	 * @uses \Crux\Type\VariantMap::set()
	 * @uses \Crux\Core\Exception\Fluent\Sql\Record::__construct()
	 * @uses \PDOException::getMessage()
	 * @uses \PDOException::getCode()
	 */
	public function save(bool $blnForceInsert = false) : Record
	{
		// Parse the table's description
		$this->parseTableDescription();
		// Try to execute the statements
		try {
			// Check for a primary key
			if (!$this->primaryKeyIsEmpty() && !$blnForceInsert) {
				// Execute the statement
				Sql\Engine::getConnection($this->mConnection)->executeQuery($this->updateStatement());
			} elseif ($this->engine()->getDriver() === Sql\Engine::DRIVER_PGSQL) {
				// Execute the statement
				$qryInsert = Sql\Engine::getConnection($this->mConnection)->executeQuery($this->insertStatement($blnForceInsert));
				// Set the primary key
				$this->mContainer->set($this->mPrimaryKey, $qryInsert->fetchAll(\PDO::FETCH_ASSOC)[0][$this->mPrimaryKey]);
			} else {
				// Execute the statement
				Sql\Engine::getConnection($this->mConnection)->executeQuery($this->insertStatement($blnForceInsert));
				// Set the primary key
				$this->mContainer->set($this->mPrimaryKey, $this->engine()->lastInsertId());
			}
			// We're done, return the instance
			return $this;
		} catch (\PDOException $pdoException) {
			// Throw the new exception
			throw new Core\Exception\Fluent\Sql\Record($pdoException->getMessage(), $pdoException->getCode(), $pdoException);
		}
	}

	/**
	 * This method returns the schema's name, with the ability to set it inline
	 * @access public
	 * @name \Crux\Fluent\Sql\Record::schemaName()
	 * @package \Crux\Fluent\Sql\Record
	 * @param string $strSchema ['']
	 * @return string
	 */
	public function schemaName(string $strSchema = '') : string
	{
		// Check for a provided schema name
		if (!Core\Is::empty($strSchema)) {
			// Reset the schema name into the instance
			$this->mSchema = $strSchema;
		}
		// We're done, return the schema name from the instance
		return $this->mSchema;
	}

	/**
	 * This method returns the table's description, with the ability to set it inline
	 * @access public
	 * @name \Crux\Fluent\Sql\Record::tableDescription()
	 * @package \Crux\Fluent\Sql\Record
	 * @param array<string, array<int, string>> $arrTableDescription [array()]
	 * @return array<string, array<int, string>>
	 * @uses \Crux\Core\Is::empty()
	 * @uses \Crux\Fluent\Sql\Record::parseTableDescription()
	 */
	public function tableDescription(array $arrTableDescription = []) : array
	{
		// Check for a provided table description
		if (!Core\Is::empty($arrTableDescription)) {
			// Reset the table description into the instance
			$this->mDescription = $arrTableDescription;
			// Parse the description into the instance
			$this->parseTableDescription(true);
		}
		// We're done, return the table description
		return $this->mDescription;
	}

	/**
	 * This method returns the table's name, with the ability to set it inline
	 * @access public
	 * @name \Crux\Fluent\Sql\Record::tableName()
	 * @package \Crux\Fluent\Sql\Record
	 * @param string $strTable ['']
	 * @return string
	 */
	public function tableName(string $strTable = '') : string
	{
		// Check for a provided table name
		if (!Core\Is::empty($strTable)) {
			// Reset the table name into the instance
			$this->mTable = $strTable;
		}
		// We're done, return the table name
		return $this->mTable;
	}

	/**
	 * This method undoes a \Crux\Fluent\Sql\Record::delete() call, assuming the data is still present in the model
	 * @access public
	 * @name \Crux\Fluent\Sql\Record::undo()
	 * @package \Crux\Fluent\Sql\Record
	 * @return \Crux\Fluent\Sql\Record $this
	 * @uses \Crux\Fluent\Sql\Record::isEmpty()
	 * @uses \Crux\Fluent\Sql\Record::save()
	 */
	public function undo() : Record
	{
		// Check the empty state
		if (!$this->isEmpty() && $this->mDeleteCalled) {
			// Save the model back to the database
			$this->save(true);
		}
		// We're done, return the instance
		return $this;
	}

	/**
	 * This method adds a custom WHERE clause to the instance
	 * @access public
	 * @name \Crux\Fluent\Sql\Record::where()
	 * @package \Crux\Fluent\Sql\Record
	 * @param string $strClause
	 * @param array<int, mixed> ...$arrReplacements
	 * @return \Crux\Fluent\Sql\Record $this
	 * @uses \Crux\Provider\Sql\Engine::getConnection()
	 * @uses \Crux\Provider\Sql\Engine::queryf()
	 * @uses array_unshift()
	 * @uses call_user_func_array()
	 */
	public function where(string $strClause, ...$arrReplacements) : Record
	{
		// Add the statement to the beginning of the replacements
		array_unshift($arrReplacements, $strClause);
		// Process the query
		$this->mWhere = call_user_func_array([Sql\Engine::getConnection($this->mConnection), 'queryf'], $arrReplacements);
		// We're done, return the instance
		return $this;
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Implemented Converters ///////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method converts the records to a JSON string
	 * @access public
	 * @name \Crux\Fluent\Sql\Record::toJson()
	 * @package \Crux\Fluent\Sql\Record
	 * @param bool $blnPrettyPrint [false]
	 * @return string
	 * @uses \Crux\Type\VariantMap::toJson()
	 */
	public function toJson(bool $blnPrettyPrint = false) : string
	{
		// Return the JSON
		return $this->mContainer->toMap()->toJson();
	}

	/**
	 * This method converts the columns to XML
	 * @access public
	 * @name \Crux\Fluent\Sql\Record::toXml()
	 * @package \Crux\Fluent\Sql\Record
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
		return $this->mContainer->toMap()->toXml($strRootNode, $blnPrettyPrint);
	}

	/**
	 * This method is called when json_encode() is executed on an object of this class
	 * @access public
	 * @name \Crux\Fluent\Sql\Record::jsonSerialize()
	 * @package \Crux\Fluent\Sql\Record
	 * @return \Crux\Collection\Map
	 * @uses \Crux\Fluent\Sql\Record::toCollection()
	 */
	public function jsonSerialize()
	{
		// Return the collection variant of this model
		return $this->toCollection();
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Converters ///////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method converts the columns to an associative array
	 * @access public
	 * @name \Crux\Fluent\Sql\Record::toArray()
	 * @package \Crux\Fluent\Sql\Record
	 * @return array<string, mixed>
	 * @uses \Crux\Fluent\Sql\Record::toCollection()
	 */
	public function toArray() : array
	{
		// Return the array of columns
		return $this->toCollection()->toArray();
	}

	/**
	 * This method converts the columns to a PHireworks Map
	 * @access public
	 * @name \Crux\Fluent\Sql\Record::toCollection()
	 * @package \Crux\Fluent\Sql\Record
	 * @return \Crux\Collection\Map
	 * @uses \Crux\Type\VariantMap::toMap()
	 */
	public function toCollection() : Collection\Map
	{
		// Localize the map
		$mapRecord = $this->mContainer->toMap();
		// Iterate over the one-to-one relationships
		foreach ($this->mOneToOne as $strTable => $modRelationship) {
			// Add the relationship to the map
			$mapRecord->set($strTable, $modRelationship->toCollection());
		}
		// Iterate over the one-to-many relationships
		foreach ($this->mOneToMany as $strTable => $colRelationship) {
			// Add the relationship to the map
			$mapRecord->set($strTable, $colRelationship->toCollection());
		}
		// Return the collection of columns
		return $mapRecord;
	}

	/**
	 * This method converts the columns to an instance of \stdClass
	 * @access public
	 * @name \Crux\Fluent\Sql\Record::toObject()
	 * @package \Crux\Fluent\Sql\Record
	 * @return \stdClass
	 * @uses \Crux\Type\VariantMap::toObject()
	 */
	public function toObject() : \stdClass
	{
		// Localize the object
		$objRecord = $this->mContainer->toObject();
		// Iterate over the one-to-one relationships
		foreach ($this->mOneToOne as $strTable => $modRelationship) {
			// Add the relationship to the map
			$objRecord->$strTable = $modRelationship->toObject();
		}
		// Iterate over the one-to-many relationships
		foreach ($this->mOneToMany as $strTable => $colRelationship) {
			// Add the relationship to the map
			$objRecord->$strTable = $colRelationship->toObject();
		}
		// Return the object
		return $objRecord;
	}

	/**
	 * This method converts the columns to a PHireworks VariantMap
	 * @access public
	 * @name \Crux\Fluent\Sql\Record::toVariant()
	 * @package \Crux\Fluent\Sql\Record
	 * @return \Crux\Type\VariantMap
	 */
	public function toVariant() : Type\VariantMap
	{
		// Localize the container
		$mapRecord = clone $this->mContainer;
		// Iterate over the one-to-one relationships
		foreach ($this->mOneToOne as $strTable => $modRelationship) {
			// Add the relationship to the map
			$mapRecord->set($strTable, $modRelationship->toVariant());
		}
		// Iterate over the one-to-many relationships
		foreach ($this->mOneToMany as $strTable => $colRelationship) {
			// Add the relationship to the map
			$mapRecord->set($strTable, $colRelationship->toVariant());
		}
		// Return the column container
		return $mapRecord;
	}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
} /// End Crux\Fluent\Sql\Record Class Definition ////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

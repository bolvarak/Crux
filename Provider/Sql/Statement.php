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
use Crux\Type;
use \PDO;
use \PDOStatement;

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Crux\Provider\Sql\Statement Class Definition /////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class Statement extends PDOStatement
{
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Protected Properties /////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This property contains the database connection instance
	 * @access protected
	 * @name \Crux\Provider\Sql\Statement::$mConnection
	 * @package \Crux\Provider\Sql\Statement
	 * @var \Crux\Provider\Sql\Engine
	 */
	protected $mConnection;

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Constructor //////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method instantiates a new SQL statement object
	 * @access public
	 * @name \Crux\Provider\Sql\Statement::__construct()
	 * @param \Crux\Provider\Sql\Engine $sqlConnection
	 * @uses \Crux\Provider\Sql\Statement::setLastInsertId()
	 * @uses \PDO::lastInsertId()
	 * @uses \PDOStatement::setFetchMode()
	 */
	protected function __construct(Engine $sqlConnection)
	{
		// Set the connection into the instance
		$this->mConnection = $sqlConnection;
		// Set the fetch mode
		$this->setFetchMode(PDO::FETCH_CLASS|PDO::FETCH_PROPS_LATE, 'Crux\\Provider\\Sql\\Record', [$this]);
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Public Methods ///////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method returns a column from a record
	 * @access public
	 * @name \Crux\Provider\Sql\Statement::column()
	 * @package \Crux\Provider\Sql
	 * @param string $strColumn
	 * @param int $intRow [0]
	 * @return \Crux\Type\Variant
	 * @uses \Crux\Provider\Sql\Statement::record()
	 * @uses \Crux\Provider\Sql\Record::column()
	 */
	public function column(string $strColumn, int $intRow = 0) : Type\Variant
	{
		// Return the column
		return $this->record($intRow)->column($strColumn);
	}

	/**
	 * This method provides an iterative interface for the result set
	 * @access public
	 * @name \Crux\Provider\Sql\Statement::iterate()
	 * @package \Crux\Provider\Sql\Statement
	 * @param callable(\Crux\Provider\Sql\Record, int) $funCallback
	 * @return void
	 * @uses \PDOStatement::fetch()
	 * @uses call_user_func_array()
	 */
	public function iterate(callable $funCallback)
	{
		// Define our iteration
		$intRow = 0;
		// Iterate over the records
		while ($pdoRecord = $this->fetch()) {
			// Execute the callback
			$funCallback($pdoRecord, $intRow);
			// Increment the iterator
			++$intRow;
		}
	}

	/**
	 * This method fetches a record from the instance
	 * @access public
	 * @name \Crux\Provider\Sql\Statement::record()
	 * @package \Crux\Provider\Sql
	 * @param int $intRow [0]
	 * @return \Crux\Provider\Sql\Record
	 * @throws \Crux\Core\Exception\Provider\Sql\Statement
	 * @uses \Crux\Provider\Sql\Statement::rowCount()
	 * @uses \Crux\Provider\Sql\Statement::records()
	 * @uses \Crux\Core\Exception\Provider\Sql\Statement::__construct()
	 * @uses sprintf()
	 */
	public function record(int $intRow = 0) : Record
	{
		// Check the row count
		if ($this->rowCount() < ($intRow + 1)) {
			// Throw the exception
			throw new Core\Exception\Provider\Sql\Statement(sprintf('Row %d does not exists', $intRow));
		}
		// Return the record
		return $this->records()[$intRow];
	}

	/**
	 * This method fetches all of the rows from the result set
	 * @access public
	 * @name \Crux\Provider\Sql\Statement::records()
	 * @package \Crux\Provider\Sql\Statement
	 * @return array<int, \Crux\Provider\Sql\Record>
	 * @uses \Crux\Provider\Sql\Statement::fetchAll()
	 */
	public function records() : array
	{
		// Return the records
		return $this->fetchAll();
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Converters ///////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method converts the result set into an array of associative arrays
	 * @access public
	 * @name \Crux\Provider\Sql\Statement::toArray()
	 * @package \Crux\Provider\Sql\Statement
	 * @return array<int, array<string, mixed>>
	 * @uses \PDOStatement::fetchAll()
	 */
	public function toArray() : array
	{
		// Return the array
		return $this->fetchAll(PDO::FETCH_ASSOC);
	}

	/**
	 * This method converts the result set into a PHireworks Vector
	 * @access public
	 * @name \Crux\Provider\Sql\Statement::toCollection()
	 * @package \Crux\Provider\Sql\Statement
	 * @return \Crux\Collection\Vector
	 * @uses \Crux\Collection\Vector::__construct()
	 * @uses \Crux\Collection\Vector::add()
	 * @uses \Crux\Collection\Map::fromArray()
	 * @uses \PDOStatement::fetch()
	 */
	public function toCollection() : Collection\Vector
	{
		// Define our response vector
		$vecRecords = new Collection\Vector();
		// Iterate over the result set
		while ($arrRow = $this->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT, 0)) {
			// Add the row to the collection
			$vecRecords->add(Collection\Map::fromArray($arrRow));
		}
		// We're done, return the collection
		return $vecRecords;
	}

	/**
	 * This method converts the result set into a PHireworks VariantList
	 * @access public
	 * @name \Crux\Provider\Sql\Statement::toVariant()
	 * @package \Crux\Provider\Sql\Statement
	 * @return \Crux\Type\VariantList
	 * @uses \Crux\Type\VariantList::__construct()
	 * @uses \Crux\Type\VariantList::add()
	 * @uses \PDOStatement::fetch()
	 */
	public function toVariant() : Type\VariantList
	{
		// Define our response variant
		$varRecords = new Type\VariantList();
		// Iterate over the result set
		while ($arrRow = $this->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT, 0)) {
			// Add the row to the variant
			$varRecords->add($arrRow);
		}
		// We're done, return the variant
		return $varRecords;
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Getters //////////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method returns the engine object from the instance
	 * @access public
	 * @name \Crux\Provider\Sql\Statement::getConnection()
	 * @package \Crux\Provider\Sql\Statement
	 * @return \Crux\Provider\Sql\Engine
	 */
	public function getConnection() : Engine
	{
		// Return the connection object from the instance
		return $this->mConnection;
	}

	/**
	 * This method returns the last inserted ID from the instance
	 * @access public
	 * @name \Crux\Provider\Sql\Statement::getLastInsertId()
	 * @package \Crux\Provider\Sql\Statement
	 * @param string $strTable
	 * @param string $strSchema ['']
	 * @return \Crux\Type\Variant
	 * @uses \Crux\Provider\Sql\Engine::lastId()
	 */
	public function getLastInsertId(string $strTable, string $strSchema = '') : Type\Variant
	{
		// Return the last insert ID from the instance
		return $this->mConnection->lastId($strTable, $strSchema);
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Setters //////////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method sets the connection object into the instance
	 * @access public
	 * @name \Crux\Provider\Sql\Statement::setConnection()
	 * @package \PHirework\Provider\Sql\Statement
	 * @param \Crux\Provider\Sql\Engine $sqlConnection
	 * @return \Crux\Provider\Sql\Statement $this
	 */
	public function setConnection(Engine $sqlConnection) : Statement
	{
		// Set the connection into the instance
		$this->mConnection = $sqlConnection;
		// We're done, return the instance
		return $this;
	}

	/**
	 * This method sets the last inserted ID into the instance
	 * @access public
	 * @name \Crux\Provider\Sql\Statement::setLastInsertId()
	 * @package \Crux\Provider\Sql\Statement
	 * @param mixed $mixIdentifier
	 * @return \Crux\Provider\Sql\Statement $this
	 * @uses \Crux\Type\Variant::Factory()
	 */
	public function setLastInsertId($mixIdentifier) : Statement
	{
		// Set the last inserted ID into the instance
		$this->mLastInsertId = Type\Variant::Factory($mixIdentifier);
		// We're done, return the instance
		return $this;
	}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
} /// End Crux\Provider\Sql\Statement Class Definition ///////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

<?php

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Crux\Provider\Sql\Engine Namespace ///////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

namespace Crux\Provider\Sql\Engine;

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Imports //////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

use Crux\Core;
use Crux\Provider\Sql;
use Crux\Type;

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Crux\Provider\Sql\Engine\Lite Class Definition ///////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

final class Lite extends Sql\Engine
{
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Constructor //////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method instantiates a new SQLite provider object
	 * @access public
	 * @name \Crux\Provider\Sql\Engine\Lite::__construct()
	 * @package \Crux\Provider\Sql\Engine\Lite
	 * @param \PDO $strDatabase
	 * @param string $strFriendlyName ['']
	 * @param string $strUsername [null]
	 * @param string $strPassword [null]
	 * @param int $intAccess [\Crux\Provider\Sql\Engine::READ_WRITE]
	 * @throws \Crux\Core\Exception\Provider\Sql\Engine\Lite
	 * @uses \Crux\Provider\Sql\Engine::__construct()
	 */
	public function __construct($strDatabase, $strFriendlyName = '', string $strUsername = null, string $strPassword = null, int $intAccess = self::READ_WRITE)
	{
		// Set the driver
		$this->mDriver = self::DRIVER_SQLITE;
		// Set the access into the instance
		$this->mAccess = self::READ_WRITE;
		// Set the username into the instance
		$this->mUsername = $strUsername;
		// Set the password into the instance
		$this->mPassword = $strPassword;
		// Set the column escape template
		$this->mEscapeColumn = '"%s"';
		// Set the table escape template
		$this->mEscapeTable = '"%s"';
		// Set the value escape character
		$this->mEscapeValue = '\'\'';
		// Set the friendly name into the instance
		$this->mName = $strFriendlyName;
		// Set the database into the instance
		$this->mDatabase = $strDatabase;
		// Set the access level into the instance
		parent::__construct();
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Implementations //////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method returns a DATE for SQLite
	 * @access public
	 * @name \Crux\Provider\Sql\Engine\Lite::date()
	 * @package \Crux\Provider\Sql\Engine\Lite
	 * @param int|string $mixTimeStamp ['']
	 * @return string
	 * @throws \Crux\Core\Exception\Provider\Sql\Engine\Lite
	 * @uses \Crux\Core\Is::empty()
	 * @uses \Crux\Core\Is::string()
	 * @uses \Crux\Core\Is::integer()
	 * @uses \Crux\Core\Exception\Provider\Sql\Engine\Lite::__construct()
	 * @uses time()
	 * @uses strtotime()
	 * @uses date()
	 */
	public function date($mixTimeStamp = '') : string
	{
		// Check the type
		if (!Core\Is::empty($mixTimeStamp) && !Core\Is::string($mixTimeStamp) && !Core\Is::integer($mixTimeStamp)) {
			// We're done, throw the exception
			throw new Core\Exception\Provider\Sql\Engine\Lite('Timestamp must either be the string representation of time or a UNIX timestamp');
		}
		// Check for an empty timestamp
		if (Core\Is::empty($mixTimeStamp)) {
			// Reset the timestamp
			$mixTimeStamp = time();
		}
		// Return the date
		return date('Y-m-d', (Core\Is::integer($mixTimeStamp) ? $mixTimeStamp : strtotime($mixTimeStamp)));
	}

	/**
	 * This method loads the description of a table in the database
	 * @access public
	 * @name \Crux\Provider\Sql\Engine\Lite::describe()
	 * @package \Crux\Provider\Sql\Engine\Lite
	 * @param string $strTable
	 * @param string $strSchema ['']
	 * @return array<string, array<int, string>>
	 * @uses \Crux\Provider\Sql\Engine::executeQuery()
	 * @uses \Crux\Provider\Sql\Statement::iterate()
	 * @uses \Crux\Provider\Sql\Statement::__get()
	 * @uses \Crux\Type\Variant::toInt()
	 * @uses \Crux\Type\Variant::toLower()
	 * @uses \Crux\Type\Variant::toString()
	 * @uses array_push()
	 */
	public function describe(string $strTable, string $strSchema = '') : array
	{
		// Execute the query
		$pdoStatement = $this->executeQuery('PRAGMA table_info(%s)', $strTable);
		// Create our storage array
		$arrDescription = [];
		// Iterate over the data
		$pdoStatement->iterate(function(Sql\Record $clsRow) use (&$arrDescription) {
			// Define our column placeholder
			$arrColumn = [];
			// Add the type
			array_push($arrColumn, $clsRow->column('type')->toString());
			// Add the size
			array_push($arrColumn, '255');
			// Check for a primary key
			if ($clsRow->column('pk')->toInt() === 1) {
				// Append the primary key identifier
				array_push($arrColumn, self::PRIMARY_KEY_IDENTIFIER);
			}
			// Add the column to the description
			$arrDescription[$clsRow->column('name')->toString()] = $arrColumn;
		});
		// We're done, return the column description
		return $arrDescription;
	}

	/**
	 * This method returns the last inserted ID from the database
	 * @access public
	 * @name \Crux\Provider\Sql\My::lastId()
	 * @package \Crux\Provider\Sql\My
	 * @param string $strTable
	 * @param string $strSchema ['']
	 * @return \Crux\Type\Variant
	 * @uses \Crux\Provider\Sql\Engine::lastInsertId()
	 * @uses \Crux\Type\Variant::Factory()
	 */
	public function lastId(string $strTable, string $strSchema = '') : Type\Variant
	{
		// Return the data
		return Type\Variant::Factory($this->lastInsertId());
	}

	/**
	 * This method returns a DATETIME/TIMESTAMP for SQLite
	 * @access public
	 * @name \Crux\Provider\Sql\Engine\Lite::timeStamp()
	 * @package \Crux\Provider\Sql\Engine\Lite
	 * @param int|string $mixTimeStamp ['']
	 * @return string
	 * @throws \Crux\Core\Exception\Provider\Sql\Engine\Lite
	 * @uses \Crux\Core\Is::empty()
	 * @uses \Crux\Core\Is::string()
	 * @uses \Crux\Core\Is::integer()
	 * @uses \Crux\Core\Exception\Provider\Sql\Engine\Lite::__construct()
	 * @uses time()
	 * @uses strtotime()
	 * @uses date()
	 */
	public function timeStamp($mixTimeStamp = '') : string
	{
		// Check the type
		if (!Core\Is::empty($mixTimeStamp) && !Core\Is::string($mixTimeStamp) && !Core\Is::integer($mixTimeStamp)) {
			// We're done, throw the exception
			throw new Core\Exception\Provider\Sql\Engine\Lite('Timestamp must either be the string representation of time or a UNIX timestamp');
		}
		// Check for an empty timestamp
		if (Core\Is::empty($mixTimeStamp)) {
			// Reset the timestamp
			$mixTimeStamp = time();
		}
		// Return the date
		return date('Y-m-d H:i:s', (Core\Is::integer($mixTimeStamp) ? $mixTimeStamp : strtotime($mixTimeStamp)));
	}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
} /// End Crux\Provider\Sql\Engine\Lite Class Definition /////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

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
/// Crux\Provider\Sql\Engine\Postgre Class Definition ////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class Postgre extends Sql\Engine
{
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Constructor //////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method instantiates a new PostgreSQL provider object
	 * @access public
	 * @name \Crux\Provider\Sql\Engine\Postgre::__construct()
	 * @package \Crux\Provider\Sql\Engine\Postgre
	 * @param string $strDatabase
	 * @param string $strUsername
	 * @param string $strPassword
	 * @param string $strFriendlyName ['']
	 * @param string $strHost [\Crux\Provider\Sql\Engine::LOCALHOST_SOCKET]
	 * @param int $intPort [5432]
	 * @param int $intAccess [\Crux\Provider\Sql\Engine::READ_WRITE]
	 * @uses \Crux\Provider\Sql\Engine::__construct()
	 */
	public function __construct(string $strDatabase, string $strUsername, string $strPassword, string $strFriendlyName = '', string $strHost = self::LOCALHOST_SOCKET, int $intPort = 5432, int $intAccess = self::READ_WRITE)
	{
		// Set the driver
		$this->mDriver = self::DRIVER_PGSQL;
		// Set the access level into the instance
		$this->mAccess = $intAccess;
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
		// Set the host name
		$this->mHost = $strHost;
		// Set the port number
		$this->mPort = $intPort;
		// Set the access level into the instance
		parent::__construct();
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Implementations //////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method returns a DATE for PostgreSQL
	 * @access public
	 * @name \Crux\Provider\Sql\Engine\Postgre::date()
	 * @package \Crux\Provider\Sql\Engine\Postgre
	 * @param int|string $mixTimeStamp ['']
	 * @return string
	 * @throws \Crux\Core\Exception\Provider\Sql\Engine\Postgre
	 * @uses \Crux\Core\Is::empty()
	 * @uses \Crux\Core\Is::string()
	 * @uses \Crux\Core\Is::integer()
	 * @uses \Crux\Core\Is::float()
	 * @uses \Crux\Core\Exception\Provider\Sql\Engine\Postgre::__construct()
	 * @uses time()
	 * @uses explode()
	 * @uses intval()
	 * @uses strtotime()
	 * @uses date()
	 */
	public function date($mixTimeStamp = '') : string
	{
		// Check the type
		if (!Core\Is::empty($mixTimeStamp) && !Core\Is::string($mixTimeStamp) && !Core\Is::integer($mixTimeStamp) && !Core\Is::float($mixTimeStamp)) {
			// We're done, throw the exception
			throw new Core\Exception\Provider\Sql\Engine\Postgre('Timestamp must either be the string representation of time, a UNIX timestamp or a microtime floating point');
		}
		// Check for an empty timestamp
		if (Core\Is::empty($mixTimeStamp)) {
			// Reset the timestamp
			$mixTimeStamp = time();
		}
		// Check for a float
		if (Core\Is::float($mixTimeStamp)) {
			// Reset the timestamp
			$mixTimeStamp = intval(explode('.', $mixTimeStamp)[0]);
		}
		// Return the date
		return date('Y-m-d', (Core\Is::integer($mixTimeStamp) ? $mixTimeStamp : strtotime($mixTimeStamp)));
	}

	/**
	 * This method describes a PostgreSQL table inside of a schema
	 * @access public
	 * @name \Crux\Provider\Sql\Engine\Postgre::describe()
	 * @package \Crux\Provider\Sql\Engine\Postgre
	 * @param string $strTable
	 * @param string $strSchema ['public']
	 * @return array<string, array<int, string>>
	 * @uses \Crux\Provider\Sql\Engine::executeQuery()
	 * @uses \Crux\Provider\Sql\Statement::iterate()
	 * @uses \Crux\Provider\Sql\Statement::__get()
	 * @uses \Crux\Type\Variant::toLower()
	 * @uses \Crux\Type\Variant::toString()
	 * @uses array_push()
	 */
	public function describe(string $strTable, string $strSchema = 'public') : array
	{
		// Create the statement
		$pdoStatement = $this->executeQuery('
			SELECT DISTINCT
				"information_schema"."columns"."column_name",
				"information_schema"."columns"."data_type",
				"information_schema"."columns"."column_default",
				"information_schema"."columns"."is_nullable",
				"information_schema"."columns"."character_maximum_length",
				"information_schema"."table_constraints"."constraint_type"
			FROM
				"information_schema"."columns"
			LEFT JOIN "information_schema"."key_column_usage" ON (
				"information_schema"."key_column_usage"."column_name" = "information_schema"."columns"."column_name"
			)
			LEFT JOIN "information_schema"."table_constraints" ON (
				"information_schema"."table_constraints"."constraint_catalog" = "information_schema"."columns"."table_catalog"
					AND "information_schema"."table_constraints"."constraint_schema" = "information_schema"."columns"."table_schema"
					AND "information_schema"."table_constraints"."constraint_name" = "information_schema"."key_column_usage"."constraint_name"
			)
			WHERE
				"information_schema"."columns"."table_catalog" = %s
					AND "information_schema"."columns"."table_schema" = %s
					AND "information_schema"."columns"."table_name" = %s
			;
		', $this->mDatabase, $strSchema, $strTable);
		// Define the description container
		$arrDescription = [];
		// Iterate over the rows
		$pdoStatement->iterate(function(Record $pdoRecord, int $intRow) use (&$arrDescription) {
			// Check the description for the column
			if (!array_key_exists($pdoRecord->getColumnName()->toString(), $arrDescription)) {
				// Add the column to the description container
				$arrDescription[$pdoRecord->getColumnName()->toString()] = [
					$pdoRecord->getDataType()->toString(),
					$pdoRecord->getCharacterMaximumLength()->toInt(),
					($pdoRecord->getConstraintType()->toLower()->matches('primary key') ? self::PRIMARY_KEY_IDENTIFIER : null)
				];
			}
		});
		// We're done, return the description
		return $arrDescription;
	}

	/**
	 * This method returns the last inserted ID from the database
	 * @access public
	 * @name \Crux\Provider\Sql\Engine\Postgre::lastId()
	 * @package \Crux\Provider\Sql\Engine\Postgre
	 * @param string $strTable
	 * @param string $strSchema ['public']
	 * @return \Crux\Type\Variant
	 * @uses \Crux\Provider\Sql\Engine::executeQuery()
	 * @uses \Crux\Provider\Sql\Engine::primaryKey()
	 * @uses \Crux\Provider\Sql\Statement::rowCount()
	 * @uses \Crux\Provider\Sql\Statement::record()
	 * @uses \Crux\Provider\Sql\Record::__get()
	 * @uses \Crux\Type\Variant::Factory()
	 */
	public function lastId(string $strTable, string $strSchema = 'public') : Type\Variant
	{
		// Query for the last inserted ID
		$qryLastInsertId = $this->executeQuery('SELECT max(%C) AS %C FROM %T.%T', $this->primaryKey($strTable, $strSchema), 'LastInsertID', $strSchema, $strTable);
		// Check the record count
		if ($qryLastInsertId->rowCount()) {
			// Return the value
			return $qryLastInsertId->record(0)->column('LastInsertID');
		}
		// Return an empty variant
		return Type\Variant::Factory(null);
	}

	/**
	 * This method returns a DATETIME/TIMESTAMP for PostgreSQL
	 * @access public
	 * @name \Crux\Provider\Sql\Engine\Postgre::timeStamp()
	 * @package \Crux\Provider\Sql\Engine\Postgre
	 * @param int|string $mixTimeStamp ['']
	 * @return string
	 * @throws \Crux\Core\Exception\Provider\Sql\Engine\Postgre
	 * @uses \Crux\Core\Is::empty()
	 * @uses \Crux\Core\Is::string()
	 * @uses \Crux\Core\Is::integer()
	 * @uses \Crux\Core\Exception\Provider\Sql\Engine\Postgre::__construct()
	 * @uses microtime())
	 * @uses explode()
	 * @uses intval()
	 * @uses sprintf()
	 * @uses strtotime()
	 * @uses date()
	 */
	public function timeStamp($mixTimeStamp = '') : string
	{
		// Check the type
		if (!Core\Is::empty($mixTimeStamp) && !Core\Is::string($mixTimeStamp) && !Core\Is::integer($mixTimeStamp) && !Core\Is::float($mixTimeStamp)) {
			// We're done, throw the exception
			throw new Core\Exception\Provider\Sql\Engine\Postgre('Timestamp must either be the string representation of time, a UNIX timestamp or a microtime floating point');
		}
		// Check for an empty timestamp
		if (Core\Is::empty($mixTimeStamp)) {
			// Reset the timestamp
			$mixTimeStamp = microtime(true);
		}
		// Check the type
		if (Core\Is::float($mixTimeStamp)) {
			// Explode the timestamp
			$strTimeStamp = explode('.', $mixTimeStamp);
			// Return the timestamp
			return sprintf('%s.%d', date('Y-m-d H:i:s', intval($strTimeStamp[0])), intval($strTimeStamp[1]));
		} elseif (Core\Is::integer($mixTimeStamp)) {
			// Return the timestamp
			return date('Y-m-d H:i:s', $mixTimeStamp);
		} else {
			// Return the timestamp
			return date('Y-m-d H:i:s', strtotime($mixTimeStamp));
		}
	}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
} /// End Crux\Provider\Sql\Engine\Postgre Class Definition //////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

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
/// Crux\Provider\Sql\Engine\My Class Definition /////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class My extends Sql\Engine
{
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Constructor //////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	
	/**
	 * This method instantiates a new MySQL provider object
	 * @access public
	 * @name \Crux\Provider\Sql\Engine\My::__construct()
	 * @package \Crux\Provider\Sql\Engine\My
	 * @param string $strDatabase
	 * @param string $strUsername
	 * @param string $strPassword
	 * @param string $strFriendlyName ['']
	 * @param string $strHost [\Crux\Provider\Sql\Engine::LOCALHOST_SOCKET]
	 * @param int $intPort [3306]
	 * @param int $intAccess [\Crux\Provider\Sql\Engine::READ_WRITE]
	 * @uses \Crux\Provider\Sql\Engine::__construct()
	 */
	public function __construct(string $strDatabase, string $strUsername, string $strPassword, string $strFriendlyName = '', string $strHost = self::LOCALHOST_SOCKET, int $intPort = 3306, int $intAccess = self::READ_WRITE)
	{
		// Set the driver
		$this->mDriver = self::DRIVER_MYSQL;
		// Set the access level into the instance
		$this->mAccess = $intAccess;
		// Set the username into the instance
		$this->mUsername = $strUsername;
		// Set the password into the instance
		$this->mPassword = $strPassword;
		// Set the column escape template
		$this->mEscapeColumn = '`%s`';
		// Set the table escape template
		$this->mEscapeTable = '`%s`';
		// Set the value escape character
		$this->mEscapeValue = '\\\'';
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
	 * This method returns a DATE for MySQL
	 * @access public
	 * @name \Crux\Provider\Sql\Engine\My::date()
	 * @package \Crux\Provider\Sql\Engine\My
	 * @param int|string $mixTimeStamp ['']
	 * @return string
	 * @throws \Crux\Core\Exception\Provider\Sql\Engine\My
	 * @uses \Crux\Core\Is::empty()
	 * @uses \Crux\Core\Is::string()
	 * @uses \Crux\Core\Is::integer()
	 * @uses \Crux\Core\Exception\Provider\Sql\Engine\My::__construct()
	 * @uses time()
	 * @uses strtotime()
	 * @uses date()
	 */
	public function date($mixTimeStamp = '') : string
	{
		// Check the type
		if (!Core\Is::empty($mixTimeStamp) && !Core\Is::string($mixTimeStamp) && !Core\Is::integer($mixTimeStamp)) {
			// We're done, throw the exception
			throw new Core\Exception\Provider\Sql\Engine\My('Timestamp must either be the string representation of time or a UNIX timestamp');
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
	 * This method describes a MySQL table
	 * @access public
	 * @name \Crux\Provider\Sql\Engine\My::describe()
	 * @package \Crux\Provider\Sql\Engine\My
	 * @param string $strTable
	 * @param string $strSchema ['']
	 * @return array<string, array<int, string>>
	 * @uses \Crux\Provider\Sql\Engine::executeQuery()
	 * @uses \Crux\Provider\Sql\Statement::iterate()
	 * @uses \Crux\Provider\Sql\Statement::__get()
	 * @uses \Crux\Type\Variant::toLower()
	 * @uses \Crux\Type\Variant::toString()
	 * @uses array_push()
	 */
	public function describe(string $strTable, string $strSchema = '') : array
	{
		// Grab the statement
		$pdoStatement = $this->executeQuery('DESCRIBE %T', $strTable);
		// Define our description container
		$arrDescription = [];
		// Iterate over the result set
		$pdoStatement->iterate(function(Record $clsRow, int $intRow) use (&$arrDescription) {
			// Define our column container
			$arrColumn = [];
			// Split the type into its pieces
			preg_match('/([a-zA-Z]+)(\(([0-9]*)\))?/i', $clsRow->getType()->toString(), $arrType);
			// Set the type into the column
			array_push($arrColumn, $arrType[1]);
			// Set the length into the column
			array_push($arrColumn, ($arrColumn[3] ?? ''));
			// Check for a primary key
			if ($clsRow->getKey()->toLower()->toString() === 'pri') {
				// Append the primary key identifier
				array_push($arrColumn, self::PRIMARY_KEY_IDENTIFIER);
			}
			// Add the column to the container
			$arrDescription[$clsRow->getField()->toString()] = $arrColumn;
		});
		// We're done, return the description
		return $arrDescription;
	}

	/**
	 * This method returns the last inserted ID from the database
	 * @access public
	 * @name \Crux\Provider\Sql\Engine\My::lastId()
	 * @package \Crux\Provider\Sql\Engine\My
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
	 * This method returns a DATETIME/TIMESTAMP for MySQL
	 * @access public
	 * @name \Crux\Provider\Sql\Engine\My::timeStamp()
	 * @package \Crux\Provider\Sql\Engine\My
	 * @param int|string $mixTimeStamp ['']
	 * @return string
	 * @throws \Crux\Core\Exception\Provider\Sql\Engine\My
	 * @uses \Crux\Core\Is::empty()
	 * @uses \Crux\Core\Is::string()
	 * @uses \Crux\Core\Is::integer()
	 * @uses \Crux\Core\Exception\Provider\Sql\Engine\My::__construct()
	 * @uses time()
	 * @uses strtotime()
	 * @uses date()
	 */
	public function timeStamp($mixTimeStamp = '') : string
	{
		// Check the type
		if (!Core\Is::empty($mixTimeStamp) && !Core\Is::string($mixTimeStamp) && !Core\Is::integer($mixTimeStamp)) {
			// We're done, throw the exception
			throw new Core\Exception\Provider\Sql\Engine\My('Timestamp must either be the string representation of time or a UNIX timestamp');
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
} /// End Crux\Provider\Sql\Engine\My Class Definition ///////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

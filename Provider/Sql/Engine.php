<?php

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Crux\Provider\Sql Namespace //////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

namespace Crux\Provider\Sql;

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Imports //////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

use Crux\Core;
use Crux\Type;
use \PDO;
use \PDOException;

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Crux\Provider\Sql\Engine Abstract Class Definition ///////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

abstract class Engine extends PDO
{
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Constants ////////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This constant defines the default connection name
	 * @name \Crux\Provider\Sql\Engine::DEFAULT_CONNECTION
	 * @package \Crux\Provider\Sql\Engine
	 * @static
	 * @var string
	 */
	const DEFAULT_CONNECTION = '__default__';

	/**
	 * This constant contains the driver name for MySQL/MariaDB
	 * @name \Crux\Provider\Sql\Engine::DRIVER_MYSQL
	 * @package \Crux\Provider\Sql\Engine
	 * @static
	 * @var string
	 */
	const DRIVER_MYSQL = 'mysql';

	/**
	 * This constant contains the driver name for Oracle
	 * @name \Crux\Provider\Sql\Engine::DRIVER_ORACLE
	 * @package \Crux\Provider\Sql\Engine
	 * @static
	 * @var string
	 */
	const DRIVER_ORACLE = 'oci';

	/**
	 * This constant contains the driver name for PostgreSQL
	 * @name \Crux\Provider\Sql\Engine::DRIVER_PGSQL
	 * @package \Crux\Provider\Sql\Engine
	 * @static
	 * @var string
	 */
	const DRIVER_PGSQL = 'pgsql';

	/**
	 * This constant contains the driver name for SQLite version 3+
	 * @name \Crux\Provider\Sql\Engine::DRIVER_SQLITE
	 * @package \Crux\Provider\Sql\Engine
	 * @static
	 * @var string
	 */
	const DRIVER_SQLITE = 'sqlite';

	/**
	 * This constant contains the driver name for SQLite version 2
	 * @name \Crux\Provider\Sql\Engine::DRIVER_SQLITE2
	 * @package \Crux\Provider\Sql\Engine
	 * @static
	 * @var string
	 */
	const DRIVER_SQLITE2 = 'sqlite2';

	/**
	 * This constant contains the driver name for Microsost SQL Server
	 * @name \Crux\Provider\Sql\Engine::DRIVER_SQL_SERVER
	 * @package \Crux\Provider\Sql\Engine
	 * @static
	 * @var string
	 */
	const DRIVER_SQL_SERVER = 'sqlsrv';

	/**
	 * This constant defines the localhost IPv4 address
	 * @name \Crux\Provider\Sql\Engine::LOCALHOST_IPv4
	 * @package \Crux\Provider\Sql\Engine
	 * @static
	 * @var string
	 */
	const LOCALHOST_IPv4 = '127.0.0.1';

	/**
	 * This constant defines the localhost IPv6 address
	 * @name \Crux\Provider\Sql\Engine::LOCALHOST_IPv6
	 * @package \Crux\Provider\Sql\Engine
	 * @static
	 * @var string
	 */
	const LOCALHOST_IPv6 = '::1';

	/**
	 * This constant defines the localhost socket name
	 * @name \Crux\Provider\Sql\Engine::LOCALHOST_SOCKET
	 * @package \Crux\Provider\Sql\Engine
	 * @static
	 * @var string
	 */
	const LOCALHOST_SOCKET = 'localhost';

	/**
	 * This constant defines the Primary Key column identifier
	 * @name \Crux\Provider\Sql\Engine::PRIMARY_KEY_IDENTIFIER
	 * @package \Crux\Provider\Sql\Engine
	 * @static
	 * @var string
	 */
	const PRIMARY_KEY_IDENTIFIER = 'PRI';

	/**
	 * This constant defines a connection as read-only
	 * @name \Crux\Provider\Sql\Engine::READ_ONLY
	 * @package \Crux\Provider\Sql\Engine
	 * @static
	 * @var int
	 */
	const READ_ONLY = 0x01;

	/**
	 * This constant defines a connection as read-write
	 * @name \Crux\Provider\Sql\Engine::READ_WRITE
	 * @package \Crux\Provider\Sql\Engine
	 * @static
	 * @var int
	 */
	const READ_WRITE = 0x02;

	/**
	 * This constant defines the name for a SQLite DB in memory
	 * @name \Crux\Provider\Sql\Engine::SQLITE_MEMEORY
	 * @package \Crux\Provider\Sql\Engine
	 * @static
	 * @var string
	 */
	const SQLITE_MEMORY = ':memory:';

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Protected Static Properties //////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This property contains the pool of active connections
	 * @access protected
	 * @name \Crux\Provider\Sql\Engine::$mConnectionPool
	 * @package \PHirework\Provider\Sql\Engine
	 * @static
	 * @var array<string, \Crux\Provider\Sql\Engine>
	 */
	protected static $mConnectionPool = [];

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Protected Properties /////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This property defines the access this database has
	 * @access protected
	 * @name \Crux\Provider\Sql\Engine::$mAccess
	 * @package \Crux\Provider\Sql\Engine
	 * @var int
	 */
	protected $mAccess = self::READ_WRITE;

	/**
	 * This property contains a flag which denotes the open state of the connection
	 * @access protected
	 * @name \Crux\Provider\Sql\Engine::$mConnectionIsOpen
	 * @package \Crux\Provider\Sql\Engine
	 * @var bool
	 */
	protected $mConnectionIsOpen = false;

	/**
	 * This property contains the name of the database to connect to
	 * @access protected
	 * @name \Crux\Provider\Sql\Engine::$mDatabase
	 * @package \Crux\Provider\Engine
	 * @var string
	 */
	protected $mDatabase = self::SQLITE_MEMORY;

	/**
	 * This property contains the description for the database
	 * @access protected
	 * @name \Crux\Provider\Sql\Engine::$mDescription
	 * @package \Crux\Provider\Sql\Engine
	 * @var array<string, array<string, array<string>>>
	 */
	protected $mDescription = [];

	/**
	 * This property contains the driver name for the database engine
	 * @access protected
	 * @name \Crux\Provider\Sql\Engine::$mDriver
	 * @package \Crux\Provider\Sql\Engine
	 * @var string
	 */
	protected $mDriver = self::DRIVER_SQLITE;

	/**
	 * This property contains the connection's data-source name (connection string)
	 * @access protected
	 * @name \Crux\Provider\Sql\Engine::$mDsn
	 * @package \Crux\Provider\Sql\Engine
	 * @var string
	 */
	protected $mDsn = '';

	/**
	 * This property contains the quoted column escape character
	 * @access protected
	 * @name \Crux\Provider\Sql\Engine::$mEscapeColumn
	 * @package \Crux\Provider\Sql\Engine
	 * @var string
	 */
	protected $mEscapeColumn = '"%s"';

	/**
	 * This property contains the quoted table/schema escape character
	 * @access protected
	 * @name \Crux\Provider\Sql\Engine::$mEscapeValue
	 * @package \Crux\Provider\Sql\Engine
	 * @var string
	 */
	protected $mEscapeTable = '"%s"';

	/**
	 * This property contains the quoted value escape character
	 * @access protected
	 * @name \Crux\Provider\Sql\Engine::$mEscapeValue
	 * @package \Crux\Provider\Sql\Engine
	 * @var string
	 */
	protected $mEscapeValue = '\'\'';

	/**
	 * This property contains the host address to establish a connectio nto
	 * @access protected
	 * @name \Crux\Provider\Sql\Engine::$mHost
	 * @package \Crux\Provider\Sql\Engine
	 * @var string
	 */
	protected $mHost = self::LOCALHOST_SOCKET;

	/**
	 * This property contains the friendly name of the connection
	 * @access protected
	 * @name \Crux\Provider\Sql\Engine::$mName
	 * @package \Crux\Provider\Sql\Engine
	 * @var string
	 */
	protected $mName = '';

	/**
	 * This property contains the password to authenticate against the database
	 * @access protected
	 * @name \Crux\Provider\Sql\Engine::$mPassword
	 * @package \Crux\Provider\Sql\Engine
	 * @var string
	 */
	protected $mPassword = '';

	/**
	 * This property contains the port to establish a connection on
	 * @access protected
	 * @name \Crux\Provider\Sql\Engine::$mPort
	 * @package \Crux\Provider\Sql\Engine
	 * @var int
	 */
	protected $mPort = 0;

	/**
	 * This property contains a flag that tells the instance that this engine has a boolean type
	 * @access protected
	 * @name \Crux\Provider\Sql\Engine::$mSupportsBooleans
	 * @package \Crux\Provider\Sql\Engine
	 * @var bool
	 */
	protected $mSupportsBooleans = false;

	/**
	 * This property contains a flag that tells the instance that this engine has a JSON type
	 * @access protected
	 * @name \Crux\Provider\Sql\Engine::$mSupportsJson
	 * @package \Crux\Provider\Sql\Engine
	 * @var bool
	 */
	protected $mSupportsJson = false;

	/**
	 * This property contains a flag that tells the instance that this engine has an XML type
	 * @access protected
	 * @name \Crux\Provider\Sql\Engine::$mSupportsXml
	 * @package \Crux\Provider\Sql\Engine
	 * @var bool
	 */
	protected $mSupportsXml = false;

	/**
	 * This property contains the username to authenticate against the database
	 * @access protected
	 * @name \Crux\Provider\Sql\Engine::$mUsername
	 * @package \Crux\Provider\Sql\Engine
	 * @var string
	 */
	protected $mUsername = '';

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Constructor //////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method instantiates a new SQL database engine object
	 * @access public
	 * @name \Crux\Provider\Sql\Engine::__construct()
	 * @package \Crux\Provider\Sql\Engine
	 * @throws \Crux\Core\Exception\Provider\Sql\Engine
	 */
	public function __construct()
	{
		// Try to establish a connection
		try {
			// Check for a connection name
			if (empty($this->mName)) {
				// Set the name to the name of the database
				$this->setName($this->mDatabase);
			}
			// Build the DSN
			$this->buildDsn();
			// Initiate the connection
			parent::__construct($this->mDsn, (empty($this->mUsername) ? null : $this->mUsername), (empty($this->mPassword) ? null : $this->mPassword));
			// Set the fetch class
			$this->setAttribute(PDO::ATTR_STATEMENT_CLASS, ['Crux\\Provider\\Sql\\Statement', [$this]]);
			// Reset the connection open state flag
			$this->setConnectionState(true);
		} catch (PDOException $pdoException) {
			// Throw a new Engine exception
			throw new Core\Exception\Provider\Sql\Engine($pdoException->getMessage(), $pdoException->getCode(), $pdoException);
		}
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Bootstrap ////////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method initializes the database connection(s) from the configuration file
	 * @access public
	 * @name \Crux\Provider\Sql\Engine::Initialize()
	 * @package \Crux\Provider\Sql\Engine
	 * @return void
	 * @static
	 * @uses \Crux\Core\Config::get()
	 * @use \Crux::environment()
	 * @uses \Crux\Provider\Sql\Lite::__construct()
	 * @uses \Crux\Provider\Sql\My::__construct()
	 * @uses \Crux\Provider\Sql\Postgre::__construct()
	 * @uses \Crux\Provider\Sql\Engine::addConnection()
	 */
	public static function Initialize()
	{
		// Iterate over the database definitions in the configuration
		foreach (Core\Config::get(sprintf('db.%s', \Crux::environment())) as $strFriendlyName => $arrConfiguration) {
			// Check the driver
			if (strtolower($arrConfiguration['driver']) === 'sqlite') {
				// Instantiate the driver
				$sqlEngine = new Engine\Lite($arrConfiguration['name'], $strFriendlyName, ($arrConfiguration['username'] ?? nulll), ($arrConfiguration['password'] ?? null));
			} elseif (strtolower($arrConfiguration['driver']) === 'pgsql')  {
				// Instantiate the driver
				$sqlEngine = new Engine\Postgre($arrConfiguration['name'], $arrConfiguration['username'], $arrConfiguration['password'], $strFriendlyName, ($arrConfiguration['host'] ?? self::LOCALHOST_SOCKET), ($arrConfiguration['port'] ?? 5432));
			} else {
				// Instantiate the driver
				$sqlEngine = new Engine\My($arrConfiguration['name'], $arrConfiguration['username'], $arrConfiguration['password'], $strFriendlyName, ($arrConfiguration['host'] ?? self::LOCALHOST_SOCKET), ($arrConfiguration['port'] ?? 3306));
			}
			// Add the connection to the pool
			self::addConnection($sqlEngine);
		}
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Public Abstract Methods //////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method generates a date for the specific database engine
	 * @abstract
	 * @access public
	 * @name \Crux\Provider\Sql\Engine::date()
	 * @package \Crux\Provider\Sql\Engine
	 * @param int|string $mixTimeStamp ['']
	 * @return string
	 */
	abstract public function date($mixTimeStamp = '') : string;

	/**
	 * This method describes a table in the database
	 * @abstract
	 * @access public
	 * @name \Crux\Provider\Sql\Engine::describe()
	 * @package \Crux\Provider\Sql\Engine
	 * @param string $strTable
	 * @param string $strSchema
	 * @return array<string, array<int, string>>
	 */
	abstract public function describe(string $strTable, string $strSchema = '') : array;

	/**
	 * This method fetches the last inserted ID from the database
	 * @abstract
	 * @access public
	 * @name \Crux\Provider\Sql\Engine::lastId()
	 * @package \Crux\Provider\Sql\Engine
	 * @param string $strTable
	 * @param string $strSchema ['']
	 * @return \Crux\Type\Variant
	 */
	abstract public function lastId(string $strTable, string $strSchema = '') : Type\Variant;

	/**
	 * This method generates a timestamp for the specific database engine
	 * @abstract
	 * @access public
	 * @name \Crux\Provider\Sql\Engine::timeStamp()
	 * @package \Crux\Provider\Sql\Engine
	 * @param float|int|string $mixTimeStamp ['']
	 * @return string
	 */
	abstract public function timeStamp($mixTimeStamp = '') : string;

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Public Static Methods ////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method stores a connection into memory identified by its friendly name
	 * @access public
	 * @name \Crux\Provider\Sql\Engine::addConnection()
	 * @package \Crux\Provider\Sql\Engine
	 * @param \Crux\Provider\Sql\Engine $sqlConnection
	 * @return void
	 * @static
	 */
	public static function addConnection(Engine $sqlConnection)
	{
		// Set the connection into memory
		self::$mConnectionPool[$sqlConnection->getName()] = $sqlConnection;
	}

	/**
	 * This method removes a connection from memory by its friendly name
	 * @access public
	 * @name \Crux\Provider\Sql\Engine::deleteConnection()
	 * @package \Crux\Provider\Sql\Engine
	 * @param string $strFriendlyName
	 * @return void
	 * @static
	 */
	public static function deleteConnection(string $strFriendlyName)
	{
		// Remove the connection from memory
		unset(self::$mConnectionPool[$strFriendlyName]);
	}

	/**
	 * This method returns a single connection from memory by its friendly name
	 * @access public
	 * @name \Crux\Provider\Sql\Engine::getConnection()
	 * @package \Crux\Provider\Sql\Engine
	 * @param string $strFriendlyName
	 * @return \Crux\Provider\Sql\Engine
	 * @static
	 */
	public static function getConnection(string $strFriendlyName) : Engine
	{
		// Return the connection from memory
		return self::$mConnectionPool[$strFriendlyName];
	}

	/**
	 * This method returns the connection pool from memory
	 * @access public
	 * @name \Crux\Provider\Sql\Engine::getConnectionPool()
	 * @package \Crux\Provider\Sql\Engine
	 * @return array<string, \Crux\Provider\Sql\Engine>
	 * @static
	 */
	public static function getConnectionPool() : array
	{
		// Return the connection pool from memory
		return self::$mConnectionPool;
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Protected Methods ////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method build a DSN connection string
	 * @access protected
	 * @name \Crux\Provider\Sql\Engine::buildDsn()
	 * @package \Crux\Provider\Sql
	 * @return void
	 */
	protected function buildDsn()
	{
		// Determine the driver
		if (($this->mDriver === self::DRIVER_SQLITE) || ($this->mDriver === self::DRIVER_SQLITE2)) {
			// set the DSN
			$this->setDsn(sprintf('%s:%s', $this->mDriver, $this->mDatabase));
		} else if ($this->mDriver === self::DRIVER_SQL_SERVER) {
			// Set the DSN
			$this->setDsn(sprintf('%s:Server=%s,%d;Database=%s', $this->mDriver, $this->mHost, $this->mPort, $this->mDatabase));
		} else if ($this->mDriver === self::DRIVER_ORACLE) {
			// Set the DSN
			$this->setDsn(sprintf('%s:dbname=//%s:%d/%s', $this->mDriver, $this->mHost, $this->mPort, $this->mDatabase));
		} elseif ($this->mDriver === self::DRIVER_MYSQL) {
			// Set the DSN
			$this->setDsn(sprintf('%s:host=%s;port=%s;dbname=%s;charset=utf8', $this->mDriver, $this->mHost, $this->mPort, $this->mDatabase));
		} else {
			// Set the DSN
			$this->setDsn(sprintf('%s:host=%s;port=%d;dbname=%s', $this->mDriver, $this->mHost, $this->mPort, $this->mDatabase));
		}
	}

	/**
	 * This method dissects the DSN and organizes information about the connection
	 * @access protected
	 * @name \Crux\Provider\Sql\Engine::dsnInformation()
	 * @package \Crux\Provider\Sql\Engine
	 * @return array<string, string|int>
	 */
	protected function dsnInformation() : array
	{
		// Define our DSN array
		$arrDsn = [
			'driver' => '',
			'host' => '',
			'port' => 0,
			'dbname' => '',
			'charset' => ''
		];
		// Split the parts
		$arrParts = explode(':', $this->mDsn);
		// Set the driver
		$arrDsn['driver'] = strtolower($arrParts[0]);
		// Check the driver
		if ($arrDsn['driver'] === 'sqlite') {
			// Set the database name
			$arrDsn['dbname'] = $arrParts[1];
		} elseif ($arrDsn['driver'] === 'sqlite2') {
			// Set the database name
			$arrDsn['dbname'] = $arrParts[1];
		} else {
			// Iterate over the sub-parts
			foreach (explode(';', $arrParts[1]) as $strSubPart) {
				// Grab the pair
				$arrPair = explode('=', $strSubPart);
				// Check the key
				if (strtolower($arrPair[0]) === 'server') {
					// Split the pair
					$arrServerPort = explode(',', $arrPair[1]);
					// Set the host
					$arrDsn['host'] = $arrServerPort[0];
					// Set the port
					$arrDsn['port'] = intval($arrServerPort[1]);
				} elseif (strtolower($arrPair[0]) === 'database') {
					// Set the database name
					$arrDsn['dbname'] = $arrPair[1];
				} elseif (strtolower($arrPair[0]) === 'dbname') {
					// Set the database name
					$arrDsn['dbname'] = $arrPair[1];
				} elseif (strtolower($arrPair[0]) === 'host') {
					// Set the host name
					$arrDsn['host'] = $arrPair[1];
				} elseif (strtolower($arrPair[0]) === 'port') {
					// Set the port number
					$arrDsn['port'] = intval($arrPair[1]);
				} elseif (strtolower($arrPair[0]) === 'charset') {
					// Set the character encoding
					$arrDsn['charset'] = $arrPair[1];
				} else {
					// Set the key and value into the DSN array
					$arrDsn[$arrPair[0]] = $arrPair[1];
				}
			}
		}
		// We're done, return the DSN information
		return $arrDsn;
	}

	/**
	 * This method turns the boolean support flag on
	 * @access protected
	 * @name \Crux\Provider\Sql\Engine::supportsBooleans()
	 * @package \Crux\Provider\Sql\Engine
	 * @return \Crux\Provider\Sql\Engine $this
	 */
	protected function supportsBooleans() : Engine
	{
		// Reset the booleans supported flag
		$this->mSupportsBooleans = true;
		// We're done, return the instance
		return $this;
	}

	/**
	 * This method turns the JSON support flag on
	 * @access protected
	 * @name \Crux\Provider\Sql\Engine::supportsJson()
	 * @package \Crux\Provider\Sql\Engine
	 * @return \Crux\Provider\Sql\Engine $this
	 */
	protected function supportsJson() : Engine
	{
		// Reset the JSON supported flag
		$this->mSupportsJson = true;
		// We're done, return the instance
		return $this;
	}

	/**
	 * This method turns the XML support flag on
	 * @access protected
	 * @name \Crux\Provider\Sql\Engine::supportsXml()
	 * @package \Crux\Provider\Sql\Engine
	 * @return \Crux\Provider\Sql\Engine $this
	 */
	protected function supportsXml() : Engine
	{
		// Reset the XML supported flag
		$this->mSupportsXml = true;
		// We're done, return the instance
		return $this;
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Public Methods ///////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method escapes a column name for a SQL statement
	 * @access public
	 * @name \Crux\Provider\Sql\Engine::escapeColumn()
	 * @package \Crux\Provider\Sql\Engine
	 * @param string $strColumn
	 * @return string
	 * @uses strip_tags()
	 * @uses trim()
	 * @uses sprintf()
	 */
	public function escapeColumn(string $strColumn) : string
	{
		// Return the escaped column
		return sprintf($this->mEscapeColumn, trim(strip_tags($strColumn)));
	}

	/**
	 * This method escapes a table name for a SQL statement
	 * @access public
	 * @name \Crux\Provider\Sql\Engine::escapeTable()
	 * @package \Crux\Provider\Sql\Engine
	 * @param string $strTable
	 * @return string
	 * @uses strip_tags()
	 * @uses trim()
	 * @uses sprintf()
	 */
	public function escapeTable(string $strTable) : string
	{
		// Return the escaped table
		return sprintf($this->mEscapeTable, trim(strip_tags($strTable)));
	}

	/**
	 * This method escapes a value for a SQL statement
	 * @access public
	 * @name \Crux\Provider\Sql\Engine::escapeValue()
	 * @package \Crux\Provider\Sql\Engine
	 * @param mixed $mixValue
	 * @return string
	 * @uses \Crux\Core\Is::sqlColumn()
	 * @uses \Crux\Core\Is::null()
	 * @uses \Crux\Core\Is::boolean()
	 * @uses \Crux\Core\Is::json()
	 * @uses \Crux\Core\Is::xml()
	 * @uses \Crux\Core\Is::html()
	 * @uses \Crux\Core\Is::string()
	 * @uses \Crux\Core\Is::number()
	 * @uses \Crux\Core\Is::variant()
	 * @uses \Crux\Type\Variant::getData()
	 * @uses \Crux\Provider\Sql\Engine::escapeValue()
	 * @uses \PDO::quote()
	 * @uses htmlspecialchars()
	 * @uses serialize()
	 */
	public function escapeValue($mixValue) : string
	{
		// Check the value type
		if (Core\Is::null($mixValue)) {
			// Return the null pointer
			return 'NULL';
		} elseif (Core\Is::boolean($mixValue) && $this->mSupportsBooleans) {
			// Return the boolean
			return $this->quote($mixValue, PDO::PARAM_BOOL);
		} elseif (Core\Is::boolean($mixValue) && !$this->mSupportsBooleans) {
			// Return the boolean integer
			return $this->quote(($mixValue ? 1 : 0), PDO::PARAM_INT);
		} elseif (Core\Is::json($mixValue) && $this->mSupportsJson) {
			// Return the JSON
			return $mixValue;
		} elseif (Core\Is::json($mixValue) && !$this->mSupportsJson) {
			// Return the quoted JSON
			return $this->quote($mixValue, PDO::PARAM_STR);
		} elseif (Core\Is::xml($mixValue) && $this->mSupportsXml) {
			// Return the XML
			return $mixValue;
		} elseif (Core\Is::xml($mixValue) && !$this->mSupportsXml) {
			// Return the quoted XML
			return $this->quote($mixValue, PDO::PARAM_STR);
		} elseif (Core\Is::html($mixValue)) {
			// Return the quoted value
			return $this->quote(htmlspecialchars($mixValue, ENT_QUOTES), PDO::PARAM_STR);
		} elseif (Core\Is::string($mixValue)) {
			// Return the quoted value
			return $this->quote($mixValue, PDO::PARAM_STR);
		} elseif (Core\Is::number($mixValue)) {
			// Return the quoted value
			return $this->quote($mixValue);
		} elseif (Core\Is::variant($mixValue)) {
			// Return this method
			return $this->escapeValue($mixValue->getData());
		} else {
			// Return the quoted variable serialization
			return $this->quote(serialize($mixValue), PDO::PARAM_STR);
		}
	}

	/**
	 * This method executes a SQL statement against the database
	 * @access public
	 * @name \Crux\Provider\Sql\Engine::executeQuery()
	 * @package \Crux\Provider\Sql\Engine
	 * @param string $strStatement
	 * @param array<int, mixed> ...$arrValues
	 * @return \Crux\Provider\Sql\Statement
	 * @throws \Crux\Core\Exception\Provider\Sql\Engine
	 * @uses \Crux\Provider\Sql\Engine::queryf()
	 * @uses \PDO::prepare()
	 * @uses \PDOStatement::execute()
	 * @uses \PDOException::getMessage()
	 * @uses \PDOException::getCode()
	 * @uses array_unshift()
	 * @uses call_user_func_array()
	 */
	public function executeQuery(string $strStatement, ...$arrValues) : Statement
	{
		// Check for replacements
		if (empty($arrValues)) {
			// Prepare the statement
			$pdoStatement = $this->prepare($strStatement);
		} else {
			// Add the statement to the beginning of the values
			array_unshift($arrValues, $strStatement);
			// Prepare the statement
			$pdoStatement = $this->prepare(call_user_func_array([$this, 'queryf'], $arrValues));
		}
		// Try to execute the statement
		try {
			// Execute the statement
			$pdoStatement->execute();
			// We're done, return the statement
			return $pdoStatement;
		} catch (PDOException $pdoException) {
			// Throw a new PHireworks Exception
			throw new Core\Exception\Provider\Sql\Engine($pdoException->getMessage(), $pdoException->getCode(), $pdoException);
		}
	}

	/**
	 * This method returns the primary key for a table
	 * @access public
	 * @name \Crux\Provider\Sql\Engine::primaryKey()
	 * @package \Crux\Provider\Sql\Engine
	 * @param string $strTable
	 * @param string $strSchema ['']
	 * @return string
	 * @uses \Crux::environment()
	 * @uses \Crux\Provider\Sql\Engine::describe()
	 * @uses \Crux\Provider\Sql\Engine::getName()
	 * @uses \Crux\Core\Is::empty()
	 * @uses \Crux\Core\Config::get()
	 * @uses \Crux\Core\Cache::hasExpired()
	 * @uses \Crux\Core\Cache::get()
	 * @uses \Crux\Core\Cache::set()
	 * @uses sprintf()
	 */
	public function primaryKey(string $strTable, string $strSchema = '') : string
	{
		// Define the cache name
		$strCacheName = sprintf('db.%s.%s.%s.%s', \Crux::environment(), $this->getName(), (Core\Is::empty($strSchema) ? 'none' : $strSchema), $strTable);
		// Check for cache
		if (Core\Config::get('db.cacheMeta') && !Core\Cache::hasExpired($strCacheName)) {
			// Set the table description from cache
			$arrDescription = Core\Cache::get($strCacheName)->toArray();
		} elseif (Core\Config::get('db.cacheMeta') && Core\Cache::hasExpired($strCacheName)) {
			// Load the table description
			$arrDescription = $this->describe($strTable, $$strSchema);
			// Save the description to cache
			Core\Cache::set($strCacheName, $this->mDescription);
		} else {
			// Load the table description
			$arrDescription = $this->describe($strTable, $strTable);
		}
		// Iterate over the description
		foreach ($arrDescription as $strColumn => $arrMetaData) {
			// Check the identifier
			if (($arrMetaData[2] ?? null) === self::PRIMARY_KEY_IDENTIFIER) {
				// We're done, return the column
				return $strColumn;
			}
		}
		// We're done, return an empty string
		return '';
	}

	/**
	 * This method formats a SQL statement with replacements, it works similar to sprintf
	 * @access public
	 * @name \Crux\Provider\Sql\Engine::queryf()
	 * @package \Crux\Provider\Sql\Engine
	 * @param string $strStatement
	 * @param array<int, mixed> ...$arrValues
	 * @return string
	 * @example %C = Column, %T = Table/Schema, %s = String, %d = Integer, %f = Float
	 * @uses \Crux\Core\Is::array()
	 * @uses \Crux\Provider\Sql\Engine::escapeColumn()
	 * @uses \Crux\Provider\Sql\Engine::escapeTable()
	 * @uses \Crux\Provider\Sql\Engine::escapeValue()
	 * @uses preg_replace_callback()
	 * @uses strtolower()
	 * @uses str_ireplace()
	 * @uses explode()
	 * @uses array_push()
	 * @uses intval()
	 * @uses floatval()
	 * @uses implode()
	 */
	public function queryf(string $strStatement, ...$arrValues) : string
	{
		// Create the iterator
		$intIterator = 0;
		// Iterate over the matches
		$strStatement = preg_replace_callback('/%=?(\=list|\=intlist|\=fltlist|\=strlist|[cdfmst]{1})/i', function(array $arrMatches) use ($arrValues, &$intIterator) {
			// Check the placeholder
			if (strtolower($arrMatches[0]) === '%t') {
				// et the replacement
				$strReplacement = $this->escapeTable($arrValues[$intIterator]);
			} elseif (strtolower($arrMatches[0]) === '%c') {
				// Set the replacement
				$strReplacement = $this->escapeColumn($arrValues[$intIterator]);
			} elseif (strtolower($arrMatches[0]) === '%=intlist') {
				// Create the placeholder
				$arrNumbers = [];
				// Check the replacements
				if (Core\Is::array($arrValues[$intIterator])) {
					// Set the source array
					$arrSource = $arrValues[$intIterator];
				} else {
					// Set the source array
					$arrSource = explode(',', str_ireplace(', ', ',', $arrValues[$intIterator]));
				}
				// Iterate over the data
				foreach ($arrSource as $strNumber) {
					// Convert it to a hard integer
					array_push($arrNumbers, intval($strNumber));
				}
				// Set the replacement
				$strReplacement = implode(',', $arrNumbers);
			} elseif (strtolower($arrMatches[0]) === '%=fltlist') {
				// Create the placeholder
				$arrNumbers     = [];
				// Check the replacements
				if (Core\Is::array($arrValues[$intIterator])) {
					// Set the source array
					$arrSource = $arrValues[$intIterator];
				} else {
					// Set the source array
					$arrSource = explode(',', str_ireplace(', ', ',', $arrValues[$intIterator]));
				}
				// Iterate over the data
				foreach ($arrSource as $strNumber) {
					// Convert it to a hard integer
					array_push($arrNumbers, floatval($strNumber));
				}
				// Set the replacement
				$strReplacement = implode(',', $arrNumbers);
			} elseif ((strtolower($arrMatches[0]) === '%=strlist') || (strtolower($arrMatches[0]) === '%=list')) {
				// Create the placeholder
				$arrEntries     = [];
				// Check the replacements
				if (Core\Is::array($arrValues[$intIterator])) {
					// Set the source array
					$arrSource = $arrValues[$intIterator];
				} else {
					// Set the source array
					$arrSource = explode(',', str_ireplace(', ', ',', $arrValues[$intIterator]));
				}
				// Iterate over the data
				foreach ($arrSource as $strEntry) {
					// Convert it to a hard integer
					array_push($arrEntries, $this->escapeValue($strEntry));
				}
				// Set the replacement
				$strReplacement = implode(',', $arrEntries);
			} else {
				// Set the replacement
				$strReplacement = $this->escapeValue($arrValues[$intIterator]);
			}
			// Increment the iterator
			++$intIterator;
			// Return the replacement
			return $strReplacement;
		}, $strStatement);
		// We're done
		return $strStatement;
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Getters //////////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method returns the connection's access level from the instance
	 * @access public
	 * @name \Crux\Provider\Sql\Engine::getAccess()
	 * @package \Crux\Provider\Sql\Engine
	 * @return int
	 */
	public function getAccess() : int
	{
		// Return the connection's access level from the instance
		return $this->mAccess;
	}

	/**
	 * This method returns the connection's state from the instance
	 * @access public
	 * @name \Crux\Provider\Sql\Engine::getConnectionState()
	 * @package \Crux\Provider\Sql\Engine
	 * @return bool
	 */
	public function getConnectionState() : bool
	{
		// Return the connection flag from the instance
		return $this->mConnectionIsOpen;
	}

	/**
	 * This method returns the database name from the instance
	 * @access public
	 * @name \Crux\Provider\Sql\Engine::getDatabase()
	 * @package \Crux\Provider\Sql\Engine
	 * @return string
	 */
	public function getDatabase() : string
	{
		// Return the database name from the instance
		return $this->mDatabase;
	}

	/**
	 * This method returns the database driver from the instance
	 * @access public
	 * @name \Crux\Provider\Sql\Engine::getDriver()
	 * @package \Crux\Provider\Sql\Engine
	 * @return string
	 */
	public function getDriver() : string
	{
		// Return the driver from the instance
		return $this->mDriver;
	}

	/**
	 * This method returns the connection's data-source name (connection string) from the instance
	 * @access public
	 * @name \Crux\Provider\Sql\Engine::getDsn()
	 * @package \Crux\Provider\Sql\Engine
	 * @return string
	 */
	public function getDsn() : string
	{
		// Return the data-source name from the instance
		return $this->mDsn;
	}

	/**
	 * This method returns the database hostname from the instance
	 * @access public
	 * @name \Crux\Provider\Sql\Engine::getHost()
	 * @package \Crux\Provider\Sql\Engine
	 * @return string
	 */
	public function getHost() : string
	{
		// Return the hostname from the instance
		return $this->mHost;
	}

	/**
	 * This method returns the connection's friendly name from the instance
	 * @access public
	 * @name \Crux\Provider\Sql\Engine::getName()
	 * @package \Crux\Provider\Sql\Engine
	 * @return string
	 */
	public function getName() : string
	{
		// Return the friendly name from the instance
		return $this->mName;
	}

	/**
	 * This method returns the database's authentication password from the instance
	 * @access public
	 * @name \Crux\Provider\Sql\Engine::getPassword()
	 * @package \Crux\Provider\Sql\Engine
	 * @return string
	 */
	public function getPassword() : string
	{
		// Return the password from the instance
		return $this->mPassword;
	}

	/**
	 * This method returns the database host's connection port from the instance
	 * @access public
	 * @name \Crux\Provider\Sql\Engine::getPort()
	 * @package \Crux\Provider\Sql\Engine
	 * @return int
	 */
	public function getPort() : int
	{
		// Return the port from the instance
		return $this->mPort;
	}

	/**
	 * This method returns the database authentication username from the instance
	 * @access public
	 * @name \Crux\Provider\Sql\Engine::getUsername()
	 * @package \Crux\Provider\Sql\Engine
	 * @return string
	 */
	public function getUsername() : string
	{
		// Return the username from the instance
		return $this->mUsername;
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Setters //////////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method sets the connection's access level into the instance
	 * @access public
	 * @name \Crux\Provider\Sql\Engine::setAccess()
	 * @package \Crux\Provider\Sql\Engine
	 * @param int $intLevel
	 * @return \Crux\Provider\Sql\Engine $this
	 */
	public function setAccess(int $intLevel) : Engine
	{
		// Set the access level into the instance
		$this->mAccess = $intLevel;
		// We're done, return the instance
		return $this;
	}

	/**
	 * This method sets the connection's open state into the instance
	 * @access public
	 * @name \Crux\Provider\Sql\Engine::setConnectionState()
	 * @package \Crux\Provider\Sql\Engine
	 * @param bool $blnConnected
	 * @return \Crux\Provider\Sql\Engine $this
	 */
	public function setConnectionState(bool $blnConnected) : Engine
	{
		// Set the connection state into the instance
		$this->mConnectionIsOpen = $blnConnected;
		// We're done, return the instance
		return $this;
	}

	/**
	 * This method sets the database name into the instance
	 * @access public
	 * @name \Crux\Provider\Sql\Engine::setDatabase()
	 * @package \Crux\Provider\Sql\Engine
	 * @param string $strDatabase
	 * @return \Crux\Provider\Sql\Engine $this
	 */
	public function setDatabase(string $strDatabase) : Engine
	{
		// Set the database name into the instance
		$this->mDatabase = $strDatabase;
		// We're done, return the instance
		return $this;
	}

	/**
	 * This method sets the PDO driver into the instance
	 * @access public
	 * @name \Crux\Provider\Sql\Engine::setDriver()
	 * @package \Crux\Provider\Sql\Engine
	 * @param string $strDriver
	 * @return \Crux\Provider\Sql\Engine $this
	 */
	public function setDriver(string $strDriver) : Engine
	{
		// Set the driver into the instance
		$this->mDriver = $strDriver;
		// We're done, return the instance
		return $this;
	}

	/**
	 * This method sets the connection's data-source name (connection string) into the instance
	 * @access public
	 * @name \Crux\Provider\Sql\Engine::setDsn()
	 * @package \Crux\Provider\Sql\Engine
	 * @param string $strConnectionString
	 * @return \Crux\Provider\Sql\Engine $this
	 */
	public function setDsn(string $strConnectionString) : Engine
	{
		// Set the data-source name into the instance
		$this->mDsn = $strConnectionString;
		// We're done, return the instance
		return $this;
	}

	/**
	 * This method sets the connection host name into the instance
	 * @access public
	 * @name \Crux\Provider\Sql\Engine::setHost()
	 * @package \Crux\Provider\Sql\Engine
	 * @param string $strHost
	 * @return \Crux\Provider\Sql\Engine $this
	 */
	public function setHost(string $strHost) : Engine
	{
		// Set the host name into the instance
		$this->mHost = $strHost;
		// We're done, return the instance
		return $this;
	}

	/**
	 * This method sets the connection's friendly name into the instance
	 * @access public
	 * @name \Crux\Provider\Sql\Engine::setName()
	 * @package \Crux\Provider\Sql\Engine
	 * @param string $strFriendlyName
	 * @return \Crux\Provider\Sql\Engine $this
	 */
	public function setName(string $strFriendlyName) : Engine
	{
		// Set the connection's friendly name into the instance
		$this->mName = $strFriendlyName;
		// We're done, return the instance
		return $this;
	}

	/**
	 * This method sets the authentication password into the instance
	 * @access public
	 * @name \Crux\Provider\Sql\Engine::setPassword()
	 * @package \Crux\Provider\Sql\Engine
	 * @param string $strPassword
	 * @return \Crux\Provider\Sql\Engine $this
	 */
	public function setPassword(string $strPassword) : Engine
	{
		// Set the password into the instance
		$this->mPassword = $strPassword;
		// We're done, return the instance
		return $this;
	}

	/**
	 * This method sets the host connection port into the instance
	 * @access public
	 * @name \Crux\Provider\Sql\Engine::setPort()
	 * @package \Crux\Provider\Sql\Engine
	 * @param int $intPort
	 * @return \Crux\Provider\Sql\Engine $this
	 */
	public function setPort(int $intPort) : Engine
	{
		// Set the port into the instance
		$this->mPort = $intPort;
		// We're done, return the instance
		return $this;
	}

	/**
	 * This method sets the authentication username into the instance
	 * @access public
	 * @name \Crux\Provider\Sql\Engine::setUsername()
	 * @package \Crux\Provider\Sql\Engine
	 * @param string $strUsername
	 * @return \Crux\Provider\Sql\Engine $this
	 */
	public function setUsername(string $strUsername) : Engine
	{
		// Set the username into the instance
		$this->mUsername = $strUsername;
		// We're done, return the instance
		return $this;
	}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
} /// End Crux\Provider\Sql\Engine Abstract Class Definition /////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

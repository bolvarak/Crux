<?php

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Crux\Fluent\Sql Namespace ////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

namespace Crux\Fluent\Sql;

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Imports //////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

use Crux\Core;
use Crux\Provider\Sql;
use Crux\Type;
use PDO;

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Crux\Fluent\Sql\Column Class Definition //////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

abstract class Column
{
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Properties ///////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This property contains a flag that tells the instance whether or not the value has changed
	 * @access protected
	 * @name \Crux\Fluent\Sql\Column::$mChanged
	 * @package \Crux\Fluent\Sql\Column
	 * @var bool
	 */
	protected $mChanged = false;

	/**
	 * This property contains the connection instance
	 * @access protected
	 * @name \Crux\Fluent\Sql\Column::$mConnection
	 * @package \Crux\Fluent\Sql\Column
	 * @var string
	 */
	protected $mConnection = Sql\Engine::DEFAULT_CONNECTION;

	/**
	 * This property contains the default value for the column
	 * @access protected
	 * @name \Crux\Fluent\Sql\Column::$mType
	 * @package \Crux\Fluent\Sql\Column
	 * @var mixed|null
	 */
	protected $mDefault = null;

	/**
	 * This property contains the length of the column value
	 * @access protected
	 * @name \Crux\Fluent\Sql\Column::$mLength
	 * @package \Crux\Fluent\Sql\Column
	 * @var int
	 */
	protected $mLength = -1;

	/**
	 * This property contains the PDO parameter type for quoting
	 * @access protected
	 * @name \Crux\Fluent\Sql\Column::$mPdoParam
	 * @package \Crux\Fluent\Sql\Column
	 * @var int
	 */
	protected $mPdoParam = PDO::PARAM_STR;

	/**
	 * This property contains the SQL column type
	 * @access protected
	 * @name \Crux\Fluent\Sql\Column::$mType
	 * @package \Crux\Fluent\Sql\Column
	 * @var string
	 */
	protected $mType = '';

	/**
	 * This property contains the usable  variant value
	 * @access protected
	 * @name \Crux\Fluent\Sql\Column::$mValue
	 * @package \Crux\Fluent\Sql\Column
	 * @var \Crux\Type\Variant
	 */
	protected $mValue;

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Abstract Methods /////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method transforms the PHP type to the SQL type
	 * @abstract
	 * @access public
	 * @name \Crux\Fluent\Sql\Column::transform()
	 * @package \Crux\Fluent\Sql\Column
	 * @return string
	 */
	abstract public function transform() : string;

	/**
	 * This method validates the value in the instance before it is sent to the query
	 * @abstract
	 * @access public
	 * @name \Crux\Fluent\Sql\Column::validate()
	 * @package \Crux\Fluent\Sql\Column
	 * @return bool
	 */
	abstract public function validate() : bool;

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Public Methods ///////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////




	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Inline Methods ///////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method returns the column changed flag from the instance with the ability to reset it inline
	 * @access public
	 * @name \Crux\Fluent\Sql\Column::changed()
	 * @package \Crux\Fluent\Sql\Column
	 * @param bool $blnChanged [null]
	 * @return bool
	 */
	public function changed(bool $blnChanged = null) : bool
	{
		// Check for a provided changed flag
		if (!Core\Is::null($blnChanged)) {
			// Reset the changed flag into the instance
			$this->mChanged = $blnChanged;
		}
		// Return the changed flag from the instance
		return $this->mChanged;
	}

	/**
	 * This method returns the connection instance from the instance with the ability to reset it inline
	 * @access public
	 * @name \Crux\Fluent\Sql\Column::connection()
	 * @package \Crux\Fluent\Sql\Column
	 * @param string $strConnection [null]
	 * @return string
	 */
	public function connection(string $strConnection = null) : string
	{
		// Check for a provided connection instance
		if (!Core\Is::null($strConnection)) {
			// Reset the connection instance into the instance
			$this->mConnection = $strConnection;
		}
		// Return the connection instance from the instance
		return $this->mConnection;
	}

	/**
	 * This method returns the default column value from the instance with the ability to reset it inline
	 * @access public
	 * @name \Crux\Fluent\Sql\Column::default()
	 * @package \Crux\Fluent\Sql\Column
	 * @param mixed $mixDefault [\Crux::NoValue]
	 * @return mixed
	 */
	public function default($mixDefault = \Crux::NoValue)
	{
		// Check for a provided value
		if ($mixDefault !== \Crux::NoValue) {
			// Reset the default value into the instance
			$this->mDefault = $mixDefault;
		}
		// Return the default value from the instance
		return $this->mDefault;
	}

	/**
	 * This method returns the maximum value length from the instance with the ability to reset it inline
	 * @access public
	 * @name \Crux\Fluent\Sql\Column::length()
	 * @package \Crux\Fluent\Sql\Column
	 * @param int $intLength [null]
	 * @return int
	 */
	public function length(int $intLength = null) : int
	{
		// Check for a provided length
		if (!Core\Is::null($intLength)) {
			// Reset the length into the instance
			$this->mLength = $intLength;
		}
		// Return the length from the instance
		return $this->mLength;
	}

	/**
	 * This method returns the PDO param type from the instance with the ability to reset it inline
	 * @access public
	 * @name \Crux\Fluent\Sql\Column::pdoParam()
	 * @package \Crux\Fluent\Sql\Column
	 * @param int $intPdoParam [null]
	 * @return int
	 */
	public function pdoParam(int $intPdoParam = null) : int
	{
		// Check for a provided PDO param
		if (!Core\Is::null($intPdoParam)) {
			// Reset the PDO param into the instance
			$this->mPdoParam = $intPdoParam;
		}
		// Return the PDO param from the instance
		return $this->mPdoParam;
	}

	/**
	 * This method returns the column type from the instance with the ability to reset it inline
	 * @access public
	 * @name \Crux\Fluent\Sql\Column::type()
	 * @package \Crux\Fluent\Sql\Column
	 * @param string $strType [null]
	 * @return string
	 */
	public function type(string $strType = null) : string
	{
		// Check for a provided type
		if (!Core\Is::null($strType)) {
			// Reset the type into the instance
			$this->mType = $strType;
		}
		// Return the type from the instance
		return $this->mType;
	}

	/**
	 * This method returns the column value from the instance with the ability to reset it inline
	 * @access public
	 * @name \Crux\Fluent\Sql\Column::value()
	 * @package \Crux\Fluent\Sql\Column
	 * @param mixed $mixValue [\Crux::NoValue]
	 * @return \Crux\Type\Variant
	 */
	public function value($mixValue = \Crux::NoValue) : Type\Variant
	{
		// Check for a provided value
		if ($mixValue !== \Crux::NoValue) {
			// Reset the value into the instance
			$this->mValue = (Core\Is::variant($mixValue) ? $mixValue : new Type\Variant($mixValue));
		}
		// Return the value from the instance
		return $this->mValue;
	}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
} /// End Crux\Fluent\Sql\Column Class Definition ////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

<?php

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Crux\Serialize Namespace /////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

namespace Crux\Serialize;

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Imports //////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

use Crux\Collection;
use Crux\Core;
use Crux\Type;

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Crux\Serialize\Json Class Definition /////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class Json extends Es
{
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Protected Properties /////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This property contains the serialized output data
	 * @access protected
	 * @name \Crux\Serialize\Json::$mOutput
	 * @package \Crux\Serialize\Json
	 * @var array
	 */
	protected $mOutput = [];

	/**
	 * This property contains the pretty-print flag
	 * @access protected
	 * @name \Crux\Serialize\Json::$mPrettyPrint
	 * @package \Crux\Serialize\Json
	 * @var bool
	 */
	protected $mPrettyPrint = false;

	/**
	 * This property contains the unserialized input data
	 * @access protected
	 * @name \Crux\Serialize\Json::$mSource
	 * @package \Crux\Serialize\Json
	 * @var string
	 */
	protected $mSource = '{}';

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Constructor //////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method instantiates a new JSON serializer object
	 * @access public
	 * @name \Crux\Serialize\Json::__construct()
	 * @package \Crux\Serialize\Json
	 */
	public function __construct()
	{
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Protected Methods ////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method handles any JSON errors that occur
	 * @access protected
	 * @name \Crux\Serialize\Json::error()
	 * @param int $intJsonError
	 * @return void
	 * @throws \Crux\Core\Exception\Serialize\Json
	 * @uses \Crux\Core\Exception\Serialize\Json::__construct()
	 *
	 */
	protected function error(int $intJsonError)
	{
		// Check for no error
		if ($intJsonError === JSON_ERROR_NONE) {
			// We're done
			return;
		}
		// Determine the error
		switch ($intJsonError) {
			case JSON_ERROR_CTRL_CHAR:
				// Reset the message
				$strMessage = 'Control character error, possibly incorrectly encoded';
				// We're done
				break;
			case JSON_ERROR_DEPTH:
				// Reset the message
				$strMessage = 'The maximum stack depth has been exceeded';
				// We're done
				break;
			case JSON_ERROR_INF_OR_NAN:
				// Reset the message
				$strMessage = 'One or more NaN or INF values in the value to be encoded';
				break;
			case JSON_ERROR_RECURSION:
				// Reset the message
				$strMessage = 'One or more recursive references in the value to be encoded';
				// We're done
				break;
			case JSON_ERROR_SYNTAX:
				// Reset the message
				$strMessage = 'There was a JSON syntax error';
				// We're done
				break;
			case JSON_ERROR_UNSUPPORTED_TYPE:
				// Reset the message
				$strMessage = 'A value of a type that cannot be encoded was given';
				// We're done
				break;
			case JSON_ERROR_UTF8:
				// Reset the message
				$strMessage = 'Malformed UTF-8 characters, possibly incorrectly encoded';
				// We're done
				break;
			default:
				// Reset the message
				$strMessage = 'An unknown JSON error occurred';
				// We're done
				break;
		}
		// We're done, throw the exception
		throw new Core\Exception\Serialize\Json($strMessage);
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Implementations //////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method deserializes a JSON string into PHP data
	 * @access public
	 * @name \Crux\Serialize\Json::deserialize()
	 * @package \Crux\Serialize\Json
	 * @param string $strSource
	 * @return \Crux\Serialize\Json $this
	 * @throws \Crux\Core\Exception\Serialize\Json
	 * @uses \Crux\Core\Is
	 * @uses \Crux\Core\Exception\Serialize\Json::__construct()
	 * @uses \Crux\Serialize\Json::error()
	 * @uses json_decode()
	 */
	public function deserialize(string $strSource) : Json
	{
		// Make sure we have a JSON string
		if (!Core\Is::json($strSource)) {
			// Throw the exception
			throw new Core\Exception\Serialize\Json('Unable to deserialize source.  Not in JSON format.');
		}
		// Store the source into the instance
		$this->mSource = $strSource;
		// Deserialize the JSON source
		$this->mOutput = json_decode($strSource, true);
		// Handle any errors
		$this->error(json_last_error());
		// We're done, return the instance
		return $this;
	}

	/**
	 * This method serializes the PHP data into a JSON string
	 * @access public
	 * @name \Crux\Serialize\Json::serialize()
	 * @package \Crux\Serialize\Json
	 * @param mixed $mixSource
	 * @return string
	 * @uses \Crux\Serialize\Json::error()
	 * @uses json_encode()
	 */
	public function serialize($mixSource) : string
	{
		// Check the pretty-print flag
		if ($this->mPrettyPrint) {
			// We're done, return the serialized JSON
			$strJson = json_encode($mixSource, JSON_PRETTY_PRINT);
		} else {
			// We're done, return the serialized JSON
			$strJson =  trim(json_encode($mixSource));
		}
		// Handle any errors
		$this->error(json_last_error());
		// We're done, return the JSON
		return $strJson;
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Public Methods ///////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method tells the serializer to format the output string into a human readable form
	 * @access public
	 * @name \Crux\Serialize\Json::prettyPrint()
	 * @package \Crux\Serialize\Json
	 * @return \Crux\Serialize\Json $this
	 */
	public function prettyOutput() : Json
	{
		// Reset the pretty-print flag
		$this->mPrettyPrint = true;
		// We're done, return the instance
		return $this;
	}

	/**
	 * This method tells the serializer to trim any unnecessary whitespace and control characters from the output string
	 * @access public
	 * @name \Crux\Serialize\Json::trimmedOutput()
	 * @package \Crux\Serialize\Json
	 * @return \Crux\Serialize\Json $this
	 */
	public function trimmedOutput() : Json
	{
		// Reset the pretty-print flag
		$this->mPrettyPrint = false;
		// We're done, return the instance
		return $this;
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Implemented Converters ///////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method returns the deserialized output as a PHP array
	 * @access public
	 * @name \Crux\Serialize\Json::toArray()
	 * @package \Crux\Serialize\Json
	 * @return array<string, mixed>|array<int, mixed>
	 */
	public function toArray() : array
	{
		// We're done, return the array
		return $this->mOutput;
	}

	/**
	 * This method converts the deserialized output into a PHireworks Collection
	 * @access public
	 * @name \Crux\Serialize\Json::toCollection()
	 * @package \Crux\Serialize\Json
	 * @return \Crux\Collection\Map|\Crux\Collection\Vector
	 * @uses \Crux\Core\Is::associativeArray()
	 * @uses \Crux\Collection\Map::fromArray()
	 * @uses \Crux\Collection\Vector::fromArray()
	 */
	public function toCollection()
	{
		// Check the data type
		if (Core\Is::associativeArray($this->mOutput)) {
			// Return the Map
			return Collection\Map::fromArray($this->mOutput);
		} else {
			// Return the Vector
			return Collection\Vector::fromArray($this->mOutput);
		}
	}

	/**
	 * This method converts the deserialized output back into a JSON string
	 * @access public
	 * @name \Crux\Serialize\Json::toJson()
	 * @package \Crux\Serialize\Json
	 * @param bool $blnPrettyPrint [false]
	 * @return string
	 * @uses \Crux\Serialize\Json::__construct()
	 * @uses \Crux\Serialize\Json::prettyOutput()
	 * @uses \Crux\Serialize\Json::trimmedOutput()
	 * @uses \Crux\Serialize\Json::serialize()
	 */
	public function toJson(bool $blnPrettyPrint = false) : string
	{
		// Instantiate the new JSON object
		$clsJson = new self();
		// Check the pretty print flag
		if ($blnPrettyPrint) {
			// Turn pretty printing on
			$clsJson->prettyOutput();
		} else {
			// Turn pretty printing off
			$clsJson->trimmedOutput();
		}
		// We're done, return the JSON string
		return $clsJson->serialize($this->mOutput);
	}

	/**
	 * This method returns the deserialized output as a PHP stdClass
	 * @access public
	 * @name \Crux\Serialize\Json::toObject()
	 * @package \Crux\Serialize\Json
	 * @return \stdClass
	 * @uses json_encode()
	 */
	public function toObject() : \stdClass
	{
		// We're done, return the class object
		return json_encode($this->mSource);
	}

	/**
	 * This method converts the deserialized output into a PHireworks Variant
	 * @access public
	 * @name \Crux\Serialize\Json::toVariant()
	 * @package \Crux\Serialize\Json
	 * @return \Crux\Type\Variant|\Crux\Type\VariantList|\Crux\Type\Map
	 * @uses \Crux\Core\Is::associativeArray()
	 * @uses \Crux\Core\Is::sequentialArray()
	 * @uses \Crux\Type\Variant::Factory()
	 * @uses \Crux\Type\VariantList::fromArray()
	 * @uses \Crux\Type\Map::fromArray()
	 */
	public function toVariant()
	{
		// Check the data type
		if (Core\Is::associativeArray($this->mOutput)) {
			// Return the VariantMap
			return Type\Map::fromArray($this->mOutput);
		} else if (Core\Is::sequentialArray($this->mOutput)) {
			// Return the VariantList
			return Type\VariantList::fromArray($this->mOutput);
		} else {
			// Return the Variant
			return Type\Variant::Factory($this->mOutput);
		}
	}

	/**
	 * This method converts the deserialized output into an XML string
	 * @access public
	 * @name \Crux\Serialize\Json::toXml()
	 * @pacage \Crux\Serialize\Json
	 * @param bool $blnPrettyPrint [false]
	 * @return string
	 * @uses \Crux\Serialize\Xml::__construct()
	 * @uses \Crux\Serialize\Xml::prettyOutput()
	 * @uses \Crux\Serialize\Xml::trimmedOutput()
	 * @uses \Crux\Serialize\Xml::serialize()
	 */
	public function toXml(bool $blnPrettyPrint = false) : string
	{
		// Instantiate the XML serializer
		$clsXml = new Xml();
		// Check the pretty print flag
		if ($blnPrettyPrint) {
			// Turn pretty printing on
			$clsXml->prettyOutput();
		} else {
			// Turn pretty printing off
			$clsXml->trimmedOutput();
		}
		// We're done, return the XML string
		return $clsXml->serialize($this->mOutput);
	}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
} /// End Crux\Serializable\Json Class Definition ////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

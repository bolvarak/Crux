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
use Crux\Grammar;
use Crux\Type;

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Crux\Serialize\Xml Class Definition //////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class Xml extends Es
{
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Protected Properties /////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This property contains the name for array/list nodes
	 * @access protected
	 * @name \Crux\Serialize\Xml::$mChildListNode
	 * @package \Crux\Serialize\Xml
	 * @var string
	 */
	protected $mChildListNode = 'item';

	/**
	 * This property tells the serializer whether or not to define the document as XML
	 * @access protected
	 * @name \Crux\Serialize\Xml::$mDefineDocument
	 * @package \Crux\Serialize\Xml
	 * @var bool
	 */
	protected $mDefineDocument = true;

	/**
	 * This property contains the headers for the XML definition
	 * @access protected
	 * @name \Crux\Serialize\Xml::$mHeaders
	 * @package \Crux\Serialize\Xml
	 * @var array<string, string>
	 */
	protected $mHeaders = [
		'version' => '1.0',
		'encoding' => 'UTF-8'
	];

	/**
	 * This property contains the deserialized output
	 * @access protected
	 * @name \Crux\Serialize\Xml::$mOutput
	 * @package \Crux\Serialize\Xml
	 * @var array<string, mixed>
	 */
	protected $mOutput = [];

	/**
	 * This property contains the pretty-print flag
	 * @access protected
	 * @name \Crux\Serialize\Xml::$mPrettyPrint
	 * @package \Crux\Serialize\Xml
	 * @var bool
	 */
	protected $mPrettyPrint = false;

	/**
	 * This property contains the name for the root node if the source input is a list type
	 * @access protected
	 * @name \Crux\Serialize\Xml::$mRootNode
	 * @package \Crux\Serialize\Xml
	 * @var string
	 */
	protected $mRootNode = 'payload';

	/**
	 * This property contains the serialized source input
	 * @access protected
	 * @name \Crux\Serialize\Xml::$mSource
	 * @package \Crux\Serialize\Xml
	 * @var string
	 */
	protected $mSource = '<?xml version="1.0">';

	/**
	 * This property contains the current XML build string
	 * @access protected
	 * @name \Crux\Serialize\Xml::$mXmlBuild
	 * @package \Crux\Serialize\Xml
	 * @var string
	 */
	protected $mXmlBuild = '';

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Constructor //////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method instantiates a new XML serializer object
	 * @access public
	 * @name \Crux\Serialize\Xml::__construct()
	 * @package \Crux\Serialize\Xml
	 */
	public function __construct()
	{}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Protected Methods ////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method defines the document as XML
	 * @access protected
	 * @name \Crux\Serialize\Xml::documentDefinition()
	 * @package \Crux\Serialize\Xml
	 * @return \Crux\Serialize\Xml $this
	 */
	protected function documentDefinition() : Xml
	{
		// Start the XML
		$this->mXmlBuild = '<?xml';
		// Iterate over the headers
		foreach ($this->mHeaders as $strKey => $mixValue) {
			// Append to the header
			$this->mXmlBuild .= sprintf(' %s="%s"', $strKey, (string) $mixValue);
		}
		// Finalize the document definition
		$this->mXmlBuild .= ' ?>';
		// We're done, return the instance
		return $this;
	}

	/**
	 * This method processes a node and serializes it into XML
	 * @access protected
	 * @name \Crux\Serialize\Xml::processNode()
	 * @package \Crux\Serialize\Xml
	 * @param mixed $mixData
	 * @param string|null $strNode [null]
	 * @return \Crux\Serialize\Xml $this
	 * @uses \Crux\Core\Is::null()
	 * @uses \Crux\Core\Is::map()
	 * @uses \Crux\Core\Is::vector()
	 * @uses \Crux\Core\Is::variantList()
	 * @uses \Crux\Core\Is::variantMap()
	 * @uses \Crux\Core\Is::variant()
	 * @uses \Crux\Core\Is::associativeArray()
	 * @uses \Crux\Core\Is::sequentialArray()
	 * @uses \Crux\Core\Is::object()
	 * @uses \Crux\Core\Is::boolean()
	 * @uses \Crux\Serialize\Xml::processNode()
	 * @uses \Crux\Serialize\Xml::serializeAssociativeArray()
	 * @uses \Crux\Serialize\Xml::serializeSequentialArray()
	 * @uses \Crux\Serialize\Xml::serializeObject()
	 * @uses \Crux\Serialize\Xml::serializeScalar()
	 * @uses sprintf()
	 * @uses preg_match()
	 */
	protected function processNode($mixData, string $strNode = null) : Xml
	{
		// Check for a root node
		if (Core\Is::null($strNode)) {
			// Reset the node name
			$strNode = $this->mRootNode;
		}
		// Check the data type
		if (Core\Is::a('\\Crux\\Serialize\\Able', $mixData)) {
			// Append to the XML string
			$this->mXmlBuild .= $mixData->toXml(false, $strNode, $this->mChildListNode);
		} elseif (Core\Is::map($mixData)) {
			// Append to the XML string
			$this->mXmlBuild .= $mixData->toXml($strNode, false);
		} elseif (Core\Is::vector($mixData)) {
			// Append to the XML string
			$this->mXmlBuild .= $mixData->toXml($strNode, false, $strNode);
		} elseif (Core\Is::variantList($mixData)) {
			// Append to the XML string
			$this->mXmlBuild .= $mixData->toXml($strNode, false);
		} elseif (Core\Is::variantMap($mixData)) {
			// Append to the XML string
			$this->mXmlBuild .= $mixData->toXml($strNode, false, $strNode);
		} elseif (Core\Is::variant($mixData)) {
			// Encode the Variant
			$this->processNode($mixData->getData(), $strNode);
		} elseif (Core\Is::associativeArray($mixData)) {
			// Encode the associative array
			$this->serializeAssociativeArray($mixData, $strNode);
		} elseif (Core\Is::sequentialArray($mixData)) {
			// Encode the sequential array
			$this->serializeSequentialArray($mixData, $strNode);
		} elseif (Core\Is::object($mixData)) {
			// Encode the object
			$this->serializeObject($mixData, $strNode);
		} else {
			// Serialize the scalar value
			$this->serializeScalar($mixData, $strNode);
		}
		// We're done, return the instance
		return $this;
	}

	/**
	 * This method serializes a PHP associative array into XML
	 * @access protected
	 * @name \Crux\Serialize\Xml::serializeAssociativeArray()
	 * @pacakge \Crux\Serialize\Xml
	 * @param array<string, mixed> $arrData
	 * @param string $strNode
	 * @return \Crux\Serialize\Xml $this
	 * @uses \Crux\Core\Is::scalar()
	 * @uses \Crux\Serialize\Xml::serializeScalar()
	 * @uses \Crux\Serialize\Xml::processNode()
	 * @uses sprintf()
	 */
	protected function serializeAssociativeArray(array $arrData, string $strNode) : Xml
	{
		// Append to the XML
		$this->mXmlBuild .= sprintf('<%s type="struct">', $strNode);
		// Iterate over the array
		foreach ($arrData as $strKey => $mixValue) {
			// Check for a scalar
			if (Core\Is::scalar($mixValue)) {
				// Append the scalar to the XML
				$this->serializeScalar($mixValue, $strKey);
			} else {
				// Process the child node
				$this->processNode($mixValue, $strKey);
			}
		}
		// Finalize the node
		$this->mXmlBuild .= sprintf('</%s>', $strNode);
		// We're done, return the instance
		return $this;
	}

	/**
	 * This method serializes a class or object into XML
	 * @access protected
	 * @name \Crux\Serialize\Xml::serializeObject()
	 * @package \Crux\Serialize\Xml
	 * @param \stdClass|object $objData
	 * @param string $strNode
	 * @return \Crux\Serialize\Xml $this
	 * @uses \Crux\Core\Is::scalar()
	 * @uses \Crux\Serialize\Xml::serializeScalar()
	 * @uses \Crux\Serialize\Xml::processNode()
	 * @uses sprintf()
	 * @uses get_object_vars()
	 */
	protected function serializeObject($objData, string $strNode) : Xml
	{
		// Append to the XML
		$this->mXmlBuild .= sprintf('<%s type="object">', $strNode);
		// Iterate over the object's properties
		foreach (get_object_vars($objData) as $strName => $mixValue) {
			// Check the type
			if (Core\Is::scalar($mixValue)) {
				// Encode and append the scalar to the XML string
				$this->serializeScalar($mixValue, $strName);
			} else {
				// Process the child node
				$this->processNode($mixValue, $strName);
			}
		}
		// Finalize the node
		$this->mXmlBuild .= sprintf('</%s>', $strNode);
		// We're done return the instance
		return $this;
	}

	/**
	 * This method serializes a scalar (bool, double, float, int, null, string) into XML
	 * @access protected
	 * @name \Crux\Serialize\Xml::serializeScalar()
	 * @package \Crux\Serialize\Xml
	 * @param mixed $mixData
	 * @param string $strNode
	 * @return \Crux\Serialize\Xml $this
	 * @uses \Crux\Core\Is::boolean()
	 * @uses \Crux\Core\Is::null()
	 * @uses preg_match()
	 * @uses sprintf()
	 * @uses gettype()
	 */
	protected function serializeScalar($mixData, string $strNode) : Xml
	{
		// Check for nested HTML
		if (preg_match('/(\<|\>|%|&|"|\')/', $mixData)) {
			// Escape the data and append it to the XML
			$this->mXmlBuild .= sprintf('<%s type="string"><![CDATA[%s]]></%s>', $strNode, $mixData, $strNode);
		} elseif (Core\Is::string($mixData) && preg_match('/^{(.*?)}\$([0-9]+)\$(.*?)$/i', $mixData)) {
			// Append to the XML
			$this->mXmlBuild .= sprintf('<%s type="hash">%s</%s>', $strNode, $mixData, $strNode);
		} elseif (Core\Is::boolean($mixData)) {
			// Append to the XML
			$this->mXmlBuild .= sprintf('<%s type="boolean">%s</%s>', $strNode, ($mixData ? 'true' : 'false'), $strNode);
		} elseif (Core\Is::null($mixData)) {
			// Append to the XML
			$this->mXmlBuild .= sprintf('<%s type="nil" />', $strNode);
		} else {
			// Append to the XML
			$this->mXmlBuild .= sprintf('<%s type="%s">%s</%s>', $strNode, gettype($mixData), $mixData, $strNode);
		}
		// We're done, return the instance
		return $this;
	}

	/**
	 * This method serializes a sequential array (array<int, mixed>) into XML
	 * @access protected
	 * @name \Crux\Serialize\Xml::serializeSequentialArray()
	 * @package \Crux\Serialize\Xml
	 * @param array<int, mixed> $arrData
	 * @param string $strNode
	 * @return \Crux\Serialize\Xml $this
	 * @uses \Crux\Core\Is::scalar()
	 * @uses \Crux\Grammar\Inflection::pluralize()
	 * @uses \Crux\Grammar\Inflection::singularize()
	 * @uses \Crux\Serialize\Xml::serializeScalar()
	 * @uses \Crux\Serialize\Xml::processNode()
	 */
	protected function serializeSequentialArray(array $arrData, string $strNode) : Xml
	{
		// Pluralize the node name
		$strNode = Grammar\Inflection::pluralize($strNode);
		// Append to the XML
		$this->mXmlBuild .= sprintf('<%s type="array">', $strNode);
		// Iterate over the array
		foreach ($arrData as $mixValue) {
			// Check the data type
			if (Core\Is::scalar($mixValue)) {
				// Serialize the scalar value
				$this->serializeScalar($mixValue, Grammar\Inflection::singularize($strNode));
			} else {
				// Process the child node
				$this->processNode($mixValue, Grammar\Inflection::singularize($strNode));
			}
		}
		// Finalize the node
		$this->mXmlBuild .= sprintf('</%s>', $strNode);
		// We're done, return the instance
		return $this;
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Public Methods ///////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method adds a header to the XML definition
	 * @access public
	 * @name \Crux\Serialize\Xml::addHeader()
	 * @package \Crux\Serialize\Xml
	 * @param string $strName
	 * @param mixed $mixValue
	 * @return \Crux\Serialize\Xml $this
	 */
	public function addHeader(string $strName, $mixValue) : Xml
	{
		// Add the header to the instance
		$this->mHeaders[$strName] = (string)$mixValue;
		// We're done, return the instance
		return $this;
	}

	/**
	 * This method sets the name for the child list nodes into the instance
	 * @access public
	 * @name \Crux\Serialize\Xml::childListNode()
	 * @package \Crux\Serialize\Xml
	 * @param string $strName
	 * @return \Crux\Serialize\Xml $this
	 */
	public function childListNode(string $strName) : Xml
	{
		// Reset the child list node name into the instance
		$this->mChildListNode = $strName;
		// We're done, return the instance
		return $this;
	}

	/**
	 * This method tells the serialize TO define the document as XML
	 * @access public
	 * @name \Crux\Serialize\Xml::defineDocument()
	 * @package \Crux\Serialize\Xml
	 * @return \Crux\Serialize\Xml $this
	 */
	public function defineDocument() : Xml
	{
		// Reset the document definition flag
		$this->mDefineDocument = true;
		// We're done, return the instance
		return $this;
	}

	/**
	 * This method tells the serializer NOT TO define the document as XML
	 * @access public
	 * @name \Crux\Serialize\Xml::dontDefineDocument()
	 * @package \Crux\Serialize\Xml
	 * @return \Crux\Serialize\Xml $this
	 */
	public function dontDefineDocument() : Xml
	{
		// Reset the document definition flag
		$this->mDefineDocument = false;
		// We're done, return the instance
		return $this;
	}

	/**
	 * This method turns pretty printing on for the output XML string
	 * @access public
	 * @name \Crux\Serialize\Xml::prettyOutput()
	 * @package \Crux\Serialize\Xml
	 * @return \Crux\Serialize\Xml $this
	 */
	public function prettyOutput() : Xml
	{
		// Reset the pretty-print flag
		$this->mPrettyPrint = true;
		// We're done, return the instance
		return $this;
	}

	/**
	 * This method sets the name of the root node into the instance
	 * @access public
	 * @name \Crux\Serialize\Xml::rootNode()
	 * @package \Crux\Serialize\Xml
	 * @param string $strName
	 * @return \Crux\Serialize\Xml $this
	 */
	public function rootNode(string $strName) : Xml
	{
		// Reset the root node name into the instance
		$this->mRootNode = $strName;
		// We're done, return the instance
		return $this;
	}

	/**
	 * This method turns pretty printing off for the output XML string
	 * @access public
	 * @name \Crux\Serialize\Xml::trimmedOutput()
	 * @package \Crux\Serialize\Xml
	 * @return \Crux\Serialize\Xml $this
	 */
	public function trimmedOutput() : Xml
	{
		// Reset the pretty-print flag
		$this->mPrettyPrint = false;
		// We're done, return the instance
		return $this;
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Implementations //////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method deserializes an XML string
	 * @access public
	 * @name \Crux\Serialize\Xml::deserialize()
	 * @package \Crux\Serialize\Xml
	 * @param string $strSource
	 * @return \Crux\Serialize\Xml $this
	 * @throws \Crux\Core\Exception\Serialize\Xml
	 * @uses \Crux\Core\Is::xml()
	 * @uses \Crux\Core\Is::string()
	 * @uses \Crux\Core\Exception\Serialize\Xml::__construct()
	 * @uses preg_replace_callback()
	 * @uses htmlspecialchars()
	 * @uses trim()
	 * @uses simplexml_load_string()
	 * @uses json_encode()
	 * @uses json_decode()
	 * @uses array_walk_recursive()
	 * @uses htmlspecialchars_decode()
	 */
	public function deserialize(string $strSource) : Xml
	{
		// Check the source for XML
		if (!Core\Is::xml($strSource)) {
			// Throw the exception
			throw new Core\Exception\Serialize\Xml('Unable to deserialize source.  Not in XML format.');
		}
		// Set the source into the instance
		$this->mSource = $strSource;
		// Remove the CDATA
		$strSource = preg_replace_callback('/<!\[CDATA\[(.*)\]\]>/i', function (array $arrMatches) {
			// Return the encoded string
			return trim(htmlspecialchars($arrMatches[1]));
		}, $strSource);
		// Decode the XML and return it
		$arrData = json_decode(json_encode(simplexml_load_string($strSource)), true);
		// Decode the HTML
		array_walk_recursive($arrData, function (&$mixValue) {
			// Check for an array
			if (Core\Is::string($mixValue)) {
				// Decode the string
				htmlspecialchars_decode($mixValue);
			}
		});
		// Set the output into the instance
		$this->mOutput = $arrData;
		// We're done, return the instance
		return $this;
	}

	/**
	 * This method serializes PHP and PHireworks data into an XML string
	 * @access public
	 * @name \Crux\Serialize\Xml::serialize()
	 * @package \Crux\Serialize\Xml
	 * @param mixed $mixSource
	 * @return string
	 * @uses \Crux\Serialize\Xml::documentDefinition()
	 * @uses \Crux\Serialize\Xml::processNode()
	 * @uses \DOMDocument::__construct()
	 * @uses \DOMDocument::loadXML()
	 * @uses \DOMDocument::saveXML()
	 */
	public function serialize($mixSource) : string
	{
		// Check the document definition flag
		if ($this->mDefineDocument) {
			// Define the document
			$this->documentDefinition();
		}
		// Start processing the XML
		$this->processNode($mixSource);
		// Check for pretty print
		if ($this->mPrettyPrint) {
			// Create the DOM document
			$domXml = new \DOMDocument();
			// Load the XML string
			$domXml->loadXML($this->mXmlBuild);
			// Preserve the whitespace
			$domXml->preserveWhiteSpace = true;
			// We want the output formatted
			$domXml->formatOutput = true;
			// Reset the XML string
			$this->mXmlBuild = $domXml->saveXML();
		}
		// We're done, return the XML string
		return trim($this->mXmlBuild);
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Implemented Converters ///////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method converts the output data into a PHP array
	 * @access public
	 * @name \Crux\Serialize\Xml::toArray()
	 * @package \Crux\Serialize\Xml
	 * @return array<string, mixed>|array<int, mixed>
	 */
	public function toArray() : array
	{
		// We're done, return the output
		return $this->mOutput;
	}

	/**
	 * This method converts the output data into a PHireworks collection
	 * @access public
	 * @name \Crux\Serialize\Xml::toCollection()
	 * @package \Crux\Serialize\Xml
	 * @return \Crux\Collection\Map|\Crux\Collection\Vector
	 * @uses \Crux\Core\Is::associativeArray()
	 * @uses \Crux\Collection\Map::fromArray()
	 * @uses \Crux\Collection\Vector::fromArray()
	 */
	public function toCollection()
	{
		// Check the data type
		if (Core\Is::associativeArray($this->mOutput)) {
			// Return the new map
			return Collection\Map::fromArray($this->mOutput);
		} elseif (Core\Is::empty($this->mOutput)) {
			// Return an empty map
			return new Collection\Map();
		} else {
			// Return the new vector
			return Collection\Vector::fromArray($this->mOutput);
		}
	}

	/**
	 * This method converts the output data into a JSON string
	 * @access public
	 * @name \Crux\Serialize\Xml::toJson()
	 * @package \Crux\Serialize\Xml
	 * @param bool $blnPrettyPrint [false]
	 * @return string
	 * @uses \Crux\Serialize\Json::__construct()
	 * @uses \Crux\Serialize\Json::prettyOutput()
	 * @uses \Crux\Serialize\Json::trimmedOutput()
	 * @uses \Crux\Serialize\Json::serialize()
	 */
	public function toJson(bool $blnPrettyPrint = false) : string
	{
		// Instantiate the JSON serializer
		$clsJson = new Json();
		// Check the pretty-print flag
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
	 * This method converts the output data into a standard PHP object
	 * @access public
	 * @name \Crux\Serialize\Xml::toObject()
	 * @package \Crux\Serialize\Xml
	 * @return \stdClass
	 * @uses json_encode()
	 * @uses json_decode()
	 */
	public function toObject() : \stdClass
	{
		// Return the object
		return json_decode(json_encode($this->mOutput));
	}

	/**
	 * This method converts the output data into a PHireworks variant
	 * @access public
	 * @name \Crux\Serialize\Xml::toVariant()
	 * @package \Crux\Serialize\Xml
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
			// Return the new variant map
			return Type\Map::fromArray($this->mOutput);
		} elseif (Core\Is::sequentialArray($this->mOutput)) {
			// Return the new variant list
			return Type\VariantList::fromArray($this->mOutput);
		} else {
			// Return the new variant scalar
			return Type\Variant::Factory($this->mOutput);
		}
	}

	/**
	 * This method converts the output data back into XML
	 * @access public
	 * @name \Crux\Serialize\Xml::toXml()
	 * @package \Crux\Serialize\Xml
	 * @param bool $blnPrettyPrint [false]
	 * @return string
	 * @uses \Crux\Serialize\Xml::__construct()
	 * @uses \Crux\Serialize\Xml::prettyOutput()
	 * @uses \Crux\Serialize\Xml::trimmedOutput()
	 * @uses \Crux\Serialize\Xml::serialize()
	 */
	public function toXml(bool $blnPrettyPrint = false) : string
	{
		// Instantiate a new instance of ourselves
		$clsXml = new self();
		// Check the pretty-print flag
		if ($blnPrettyPrint) {
			// Turn pretty printing on
			$clsXml->prettyOutput();
		} else {
			// Turn pretty printing off
			$clsXml->trimmedOutput();
		}
		// WE're done, return the XML string
		return $clsXml->serialize($this->mOutput);
	}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
} /// End Crux\Serialize\Xml Class Definition ////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

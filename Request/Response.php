<?php

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Crux\Request Namespace ///////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

namespace Crux\Request;

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Imports //////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

use Crux\Core;
use Crux\Markup;
use Crux\Serialize;
use Crux\Type;

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Crux\Request\Response Class Definition ///////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class Response
{
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Response Type Constants //////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This constant defines a bad response
	 * @name \Crux\Request\Response::Bad
	 * @package \Crux\Request\Response
	 * @var int
	 */
	const Bad = 0x01;

	/**
	 * This constant defines a good response
	 * @name \Crux\Request\Response::Good
	 * @package \Crux\Request\Response
	 * @var int
	 */
	const Good = 0x02;

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Response Format Constants ////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This constant defines a CSS response type
	 * @name \Crux\Request\Response::CSS
	 * @package \Crux\Request\Response
	 * @var int
	 */
	const CSS = 0x00;

	/**
	 * This constant defines an HTML response type
	 * @name \Crux\Request\Response::HTML
	 * @package \Crux\Request\Response
	 * @var int
	 */
	const HTML = 0x01;

	/**
	 * This constant defines a JS response type
	 * @name \Crux\Request\Response::JAVASCRIPT
	 * @package \Crux\Request\Response
	 * @var int
	 */
	const JAVASCRIPT = 0x02;

	/**
	 * This constant defines a JSON response type
	 * @name \Crux\Request\Response::JSON
	 * @package \Crux\Request\Response
	 * @var int
	 */
	const JSON = 0x03;

	/**
	 * This constant defines a JSON persistent response type
	 * @name \Crux\Request\Response::JSONP
	 * @package \Crux\Request\Response
	 * @var int
	 */
	const JSONP = 0x04;

	/**
	 * This constant defines a PDF response type
	 * @name \Crux\Request\Response::PDF
	 * @package \Crux\Request\Response
	 * @var int
	 */
	const PDF = 0x05;

	/**
	 * This constant defines a PHP response type
	 * @name \Crux\Request\Response::PHP
	 * @package \Crux\Request\Response
	 * @var int
	 */
	const PHP = 0x06;

	/**
	 * This constant defines a plain text response type
	 * @name \Crux\Request\Response::TEXT
	 * @package \Crux\Request\Response
	 * @var int
	 */
	const TEXT = 0x07;

	/**
	 * This constant defines an XML response type
	 * @name \Crux\Request\Response::XML
	 * @package \Crux\Request\Response
	 * @var int
	 */
	const XML = 0x08;

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Protected Properties /////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This property contains the content to send if using traditional MVC
	 * @access protected
	 * @name \Crux\Request\Response::$mContent
	 * @package \Crux\Request\Response
	 * @var string
	 */
	protected $mContent = '';

	/**
	 * This property contains the filename for conten disposition
	 * @access protected
	 * @name \Crux\Request\Response::$mFileName
	 * @package \Crux\Request\Response
	 * @var string
	 */
	protected $mFileName = '';

	/**
	 * This property contains the serialization definition for the response
	 * @access protected
	 * @name \Crux\Request\Response::$mFormat
	 * @package \Crux\Request\Response
	 * @var int
	 */
	protected $mFormat = self::JSON;

	/**
	 * This property contains the response type
	 * @access protected
	 * @name \Crux\Request\Response::$mResponseType
	 * @package \Crux\Request\Response
	 * @var int
	 */
	protected $mResponseType = self::Good;

	/**
	 * This property contains the values to send in the response
	 * @access protected
	 * @name \Crux\Request\Response::$mValues
	 * @package \Crux\Request\Response
	 * @var array<string, mixed>
	 */
	protected $mValues = [];

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Constructor //////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method instantiates a new request response object
	 * @access public
	 * @name \Crux\Request\Response::__construct()
	 * @package \Crux\Request\Response
	 */
	public function __construct()
	{
		// Reset the default format
		$this->mFormat = self::JSON;
		// Reset the content
		$this->mContent = '';
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Protected Methods ////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method encodes the values into a JSON response
	 * @access protected
	 * @name \Crux\Request\Response::json()
	 * @package \Crux\Request\Response
	 * @return string
	 * @uses \Crux\Type\VariantMap::toJson()
	 */
	protected function json() : string
	{
		// Instantiate our serializer
		$jsonSerializer = new Serialize\Json();
		// We want pretty printing
		$jsonSerializer->prettyOutput();
		// Return the JSON version of the values
		return $jsonSerializer->serialize($this->mValues);
	}

	/**
	 * This method encodes the values into a JSON persistent response
	 * @access protected
	 * @name \Crux\Request\Response::jsonp()
	 * @package \Crux\Request\Response
	 * @return string
	 * @uses \Crux\Request\Router::request()
	 * @uses \Crux\Request\Container::get()
	 * @uses \Crux\Request\Response::json()
	 * @uses \Crux\Type\Variant::toString()
	 * @uses sprintf()
	 */
	protected function jsonp() : string
	{
		// Return the JSONP version of the values
		return sprintf('/**/%stypeof %s === \'function\' && %s(%s);', PHP_EOL, Router::request()->get('callback')->toString(), Router::request()->get('callback')->toString(), $this->json());
	}

	/**
	 * This method encodes the values into a PHP serialized string response
	 * @access protected
	 * @name \Crux\Request\Response::php()
	 * @package \Crux\Request\Response
	 * @return string
	 * @uses \Crux\Type\VariantMap::toMap()
	 * @uses \Crux\Collection\Map::toArray()
	 * @uses serialize()
	 */
	protected function php() : string
	{
		// Return the serialized version of the values
		return serialize($this->mValues);
	}

	/**
	 * This method sends the response headers based on the response format
	 * @access protected
	 * @name \Crux\Request\Response::sendHeaders()
	 * @package \Crux\Request\Response
	 * @return void
	 * @uses header()
	 */
	protected function sendHeaders()
	{
		// Switch on the response format
		switch ($this->mFormat) {
			case self::CSS        :
				header('Content-Type:  text/css; Charset="UTF-8"');
				break; // CSS
			case self::JAVASCRIPT :
				header('Content-Type:  text/javascript; Charset="UTF-8"');
				break; // JavaScript
			case self::JSON       :
				header('Content-Type:  application/json; Charset="UTF-8"');
				break; // JSON
			case self::JSONP      :
				header('Content-Type:  text/javascript; Charset="UTF-8"');
				break; // JSONP
			case self::PDF        :
				header('Content-Type:  application/pdf');
				break; // PDF
			case self::PHP        :
				header('Content-Type:  text/php; Charset="UTF-8"');
				break; // PHP
			case self::TEXT       :
				header('Content-Type:  text/plain; Charset="UTF-8"');
				break; // Text
			case self::XML        :
				header('Content-Type:  text/xml; Charset="UTF-8"');
				break; // XML
			default                        :
				header('Content-Type:  text/html; Charset="UTF-8"');
				break; // Default
		}
	}

	/**
	 * This method encodes the values into an XML response
	 * @access protected
	 * @name \Crux\Request\Response::xml()
	 * @package \Crux\Request\Response
	 * @param string $strRootNode [null]
	 * @return string
	 * @uses \Crux\Core\Is::null()
	 * @uses \Crux\Request\Router::xmlRootNode()
	 * @uses \Crux\Serialize\Xml::__construct()
	 * @uses \Crux\Serialize\Xml::rootNode()
	 * @uses \Crux\Serialize\Xml::defineDocument()
	 * @uses \Crux\Serialize\Xml::prettyOutput()
	 * @uses \Crux\Serialize\Xml::serialize()
	 */
	protected function xml(string $strRootNode = null) : string
	{
		// Check for a root node
		if (Core\Is::null($strRootNode)) {
			// Reset the root node
			$strRootNode = Router::xmlRootNode();
		}
		// Instantiate our serializer
		$xmlSerializer = new Serialize\Xml();
		// Set the root node
		$xmlSerializer->rootNode($strRootNode);
		// We want document definition
		$xmlSerializer->defineDocument();
		// We want pretty printing
		$xmlSerializer->prettyOutput();
		// Return the XML version of the values
		return $xmlSerializer->serialize($this->mValues);
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Debugging Converters /////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method converts the response to JSON without sending the content to the browser
	 * @access public
	 * @name \Crux\Request\Response::toJson()
	 * @package \Crux\Request\Response
	 * @return string
	 * @uses \Crux\Core\Is::empty()
	 * @uses \Crux::coreBlock()
	 * @uses \Crux\Request\Response::format()
	 * @uses \Crux\Request\Response::json()
	 * @uses \Crux::coreBlock()
	 * @uses in_array()
	 * @uses ini_set()
	 * @uses ob_start()
	 * @uses ob_get_clean()
	 * @uses json_encode()
	 */
	public function toJson() : string
	{
		// Check the format
		if (in_array($this->mFormat, [self::CSS, self::HTML, self::JAVASCRIPT, self::PDF, self::TEXT])) {
			// Check for content
			if (Core\Is::empty($this->mContent)) {
				// Turn HTML errors on
				ini_set('html_errors', true);
				// Start the output buffer
				ob_start();
				// Load the block
				require_once(\Crux::coreBlock('dump.phtml'));
				//
				// We're done, send the response
				return json_encode([
					'html' => ob_get_clean()
				]);
			} else {
				// We're done, send the response
				return json_encode([
					'html' => $this->mContent
				]);
			}
		} else {
			// We're done, send the response
			return $this->json();
		}
	}

	/**
	 * This method converts the response to XML without sending the content to the browser
	 * @access public
	 * @name \Crux\Request\Response::toJson()
	 * @package \Crux\Request\Response
	 * @return string
	 * @uses \Crux\Core\Is::empty()
	 * @uses \Crux::coreBlock()
	 * @uses \Crux\Request\Response::format()
	 * @uses \Crux\Request\Response::xml()
	 * @uses \Crux\Core\Is::null()
	 * @uses \Crux\Request\Router::xmlRootNode()
	 * @uses \Crux::coreBlock()
	 * @uses \Crux\Serialize\Xml::__construct()
	 * @uses \Crux\Serialize\Xml::rootNode()
	 * @uses \Crux\Serialize\Xml::defineDocument()
	 * @uses \Crux\Serialize\Xml::prettyOutput()
	 * @uses \Crux\Serialize\Xml::serialize()
	 * @uses in_array()
	 * @uses ini_set()
	 * @uses ob_start()
	 * @uses ob_get_clean()
	 */
	public function toXml(string $strRootNode = null) : string
	{
		// Check for a root node
		if (Core\Is::null($strRootNode)) {
			// Reset the root node
			$strRootNode = Router::xmlRootNode();
		}
		// Check the format
		if (in_array($this->mFormat, [self::CSS, self::HTML, self::JAVASCRIPT, self::PDF, self::TEXT])) {
			// Check for content
			if (Core\Is::empty($this->mContent)) {
				// Turn HTML errors on
				ini_set('html_errors', true);
				// Start the output buffer
				ob_start();
				// Load the block
				require_once(\Crux::coreBlock('dump.phtml'));
				// Instantiate our serializer
				$xmlSerializer = new Serialize\Xml();
				// Set the root node
				$xmlSerializer->rootNode($strRootNode);
				// We want document definition
				$xmlSerializer->defineDocument();
				// We want pretty printing
				$xmlSerializer->prettyOutput();
				// Return the XML version of the values
				return $xmlSerializer->serialize([
					'html' => ob_get_clean()
				]);
			} else {
				// Instantiate our serializer
				$xmlSerializer = new Serialize\Xml();
				// Set the root node
				$xmlSerializer->rootNode($strRootNode);
				// We want document definition
				$xmlSerializer->defineDocument();
				// We want pretty printing
				$xmlSerializer->prettyOutput();
				// Return the XML version of the values
				return $xmlSerializer->serialize([
					'html' => $this->mContent
				]);
			}
		} else {
			// We're done, send the response
			return $this->xml();
		}
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Public Methods ///////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method sets a value into the instance
	 * @access public
	 * @name \Crux\Request\Response::addValue()
	 * @package \Crux\Request\Response
	 * @param string $strName
	 * @param mixed $mixValue
	 * @return \Crux\Request\Response $this
	 */
	public function addValue(string $strName, $mixValue) : Response
	{
		// Set the value into the instance
		$this->mValues[$strName] = $mixValue;
		// We're done, return the instance
		return $this;
	}

	/**
	 * This method returns the content from the instance with the ability to reset it inline
	 * @access public
	 * @name \Crux\Request\Response::content()
	 * @package \Crux\Request\Response
	 * @param \Crux\Markup\Block|string $mixContent [\Crux::NoValue]
	 * @return string
	 * @uses \Crux::NoValue
	 * @uses \Crux\Core\Is::scalar()
	 * @uses \Crux\Core\Is::a()
	 * @uses \Crux\Core\Is::empty()
	 * @uses \Crux\Markup\Block::render()
	 * @uses \Crux\Markup\Block::content()
	 */
	public function content($mixContent = \Crux::NoValue) : string
	{
		// Check for provided content
		if (($mixContent !== \Crux::NoValue) && Core\Is::scalar($mixContent)) {
			// Reset the content into the instance
			$this->mContent = $mixContent;
		}
		// Check for a provided block instance
		if (Core\Is::a('\\Crux\\Markup\\Block', $mixContent)) {
			// Check for content
			if (Core\Is::empty($mixContent->content())) {
				// Reset the content into the instance
				$this->mContent = $mixContent->render()->content();
			} else {
				// Reset the content into the instance
				$this->mContent = $mixContent->content();
			}
		}
		// Return the content from the instance
		return $this->mContent;
	}

	/**
	 * This method returns the content disposition file name from the instance with the ability to reset it inline
	 * @access public
	 * @name \Crux\Request\Response::fileName()
	 * @package \Crux\Request\Response
	 * @param string $strFileName ['']
	 * @return string
	 * @uses \Crux\Core\Is::empty()
	 */
	public function fileName(string $strFileName = '') : string
	{
		// Check for a provided file name
		if (!Core\Is::empty($strFileName)) {
			// Reset the file name into the instance
			$this->mFileName = $strFileName;
		}
		// Return the file name from the instance
		return $this->mFileName;
	}

	/**
	 * This method returns the format from the instance with the ability to reset it inline
	 * @access public
	 * @name \Crux\Request\Response::format()
	 * @package \Crux\Request\Response
	 * @param int $intFormat [-1]
	 * @return int
	 */
	public function format(int $intFormat = -1) : int
	{
		// Check for a provided format
		if ($intFormat > -1) {
			// Reset the format into the instance
			$this->mFormat = $intFormat;
		}
		// Return the format from the instance
		return $this->mFormat;
	}

	/**
	 * This method sends the headers and response to the client
	 * @access public
	 * @name \Crux\Request\Response::send()
	 * @package \Crux\Request\Response
	 * @param bool $blnSuccess [true]
	 * @return void
	 * @uses \Crux\Request\Response::type()
	 * @uses \Crux\Request\Response::sendHeaders()
	 * @uses \Crux\Request\Response::serializeResponse()
	 * @uses \Crux\Request\Router::processAfterHooks()
	 * @uses in_array()
	 * @uses die()
	 */
	public function send(bool $blnSuccess = true)
	{
		// Check for success
		if (!$blnSuccess) {
			// Set the response type
			$this->type(self::Bad);
		}
		// Check the format
		if (in_array($this->mFormat, [self::CSS, self::HTML, self::JAVASCRIPT, self::PDF, self::TEXT])) {
			// Check for a filename
			if (!Core\Is::empty($this->mFileName)) {
				// Send the content-disposition header
				header(sprintf('Content-Disposition: inline; filename=%s', $this->mFileName));
			}
			// Check for content
			if (Core\Is::empty($this->mContent)) {
				// Reset the format
				self::format(self::HTML);
				// Turn HTML errors on
				ini_set('html_errors', true);
				// Start the output buffer
				ob_start();
				// Load the block
				require_once(\Crux::coreBlock('dump.phtml'));
				// Dump the data
				$responseContent = ob_get_clean();
			} else {
				// Send the response headers
				$this->sendHeaders();
				// Send the response
				$responseContent = $this->mContent;
			}
		} else {
			// Send the response headers
			$this->sendHeaders();
			// Send the response
			$responseContent = $this->serializeResponse($blnSuccess);
		}
		// Process the post-route hooks
		Router::processAfterHooks($blnSuccess, $responseContent);
		// Send the response
		die($responseContent);
	}

	/**
	 * This method serializes and returns the response
	 * @access public
	 * @name \Crux\Request\Response::serializeResponse()
	 * @package \Crux\Request\Response
	 * @param bool $blnSuccess [true]
	 * @return string
	 * @uses \Crux\Request\Response::jsonp()
	 * @uses \Crux\Request\Response::php()
	 * @uses \Crux\Request\Response::xml()
	 * @uses \Crux\Request\Response::json()
	 */
	public function serializeResponse(bool $blnSuccess = true) : string
	{
		// Set the success flag
		$this->mValues['success'] = $blnSuccess;
		// Determine the response format
		if ($this->mFormat === self::JSONP) {
			// Encode the response into JSONP
			return $this->jsonp();
		} elseif ($this->mFormat === self::PHP) {
			// Encode the response into PHP
			return $this->php();
		} elseif ($this->mFormat === self::XML) {
			// Encode the response into XML
			return $this->xml();
		} else {
			// Encode the response into JSON
			return $this->json();
		}
	}

	/**
	 * This method returns the response type from the instance with the ability to reset it inline
	 * @access public
	 * @name \Crux\Request\Response::type()
	 * @package \Crux\Request\Response
	 * @param int $intType [-1]
	 * @return int
	 */
	public function type(int $intType = -1) : int
	{
		// Check for a provided response type
		if ($intType > - 1) {
			// Reset the response type into the instance
			$this->mResponseType = $intType;
		}
		// Return the response type from the instance
		return $this->mResponseType;
	}

	/**
	 * This method returns a value from the instance with the ability to reset it inline
	 * @access public
	 * @name \Crux\Request\Response::value()
	 * @package \Crux\Request\Response
	 * @param string $strName
	 * @param mixed $mixValue [\Crux::NoValue]
	 * @return mixed|null
	 * @uses \Crux\Core\Is::null()
	 */
	public function value(string $strName, $mixValue = \Crux::NoValue)
	{
		// Check for a provided value
		if ($mixValue !== \Crux::NoValue) {
			// Reset the value into the instance
			$this->mValues[$strName] = $mixValue;
		}
		// Return the value from the instance
		return ($this->mValues[$strName] ?? null);
	}

	/**
	 * This method returns the values from the instance with the ability to reset them inline
	 * @access public
	 * @name \Crux\Request\Response::values()
	 * @package \Crux\Request\Response
	 * @param array<string, mixed> $arrValues [null]
	 * @return array<string, mixed>
	 * @uses \Crux\Core\Is::null()
	 */
	public function values(array $arrValues = null) : array
	{
		// Check for provided values
		if (!Core\Is::null($arrValues)) {
			// Reset the values into the instance
			$this->mValues = $arrValues;
		}
		// Return the values from the instance
		return $this->mValues;
	}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
} /// End Crux\Request\Response Class Definition /////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

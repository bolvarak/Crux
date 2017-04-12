<?php

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Crux\Request Namespace ///////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

namespace Crux\Request;

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Imports //////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

use Crux\Core;

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Crux\Request\Endpoint Class Definition ///////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

abstract class Endpoint
{
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Protected Properties /////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This property contains the request container
	 * @access protected
	 * @name \Crux\Request\Endpoint::$mContainer
	 * @package \Crux\Request\Endpoint
	 * @var \Crux\Request\Container
	 */
	protected $mContainer;

	/**
	 * This property contains the response object
	 * @access protected
	 * @name \Crux\Request\Endpoint::$mResponse
	 * @package \Crux\Request\Endpoint
	 * @var \Crux\Request\Response
	 */
	protected $mResponse;

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Constructor //////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method instantiates a new request endpoint object
	 * @access public
	 * @name \Crux\Request\Endpoint::__construct()
	 * @package \Crux\Request\Endpoint
	 */
	public function __construct()
	{
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Router Bootstrap Methods /////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method prepares the endpoint handler for execution
	 * @access public
	 * @name \Crux\Request\Endpoint::__bootstrap()
	 * @package \Crux\Request\Endpoint
	 * @param \Crux\Request\Container $httpRequest
	 * @param \Crux\Request\Response $httpResponse
	 * @return \Crux\Request\Endpoint $this
	 */
	public function __bootstrap(Container &$httpRequest, Response &$httpResponse) : Endpoint
	{
		// Set the HTTP request container into the instance
		$this->mContainer = $httpRequest;
		// Set the HTTP response container into the instance
		$this->mResponse = $httpResponse;
		// We're done, return the instance
		return $this;
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Abstract Methods /////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method allows the endpoint to execute tasks before the endpoint method is called
	 * @abstract
	 * @access public
	 * @name \Crux\Request\Endpoint::__init()
	 * @package \Crux\Request\Endpoint
	 * @return void
	 */
	abstract public function __init();

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Protected Methods ////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method loads and renders a block file
	 * @access protected
	 * @name \Crux\Request\Endpoint::block()
	 * @package \Crux\Request\Endpoint
	 * @param string $strName
	 * @param array<string, mixed> $arrScope [array()]
	 * @return void
	 * @throws \Crux\Core\Exception\Request\Endpoint
	 * @uses \Crux\Request\Endpoint::blockExists()
	 * @uses \Crux\Request\Endpoint::raise()
	 * @uses \Crux::blockPath()
	 * @uses sprintf()
	 * @uses extract()
	 * @uses ltrim()
	 */
	protected function block(string $strName, array $arrScope = [])
	{
		// Make sure the block file exists
		if (!$this->blockExists($strName)) {
			// Throw the exception
			$this->raise(sprintf('Block %s does not exist in %s', $strName, \Crux::blockPath()));
		}
		// Extract the scope
		extract($arrScope);
		// Load the block
		require_once(sprintf('%s%s%s', \Crux::blockPath(), DIRECTORY_SEPARATOR, ltrim($strName, DIRECTORY_SEPARATOR)));
	}

	/**
	 * This method determines whether or not a block file exists
	 * @access protected
	 * @name \Crux\Request\Endpoint::blockExists()
	 * @package \Crux\Request\Endpoint
	 * @param string $strName
	 * @return bool
	 * @uses \Crux::blockPath()
	 * @uses ltrim()
	 * @uses sprintf()
	 * @uses file_exists()
	 */
	protected function blockExists(string $strName) : bool
	{
		// Check for the block
		return file_exists(sprintf('%s%s%s', \Crux::blockPath(), DIRECTORY_SEPARATOR, ltrim($strName, DIRECTORY_SEPARATOR)));
	}

	/**
	 * This method renders multiple blocks
	 * @access protected
	 * @name \Crux\Request\Endpoint::blocks()
	 * @package \Crux\Request\Endpoint
	 * @return void
	 * @uses \Crux\Core\Is::array()
	 * @uses func_num_args()
	 * @uses func_get_arg()
	 * @uses array_push()
	 * @uses count()
	 */
	protected function blocks()
	{
		// Define the blocks container
		$arrBlocks = [];
		// Define the scope container
		$arrScope = [];
		// Iterate over the function arguments
		for ($intArgument = 0; $intArgument < func_num_args(); ++$intArgument) {
			// Check the argument for an array
			if (Core\Is::array(func_get_arg($intArgument))) {
				// Reset the scope
				$arrScope = func_get_arg($intArgument);
			} else {
				// Add the block file to the blocks container
				array_push($arrBlocks, func_get_arg($intArgument));
			}
		}
		// Iterate over the blocks
		for ($intBlock = 0; $intBlock < count($arrBlocks); ++$intBlock) {
			// Render the block
			$this->block($arrBlocks[$intBlock], $arrScope);
		}
	}

	/**
	 * This method renders multiple blocks to a string
	 * @access protected
	 * @name \Crux\Request\Endpoint::blocksToString()
	 * @package \Crux\Request\Endpoint
	 * @return string
	 * @uses \Crux\Request\Endpoint::blocks()
	 * @uses ob_start()
	 * @uses func_get_args()
	 * @uses call_user_func_array()
	 * @uses ob_get_clean()
	 */
	protected function blocksToString() : string
	{
		// Start the output buffer
		ob_start();
		// Render the blocks
		call_user_func_array([$this, 'blocks'], func_get_args());
		// Return the output buffer
		return ob_get_clean();
	}

	/**
	 * This method renders a block to a string container
	 * @access protected
	 * @name \Crux\Request\Endpoint::blockToString()
	 * @package \Crux\Request\Endpoint
	 * @param string $strName
	 * @param array<string, mixed> $arrScope [array()]
	 * @return string
	 * @uses \Crux\Request\Endpoint::block()
	 * @uses ob_start()
	 * @uses ob_get_clean()
	 */
	protected function blockToString(string $strName, $arrScope = []) : string
	{
		// Start the output buffer
		ob_start();
		// Render the block
		$this->block($strName, $arrScope);
		// Return the output buffer
		return ob_get_clean();
	}

	/**
	 * This method minifies CSS
	 * @access protected
	 * @name \Crux\Request\Endpoint::minifyCss()
	 * @package \Crux\Request\Endpoint
	 * @param string $strSource
	 * @return string
	 * @uses preg_replace()
	 */
	protected function minifyCss(string $strSource) : string
	{
		// Remove all single line comments
		$strCSS = preg_replace('/(\/\*[\w\'\s\r\n\*]*\*\/)|(\<![\-\-\s\w\>\/]*\>)/', null, $strSource);
		// Remove Multi-Line Comments
		$strCSS = preg_replace('#/\*.*?\*/#s', null, $strCSS);
		// Remove Unnecessary Whitespace
		$strCSS = preg_replace('/\s*([{}|:;,])\s+/', '$1', $strCSS);
		// Remove Trailing whitespace
		$strCSS = preg_replace('/\s\s+(.*)/', '$1', $strCSS);
		// Remove Unnecessary semi-colons
		$strCSS = str_replace(';}', '}', $strCSS);
		// Remove all tabs
		$strCSS = preg_replace('/\t+/', ' ', $strCSS);
		// Remove all double spaces
		$strCSS = preg_replace('/\s+/', ' ', $strCSS);
		// Remove all new lines
		$strCSS = preg_replace('/' . PHP_EOL . '/', null, $strCSS);
		// Return the minified string
		return $strCSS;
	}

	/**
	 * This method minifies inline CSS
	 * @access protected
	 * @name \Crux\Request\Endpoint::minifyEmbeddedCss()
	 * @package \Crux\Request\Endpoint
	 * @param string $strSource
	 * @return string
	 * @uses \Crux\Request\Endpoint::minifyCss()
	 * @uses preg_replace_callback()
	 */
	protected function minifyEmbeddedCss(string $strSource) : string
	{
		// Find the CSS
		return preg_replace_callback('#\<style.*?\>(.*?)\<\/style\>#i', function (array $arrMatches) {
			// Minify the CSS
			return $this->minifyCss($arrMatches[1]);
		}, $strSource);
	}

	/**
	 * This method minifies inline JS
	 * @access protected
	 * @name \Crux\Request\Endpoint::minifyEmbeddedJs()
	 * @package \Crux\Request\Endpoint
	 * @param string $strSource
	 * @return string
	 * @uses \Crux\Request\Endpoint::minifyJs()
	 * @uses preg_replace_callback()
	 */
	protected function minifyEmbeddedJs(string $strSource) : string
	{
		// Find the JS
		return preg_replace_callback('#\<script.*?\>(.*?)\<\/script\>#i', function (array $arrMatches) {
			// Minify the JS
			return $this->minifyJs($arrMatches[1]);
		}, $strSource);
	}

	/**
	 * This method minifies HTML, inline JS and inline CSS
	 * @access protected
	 * @name \Crux\Request\Endpoint::minifyHtml()
	 * @package \Crux\Request\Endpoint
	 * @param string $strSource
	 * @param bool $blnAssetsToo [true]
	 * @return string
	 * @uses \Crux\Request\Endpoint::minifyEmbeddedCss()
	 * @uses \Crux\Request\Endpoint::minifyEmbeddedJs()
	 * @uses str_replace()
	 * @uses preg_replace()
	 */
	protected function minifyHtml(string $strSource, bool $blnAssetsToo = true) : string
	{
		// Check to see if we need to minify the assets
		if ($blnAssetsToo) {
			// Minify the CSS
			$strSource = $this->minifyEmbeddedCss($strSource);
			// Minify the JS
			$strSource = $this->minifyEmbeddedJs($strSource);
		}
		// Remove the line breaks and tabs
		$strSource = str_replace(array(PHP_EOL, "\r\n", "\n", "\t"), null, $strSource);
		// Remove double spaces
		$strSource = preg_replace('/\s+/', ' ', $strSource);
		// Remove comments
		$strSource = preg_replace('/<!--(?!\s*(?:\[if [^\]]+]|<!|>))(?:(?!-->).)*-->/', null, $strSource);
		// We're done
		return $strSource;
	}

	/**
	 * This method minifies javascript inline
	 * @access protected
	 * @name \Crux\Request\Endpoint::minifyJs()
	 * @package \Crux\Request\Endpoint
	 * @param string $strSource
	 * @return string
	 * @uses preg_replace()
	 */
	protected function minifyJs(string $strSource) : string
	{
		// Remove CDATA tags
		$strJS = preg_replace('~//<!\[CDATA\[\s*|\s*//\]\]>~', '', $strSource);
		// Remove comments from single quoted strings
		$strJS = preg_replace('#\'([^\n\']*?)/\*([^\n\']*)\'#', "'\1/'+\\'\\'+'*\2#'", $strJS);
		// Remove comments from double quoted strings
		$strJS = preg_replace('#\"([^\n\"]*?)/\*([^\n\"]*)\"#', '"\1/"+\'\'+"*\2"', $strJS);
		// Strip C-style comments
		$strJS = preg_replace('#/\*.*?\*/#s', '', $strJS);
		// Remove blank lines and carriage returns
		$strJS = preg_replace('#[\r\n]+#', "\n", $strJS);
		// Strip whole line comments
		$strJS = preg_replace('#\n([ \t]*//.*?\n)*#s', "\n", $strJS);
		// Strip line comments
		$strJS = preg_replace('#([^\\])//([^\'"\n]*)\n#s', "\\1\n", $strJS);
		// Strip excess whitespace
		$strJS = preg_replace('#\n\s+#', "\n", $strJS);
		$strJS = preg_replace('#\s+\n#', "\n", $strJS);
		// Strip extra line feed after comment removal
		$strJS = preg_replace('#(//[^\n]*\n)#s', "\\1\n", $strJS);
		// Remove all tabs
		$strJS = preg_replace('/\t+/', ' ', $strJS);
		// Remove all double spaces
		$strJS = preg_replace('/\s+/', ' ', $strJS);
		// Remove all new lines
		$strJS = preg_replace('/' . PHP_EOL . '/', null, $strJS);
		// Restore comments in strings
		$strJS = preg_replace('#/([\'"])\+\'\'\+([\'"])\*#', '/*', $strJS);
		// Return the minified string
		return $strJS;
	}

	/**
	 * This method raises an exception on the endpoint
	 * @access protected
	 * @name \Crux\Request\Endpoint::raise()
	 * @package \Crux\Request\Endpoint
	 * @param string $strMessage
	 * @param int $intCode [0]
	 * @throws \Crux\Core\Exception\Request\Endpoint
	 * @uses \Crux\Core\Exception\Request\Endpoint::__construct()
	 */
	protected function raise(string $strMessage, int $intCode = 0)
	{
		// Throw the exception
		throw new Core\Exception\Request\Endpoint($strMessage, $intCode);
	}

	/**
	 * This method is a fluid accessor for the request container in the instance
	 * @access protected
	 * @name \Crux\Request\Endpoint::request()
	 * @package \Crux\Request\Endpoint
	 * @return \Crux\Request\Container
	 */
	protected function request() : Container
	{
		// Return the request container from the instance
		return $this->mContainer;
	}

	/**
	 * This method is a fluid accessor for the response object in the instance
	 * @access protected
	 * @name \Crux\Request\Endpoint::response()
	 * @package \Crux\Request\Endpoint
	 * @return \Crux\Request\Response
	 */
	protected function response() : Response
	{
		// Return the response container from the instance
		return $this->mResponse;
	}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
} /// End Crux\Request\Endpoint Class Definition /////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

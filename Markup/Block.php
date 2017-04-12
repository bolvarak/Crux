<?php

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Crux\Markup Namespace ////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

namespace Crux\Markup;

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Imports //////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

use Crux;
use Crux\Collection\Map;
use Crux\Core\Exception;
use Crux\Core\Is;

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Crux\Markup\Block Class Definition ///////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class Block
{
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Properties ///////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This property contains the rendered content of the block
	 * @access protected
	 * @name \Crux\Markup\Block::$mContent
	 * @package \Crux\Markup\Block
	 * @var string
	 */
	protected $mContent = '';

	/**
	 * This property contains the content block to load
	 * @access protected
	 * @name \Crux\Markup\Block::$mContentBlock
	 * @package \Crux\Markup\Block
	 * @var string
	 */
	protected $mContentBlock = '';

	/**
	 * This property contains the values for the block
	 * @access protected
	 * @name \Crux\Markup\Block::$mValues
	 * @package \Crux\Markup\Block
	 * @var array<string, mixed>
	 */
	protected $mValues = [];

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Constructor //////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method constructs a new block instance
	 * @access public
	 * @name \Crux\Markup\Block::__constructor()
	 * @package \Crux\Markup\Block
	 * @param string $strContentBlock
	 * @param array<string, mixed> $arrValues [null]
	 * @uses \Crux\Markup\Block::contentBlock()
	 * @uses \Crux\Markup\Block::layoutBlock()
	 * @uses \Crux\Markup\Block::values()
	 */
	public function __construct(string $strContentBlock, array $arrValues = null)
	{
		// Set the content block
		$this->contentBlock($strContentBlock);
		// Set the values
		$this->values($arrValues);
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Protected Methods ////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method normalizes a block path
	 * @access protected
	 * @name \Crux\Markup\Block::normalizePath()
	 * @package \Crux\Markup\Block
	 * @param string $strBlock
	 * @return string
	 * @throws \Crux\Core\Exception
	 * @uses \Crux\Core\Exception::__construct()
	 * @uses \Crux::blockPath()
	 * @uses sprintf()
	 * @uses file_exists()
	 */
	protected function normalizePath(string $strBlock) : string
	{
		// Check for the existence of the content block
		if (file_exists($strBlock)) {
			// Set the content block
			return $strBlock;
		} elseif (file_exists(sprintf('%s%s%s', Crux::blockPath(), DIRECTORY_SEPARATOR, $strBlock))) {
			// Set the content block
			return sprintf('%s%s%s', Crux::blockPath(), DIRECTORY_SEPARATOR, $strBlock);
		} else {
			// We're done, send the exception
			throw new Exception(sprintf('Content Block [%s] does not exist', $strBlock), 404);
		}
	}

	/**
	 * This method renders a single block or sub-block file
	 * @access protected
	 * @name \Crux\Markup\Block::renderBlock()
	 * @package \Crux\Markup\Block
	 * @param string $strBlockPath
	 * @return void
	 * @uses \Crux\Markup\Block::normalizePath()
	 * @uses require_once()
	 */
	protected function renderBlock(string $strBlockPath)
	{
		// Define our full block path
		$strBlock = $this->normalizePath($strBlockPath);
		// Load the block
		require_once($strBlock);
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Rendering ////////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method renders the content block from the instance
	 * @access public
	 * @name \Crux\Markup\Block::render()
	 * @package \Crux\Markup\Block
	 * @return \Crux\Markup\Block
	 * @uses \Crux\Markup\Block::contentBlock()
	 * @uses \Crux\Markup\Block::renderBlock()
	 * @uses \Crux\Markup\Block::content()
	 * @uses ob_start()
	 * @uses require_once()
	 * @uses flush()
	 * @uses ob_get_clean()
	 */
	public function render() : Block
	{
		// Start the output buffer
		ob_start();
		// Render the block
		$this->renderBlock($this->contentBlock());
		// Flush the content
		flush();
		// Set the markup into the instance
		$this->content(ob_get_clean());
		// We're done, return the instance
		return $this;
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Inline Methods ///////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method returns the rendered content from the instance with the ability to reset it inline
	 * @access public
	 * @name \Crux\Markup\Block::content()
	 * @package \Crux\Markup\Block
	 * @param string $strMarkup [\Crux::NoValue]
	 * @return string
	 * @uses \Crux::NoValue
	 */
	public function content(string $strMarkup = Crux::NoValue) : string
	{
		// Check for provided content
		if ($strMarkup !== Crux::NoValue) {
			// Reset the content into the instance
			$this->mContent = $strMarkup;
		}
		// Return the content from the instance
		return $this->mContent;
	}

	/**
	 * This method returns the content block path from the instance with the ability to reset it inline
	 * @access public
	 * @name \Crux\Markup\Block::contentBlock()
	 * @package \Crux\Markup\Block
	 * @param string $strBlock ['']
	 * @return string
	 * @uses \Crux\Core\Is::empty()
	 * @uses \Crux::blockPath()
	 */
	public function contentBlock(string $strBlock = '') : string
	{
		// Check for a provided content block
		if (!Is::empty($strBlock)) {
			// Reset the content block into the instance
			$this->mContentBlock = $this->normalizePath($strBlock);
		}
		// Return the content block from the instance
		return $this->mContentBlock;
	}

	/**
	 * This method returns a value from the instance with the ability to reset it inline
	 * @access public
	 * @name \Crux\Markup\Block::value()
	 * @package \Crux\Markup\Block
	 * @param string $strName
	 * @param mixed $mixValue [\Crux::NoValue]
	 * @return mixed
	 * @uses \Crux::NoValue
	 */
	public function value(string $strName, $mixValue = Crux::NoValue)
	{
		// Check for a provided value
		if ($mixValue !== Crux::NoValue) {
			// Reset the value into the instance
			$this->mValues[$strName] = $mixValue;
		}
		// Return the value from the instance
		return ($this->mValues[$strName] ?? null);
	}

	/**
	 * This method returns the values container from the instance with the ability to reset it inline
	 * @access public
	 * @name \Crux\Markup\Block::values()
	 * @package \Crux\Markup\Block
	 * @param array<string, mixed> $arrValues [null]
	 * @return array<string, mixed>
	 * @uses \Crux\Core\Is::null()
	 */
	public function values(array $arrValues = null) : array
	{
		// Check for provided values
		if (!Is::null($arrValues)) {
			// Reset the values into the instance
			$this->mValues = $arrValues;
		}
		// Return the values from the instance
		return $this->mValues;
	}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
} /// End Crux\Markup\Block Class Definition /////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

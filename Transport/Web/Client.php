<?php

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Crux\Transport\Web Namespace /////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

namespace Crux\Transport\Web;

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Imports //////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

use Crux\Core;

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Crux\Transport\Web\Client Class Definition ////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class Client
{
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Properties ///////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This property contains the flag that tells the instance whether this client is executing a long-running send task
	 * @access protected
	 * @name \Crux\Transport\Web\Client::$mContinuousSend
	 * @package \Crux\Transport\Web\Client
	 * @var bool
	 */
	protected $mContinuousSend = false;

	/**
	 * This property contains the unique identifier associated with the client
	 * @access protected
	 * @name \Crux\Transport\Web\Client::$mId
	 * @package \Crux\Transport\Web\Client
	 * @var string
	 */
	protected $mId;

	/**
	 * This property contains the flag that tells the instance whether or not a handshake has been made
	 * @access protected
	 * @name \Crux\Transport\Web\Client::$mHandshake()
	 * @package \Crux\Transport\Web\Client
	 * @var bool
	 */
	protected $mHandshake = false;

	/**
	 * This property contains headers that are specific to the client
	 * @access protected
	 * @name \Crux\Transport\Web\Client::$mHeaders
	 * @package \Crux\Transport\Web\Client
	 * @var array<string, mixed>
	 */
	protected $mHeaders = [];

	/**
	 * This property contains the partial buffer in the case of a long-running send task
	 * @access protected
	 * @name \Crux\Transport\Web\Client::$mPartialBuffer
	 * @package \Crux\Transport\Web\Client
	 * @var string
	 */
	protected $mPartialBuffer = '';

	/**
	 * This property contains the partial message in the case of a long-running send task
	 * @access protected
	 * @name \Crux\Transport\Web\Client::$mPartialMessage
	 * @package \Crux\Transport\Web\Client
	 * @var string
	 */
	protected $mPartialMessage = '';

	/**
	 * This property contains a flag that tells the instance whether or not there is a partial packet
	 * @access protected
	 * @name \Crux\Transport\Web\Client::$mPartialPacket
	 * @package \Crux\Transport\Web\Client
	 * @var bool
	 */
	protected $mPartialPacket = false;

	/**
	 * This property contains a flag that tells the instance whether or not the client sent the close command
	 * @access protected
	 * @name \Crux\Transport\Web\Client::$mSentClose
	 * @package \Crux\Transport\Web\Client
	 * @var bool
	 */
	protected $mSentClose = false;

	/**
	 * This property contains the socket resource for the client connection
	 * @access protected
	 * @name \Crux\Transport\Web\Client::$mSocket
	 * @package \Crux\Transport\Web\Client
	 * @var
	 */
	protected $mSocket;

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Constructor //////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method instantiates a new WebSocket Client transport object
	 * @access public
	 * @name \Crux\Transport\Web\Client::__construct()
	 * @package \Crux\Transport\Web\Client
	 * @param string $strId
	 * @param socket resource $rscSocket
	 */
	public function __construct(string $strId, $rscSocket)
	{
		// Set the client ID into the instance
		$this->mId = $strId;
		// Set the socket resource into the instance
		$this->mSocket = $rscSocket;
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Inline Methods ///////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method returns the "continuous send" flag from the instance with the ability to reset it inline
	 * @access public
	 * @name \Crux\Transport\Web\Client::continuousSend()
	 * @package \Crux\Transport\Web\Client
	 * @param bool $blnFlag [null]
	 * @return bool
	 * @uses \Crux\Core\Is::null()
	 */
	public function continuousSend(bool $blnFlag = null) : bool
	{
		// Check for a provided flag
		if (!Core\Is::null($blnFlag)) {
			// Reset the flag from the instance
			$this->mContinuousSend = $blnFlag;
		}
		// Return the flag from the instance
		return $this->mContinuousSend;
	}

	/**
	 * This method returns the client identifier from the instance with the ability to reset it inline
	 * @access public
	 * @name \Crux\Transport\Web\Client::id()
	 * @package \Crux\Transport\Web\Client
	 * @param string $strIdentifier ['']
	 * @return string
	 * @uses \Crux\Core\Is::empty()
	 */
	public function id(string $strIdentifier = '') : string
	{
		// Check for a provided identifier
		if (!Core\Is::empty($strIdentifier)) {
			// Reset the identifier into the instance
			$this->mId = $strIdentifier;
		}
		// Return the identifier from the instance
		return $this->mId;
	}

	/**
	 * This method returns the "handshake" flag from the instance with the ability to reset it inline
	 * @access public
	 * @name \Crux\Transport\Web\Client::handshake()
	 * @package \Crux\Transport\Web\Client
	 * @param bool $blnFlag [null]
	 * @return bool
	 * @uses \Crux\Core\Is::null()
	 */
	public function handshake(bool $blnFlag = null) : bool
	{
		// Check for a provided flag
		if (!Core\Is::null($blnFlag)) {
			// Reset the flag into the instance
			$this->mHandshake = $blnFlag;
		}
		// return the flag from the instance
		return $this->mHandshake;
	}

	/**
	 * This method returns a single header from the instance with the ability to reset it inline
	 * @access public
	 * @name \Crux\Transport\Web\Client::header()
	 * @package \Crux\Transport\Web\Client
	 * @param string $strName
	 * @param mixed $mixValue [\Crux::NoValue]
	 * @return mixed
	 */
	public function header(string $strName, $mixValue = \Crux::NoValue)
	{
		// Check for a provided
		if ($mixValue !== \Crux::NoValue) {
			// Reset the header into the instance
			$this->mHeaders[$strName] = $mixValue;
		}
		// Return the header from the instance
		return ($this->mHeaders[$strName] ?? null);
	}

	/**
	 * This method returns the headers from the instance with the ability to reset them inline
	 * @access public
	 * @name \Crux\Transport\Web\Client::headers()
	 * @package \Crux\Tranport\Web\Client
	 * @param array<string, mixed> $arrHeaders [null]
	 * @return array<string, mixed>
	 * @uses \Crux\Core\Is::null()
	 */
	public function headers(array $arrHeaders = null) : array
	{
		// Check for provided headers
		if (!Core\Is::null($arrHeaders)) {
			// Reset the headers into the instance
			$this->mHeaders = $arrHeaders;
		}
		// Return the headers from the instance
		return $this->mHeaders;
	}

	/**
	 * This method returns the partial buffer from the instance with the ability to reset it inline
	 * @access public
	 * @name \Crux\Transport\Web\Client::partialBuffer()
	 * @package \Crux\Transport\Web\Client
	 * @param string $strBuffer [\Crux::NoValue]
	 * @return string
	 */
	public function partialBuffer(string $strBuffer = \Crux::NoValue) : string
	{
		// Check for a provided buffer
		if ($strBuffer !== \Crux::NoValue) {
			// Reset the partial buffer into the instance
			$this->mPartialBuffer = $strBuffer;
		}
		// Return the partial buffer from the instance
		return $this->mPartialBuffer;
	}

	/**
	 * This method returns the partial message from the instance with the ability to reset it inline
	 * @access public
	 * @name \Crux\Transport\Web\Client::partialMessage()
	 * @package \Crux\Transport\Web\Client
	 * @param string $strMessage [\Crux::NoValue]
	 * @return string
	 */
	public function partialMessage(string $strMessage = \Crux::NoValue) : string
	{
		// Check for a provided message
		if ($strMessage !== \Crux::NoValue) {
			// Reset the partial message into the instance
			$this->mPartialMessage = $strMessage;
		}
		// Return the partial message from the instance
		return $this->mPartialMessage;
	}

	/**
	 * This method returns the "partial packet" flag from the instance with the ability to reset it inline
	 * @access public
	 * @name \Crux\Transport\Web\Client::partialPacket()
	 * @package \Crux\Transport\Web\Client
	 * @param bool $blnFlag [null]
	 * @return bool
	 * @uses \Crux\Core\Is::null()
	 */
	public function partialPacket(bool $blnFlag = null) : bool
	{
		// Check for a provided flag
		if (!Core\Is::null($blnFlag)) {
			// Reset the flag into the instance
			$this->mPartialPacket = $blnFlag;
		}
		// Return the flag from the instance
		return $this->mPartialPacket;
	}

	/**
	 * This method returns the "sent close" flag from the instance with the ability to reset it inline
	 * @access public
	 * @name \Crux\Transport\Web\Client::sentClose()
	 * @package \Crux\Transport\Web\Client
	 * @param bool $blnFlag [null]
	 * @return bool
	 * @uses \Crux\Core\Is::null()
	 */
	public function sentClose(bool $blnFlag = null) : bool
	{
		// Check for a provided flag
		if (!Core\Is::null($blnFlag)) {
			// Reset the flag into the instance
			$this->mSentClose = $blnFlag;
		}
		// Return the flag from the instance
		return $this->mSentClose;
	}

	/**
	 * This method returns the socket resource from the instance with the ability to reset it inline
	 * @access public
	 * @name \Crux\Transport\Web\Client::socket()
	 * @package \Crux\Transport\Web\Client
	 * @param resource $rscSocket [null]
	 * @return resource
	 * @uses \Crux\Core\Is::null()
	 * @uses \Crux\Core\Is::resource()
	 */
	public function socket($rscSocket = null)
	{
		// Check for a provided socket resource
		if (!Core\Is::null($rscSocket) && Core\Is::resource($rscSocket)) {
			// Reset the socket resource into the instance
			$this->mSocket = $rscSocket;
		}
		// Return the socket resource from the instance
		return $this->mSocket;
	}




///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
} /// End Crux\Transport\Web\Client Class Definition /////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

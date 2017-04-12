<?php

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Crux\Transport\Web Namespace /////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

namespace Crux\Transport\Web;

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Imports //////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

use Crux\Core;
use Crux\Request;

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Crux\Transport\Web\Socket Class Definition ///////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

abstract class Socket
{
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Constants ////////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This property contains the name for the master socket
	 * @name \Crux\Transport\Web\Socket::MASTER
	 * @package \Crux\Transport\Web\Socket
	 * @var string
	 */
	const MASTER = '__phireworks_master_socket';

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Protocol Constants ///////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This constant defines an JSON Sub-Protocol for WebSockets
	 * @name \Crux\Transport\Web\Socket::PROTOCOL_JSON
	 * @package \Crux\Transport\Web\Socket
	 * @var string
	 */
	const PROTOCOL_JSON = 'json';

	/**
	 * This constant defines a SOAP Sub-Protocol for WebSockets
	 * @name \Crux\Transport\Web\Socket::PROTOCOL_SOAP
	 * @package \Crux\Transport\Web\Socket
	 * @var string
	 */
	const PROTOCOL_SOAP = 'soap';

	/**
	 * This constant defines a Web Application Messaging Protocol Sub-Protocol for WebSockets
	 * @name \Crux\Transport\Web\Socket::PROTOCOL_WAMP
	 * @package \Crux\Transport\Web\Socket
	 * @var string
	 */
	const PROTOCOL_WAMP = 'wamp';

	/**
	 * This constant defines an XML Sub-Protocol for WebSockets
	 * @name \Crux\Transport\Web\Socket::PROTOCOL_XML
	 * @package \Crux\Transport\Web\Socket
	 * @var string
	 */
	const PROTOCOL_XML  = 'xml';

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Properties ///////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This property contains the address the WebSocket will bind to
	 * @access protected
	 * @name \Crux\Transport\Web\Socket::$mAddress
	 * @package \Crux\Transport\Web\Socket
	 * @var string
	 */
	protected $mAddress = '127.0.0.1';

	/**
	 * This property contains the connected clients
	 * @access protected
	 * @name \Crux\Transport\Web\Socket::$mClients
	 * @package \Crux\Transport\Web\Socket
	 * @var array<int, \Crux\Transport\Web\Client>
	 */
	protected $mClients = [];

	/**
	 * This property contains data from the client to send to Endpoints
	 * @access protected
	 * @name \Crux\Transport\Web\Socket::$mContainer
	 * @package \Crux\Transport\Web\Socket
	 * @var \Crux\Request\Container
	 */
	protected $mContainer;

	/**
	 * This property contains a flag that tells the instance to operate interactively
	 * @access protected
	 * @name \Crux\Transport\Web\Socket::$mInteractive
	 * @package \Crux\Transport\Web\Socket
	 * @var bool
	 */
	protected $mInteractive = true;

	/**
	 * This property contains the master socket resource
	 * @access protected
	 * @name \Crux\Transport\Web\Socket::$mMaster
	 * @package \Crux\Transport\Web\Socket
	 * @var resource
	 */
	protected $mMaster;

	/**
	 * This property contains the maximum buffer length
	 * @access protected
	 * @name \Crux\Transport\Web\Socket::$mMasterBufferSize
	 * @package \Crux\Transport\Web\Socket
	 * @var int
	 */
	protected $mMaximumBufferSize = 4096;

	/**
	 * This property contains any pending messages
	 * @access protected
	 * @name \Crux\Transport\Web\Socket::$mPendingMessages
	 * @package \Crux\Transport\Web\Socket
	 * @var array<int, string>
	 */
	protected $mPendingMessages = [];

	/**
	 * This property contains the port the WebSocket will bind to
	 * @access protected
	 * @name \Crux\Transport\Web\Socket::$mPort
	 * @package \Crux\Transport\Web\Socket
	 * @var int
	 */
	protected $mPort = 8082;

	/**
	 * This property contains a flag that tells the instance that the origin header is required from the client
	 * @access protected
	 * @name \Crux\Transport\Web\Socket::$mRequireHeaderOrigin
	 * @package \Crux\Transport\Web\Socket
	 * @var bool
	 */
	protected $mRequireHeaderOrigin = false;

	/**
	 * This property contains a flag that tells the instance that the Sec-WebSocket-Extensions header is required from the client
	 * @access protected
	 * @name \Crux\Transport\Web\Socket::$mRequireHeaderSWSE
	 * @package \Crux\Transport\Web\Socket
	 * @var bool
	 */
	protected $mRequireHeaderSWSE = false;

	/**
	 * This property contains a flag that tells the instance that the Sec-WebSocket-Protocol header is required from the client
	 * @access protected
	 * @name \Crux\Transport\Web\Socket::$mRequireHeaderSWSP
	 * @package \Crux\Transport\Web\Socket
	 * @var bool
	 */
	protected $mRequireHeaderSWSP = false;

	/**
	 * This property contains the socket resource pool
	 * @access protected
	 * @name \Crux\Transport\Web\Socket::$mSockets
	 * @package \Crux\Transport\Web\Socket
	 * @var array<string, resource>
	 */
	protected $mSockets = [];

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Constructor //////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method instantiates a new WebSocket transport object
	 * @access public
	 * @name \Crux\Transport\Web\Socket::__construct()
	 * @package \Crux\Transport\Web\Socket
	 * @param string $strAddress
	 * @param int $intPort [8082]
	 * @param int $intBufferSize [4096]
	 * @throws \Crux\Core\Exception\Transport\Web\Socket
	 * @uses \Crux\Transport\Web\Socket::maximumBufferSize()
	 * @uses \Crux\Transport\Web\Socket::address()
	 * @uses \Crux\Transport\Web\Socket::port()
	 * @uses \Crux\Transport\Web\Socket::master()
	 * @uses \Crux\Transport\Web\Socket::socket()
	 * @uses \Crux\Transport\Web\Socket::container()
	 * @uses \Crux\Transport\Web\Socket::debug()
	 * @uses \Crux\Core\Exception\Transport\Web\Socket::__construct()
	 * @uses filter_var()
	 * @uses socket_create()
	 * @uses sprintf()
	 * @uses socket_set_option()
	 * @uses socket_bind()
	 * @uses socket_listen()
	 */
	public function __construct(string $strAddress, int $intPort = 8082, $intBufferSize = 4096)
	{
		// Set the maximum buffer size into the instance
		$this->maximumBufferSize($intBufferSize);
		// Set the address into the instance
		$this->address($strAddress);
		// Set the port into the instance
		$this->port($intPort);
		// Check the IP address
		if (filter_var($strAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false) {
			// Create our IPv4 master socket
			$this->master(socket_create(AF_INET, SOCK_STREAM, SOL_TCP));
		} elseif (filter_var($strAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false) {
			// Create our IPv6 master socket
			$this->master(socket_create(AF_INET6, SOCK_STREAM, SOL_TCP));
		} else {
			// We're done, throw an exception
			throw new Core\Exception\Transport\Web\Socket(sprintf('%s is not a valid network address', $strAddress), 500);
		}
		// Make sure we have a socket
		if ($this->mMaster === false) {
			// We're done, throw an exception
			throw new Core\Exception\Transport\Web\Socket(sprintf('Unable to create socket:  %s', socket_strerror(socket_last_error())), 500);
		}
		// We want to reuse the address
		if (!socket_set_option($this->mMaster, SOL_SOCKET, SO_REUSEADDR, 1)) {
			// We're done, throw an exception
			throw new Core\Exception\Transport\Web\Socket(sprintf('Unable to set socket option:  %s', socket_strerror(socket_last_error())), 500);
		}
		// Bind the socket
		if (!socket_bind($this->mMaster, $this->mAddress, $this->mPort)) {
			// We're done, throw an exception
			throw new Core\Exception\Transport\Web\Socket(sprintf('Unable to bind to address/port:  %s', socket_strerror(socket_last_error())), 500);
		}
		// Start the socket
		if (!socket_listen($this->mMaster, 20)) {
			// We're done, throw an exception
			throw new Core\Exception\Transport\Web\Socket(sprintf('Unable to listen:  %s', socket_strerror(socket_last_error())), 500);
		}
		// Set the master into the socket list
		$this->socket(static::MASTER, $this->master());
		// Instantiate the container
		$this->container(new Request\Container());
		// Send the debug message
		$this->debug(sprintf('Server listening on %s:%d with %s%s', $this->mAddress, $this->mPort, $this->mMaster, PHP_EOL));
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Abstract Methods /////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method is called when the handshake completed
	 * @abstract
	 * @access protected
	 * @name \Crux\Transport\Web\Socket::connected()
	 * @package \Crux\Transport\Web\Socket
	 * @param \Crux\Transport\Web\Client $wsClient
	 * @return void
	 */
	abstract protected function connected(Client $wsClient);

	/**
	 * This method is called when data is received from the client connection
	 * @abstract
	 * @access protected
	 * @name \Crux\Transport\Web\Socket::data()
	 * @package \Crux\Transport\Web\Socket
	 * @param \Crux\Transport\Web\Client $wsClient
	 * @param string $strMessage
	 * @return void
	 */
	abstract protected function data(Client $wsClient, string $strMessage);

	/**
	 * This method is called when the connection is closed
	 * @abstract
	 * @access protected
	 * @name \Crux\Transport\Web\Socket::disconnected()
	 * @package \Crux\Transport\Web\Socket
	 * @param \Crux\Transport\Web\Client $wsClient
	 * @return void
	 */
	abstract protected function disconnected(Client $wsClient);

	/**
	 * This method is called when a client connection is incoming (directly before the handshake)
	 * @abstract
	 * @access protected
	 * @name \Crux\Transport\Web\Socket::incoming()
	 * @package \Crux\Transport\Web\Socket
	 * @param \Crux\Transport\Web\Client $wsClient
	 * @return void
	 */
	abstract protected function incoming(Client $wsClient);

	/**
	 * This method gets called at least every second for processes that need to be executed periodically
	 * @abstract
	 * @access protected
	 * @name \Crux\Transport\Web\Socket::tick()
	 * @package \Crux\Transport\Web\Socket
	 * @return void
	 */
	abstract protected function tick();

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Semi-Abstract Methods ////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method returns the favored extensions, it should be overridden and locked down
	 * @access protected
	 * @name \Crux\Transport\Web\Socket::extensions()
	 * @package \Crux\Transport\Web\Socket
	 * @param array<int, string> $arrExtensions
	 * @return string
	 */
	protected function extensions(array $arrExtensions) : string
	{
		// Return an empty string by default as this method should be overridden
		return '';
	}

	/**
	 * This method returns the favored protocol, it should be overridden and locked down
	 * @access protected
	 * @name \Crux\Transport\Web\Socket::protocol()
	 * @package \Crux\Transport\Web\Socket
	 * @param string $strProtocol
	 * @return string
	 */
	protected function protocol(string $strProtocol) : string
	{
		// Return an empty string by default as this method should be overridden
		return '';
	}

	/**
	 * This method verifies that the client host is allowed to interact with the socket, it should be overridden and locked down
	 * @access protected
	 * @name \Crux\Transport\Web\Socket::verifyHost()
	 * @package \Crux\Transport\Web\Socket
	 * @param string $strHostName
	 * @return bool
	 */
	protected function verifyHost(string $strHostName) : bool
	{
		// Return true by default as this method should be overridden
		return true;
	}

	/**
	 * This method verifies that the origin header is valid, it should be overridden and locked down
	 * @access protected
	 * @name \Crux\Transport\Web\Socket::verifyOrigin()
	 * @package \Crux\Transport\Web\Socket
	 * @param string $strOrigin
	 * @return bool
	 */
	protected function verifyOrigin(string $strOrigin) : bool
	{
		// Return true by default as this method should be overridden
		return true;
	}

	/**
	 * This method verifies that the client extensions are accepted, this should be overridden and locked down
	 * @access protected
	 * @name \Crux\Transport\Web\Socket::verifyWebSocketExtensions()
	 * @package \Crux\Transport\Web\Socket
	 * @param array<int, string> $arrExtensions
	 * @return bool
	 */
	protected function verifyWebSocketExtensions(array $arrExtensions) : bool
	{
		// Return true by default as this method should be overridden
		return true;
	}

	/**
	 * This method verifies that the client protocol is accepted, it should be overridden and locked down
	 * @access protected
	 * @name \Crux\Transport\Web\Socket::verifyWebSocketProtocol()
	 * @package \Crux\Transport\Web\Socket
	 * @param string $strProtocol
	 * @return bool
	 */
	protected function verifyWebSocketProtocol(string $strProtocol) : bool
	{
		// Return true by default as this method should be overridden
		return true;
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Inline Methods ///////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method returns the socket binding address from the instance with the ability to reset it inline
	 * @access public
	 * @name \Crux\Transport\Web\Socket::address()
	 * @package \Crux\Reansport\Web\Socket
	 * @param string $strAddress ['']
	 * @return string
	 * @uses \Crux\Core\Is::empty()
	 * @uses filter_var()
	 */
	public function address(string $strAddress = '') : string
	{
		// Check for a provided address and valid IP
		if (!Core\Is::empty($strAddress) && (filter_var($strAddress, FILTER_VALIDATE_IP))) {
			// Reset the IP address into the instance
			$this->mAddress = $strAddress;
		}
		// Return the address from the instance
		return $this->mAddress;
	}

	/**
	 * This method returns a single client from the instance
	 * @access public
	 * @name \Crux\Transport\Web\Socket::client()
	 * @package \Crux\Reansport\Web\Socket
	 * @param int $intIndex
	 * @return \Crux\Transport\Web\Client
	 */
	public function client(int $intIndex) : Client
	{
		// Return the client from the instance
		return ($this->mClients[$intIndex] ?? null);
	}

	/**
	 * This method returns the request container from the instance with the ability to reset it inline
	 * @access public
	 * @name \Crux\Transport\Web\Socket::container()
	 * @package \Crux\Transport\Web\Socket
	 * @param \Crux\Request\Container $httpRequest [null]
	 * @return \Crux\Request\Container
	 */
	public function container(Request\Container $httpRequest = null) : Request\Container
	{
		// Check for a provided container
		if (!Core\Is::null($httpRequest)) {
			// Reset the container into the instance
			$this->mContainer = $httpRequest;
		}
		// Return the container from the instance
		return $this->mContainer;
	}

	/**
	 * This method returns the clients from the instance with the ability to reset them inline
	 * @access public
	 * @name \Crux\Transport\Web\Socket::clients()
	 * @package \Crux\Reansport\Web\Socket
	 * @param array<int, \Crux\Transport\Web\Client> $arrClients [null]
	 * @return array<int, \Crux\Transport\Web\Client>
	 * @uses \Crux\Core\Is::null()
	 */
	public function clients(array $arrClients = null) : array
	{
		// Check for provided clients
		if (!Core\Is::null($arrClients)) {
			// Reset the clients into the instance
			$this->mClients = $arrClients;
		}
		// Return the clients from the instance
		return $this->mClients;
	}

	/**
	 * This method returns the "Interactive" flag from the instance with the ability to reset it inline
	 * @access public
	 * @name \Crux\Transport\Web\Socket::interactive()
	 * @package \Crux\Reansport\Web\Socket
	 * @param bool $blnFlag [null]
	 * @return bool
	 * @uses \Crux\Core\Is::null()
	 */
	public function interactive(bool $blnFlag = null) : bool
	{
		// Check for a provided flag
		if (!Core\Is::null($blnFlag)) {
			// Reset the flag into the instance
			$this->mInteractive = $blnFlag;
		}
		// Return the flag from the instance
		return $this->mInteractive;
	}

	/**
	 * This method returns the master socket resource from the instance with the ability to reset it inline
	 * @access public
	 * @name \Crux\Transport\Web\Socket::master()
	 * @package \Crux\Reansport\Web\Socket
	 * @param resource $rscSocket [null]
	 * @return resource
	 * @uses \Crux\Core\Is::null()
	 * @uses \Crux\Core\Is::resource()
	 */
	public function master($rscSocket = null)
	{
		// Check for a provided socket
		if (!Core\Is::null($rscSocket) && Core\Is::resource($rscSocket)) {
			// Reset the master socket into the instance
			$this->mMaster = $rscSocket;
		}
		// Return the master socket from the instance
		return $this->mMaster;
	}

	/**
	 * This method returns the maximum buffer length from the instance with the ability to reset it inline
	 * @access public
	 * @name \Crux\Transport\Web\Socket::maximumBufferSize()
	 * @package \Crux\Reansport\Web\Socket
	 * @param int $intBufferSize [nu;;]
	 * @return int
	 * @uses \Crux\Core\Is::null()
	 */
	public function maximumBufferSize(int $intBufferSize = null) : int
	{
		// Check for a provided buffer size
		if (!Core\Is::null($intBufferSize)) {
			// Reset the maximum buffer size into the instance
			$this->mMaximumBufferSize = $intBufferSize;
		}
		// Return the maximum buffer size from the instance
		return $this->mMaximumBufferSize;
	}

	/**
	 * This method returns a single pending message from the instance with the ability to unset it inline
	 * @access public
	 * @name \Crux\Transport\Web\Socket::pendingMessage()
	 * @package \Crux\Reansport\Web\Socket
	 * @param int $intIndex
	 * @param bool $blnUnset [false]
	 * @return bool
	 * @uses isset()
	 * @uses unset()
	 */
	public function pendingMessage(int $intIndex, bool $blnUnset = false)
	{
		// Check the unset flag
		if ($blnUnset && isset($this->mPendingMessages[$intIndex])) {
			// Localize the message
			$mixMessage = $this->mPendingMessages[$intIndex];
			// Unset the key
			unset($this->mPendingMessages[$intIndex]);
			// Return the unset message
			return $mixMessage;
		}
		// Return the message from the instance
		return ($this->mPendingMessages[$intIndex] ?? null);
	}

	/**
	 * This method returns the pending messages from the instance with the ability to reset them inline
	 * @access public
	 * @name \Crux\Transport\Web\Socket::pendingMessages()
	 * @package \Crux\Reansport\Web\Socket
	 * @param array<int, mixed> $arrMessages [null]
	 * @return array<int, mixed>
	 * @uses \Crux\Core\Is::null()
	 */
	public function pendingMessages(array $arrMessages = null) : array
	{
		// Check for provided messages
		if (!Core\Is::null($arrMessages)) {
			// Reset the pending messages into the instance
			$this->mPendingMessages = $arrMessages;
		}
		// Return the pending messages from the instance
		return $this->mPendingMessages;
	}

	/**
	 * This method returns the socket binding port with the ability to reset it inline
	 * @access public
	 * @name \Crux\Transport\Web\Socket::port()
	 * @package \Crux\Reansport\Web\Socket
	 * @param int $intPort [null]
	 * @return int
	 * @uses \Crux\Core\Is::null()
	 */
	public function port(int $intPort = null) : int
	{
		// Check for a provided port
		if (!Core\Is::null($intPort)) {
			// Reset the port into the instance
			$this->mPort = $intPort;
		}
		// Return the port from the instance
		return $this->mPort;
	}

	/**
	 * This method returns a socket from the instance with the ability to reset it inline
	 * @access public
	 * @name \Crux\Transport\Web\Socket::socket()
	 * @package \Crux\Reansport\Web\Socket
	 * @param string $strName
	 * @param resource $rscSocket [null]
	 * @return bool
	 * @uses \Crux\Core\Is::null()
	 * @uses \Crux\Core\Is::resource()
	 */
	public function socket(string $strName, $rscSocket = null)
	{
		// Check for a provided socket
		if (!Core\Is::null($rscSocket) && Core\Is::resource($rscSocket)) {
			// Reset the socket into the instance
			$this->mSockets[$strName] = $rscSocket;
		}
		// Return the socket from the instance
		return ($this->mSockets[$strName] ?? null);
	}

	/**
	 * This method returns the sockets from the instance with the ability to reset them inline
	 * @access public
	 * @name \Crux\Transport\Web\Socket::sockets()
	 * @package \Crux\Transport\Web\Socket
	 * @param array<string, resource> $arrSockets [null]
	 * @return array<string, resource>
	 * @uses \Crux\Core\Is::null()
	 */
	public function sockets(array $arrSockets = null) : array
	{
		// Check for provided sockets
		if (!Core\Is::null($arrSockets)) {
			// Reset the sockets into the instance
			$this->mSockets = $arrSockets;
		}
		// Return the sockets from the instance
		return $this->mSockets;
	}

	/**
	 * This method returns the "require Origin header" flag from the instance with the ability to reset it inline
	 * @access public
	 * @name \Crux\Transport\Web\Socket::requireHeaderOrigin()
	 * @package \Crux\Reansport\Web\Socket
	 * @param bool $blnFlag [null]
	 * @return bool
	 * @uses \Crux\Core\Is::null()
	 */
	public function requireHeaderOrigin(bool $blnFlag = null) : bool
	{
		// Check for a provided flag
		if (!Core\Is::null($blnFlag)) {
			// Reset the flag into the instance
			$this->mRequireHeaderOrigin = $blnFlag;
		}
		// Return the flag from the instance
		return $this->mRequireHeaderOrigin;
	}

	/**
	 * This method returns the "require Sec-WebSocket-Extensions header" flag from the instance with the ability to reset it inline
	 * @access public
	 * @name \Crux\Transport\Web\Socket::requireHeaderSecWebSocketExtensions()
	 * @package \Crux\Reansport\Web\Socket
	 * @param bool $blnFlag [null]
	 * @return bool
	 * @uses \Crux\Core\Is::null()
	 */
	public function requireHeaderSecWebSocketExtensions(bool $blnFlag = null) : bool
	{
		// Check for a provided flag
		if (!Core\Is::null($blnFlag)) {
			// Reset the flag into the instance
			$this->mRequireHeaderSWSE = $blnFlag;
		}
		// Return the flag from the instance
		return $this->mRequireHeaderSWSE;
	}

	/**
	 * This method returns the "require Sec-WebSocket-Protocol header" flag from the instance with the ability to reset it inline
	 * @access public
	 * @name \Crux\Transport\Web\Socket::requireHeaderSecWebSocketProtocol()
	 * @package \Crux\Reansport\Web\Socket
	 * @param bool $blnFlag [null]
	 * @return bool
	 * @uses \Crux\Core\Is::null()
	 */
	public function requireHeaderSecWebSocketProtocol(bool $blnFlag = null) : bool
	{
		// Check for a provided flag
		if (!Core\Is::null($blnFlag)) {
			// Reset the flag into the instance
			$this->mRequireHeaderSWSP = $blnFlag;
		}
		// Return the flag from the instance
		return $this->mRequireHeaderSWSP;
	}

	/**
	 * This method is an alias for \Crux\Transport\Web\Socket::requireHeaderSecWebSocketExtensions()
	 * @access public
	 * @name \Crux\Transport\Web\Socket::requireHeaderSWSE()
	 * @package \Crux\Transport\Web\Socket
	 * @param bool $blnFlag [null]
	 * @return bool
	 * @uses \Crux\Transport\Web\Socket::requireHeaderSecWebSocketExtensions()
	 */
	public function requireHeaderSWSE(bool $blnFlag = null) : bool
	{
		// Return the primary method
		return $this->requireHeaderSecWebSocketExtensions($blnFlag);
	}

	/**
	 * This method is an alias for \Crux\Transport\Web\Socket::requireHeaderSecWebSocketProtocol()
	 * @access public
	 * @name \Crux\Transport\Web\Socket::requireHeaderSWSP()
	 * @package \Crux\Transport\Web\Socket
	 * @param bool $blnFlag [null]
	 * @return bool
	 * @uses \Crux\Transport\Web\Socket::requireHeaderSecWebSocketProtocol()
	 */
	public function requireHeaderSWSP(bool $blnFlag = null) : bool
	{
		// Return the primary method
		return $this->requireHeaderSecWebSocketProtocol($blnFlag);
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Protected Methods ////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method calculates the header offset
	 * @access protected
	 * @name \Crux\Transport\Web\Socket::calculateOffset()
	 * @package \Crux\Transport\Web\Socket
	 * @param array<string, mixed> $arrHeaders
	 * @return int
	 */
	protected function calculateOffset(array $arrHeaders) : int
	{
		// Define our offset
		$intOffset = 2;
		// Check for a hasmask header
		if ($arrHeaders['hasmask']) {
			// Increment the offset
			$intOffset += 4;
		}
		// Check the header length
		if ($arrHeaders['length'] > 65535) {
			// Increment the offset
			$intOffset += 8;
		} elseif ($arrHeaders['length'] > 125) {
			// Increment the offset
			$intOffset += 2;
		} else {
			// Just here for sanity sake
			$intOffset += 0;
		}
		// We're done, return the offset
		return $intOffset;
	}

	/**
	 * This method locates a client based on the socket resource
	 * @access protected
	 * @name \Crux\Transport\Web\Socket::clientBySocket()
	 * @package \Crux\Transport\Web\Socket
	 * @param resource $rscSocket
	 * @return \Crux\Transport\Web\Client|null
	 * @uses \Crux\Transport\Web\Socket::clients()
	 * @uses \Crux\Transport\Web\Client::socket()
	 */
	protected function clientBySocket($rscSocket) : Client
	{
		// Iterate over the client
		foreach ($this->clients() as $sockClient) {
			// Check the client socket
			if ($sockClient->socket() === $rscSocket) {
				// We have a match, return the client
				return $sockClient;
			}
		}
		// We do not have a match, return null
		return null;
	}


	protected function rsvBits(array $arrHeaders, Client $sockClient) : bool
	{
		// Check the headers
		if ((ord($arrHeaders['rsv1']) + ord($arrHeaders['rsv2']) + ord($arrHeaders['rsv3'])) > 0) {
			// Disconnect the client
			$this->disconnect($sockClient->socket());
			// We're done
			return true;
		} else {
			// We're done
			return false;
		}
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Public Methods ///////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method sends a message to the user if the socket is running interactively
	 * @access public
	 * @name \Crux\Transport\Web\Socket::debug()
	 * @package \Crux\Transport\Web\Socket
	 * @param string $strMessage
	 * @uses \Crux\Transport\Web\Socket::interactive()
	 */
	public function debug(string $strMessage)
	{
		// Check the interactive flag
		if ($this->interactive()) {
			// Send the message
			echo $strMessage;
		}
	}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
} /// End Crux\Transport\Web\Socket Class Definition /////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

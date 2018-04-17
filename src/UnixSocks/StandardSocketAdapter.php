<?php
namespace UnixSocks;


class StandardSocketAdapter implements ISocketAdapter
{
	/**
	 * Create a socket (endpoint for communication)
	 * @param int $domain
	 * @param int $type
	 * @param int $protocol
	 * @return resource Returns a socket resource on success, or <b>FALSE</b> on error
	 */
	public function create($domain, $type, $protocol)
	{
		return socket_create($domain, $type, $protocol);
	}
	
	/**
	 * Initiates a connection on a socket
	 * @param resource $socket
	 * @param string $address
	 * @param int $port [optional]
	 */
	public function connect($socket, $address): void
	{
		socket_connect($socket, $address);
	}
	
	/**
	 * Binds a name to a socket
	 * @param resource $socket
	 * @param string $address
	 * @param int $port [optional]
	 * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure
	 */
	public function bind($socket, $address): void
	{
		socket_bind($socket, $address);
	}
	
	/**
	 * Listens for a connection on a socket
	 * @param resource $socket
	 * @param int $backlog [optional]
	 * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure
	 */
	public function listen($socket): void
	{
		socket_listen($socket);
	}
	
	/**
	 * Accepts a connection on a socket
	 * @param resource $socket
	 * @return resource a new socket resource on success, or <b>FALSE</b> on error
	 */
	public function accept($socket)
	{
		return socket_accept($socket);
	}
	
	public function setNonblock($socket): void
	{
		socket_set_nonblock($socket);
	}
	
	/**
	 * Closes a socket resource
	 * @param resource $socket
	 */
	public function close($socket): void
	{
		socket_close($socket);
	}
	
	/**
	 * Reads a maximum of length bytes from a socket
	 * @param resource $socket
	 * @param int $length
	 * @param int $type [optional]
	 * @return string Returns the data as a string on success,
	 * or <b>FALSE</b> on error (including if the remote host has closed the
	 * connection)
	 */
	public function read($socket, $length): ?string
	{
		$result = socket_read($socket, $length, PHP_BINARY_READ);
		
		var_dump($result, socket_last_error($socket));
		
		return $result;
	}
	
	/**
	 * Write to a socket
	 * @param resource $socket
	 * @param string $buffer
	 * @param int $length [optional]
	 * @return int the number of bytes successfully written to the socket or <b>FALSE</b> on failure
	 */
	public function write($socket, $buffer): void
	{
		socket_write($socket, $buffer);
	}
}
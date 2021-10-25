<?php
namespace UnixSocks;


class StandardSocket
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
	 * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure
	 */
	public function connect($socket, $address, $port = 0)
	{
		return socket_connect($socket, $address, $port);
	}
	
	/**
	 * Binds a name to a socket
	 * @param resource $socket
	 * @param string $address
	 * @param int $port [optional]
	 * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure
	 */
	public function bind($socket, $address, $port = 0)
	{
		return socket_bind($socket, $address, $port);
	}
	
	/**
	 * Listens for a connection on a socket
	 * @param resource $socket
	 * @param int $backlog [optional]
	 * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure
	 */
	public function listen($socket, $backlog = 0)
	{
		return socket_listen($socket, $backlog);
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
	
	/**
	 * Sets nonblocking mode for file descriptor fd
	 * @param resource $socket
	 * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure
	 */
	public function setNonblock($socket)
	{
		return socket_set_nonblock($socket);
	}
	
	/**
	 * Closes a socket resource
	 * @param resource $socket
	 */
	public function close($socket)
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
	public function read($socket, $length, $type = PHP_BINARY_READ)
	{
		$result = socket_read($socket, $length, $type);
		
		if (!$result)
		{
			$result = socket_read($socket, $length, PHP_NORMAL_READ);
			
			if ($result === false)
			{
				throw new \Exception("There was a problem reading from socket: " . socket_strerror(socket_last_error()));
			}
		}
		
		return $result;
	}
	
	/**
	 * Write to a socket
	 * @param resource $socket
	 * @param string $buffer
	 * @param int $length [optional]
	 * @return int the number of bytes successfully written to the socket or <b>FALSE</b> on failure
	 */
	public function write($socket, $buffer, $length = 0)
	{
		return socket_write($socket, $buffer, $length);
	}
}
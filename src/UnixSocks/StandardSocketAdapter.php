<?php
namespace UnixSocks;


use UnixSocks\Exceptions\Comms\ConnectionLostException;


class StandardSocketAdapter implements ISocketAdapter
{
	/**
	 * @param int $domain
	 * @param int $type
	 * @param int $protocol
	 * @return resource
	 */
	public function create($domain, $type, $protocol)
	{
		return socket_create($domain, $type, $protocol);
	}
	
	/**
	 * @param resource $socket
	 * @param string $address
	 */
	public function connect($socket, $address): void
	{
		socket_connect($socket, $address);
	}
	
	/**
	 * @param resource $socket
	 * @param string $address
	 */
	public function bind($socket, $address): void
	{
		socket_bind($socket, $address);
	}
	
	/**
	 * @param resource $socket
	 */
	public function listen($socket): void
	{
		socket_listen($socket);
	}
	
	/**
	 * @param resource $socket
	 * @return resource
	 */
	public function accept($socket)
	{
		return socket_accept($socket);
	}
	
	public function setNonBlocking($socket): void
	{
		socket_set_nonblock($socket);
	}
	
	/**
	 * @param resource $socket
	 */
	public function close($socket): void
	{
		socket_close($socket);
	}
	
	/**
	 * @param resource $socket
	 * @param int $length
	 * @return string|null
	 */
	public function read($socket, int $length): ?string
	{
		$result = socket_read($socket, $length, PHP_BINARY_READ);
		
		if ($result === '')
		{
			$result = socket_read($socket, $length, PHP_NORMAL_READ);
			
			if (!$result)
				throw new ConnectionLostException($socket);
		}
		
		return $result;
	}
	
	/**
	 * @param resource $socket
	 * @param string $buffer
	 */
	public function write($socket, $buffer): void
	{
		$result = socket_write($socket, $buffer);
		
		if ($result === false)
			throw new ConnectionLostException($socket);
	}
}
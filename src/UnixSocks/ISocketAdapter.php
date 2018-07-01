<?php
namespace UnixSocks;


interface ISocketAdapter
{
	/**
	 * @param int $domain
	 * @param int $type
	 * @param int $protocol
	 * @return resource
	 */
	public function create($domain, $type, $protocol);
	
	/**
	 * @param resource $socket
	 * @param string $address
	 */
	public function connect($socket, $address): void;
	
	/**
	 * @param resource $socket
	 * @param string $address
	 */
	public function bind($socket, $address): void;
	
	/**
	 * @param resource $socket
	 */
	public function listen($socket): void;
	
	/**
	 * @param resource $socket
	 * @return resource 
	 */
	public function accept($socket);
	
	/**
	 * @param resource $socket
	 */
	public function setNonBlocking($socket): void;
	
	/**
	 * @param resource $socket
	 */
	public function close($socket): void;
	
	/**
	 * @param resource $socket
	 * @param int $length
	 * @return string|null
	 */
	public function read($socket, int $length): ?string;
	
	/**
	 * @param resource $socket
	 * @param string $buffer
	 */
	public function write($socket, $buffer): void;
}
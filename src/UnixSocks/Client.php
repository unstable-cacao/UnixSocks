<?php
namespace UnixSocks;


use UnixSocks\Exceptions;


class Client implements IClient
{
	/** @var string|null */
	private $file = null;
	
	/** @var IClientPlugin|null */
	private $plugin;
	
	/** @var string */
	private $buffer = '';
	
	
	private function readIntoInternalBuffer(int $maxLength = 1024): void
	{
		// TODO: Read and store in $buffer
	}
	
	private function getFromBuffer(int $maxLength): string
	{
		if (strlen($this->buffer) < $maxLength)
		{
			$result = $this->buffer;
			$this->buffer = '';
		}
		else
		{
			$result = substr($this->buffer, 0, $maxLength);
			$this->buffer = substr($this->buffer, $maxLength);
		}
		
		return $result;
	}
	
	private function validateOpen(): void
	{
		if ($this->isClosed())
		{
			throw new Exceptions\NoConnectionException();
		}
	}
	
	private function validateClosed(): void
	{
		if ($this->isClosed())
		{
			throw new Exceptions\ConnectionAlreadyOpenException();
		}
	}
	
	
	public function __construct(?string $file = null, ?IClientPlugin $plugin = null)
	{
		$this->file = $file;
		$this->plugin = $plugin;
	}
	
	public function __destruct()
	{
		$this->close();
	}
	
	
	public function setFile(string $path): void
	{
		$this->file = $path;
	}
	
	public function getFile(): string
	{
		return $this->file ?? '';
	}
	
	public function connect(?float $timeout = null): bool
	{
		$this->validateClosed();
		// TODO: Implement connect() method.
	}
	
	public function accept(?float $timeout = null): bool
	{
		$this->validateClosed();
		// TODO: Implement accept() method.
	}
	
	public function close(): void
	{
		// TODO: Implement close() method. Do nothing if already closed
		$this->buffer = '';
	}
	
	public function isOpen(): bool
	{
		// TODO: Implement isOpen() method.
	}
	
	public function isClosed(): bool
	{
		// TODO: Implement isClosed() method.
	}
	
	/**
	 * @return resource|null
	 */
	public function getSocket()
	{
		// TODO: Implement getSocket() method.
	}
	
	public function hasInput(): bool
	{
		if (!$this->buffer)
		{
			$this->readIntoInternalBuffer();
		}
		
		return $this->buffer;
	}
	
	public function read(int $maxLength = 1024, ?float $timeout = null): ?string
	{
		$this->validateOpen();
		// TODO: Read from buffer + if any thing remains in the incoming connection up to $maxLength.
		
		$result = '';
		$this->plugin->read($this, $result);
		
		return $result;
	}
	
	public function readBuffer(int $length = 1024, ?float $timeout = null): ?string
	{
		$this->validateOpen();
		// TODO: If buffer have the required length, read from it only.
		
		$result = '';
		$this->plugin->read($this, $result);
		
		return $result;
	}
	
	public function readLine(?float $timeout = null, ?int $maxLength = null): ?string
	{
		return $this->readUntil(['\n', '\r'], $timeout, $maxLength);
	}
	
	/**
	 * @param string|string[] $stop
	 * @param float|null $timeout
	 * @param int|null $maxLength
	 * @return string|null
	 */
	public function readUntil($stop, ?float $timeout = null, ?int $maxLength = null): ?string
	{
		$this->validateOpen();
		
		// TODO: Implement readUntil() method.
		
		$result = '';
		$this->plugin->read($this, $result);
		
		return $result;
	}
	
	
	public function write(string $input): void
	{
		$this->validateOpen();
		$this->plugin->write($this, $input);
	}
	
	public function writeLine(string $input): void
	{
		$this->write($input . PHP_EOL);
	}
}
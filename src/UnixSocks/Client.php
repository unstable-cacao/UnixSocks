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
	
	/** @var resource|null */
	private $ioSocket;
	
	/** @var array */
	private $allSockets = [];
	
	
	private function readIntoInternalBuffer(int $maxLength = 1024): void
	{
		$this->validateOpen();
		$readData = socket_read($this->ioSocket, $maxLength);
		
		if ($readData)
		{
			$this->buffer .= $readData;
		}
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
		if (!$this->isClosed())
		{
			throw new Exceptions\ConnectionAlreadyOpenException();
		}
	}
	
	private function validateFile(): void
	{
		if (!$this->file)
		{
			throw new \Exception("File not set");
		}
	}
	
	private function validateTimeout($timeout): void
	{
		if (!(is_null($timeout) || $timeout >= 0))
			throw new \Exception("Timeout must be 0 or bigger, or null");
	}
	
	private function validateLength($length): void
	{
		if (!(is_null($length) || $length > 0))
			throw new \Exception("Length must be null or bigger than 0");
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
	
	public function tryConnect(): bool 
	{
		$this->validateClosed();
		$conn = socket_create(AF_UNIX, SOCK_STREAM, 0);
		
		if (!$conn)
			return false;
		
		$this->validateFile();
		
		if (!socket_connect($conn, $this->file))
			return false;
		
		$this->ioSocket = $conn;
		$this->allSockets = [$conn];
		
		return true;
	}
	
	public function connect(): void
	{
		$this->validateClosed();
		$conn = socket_create(AF_UNIX, SOCK_STREAM, 0);
		
		if (!$conn) 
			throw new \Exception("Failed to create socket");
		
		$this->validateFile();
		
		if (!socket_connect($conn, $this->file))
			throw new \Exception("Failed to connect to socket");
		
		$this->ioSocket = $conn;
		$this->allSockets[] = $conn;
	}
	
	public function accept(?float $timeout = null): bool
	{
		$this->validateClosed();
		$conn = socket_create(AF_UNIX, SOCK_STREAM, 0);
		
		if (!$conn)
			return false;
		
		$this->validateFile();
		
		if (!socket_bind($conn, $this->file))
			return false;
		
		if (is_null($timeout))
		{
			socket_set_blocking($conn, true);
			$this->ioSocket = socket_accept($conn);
			$this->allSockets = [$this->ioSocket, $conn];
			
			return $this->isOpen();
		}
		else
		{
			$timeoutTime = (float)time() + $timeout;
			socket_set_blocking($conn, false);
			
			while (microtime(true) <= $timeoutTime)
			{
				$client = socket_accept($conn);
				
				if ($client)
				{
					$this->ioSocket = $client;
					$this->allSockets = [$client, $conn];
					
					return true;
				}
			}
		}
		
		return false;
	}
	
	public function close(): void
	{
		foreach ($this->allSockets as $socket)
		{
			socket_close($socket);
		}
		
		if ($this->file && file_exists($this->file))
		{
			unlink($this->file);
		}
		
		$this->buffer = '';
		$this->allSockets = [];
		$this->ioSocket = null;
	}
	
	public function isOpen(): bool
	{
		return $this->ioSocket ? true : false;
	}
	
	public function isClosed(): bool
	{
		return $this->ioSocket ? false : true;
	}
	
	/**
	 * @return resource|null
	 */
	public function getSocket()
	{
		return $this->ioSocket;
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
		$this->validateTimeout($timeout);
		$this->validateLength($maxLength);
		
		if (is_null($timeout))
		{
			while (strlen($this->buffer) < $maxLength)
			{
				$this->readIntoInternalBuffer($maxLength);
			}
		}
		else
		{
			$timeoutTime = (float)time() + $timeout;
			$isRunning = true;
			$this->readIntoInternalBuffer($maxLength);
			
			while ($isRunning && microtime(true) <= $timeoutTime && strlen($this->buffer) < $maxLength)
			{
				$this->readIntoInternalBuffer($maxLength);
			}
		}
		
		if (!$this->buffer)
			$result = null;
		else
			$result = $this->getFromBuffer($maxLength);
		
		$this->plugin->read($this, $result);
		
		return $result;
	}
	
	public function readExactly(int $length = 1024, ?float $timeout = null): ?string
	{
		$this->validateOpen();
		$this->validateTimeout($timeout);
		$this->validateLength($length);
		
		if (is_null($timeout))
		{
			while (strlen($this->buffer) < $length)
			{
				$this->readIntoInternalBuffer($length);
			}
		}
		else
		{
			$timeoutTime = (float)time() + $timeout;
			$isRunning = true;
			$this->readIntoInternalBuffer($length);
			
			while ($isRunning && microtime(true) <= $timeoutTime && strlen($this->buffer) < $length)
			{
				$this->readIntoInternalBuffer($length);
			}
		}
		
		if (!$this->buffer || strlen($this->buffer) < $length)
			$result = null;
		else
			$result = $this->getFromBuffer($length);
		
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
		
		if (!$stop)
			throw new \Exception("Stop parameter required");
		
		$this->validateTimeout($timeout);
		$this->validateLength($maxLength);
		
		if (!is_array($stop))
			$stop = [$stop];
		
		$result = null;
		
		if (is_null($timeout))
		{
			$isRunning = true;
			
			while ($isRunning)
			{
				$this->readIntoInternalBuffer();
				
				if (is_null($maxLength))
				{
					$firstStop = false;
					
					foreach ($stop as $stopString)
					{
						$position = strpos($this->buffer, $stopString);
						
						if ($position !== false)
						{
							$firstStop = $firstStop === false ? $position : min($firstStop, $position);
						}
					}
					
					if ($firstStop !== false)
					{
						$result = $this->getFromBuffer($firstStop + 1);
						$isRunning = false;
					}
				}
				else
				{
					if (strlen($this->buffer) >= $maxLength)
						$isRunning = false;
					
					$firstStop = false;
					
					foreach ($stop as $stopString)
					{
						$position = strpos($this->buffer, $stopString);
						
						if ($position !== false)
						{
							$firstStop = $firstStop === false ? $position : min($firstStop, $position);
						}
					}
					
					if ($firstStop !== false || !$isRunning)
					{
						$isRunning = false;
						
						if ($firstStop + 1 > $maxLength)
							$result = $this->getFromBuffer($maxLength);
						else
							$result = $this->getFromBuffer($firstStop + 1);
					}
				}
			}
		}
		else if ($timeout == 0)
		{
			if (is_null($maxLength))
			{
				$isRunning = true;
				$readFromSocket = socket_read($this->ioSocket, 1024);
				
				while ($isRunning)
				{
					if ($readFromSocket)
					{
						$this->buffer .= $readFromSocket;
						$readFromSocket = socket_read($this->ioSocket, 1024);
					}
					else
					{
						$isRunning = false;
					}
				}
				
				$firstStop = false;
				
				foreach ($stop as $stopString)
				{
					$position = strpos($this->buffer, $stopString);
					
					if ($position !== false)
					{
						$firstStop = $firstStop === false ? $position : min($firstStop, $position);
					}
				}
				
				if ($firstStop !== false)
				{
					$result = $this->getFromBuffer($firstStop + 1);
				}
			}
			else 
			{
				$this->readIntoInternalBuffer($maxLength);
				$firstStop = false;
				
				foreach ($stop as $stopString)
				{
					$position = strpos($this->buffer, $stopString);
					
					if ($position !== false)
					{
						$firstStop = $firstStop === false ? $position : min($firstStop, $position);
					}
				}
				
				if ($firstStop !== false)
				{
					if ($firstStop + 1 > $maxLength)
						$result = $this->getFromBuffer($maxLength);
					else
						$result = $this->getFromBuffer($firstStop + 1);
				}
			}
		}
		else
		{
			$timeoutTime = (float)time() + $timeout;
			$isRunning = true;
			
			socket_set_blocking($this->ioSocket, false);
			
			while ($isRunning && microtime(true) <= $timeoutTime)
			{
				$result = socket_read($this->ioSocket, $maxLength);
				
				if ($result)
				{
					$isRunning = false;
					
					if (strlen($result) > $maxLength)
					{
						$this->buffer = substr($result, $maxLength);
						$result = substr($result, 0, $maxLength);
					}
				}
			}
		}
		
		$this->plugin->read($this, $result);
		
		return $result;
	}
	
	
	public function write(string $input): void
	{
		$this->validateOpen();
		socket_write($this->ioSocket, $input);
		$this->plugin->write($this, $input);
	}
	
	public function writeLine(string $input): void
	{
		$this->write($input . PHP_EOL);
	}
}
<?php
namespace UnixSocks;


use UnixSocks\Exceptions;


class Client implements IClient
{
	private const BIG_FLOAT = 5000000000.0;
	
	
	/** @var string|null */
	private $file = null;
	
	/** @var IClientPlugin|null */
	private $plugin;
	
	/** @var string */
	private $buffer = '';
	
	/** @var ISocketAdapter */
	private $conn;
	
	/** @var resource|null */
	private $ioSocket;
	
	/** @var array */
	private $allSockets = [];
	
	private $fileWasExists = false;
	
	
	private function readIntoInternalBuffer(int $maxLength = 1024, ?string &$data = null): bool
	{
		$this->validateOpen();
		
		try
		{
			$data = $this->conn->read($this->ioSocket, $maxLength);
		}
		catch (Exceptions\Comms\ConnectionLostException $e)
		{
			$this->close();
			throw $e;
		}
		
		if ($data)
		{
			$this->buffer .= $data;
			return true;
		}
		else
		{
			return false;
		}
	}
	
	private function getFromBuffer(int $maxLength): ?string
	{
		if (!$this->buffer)
			return null;
		
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
			throw new Exceptions\FatalUnixSocksException("File not set");
		}
	}
	
	private function validateTimeout(&$timeout): void
	{
		if (!(is_null($timeout) || $timeout >= 0))
			throw new Exceptions\FatalUnixSocksException("Timeout must be 0 or bigger, or null");
		
		if (is_null($timeout))
			$timeout = self::BIG_FLOAT;
	}
	
	private function validateLength($length): void
	{
		if (!(is_null($length) || $length > 0))
			throw new Exceptions\FatalUnixSocksException("Length must be null or bigger than 0");
	}
	
	
	public function __construct(?string $file = null, ?ISocketAdapter $conn = null, ?IClientPlugin $plugin = null)
	{
		$this->conn = $conn ?: new StandardSocketAdapter();
		$this->file = $file;
		$this->plugin = $plugin;
		
		if ($file)
		{
			$this->fileWasExists = file_exists($file);
		}
	}
	
	public function __destruct()
	{
		$this->close();
	}
	
	
	public function setFile(string $path): void
	{
		$this->file = $path;
		$this->fileWasExists = file_exists($path);
	}
	
	public function getFile(): string
	{
		return $this->file ?? '';
	}
	
	public function tryConnect(): bool 
	{
		try
		{
			$this->connect();
		}
		catch (Exceptions\UnixSocksException $e)
		{
			return false;
		}
		
		return true;
	}
	
	public function connect(): void
	{
		$this->validateClosed();
		$conn = $this->conn->create(AF_UNIX, SOCK_STREAM, 0);
		
		$this->validateFile();
		$this->conn->connect($conn, $this->file);
		
		$this->ioSocket = $conn;
		$this->allSockets[] = $conn;
	}
	
	public function accept(?float $timeout = null): void
	{
		$this->validateClosed();
		$conn = $this->conn->create(AF_UNIX, SOCK_STREAM, 0);
		
		$this->validateFile();
		
		$this->conn->bind($conn, $this->file);
		$this->conn->listen($conn);
		
		if (is_null($timeout))
		{			
			$this->ioSocket = $this->conn->accept($conn);
			$this->allSockets = [$this->ioSocket, $conn];
		}
		else
		{
			$timeoutTime = (float)time() + $timeout;
			$this->conn->setNonBlocking($conn);
			
			while (microtime(true) <= $timeoutTime)
			{
				$client = $this->conn->accept($conn);
				
				if ($client)
				{
					$this->ioSocket = $client;
					$this->allSockets = [$client, $conn];
				}
			}
		}
		
		if ($this->ioSocket)
			$this->conn->setNonBlocking($this->ioSocket);
	}
	
	public function tryAccept(?float $timeout = null): bool
	{
		try
		{
			$this->accept($timeout);
		}
		catch (Exceptions\UnixSocksException $e)
		{
			return false;
		}
		
		return true;
	}
	
	public function close(): void
	{
		foreach ($this->allSockets as $socket)
		{
			$this->conn->close($socket);
		}
		
		if ($this->file && !$this->fileWasExists && file_exists($this->file))
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

	/**
	 * Read any input with maximum length of $maxLength. If a shorter string is ready, it will be returned.
	 * If $timeout is reached, null is returned.
	 * @param int $maxLength
	 * @param float|null $timeout
	 * @return null|string
	 */
	public function read(int $maxLength = 1024, ?float $timeout = null): ?string
	{
		$this->validateOpen();
		$this->validateTimeout($timeout);
		$this->validateLength($maxLength);
		
		$timeoutTime = microtime(true) + $timeout;
		$isRunning = true;
		
		while ($isRunning)
		{
			if ($this->readIntoInternalBuffer($maxLength))
				break;
			
			if (microtime(true) >= $timeoutTime)
				break;
			
			usleep(10000);
		}
		
		$result = $this->getFromBuffer($maxLength);
		
		if ($this->plugin)
			$this->plugin->read($this, $result);
		
		return $result;
	}

	/**
	 * Wait until exactly the sissified number of characters were read. If timeout is reached, null is retunred.
	 * @param int $length
	 * @param float|null $timeout
	 * @return null|string
	 */
	public function readExactly(int $length = 1024, ?float $timeout = null): ?string
	{
		$this->validateOpen();
		$this->validateTimeout($timeout);
		$this->validateLength($length);
		
		$timeoutTime = microtime(true) + $timeout;
		$isRunning = true;
		
		while ($isRunning)
		{
			$this->readIntoInternalBuffer($length);
			
			if (microtime(true) >= $timeoutTime)
				break;
			
			if (strlen($this->buffer) >= $length)
				break;
			
			usleep(10000);
		}
		
		if (strlen($this->buffer) < $length)
			$result = null;
		else
			$result = $this->getFromBuffer($length);
		
		if ($this->plugin)
			$this->plugin->read($this, $result);
		
		return $result;
	}
	
	public function readLine(?float $timeout = null, ?int $maxLength = null): ?string
	{
		return $this->readUntil(["\n", "\r"], $timeout, $maxLength);
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
			throw new Exceptions\FatalUnixSocksException("Stop parameter required");
		
		$this->validateTimeout($timeout);
		$this->validateLength($maxLength);
		
		$stop = (array)$stop;
		
		$result = null;
		$stopPosition = null;
		
		if (is_null($maxLength))
		{
			$breakWhenEmpty = false;
			
			if ($timeout == 0)
			{
				$timeout = 5000000;
				$breakWhenEmpty = true;
			}
			
			$timeoutTime = (float)time() + $timeout;
			$isRunning = true;
			
			while ($isRunning)
			{
				$this->readIntoInternalBuffer(1024, $readFromSocket);
					
				foreach ($stop as $stopString)
				{
					$position = strpos($this->buffer, $stopString);
					
					if ($position !== false)
					{
						$stopPosition = is_null($stopPosition) ? $position : min($stopPosition, $position);
					}
				}
				
				if (!$readFromSocket && $breakWhenEmpty)
					break;
				
				if (microtime(true) >= $timeoutTime)
					break;
				
				if (!is_null($stopPosition))
					break;
				
				usleep(10000);
			}
		}
		else
		{
			$timeoutTime = (float)time() + $timeout;
			$isRunning = true;
			
			while ($isRunning)
			{
				$this->readIntoInternalBuffer($maxLength);
				
				foreach ($stop as $stopString)
				{
					$position = strpos($this->buffer, $stopString);
					
					if ($position !== false)
					{
						$stopPosition = is_null($stopPosition) ? $position : min($stopPosition, $position);
					}
				}
				
				if (microtime(true) >= $timeoutTime)
					break;
				
				if (strlen($this->buffer) >= $maxLength)
					break;
				
				if (!is_null($stopPosition))
					break;
				
				usleep(10000);
			}
		}
		
		// $maxLength null and $stopPosition null
		if (!$maxLength && !$stopPosition)
			$result = null;
		// $maxLength and $stopPosition not null
		else if ($stopPosition && $maxLength && $stopPosition > $maxLength)
			$result = $this->getFromBuffer($maxLength);
		// $stopPosition not null and $maxLength null
		else if ($stopPosition)
			$result = $this->getFromBuffer($stopPosition + 1);
		// $maxLength not null and $stopPosition null
		else 
			$result = $this->getFromBuffer($maxLength);
		
		if ($this->plugin)
			$this->plugin->read($this, $result);
		
		return $result;
	}
	
	
	public function write(string $input): void
	{
		$this->validateOpen();
		
		try
		{
			$this->conn->write($this->ioSocket, $input);
		}
		catch (Exceptions\Comms\ConnectionLostException $e)
		{
			$this->close();
		}
		
		if ($this->plugin)
			$this->plugin->write($this, $input);
	}
	
	public function writeLine(string $input): void
	{
		$this->write($input . PHP_EOL);
	}
	
	public function getConnector(): ISocketAdapter
	{
		return $this->conn;
	}
	
	public function setNonBlocking(): void
	{
		$this->conn->setNonBlocking($this->ioSocket);
	}
	
}
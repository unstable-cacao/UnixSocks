<?php
namespace inc;


use UnixSocks\Client;
use UnixSocks\StandardSocket;


class Executor
{
	private $expect = null;
	
	/** @var Client */
	private $client;
	
	
	public function __construct()
	{
		$this->client = new Client(new StandardSocket(), __DIR__ . '/tmp/socket.io');
	}
	
	
	public function executeOne(string $command, ...$args): void
	{
		switch ($command)
		{
			case 'expectError':
				$this->expect = $args[0];
				break;
				
			case 'sleep':
				usleep($args[0] * 1000000);
				break;
			
			case 'expect':
				$expected = array_shift($args);
				$func = array_shift($args);
				
				$actual = $this->client->$func(...$args);
				
				if ($actual !== $expected)
				{
					throw new \Exception('Expected "' . 
						json_encode($expected) . '" but got "' . json_encode($actual) . '"');
				}
				
				break;
			
			default:
				$this->client->$command(...$args);
		}
	}
	
	
	public function execute(array $commands): void
	{
		try
		{
			foreach ($commands as $command)
			{
				$this->executeOne(...$command);
			}
		}
		catch (\Throwable $t)
		{
			if (get_class($t) !== $this->expect)
				throw $t;
		}
	}
}
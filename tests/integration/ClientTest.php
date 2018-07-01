<?php
namespace UnixSocks;


use inc\ScriptExecutor;
use PHPUnit\Framework\TestCase;


class ClientTest extends TestCase
{
	private static function cleanUp(): void
	{
		foreach (glob(__DIR__ . '/../inc/tmp/*.io') as $file)
		{
			unlink($file);
		}
	}


	public function test_sanity()
	{
		ScriptExecutor::run(
			[
				['accept'],
				['read']
			],
			[
				['connect'],
				['sleep', 10],
				['write', 'abc']
			],
			true);
		
		ScriptExecutor::run(
			[
				['accept'],
				['read']
			],
			[
				['sleep', 1],
				['connect'],
				['write', 'abc']
			],
			true);
	}
	
	public function test_read_maxLength()
	{
		ScriptExecutor::run(
			[
				['accept'],
				['read', 1]
			],
			[
				['sleep', 1],
				['connect'],
				['write', 'abc']
			],
			true);
	}
	
	public function test_read_timeout()
	{
		ScriptExecutor::run(
			[
				['accept'],
				['read', 100, 3]
			],
			[
				['sleep', 1],
				['connect'],
				['write', 'abc']
			],
			true);
	}
	
	public function test_readExactly_length()
	{
		ScriptExecutor::run(
			[
				['accept'],
				['readExactly', 2, 100]
			],
			[
				['sleep', 1],
				['connect'],
				['write', 'abc']
			],
			true);
	}
	
	public function test_readExactly_timeout()
	{
		ScriptExecutor::run(
			[
				['accept'],
				['readExactly', 500, 3]
			],
			[
				['sleep', 1],
				['connect'],
				['write', 'abc']
			],
			true);
	}
	
	public function test_readLine_line()
	{
		ScriptExecutor::run(
			[
				['accept'],
				['readLine']
			],
			[
				['sleep', 1],
				['connect'],
				['write', 'abc\nbcs']
			],
			true);
	}
	
	public function test_readLine_timeout()
	{
		ScriptExecutor::run(
			[
				['accept'],
				['readLine', 3]
			],
			[
				['sleep', 1],
				['connect'],
				['write', 'abc']
			],
			true);
	}
	
	public function test_readLine_maxLength()
	{
		ScriptExecutor::run(
			[
				['accept'],
				['readLine', 500, 2]
			],
			[
				['sleep', 1],
				['connect'],
				['write', 'abc']
			],
			true);
	}
	
	public function test_readUntil_stop()
	{
		ScriptExecutor::run(
			[
				['accept'],
				['readUntil', 'b']
			],
			[
				['sleep', 1],
				['connect'],
				['write', 'abc']
			],
			true);
	}
	
	public function test_readUntil_timeout()
	{
		ScriptExecutor::run(
			[
				['accept'],
				['readUntil', 'f', 3]
			],
			[
				['sleep', 1],
				['connect'],
				['write', 'abc']
			],
			true);
	}
	
	public function test_readUntil_maxLength()
	{
		ScriptExecutor::run(
			[
				['accept'],
				['readUntil', 'f', null, 2]
			],
			[
				['sleep', 1],
				['connect'],
				['write', 'abc']
			],
			true);
	}
	
	public function test_writeLine()
	{
		ScriptExecutor::run(
			[
				['accept'],
				['read']
			],
			[
				['sleep', 1],
				['connect'],
				['writeLine', 'abc']
			],
			true);
	}
	
	
	public static function tearDownAfterClass()
	{
		self::cleanUp();
	}
	
	public static function setUpBeforeClass()
	{
		self::cleanUp();
	}
}
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
				['sleep', 1],
				['connect'],
				['write', 'abc']
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
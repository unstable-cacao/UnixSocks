<?php
namespace UnixSocks;


use inc\ScriptExecutor;
use PHPUnit\Framework\TestCase;


class ClientTest extends TestCase
{
	public function test_sanity()
	{
		ScriptExecutor::run(
			[
				['accept'],
				[ 'read' ]
			],
			[
				['sleep', 1],
				['connect'],
				[ 'write', 'abc' ]
			]);
	}
}
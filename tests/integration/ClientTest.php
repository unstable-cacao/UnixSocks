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
				[ 'read' ]
			],
			[
				[ 'write', 'abc' ]
			]);
	}
}
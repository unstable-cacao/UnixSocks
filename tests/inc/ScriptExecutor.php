<?php
namespace inc;


class ScriptExecutor
{
	public static function run(array $commandsA, array $commandsB, bool $debug = false): void
	{
		$args1 = json_encode(json_encode($commandsA));
		$args2 = json_encode(json_encode($commandsB));
		
		$path = realpath(__DIR__ . '/script/run.php');
		$command1 = "php $path $args1";
		$command2 = "php $path $args2";
		
		if ($debug)
		{
			echo "Commands:" . PHP_EOL;
			echo "***********************************" . PHP_EOL;
			echo $command1 . PHP_EOL;
			echo $command2 . PHP_EOL;
			echo "***********************************" . PHP_EOL;
		}
		
		exec("$command1 & $command2", $output, $result);
		
		if ($result !== 0 || $output)
		{
			throw new \Exception("Test failed. Return Code $result, Text " . implode("\n", $output));
		}
	}
}
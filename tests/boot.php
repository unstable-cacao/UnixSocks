<?php
require_once __DIR__ . '/../vendor/autoload.php';


foreach (glob(__DIR__ . '/inc/*.php') as $file)
{
	/** @noinspection PhpIncludeInspection */
	require_once $file;
}
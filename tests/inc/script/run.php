<?php
namespace inc\script;


require_once __DIR__ . '/../../boot.php';


use inc\Executor;

$args = json_decode($argv[1]);

$exec = new Executor();
$exec->execute($args);


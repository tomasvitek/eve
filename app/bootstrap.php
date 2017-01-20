<?php

require __DIR__ . '/../vendor/autoload.php';

$configurator = new Nette\Configurator;

// $configurator->setDebugMode([127.0.0.1]); // enables debugger for given ips
// $configurator->setDebugMode(TRUE); // enables debugger for all

$configurator->enableDebugger(__DIR__ . '/../tmp/log');
$configurator->setTempDirectory(__DIR__ . '/../tmp');

$configurator->createRobotLoader()
	->addDirectory(__DIR__)
	->register();

$configurator->addConfig(__DIR__ . '/config/config.neon');

$container = $configurator->createContainer();

return $container;

#!/usr/bin/env php
<?php

/**
 * @file
 * PHP script to update php.ini configuration options.
 */

require __DIR__ . '/vendor/autoload.php';

use PhpIni\Command\SetCommand;
use Symfony\Component\Console\Application;

$command = new SetCommand();
$application = new Application('php_ini');
$application->add($command);
$application->setDefaultCommand($command->getName(), TRUE);
$application->run();

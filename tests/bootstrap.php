<?php

$loader = require __DIR__.'/../vendor/autoload.php';

$base = dirname(__DIR__);
$_ENV['APP_BASE_PATH'] = $base;
$_SERVER['APP_BASE_PATH'] = $base;
$loader->setPsr4('App\\', [$base.'/app']);
$loader->setPsr4('Database\\Factories\\', [$base.'/database/factories']);
$loader->setPsr4('Database\\Seeders\\', [$base.'/database/seeders']);
$loader->setPsr4('Tests\\', [$base.'/tests']);

return $loader;

<?php
require 'vendor/autoload.php';
use Dotenv\Dotenv;

$dotenv = DotEnv::createUnsafeImmutable(__DIR__);
$dotenv->safeLoad();

require_once __DIR__ . '/src/Bootstrap.php';

// routes
require_once __DIR__ . '/src/Routes.php';



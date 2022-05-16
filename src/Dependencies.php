<?php

$injector = new \Auryn\Injector();

$injector->alias('Http\Request', 'Http\HttpRequest');
$injector->share('Http\HttpRequest');
$injector->define('Http\HttpRequest', [
	':get' => $_GET,
	':post' => $_POST,
	':cookies' => $_COOKIE,
	':files' => $_FILES,
	':server' => $_SERVER,
	':inputStream' => file_get_contents('php://input')
]);

$injector->alias('Http\Response', 'Http\HttpResponse');
$injector->share('Http\HttpResponse');

// database
$injector->define("PDO", [
	":dsn" => "mysql:host=" . $_ENV['DB_HOST'] . ";port=" . $_ENV['DB_HOST'] . ";charset=utf8mb4;dbname=" . $_ENV['DB_NAME'],
	":username" => $_ENV['DB_USERNAME'],
	":passwd" => $_ENV['DB_PASSWORD'],
	":options" => [
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		PDO::ATTR_EMULATE_PREPARES => false,
	],
]);
$injector->share('PDO');

return $injector;

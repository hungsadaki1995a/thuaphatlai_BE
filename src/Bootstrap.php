<?php

namespace Example;

use Src\Routes;
use Dotenv\Dotenv;

require_once __DIR__ . "/../vendor/autoload.php";

error_reporting(E_ALL);

$dotenv = DotEnv::createUnsafeImmutable('./');
$dotenv->safeLoad();

$environment = getenv('DEVELOPMENT');

// Allow from any origin
if (isset($_SERVER['HTTP_ORIGIN'])) {
	// Decide if the origin in $_SERVER['HTTP_ORIGIN'] is one
	// you want to allow, and if so:
	header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
	header('Access-Control-Allow-Credentials: true');
	header('Access-Control-Max-Age: 86400');    // cache for 1 day
}

// Access-Control headers are received during OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

	if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
		// may also be using PUT, PATCH, HEAD etc
		header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

	if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
		header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

	exit(0);
}

/**
 * Register the error handler
 */
/*$whoops = new \Whoops\Run;
if ($environment !== getenv('PRODUCTION')) {
	$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
} else {
	$whoops->pushHandler(function ($e) {
		echo 'Todo: Friendly error page and send an email to the developer' . $e;
	});
}

$whoops->register();*/

/**
 * Register the request, response
 */

$injector = include('Dependencies.php');

$request = $injector->make('Http\HttpRequest');
$response = $injector->make('Http\HttpResponse');

/**
 * Register the routes
 */

$route = new Routes();

$routeInfo = $route->getDispatcher()->dispatch($request->getMethod(), $request->getPath());

switch ($routeInfo[0]) {
	case \FastRoute\Dispatcher::NOT_FOUND:
		$response->setContent('404 - Api not found');
		$response->setStatusCode(404);
		break;
	case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
		$response->setContent('405 - Method not allowed');
		$response->setStatusCode(405);
		break;
	case \FastRoute\Dispatcher::FOUND:
		$className = $routeInfo[1][0];
		$method = $routeInfo[1][1];
		$vars = $routeInfo[2];

		// create class
		$class = $injector->make($className);
		// call method from created class
		$class->$method($vars);
		break;
}

foreach ($response->getHeaders() as $header) {
	header($header, false);
}

// print response
echo $response->getContent();

<?php

namespace Src;

use FastRoute;

class Routes
{
	private FastRoute\Dispatcher $dispatcher;

	public function __construct()
	{
		$routeDefinitionCallback = function (FastRoute\RouteCollector $r) {
			$routes = self::getRoutes();
			foreach ($routes as $route) {
				$r->addRoute(
					$route['method'],
					$route['prefix'] . $route['path'],
					$route['handler']
				);
			}
		};
		$this->dispatcher = FastRoute\simpleDispatcher($routeDefinitionCallback);
	}

	public function getDispatcher(): FastRoute\Dispatcher
	{
		return $this->dispatcher;
	}

	private function getRoutes(): array
	{
		return array(
			/* upload file api start */
			array(
				// upload file
				'method' => 'POST',
				'prefix' => '/api/v1',
				'path' => '/file/upload',
				'handler' => ['Controller\File', 'upload', 'checkAuth']
			),
			array(
				// remove file
				'method' => 'POST',
				'prefix' => '/api/v1',
				'path' => '/file/remove',
				'handler' => ['Controller\File', 'remove', 'checkAuth']
			),
			/* upload file api end */

			/* users api start */
			array(
				// get all users
				'method' => 'GET',
				'prefix' => '/api/v1',
				'path' => '/users',
				'handler' => ['Controller\User', 'getList']
			),
			array(
				// get one users
				'method' => 'GET',
				'prefix' => '/api/v1',
				'path' => '/users/{id}',
				'handler' => ['Controller\User', 'getDetail']
			),
			array(
				// add new users
				'method' => 'POST',
				'prefix' => '/api/v1',
				'path' => '/users/create',
				'handler' => ['Controller\User', 'create', 'checkAuth']
			),
			array(
				// update user
				'method' => 'POST',
				'prefix' => '/api/v1',
				'path' => '/users/update',
				'handler' => ['Controller\User', 'update', 'checkAuth']
			),
			array(
				// delete user
				'method' => 'POST',
				'prefix' => '/api/v1',
				'path' => '/users/delete',
				'handler' => ['Controller\User', 'delete', 'checkAuth']
			),
			array(
				// get all users
				'method' => 'POST',
				'prefix' => '/api/v1',
				'path' => '/users/login',
				'handler' => ['Controller\User', 'login']
			),
			/* users api end */

			/* carousel api start */
			array(
				// get all carousel
				'method' => 'GET',
				'prefix' => '/api/v1',
				'path' => '/carousel',
				'handler' => ['Controller\Carousel', 'getList']
			),
			array(
				// get carousel
				'method' => 'GET',
				'prefix' => '/api/v1',
				'path' => '/carousel/{id}',
				'handler' => ['Controller\Carousel', 'getDetail']
			),
			array(
				// add new carousel
				'method' => 'POST',
				'prefix' => '/api/v1',
				'path' => '/carousel/create',
				'handler' => ['Controller\Carousel', 'create', 'checkAuth']
			),
			array(
				// update carousel
				'method' => 'POST',
				'prefix' => '/api/v1',
				'path' => '/carousel/update',
				'handler' => ['Controller\Carousel', 'update', 'checkAuth']
			),
			array(
				// delete carousel
				'method' => 'POST',
				'prefix' => '/api/v1',
				'path' => '/carousel/delete',
				'handler' => ['Controller\Carousel', 'delete', 'checkAuth']
			),
			array(
				// update carousel order
				'method' => 'POST',
				'prefix' => '/api/v1',
				'path' => '/carousel/order/swap',
				'handler' => ['Controller\Carousel', 'swapCarouselOrder', 'checkAuth']
			),
			/* carousel api end */

			/* introduce api start */
			array(
				// get all introduce
				'method' => 'GET',
				'prefix' => '/api/v1',
				'path' => '/introduce',
				'handler' => ['Controller\Introduce', 'getList']
			),
			array(
				// get introduce
				'method' => 'GET',
				'prefix' => '/api/v1',
				'path' => '/introduce/{id}',
				'handler' => ['Controller\Introduce', 'getDetail']
			),
			array(
				// add new introduce
				'method' => 'POST',
				'prefix' => '/api/v1',
				'path' => '/introduce/create',
				'handler' => ['Controller\Introduce', 'create', 'checkAuth']
			),
			array(
				// update introduce
				'method' => 'POST',
				'prefix' => '/api/v1',
				'path' => '/introduce/update',
				'handler' => ['Controller\Introduce', 'update', 'checkAuth']
			),
			array(
				// delete introduce
				'method' => 'POST',
				'prefix' => '/api/v1',
				'path' => '/introduce/delete',
				'handler' => ['Controller\Introduce', 'delete', 'checkAuth']
			),
			/* introduce api end */

			/* service api start */
			array(
				// get all service
				'method' => 'GET',
				'prefix' => '/api/v1',
				'path' => '/service',
				'handler' => ['Controller\Service', 'getList']
			),
			array(
				// get service
				'method' => 'GET',
				'prefix' => '/api/v1',
				'path' => '/service/{id}',
				'handler' => ['Controller\Service', 'getDetail']
			),
			array(
				// add new service
				'method' => 'POST',
				'prefix' => '/api/v1',
				'path' => '/service/create',
				'handler' => ['Controller\Service', 'create', 'checkAuth']
			),
			array(
				// update service
				'method' => 'POST',
				'prefix' => '/api/v1',
				'path' => '/service/update',
				'handler' => ['Controller\Service', 'update', 'checkAuth']
			),
			array(
				// delete service
				'method' => 'POST',
				'prefix' => '/api/v1',
				'path' => '/service/delete',
				'handler' => ['Controller\Service', 'delete', 'checkAuth']
			),
			/* service api end */
		);
	}
}


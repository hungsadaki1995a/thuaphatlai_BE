<?php

namespace Controller;

use Http\Request;
use Http\Response;
use PDO;
use Src;

abstract class BaseController
{
	protected Request $request;
	protected Response $response;
	protected PDO $pdo;

	protected string $status = Src\Constants::RESPONSE_STATUS_SUCCESS;
	protected array $message = [];

	public function __construct(Request $request, Response $response, PDO $pdo)
	{
		$this->request = $request;
		$this->response = $response;
		$this->pdo = $pdo;
	}

	public function getList()
	{
	}

	public function getDetail($params)
	{
	}

	public function create()
	{
	}

	public function update()
	{
	}

	public function delete()
	{
	}

	public function findById(int $id)
	{
	}

	protected function setStatusMessages(string $status, $message)
	{
		$this->status = $status;
		$this->message[] = $message;
	}

	protected function convertDataToJson(array $data)
	{
		return json_encode($data, JSON_PRETTY_PRINT);
	}
}

<?php

namespace Controller;

use PDO;
use Src\Constants;

class Introduce extends BaseController
{
	private string $table = 'introduce';
	/**
	 * Get all introduce
	 */
	public function getList()
	{
		$list = array();
		try {
			$query = "SELECT * FROM $this->table";
			$stmt = $this->pdo->prepare($query);
			$stmt->execute();
			$list = $stmt->fetchAll();
		} catch (\Exception $exception) {
			parent::setStatusMessages(Constants::RESPONSE_STATUS_FAIL, 'Failed to get introduce');
		}

		$result = json_encode(array(
			'status' => $this->status,
			'data' => $list,
			'message' => $this->message
		), JSON_PRETTY_PRINT);

		$this->response->setContent($result);
	}

	/**
	 * Get user by id
	 * @param $params
	 */
	public function getDetail($params)
	{
		$id = $params['id'] ?? '';
		$result = [];

		if (empty($id)) {
			$this->response->setContent(json_encode(array(
				'status' => Constants::RESPONSE_STATUS_FAIL,
				'data' => [],
				'message' => 'Id is required'
			), JSON_PRETTY_PRINT));
			return;
		}

		try {
			$result = self::findById($id);
			if (!$result) {
				$result = [];
				parent::setStatusMessages(Constants::RESPONSE_STATUS_FAIL, 'Introduce not found');
			}
		} catch (\Exception $e) {
			parent::setStatusMessages(Constants::RESPONSE_STATUS_FAIL, $e->getMessage());
		}

		$content = json_encode(array(
			'status' => $this->status,
			'data' => $result,
			'message' => $this->message
		), JSON_PRETTY_PRINT);

		$this->response->setContent($content);
	}

	/**
	 * Add new introduce
	 */
	public function create()
	{
		$rawBody = $this->request->getRawBody();
		$params = json_decode($rawBody, true);

		// validate params
		$validate = $this->validate($params);
		if (isset($validate) && count($validate) > 0) {
			$this->response->setContent(json_encode(array(
				'status' => Constants::RESPONSE_STATUS_FAIL,
				'data' => [],
				'message' => $validate
			), JSON_PRETTY_PRINT));
			return;
		}

		// create introduce
		try {
			$content = $params['content'];
			$showOnScreen = $params['show_on_screen'] ?? 1;

			$created_date = date("Y-m-d H:i:s");
			$query = "
                INSERT INTO $this->table (content, show_on_screen, created_date, updated_date)
				VALUES ('$content', '$showOnScreen', '$created_date', '$created_date')
            ";
			$stmt = $this->pdo->prepare($query);
			$stmt->execute();
		} catch (\Exception $exception) {
			parent::setStatusMessages(Constants::RESPONSE_STATUS_FAIL, $exception->getMessage());
		}

		$result = json_encode(array(
			'status' => $this->status,
			'data' => [],
			'message' => $this->message
		), JSON_PRETTY_PRINT);
		$this->response->setContent($result);
	}

	/**
	 * Update a introduce
	 */
	public function update()
	{
		$rawBody = $this->request->getRawBody();
		$params = json_decode($rawBody, true);
		$introduceId = $params['id'] ?? '';

		// validate isset id
		if (empty($introduceId)) {
			$this->response->setContent(json_encode(array(
				'status' => Constants::RESPONSE_STATUS_FAIL,
				'data' => [],
				'message' => 'Id is required'
			), JSON_PRETTY_PRINT));
			return;
		}

		// validate exist introduce
		$introduce = self::findById($introduceId);
		if (!$introduce) {
			$this->response->setContent(json_encode(array(
				'status' => Constants::RESPONSE_STATUS_FAIL,
				'data' => [],
				'message' => 'Introduce not exist'
			), JSON_PRETTY_PRINT));
			return;
		}

		// update introduce
		try {
			$content = $params['content'] ?? $introduce['content'];
			$show_on_screen = $params['show_on_screen'] ?? $introduce['show_on_screen'];

			$updated_date = date("Y-m-d H:i:s");
			$query = "
                UPDATE $this->table
                SET content= :content,
                    show_on_screen= :show_on_screen,
                    updated_date= :updated_date
                WHERE id= :id
            ";
			$stmt = $this->pdo->prepare($query);
			// assign params
			$stmt->bindParam(':id', $introduceId);
			$stmt->bindParam(':content', $content);
			$stmt->bindParam(':show_on_screen', $show_on_screen);
			$stmt->bindParam(':updated_date', $updated_date);
			// exec update query
			$stmt->execute();
		} catch (\Exception $exception) {
			parent::setStatusMessages(Constants::RESPONSE_STATUS_FAIL, $exception->getMessage());
		}

		$result = json_encode(array(
			'status' => $this->status,
			'data' => [],
			'message' => $this->message
		), JSON_PRETTY_PRINT);
		$this->response->setContent($result);
	}

	/**
	 *  Delete a introduce
	 */
	public function delete()
	{
		$rawBody = $this->request->getRawBody();
		$params = json_decode($rawBody, true);
		$introduceId = $params['id'] ?? '';

		// validate isset id
		if (empty($introduceId)) {
			$this->response->setContent(json_encode(array(
				'status' => Constants::RESPONSE_STATUS_FAIL,
				'data' => [],
				'message' => 'Id is required'
			), JSON_PRETTY_PRINT));
			return;
		}

		// validate exist introduce
		$user = self::findById($introduceId);
		if (!$user) {
			$this->response->setContent(json_encode(array(
				'status' => Constants::RESPONSE_STATUS_FAIL,
				'data' => [],
				'message' => 'Introduce not exist'
			), JSON_PRETTY_PRINT));
			return;
		}

		// delete introduce
		try {
			$query = "DELETE FROM $this->table WHERE id= :id";
			$stmt = $this->pdo->prepare($query);
			$stmt->bindParam(':id', $introduceId, PDO::PARAM_INT);
			$stmt->execute();
		} catch (\Exception $e) {
			parent::setStatusMessages(Constants::RESPONSE_STATUS_FAIL, $e->getMessage());
		}

		$result = json_encode(array(
			'status' => $this->status,
			'data' => [],
			'message' => $this->message
		), JSON_PRETTY_PRINT);
		$this->response->setContent($result);
	}

	/**
	 * Find user by id
	 * @param $id int
	 * @return false | object introduce
	 */
	public function findById(int $id)
	{
		$query = "SELECT * FROM $this->table WHERE id= :id";
		$stmt = $this->pdo->prepare($query);
		$stmt->bindParam(':id', $id, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}

	private function validate($params): array
	{
		$err = array();
		// email
		if (empty($params['content'])) {
			$err['content'] = "Content is required";
		}
		return $err;
	}
}

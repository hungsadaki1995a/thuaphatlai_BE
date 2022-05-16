<?php

namespace Controller;

use PDO;
use Src\Constants;

class Service extends BaseController
{
	private string $table = 'services';

	/**
	 * Get all service
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
			parent::setStatusMessages(
				Constants::RESPONSE_STATUS_FAIL,
				'Failed to get service'
			);
		}

		$result = json_encode(array(
			'status' => $this->status,
			'data' => $list,
			'message' => $this->message
		), JSON_PRETTY_PRINT);

		$this->response->setContent($result);
	}

	/**
	 * Get detail service
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
				parent::setStatusMessages(
					Constants::RESPONSE_STATUS_FAIL,
					'Service not found'
				);
			}
		} catch (\Exception $e) {
			parent::setStatusMessages(
				Constants::RESPONSE_STATUS_FAIL,
				$e->getMessage()
			);
		}

		$content = json_encode(array(
			'status' => $this->status,
			'data' => $result,
			'message' => $this->message
		), JSON_PRETTY_PRINT);

		$this->response->setContent($content);
	}

	/**
	 * Add new service
	 */
	public function create()
	{
		$rawBody = $this->request->getRawBody();
		$params = json_decode($rawBody, true);

		// validate params
		$validate = $this->validate($params);
		if (isset($validate) && count($validate) > 0) {
			$this->response->setContent(json_encode(
				array(
					'status' => Constants::RESPONSE_STATUS_FAIL,
					'data' => [],
					'message' => $validate
				), JSON_PRETTY_PRINT));
			return;
		}

		// create service
		try {
			$title = $params['title'];
			$content = $params['content'];
			$primaryImageUrl = $params['primary_image_url'];
			$other_service_flg = $params['other_service_flg'] ?? 0;
			$current_date = date("Y-m-d H:i:s");

			$query = "
                INSERT INTO $this->table (title, content, primary_image_url, other_service_flg, created_date, updated_date)
				VALUES ('$title', '$content', '$primaryImageUrl', '$other_service_flg', '$current_date', '$current_date')
            ";
			$stmt = $this->pdo->prepare($query);
			$stmt->execute();
		} catch (\Exception $exception) {
			parent::setStatusMessages(
				Constants::RESPONSE_STATUS_FAIL,
				$exception->getMessage()
			);
		}

		$result = json_encode(array(
			'status' => $this->status,
			'data' => [],
			'message' => $this->message
		), JSON_PRETTY_PRINT);
		$this->response->setContent($result);
	}

	/**
	 * Update a service
	 */
	public function update()
	{
		$rawBody = $this->request->getRawBody();
		$params = json_decode($rawBody, true);
		$serviceId = $params['id'] ?? '';

		// validate isset id
		if (empty($serviceId)) {
			$this->response->setContent(json_encode(array(
				'status' => Constants::RESPONSE_STATUS_FAIL,
				'data' => [],
				'message' => 'Id is required'
			), JSON_PRETTY_PRINT));
			return;
		}

		// validate exist service
		$service = self::findById($serviceId);
		if (!$service) {
			$this->response->setContent(json_encode(array(
				'status' => Constants::RESPONSE_STATUS_FAIL,
				'data' => [],
				'message' => 'Service not exist'
			), JSON_PRETTY_PRINT));
			return;
		}

		// update service
		try {
			$title = $params['title'] ?? $service['title'];
			$content = $params['content'] ?? $service['content'];
			$primaryImageUrl = $params['primary_image_url'] ?? $service['primary_image_url'];
			$otherServiceFlg = $params['other_service_flg'] ?? $service['other_service_flg'];

			$updated_date = date("Y-m-d H:i:s");
			$query = "
                UPDATE $this->table
                SET title= :title,
                    content= :content,
                    primary_image_url= :primary_image_url,
                    other_service_flg= :other_service_flg,
                    updated_date= :updated_date
                WHERE id= :id
            ";
			$stmt = $this->pdo->prepare($query);
			// assign params
			$stmt->bindParam(':id', $serviceId);
			$stmt->bindParam(':title', $title);
			$stmt->bindParam(':content', $content);
			$stmt->bindParam(':primary_image_url', $primaryImageUrl);
			$stmt->bindParam(':other_service_flg', $otherServiceFlg, PDO::PARAM_INT);
			$stmt->bindParam(':updated_date', $updated_date);
			// exec update query
			$stmt->execute();
		} catch (\Exception $exception) {
			parent::setStatusMessages(Constants::RESPONSE_STATUS_FAIL, $exception->getMessage());
		}

		$result = json_encode(array(
			'status' => $this->status,
			'data' => $serviceId,
			'message' => $this->message
		), JSON_PRETTY_PRINT);
		$this->response->setContent($result);
	}

	/**
	 * Delete a service
	 */
	public function delete()
	{
		$rawBody = $this->request->getRawBody();
		$params = json_decode($rawBody, true);
		$serviceId = $params['id'] ?? '';

		// validate isset id
		if (empty($serviceId)) {
			$this->response->setContent(json_encode(array(
				'status' => Constants::RESPONSE_STATUS_FAIL,
				'data' => [],
				'message' => 'Id is required'
			), JSON_PRETTY_PRINT));
			return;
		}

		// validate exist service
		$carousel = self::findById($serviceId);
		if (!$carousel) {
			$this->response->setContent(json_encode(array(
				'status' => Constants::RESPONSE_STATUS_FAIL,
				'data' => [],
				'message' => 'Service not exist'
			), JSON_PRETTY_PRINT));
			return;
		}

		// delete service
		try {
			$query = "DELETE FROM $this->table WHERE id= :id";
			$stmt = $this->pdo->prepare($query);
			$stmt->bindParam(':id', $serviceId, PDO::PARAM_INT);
			$stmt->execute();
		} catch (\Exception $e) {
			parent::setStatusMessages(Constants::RESPONSE_STATUS_FAIL, $e->getMessage());
		}

		$result = json_encode(array(
			'status' => $this->status,
			'data' => [],
			'message' => 'Delete service success'
		), JSON_PRETTY_PRINT);
		$this->response->setContent($result);
	}

	/**
	 * Find service by id
	 * @param $id int
	 * @return false | object service
	 */
	public function findById(int $id)
	{
		$query = "SELECT * FROM " . $this->table . " WHERE id= :id";
		$stmt = $this->pdo->prepare($query);
		$stmt->bindParam(':id', $id, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}

	private function validate(array $params): array
	{
		$err = array();
		// title
		if (empty($params['title'])) {
			$err['title'] = "Title is required";
		}
		// content
		if (empty($params['content'])) {
			$err['content'] = "Content is required";
		}
		// primary_image_url
		if (empty($params['primary_image_url'])) {
			$err['primary_image_url'] = "Primary image url is required";
		}

		return $err;
	}
}

<?php

namespace Controller;

use PDO;
use Src\Constants;

class Carousel extends BaseController
{
	private string $table = 'carousel';

	/**
	 * Get all carousel
	 */
	public function getList()
	{
		$list = array();
		try {
			$query = "
				SELECT *
				FROM $this->table
				ORDER BY `$this->table`.display_order
			";
			$stmt = $this->pdo->prepare($query);
			$stmt->execute();
			$list = $stmt->fetchAll();
		} catch (\Exception $exception) {
			parent::setStatusMessages(
				Constants::RESPONSE_STATUS_FAIL,
				'Failed to get carousel'
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
	 * Get detail carousel
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
					'Carousel not found'
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
	 * Add new carousel
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

		// create carousel
		try {
			$imageUrl = $params['image_url'];
			$showOnScreen = $params['show_on_screen'] ?? 1;
			$current_date = date("Y-m-d H:i:s");
			$maxOrderNumber = self::getMaxOrderNumber();
			$maxOrderNumber += 1;

			// insert carousel
			$insertCarouselQuery = "
                INSERT INTO $this->table (image_url, show_on_screen, display_order, created_date, updated_date)
					VALUES ('$imageUrl', '$showOnScreen', '$maxOrderNumber', '$current_date', '$current_date');
            ";
			$stmt = $this->pdo->prepare($insertCarouselQuery);
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
	 * Update a carousel
	 */
	public function update()
	{
		$rawBody = $this->request->getRawBody();
		$params = json_decode($rawBody, true);
		$carouselId = $params['id'] ?? '';

		// validate isset id
		if (empty($carouselId)) {
			$this->response->setContent(json_encode(array(
				'status' => Constants::RESPONSE_STATUS_FAIL,
				'data' => [],
				'message' => 'Id is required'
			), JSON_PRETTY_PRINT));
			return;
		}

		// validate exist carousel
		$carousel = self::findById($carouselId);
		if (!$carousel) {
			$this->response->setContent(json_encode(array(
				'status' => Constants::RESPONSE_STATUS_FAIL,
				'data' => [],
				'message' => 'Carousel not exist'
			), JSON_PRETTY_PRINT));
			return;
		}

		// update carousel
		try {
			$imageUrl = $params['image_url'] ?? $carousel['image_url'];
			$showOnScreen = $params['show_on_screen'] ?? $carousel['show_on_screen'];

			// validate show on screen
			if (isset($params['show_on_screen'])) {
				if (!in_array($params['show_on_screen'],
					array(Constants::HIDE_CAROUSEL, Constants::SHOW_CAROUSEL))) {
					$this->response->setContent(json_encode(array(
						'status' => Constants::RESPONSE_STATUS_FAIL,
						'data' => [],
						'message' => 'Show on screen is wrong'
					), JSON_PRETTY_PRINT));
					return;
				}
			}

			$updated_date = date("Y-m-d H:i:s");
			$query = "
                UPDATE $this->table
                SET image_url= :image_url,
                    show_on_screen= :show_on_screen,
                    updated_date= :updated_date
                WHERE id= :id
            ";
			$stmt = $this->pdo->prepare($query);
			// assign params
			$stmt->bindParam(':id', $carouselId);
			$stmt->bindParam(':image_url', $imageUrl);
			$stmt->bindParam(':show_on_screen', $showOnScreen, PDO::PARAM_INT);
			$stmt->bindParam(':updated_date', $updated_date);
			// exec update query
			$stmt->execute();
		} catch (\Exception $exception) {
			parent::setStatusMessages(Constants::RESPONSE_STATUS_FAIL, $exception->getMessage());
		}

		$result = json_encode(array(
			'status' => $this->status,
			'data' => $carouselId,
			'message' => $this->message
		), JSON_PRETTY_PRINT);
		$this->response->setContent($result);
	}

	/**
	 * Swap order number
	 */
	public function swapCarouselOrder()
	{
		$rawBody = $this->request->getRawBody();
		$params = json_decode($rawBody, true);
		$carouselId = $params['carousel_id'] ?? 0;
		$carouselNewOrderNumber = $params['new_order'] ?? 0;

		if ($carouselId === 0 || $carouselNewOrderNumber === 0) {
			$this->response->setContent(json_encode(array(
				'status' => Constants::RESPONSE_STATUS_FAIL,
				'data' => [],
				'message' => 'Swap input not valid.'
			), JSON_PRETTY_PRINT));
			return;
		}

		// find carousel order by carousel id
		$carousel = self::findById($carouselId);
		if (!$carousel) {
			$this->response->setContent(json_encode(array(
				'status' => Constants::RESPONSE_STATUS_FAIL,
				'data' => [],
				'message' => 'Carousel order not exist'
			), JSON_PRETTY_PRINT));
			return;
		}

		// find carousel order to swap by carousel id
		$carouselOrderSwap = self::findCarouseByDisplayOrder($carouselNewOrderNumber);
		if (!$carouselOrderSwap) {
			$this->response->setContent(json_encode(array(
				'status' => Constants::RESPONSE_STATUS_FAIL,
				'data' => [],
				'message' => 'Carousel to swap order not exist'
			), JSON_PRETTY_PRINT));
			return;
		}

		$this->pdo->beginTransaction();
		try {
			$query = self::generateUpdateQuery($this->table, 'display_order', 'id',
				array(
					$carousel['id'] => $carouselNewOrderNumber,
					$carouselOrderSwap['id'] => $carousel['display_order']
				)
			);
			$stmt = $this->pdo->prepare($query);
			$stmt->execute();
			$this->pdo->commit();
		} catch (\Exception $exception) {
			$this->pdo->rollBack();
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
	 * Delete a carousel
	 */
	public function delete()
	{
		$rawBody = $this->request->getRawBody();
		$params = json_decode($rawBody, true);
		$carouselId = $params['id'] ?? '';
		$isDeleteImage = $params['is_delete_image'] ?? true;

		// validate isset id
		if (empty($carouselId)) {
			$this->response->setContent(json_encode(array(
				'status' => Constants::RESPONSE_STATUS_FAIL,
				'data' => [],
				'message' => 'Id is required'
			), JSON_PRETTY_PRINT));
			return;
		}

		// validate exist carousel
		$carousel = self::findById($carouselId);
		if (!$carousel) {
			$this->response->setContent(json_encode(array(
				'status' => Constants::RESPONSE_STATUS_FAIL,
				'data' => [],
				'message' => 'Carousel not exist'
			), JSON_PRETTY_PRINT));
			return;
		}

		// delete carousel
		try {
			// remove image
			if ($isDeleteImage) {
				$isDeleteSuccess = File::removeSingleFile($carousel['image_url']);
				if (!$isDeleteSuccess) {
					$this->response->setContent(json_encode(array(
						'status' => Constants::RESPONSE_STATUS_FAIL,
						'data' => [],
						'message' => 'Delete image of carousel failed.'
					), JSON_PRETTY_PRINT));
					return;
				}
			}

			$query = "
				DELETE FROM carousel
				WHERE carousel.id= :id
			";
			$stmt = $this->pdo->prepare($query);
			$stmt->bindParam(':id', $carouselId, PDO::PARAM_INT);
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
	 * Find carousel by id
	 * @param $id int
	 * @return false | object carousel
	 */
	public function findById(int $id)
	{
		$query = "SELECT * FROM $this->table WHERE id= :id";
		$stmt = $this->pdo->prepare($query);
		$stmt->bindParam(':id', $id, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}

	/**
	 * Find carousel order by id
	 * @param int $display_order
	 * @return false | object carousel order
	 */
	public function findCarouseByDisplayOrder(int $display_order)
	{
		$query = "SELECT * FROM $this->table WHERE display_order= :display_order";
		$stmt = $this->pdo->prepare($query);
		$stmt->bindParam(':display_order', $display_order, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}

	/**
	 * Create query string to update multiple row in same table
	 * @param string $table
	 * @param string $field to update value
	 * @param string $condition to check
	 * @param array $values should like as array($carousel_id => $val_update)
	 * @return string
	 */
	private function generateUpdateQuery(
		string $table,
		string $field,
		string $condition,
		array $values
	): string {
		$caseCondition = [];
		$whereCondition = [];
		foreach ($values as $key => $value) {
			$caseCondition[] = "WHEN $key THEN $value";
			//$caseCondition[] = "WHEN 5 THEN 5";
			//$caseCondition[] = "WHEN 6 THEN 4";
			$whereCondition[] = $key;
		}
		$caseConditionStr = implode(" ", $caseCondition);
		$whereConditionStr = implode(",", $whereCondition);
		$stringQuery = "
			UPDATE $table
			SET $field = CASE $condition
				$caseConditionStr
				END
			WHERE $condition IN($whereConditionStr)	
		";
		return $stringQuery;
	}

	/**
	 * Find max display order
	 */
	private function getMaxOrderNumber(): int
	{
		$num = 0;
		$query = "SELECT MAX(display_order) AS max_order_number FROM $this->table";
		$stmt = $this->pdo->prepare($query);
		$stmt->execute();
		$findMaxDisplay = $stmt->fetch(PDO::FETCH_ASSOC);
		if (isset($findMaxDisplay['max_order_number'])) {
			$num = $findMaxDisplay['max_order_number'];
		}
		return $num;
	}

	private function validate(array $params): array
	{
		$err = array();
		// image_url
		if (empty($params['image_url'])) {
			$err['image_url'] = "Image url is required";
		}
		return $err;
	}
}

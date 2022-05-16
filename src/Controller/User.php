<?php

namespace Controller;

use PDO;
use Src\Constants;
use Src\Utils;

class User extends BaseController
{
	private string $table = 'users';

	/**
	 * Get all users
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
			parent::setStatusMessages(Constants::RESPONSE_STATUS_FAIL, 'Failed to get users');
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
				parent::setStatusMessages(Constants::RESPONSE_STATUS_FAIL, 'User not found');
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
	 * Add new user
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

		// validate duplicate email
		$isExistEmail = self::findByEmail($params['email']);
		if ($isExistEmail) {
			$this->response->setContent(json_encode(array(
				'status' => Constants::RESPONSE_STATUS_FAIL,
				'data' => [],
				'message' => "Email already exists"
			), JSON_PRETTY_PRINT));
			return;
		}

		// create user
		try {
			$email = $params['email'];
			$password = password_hash($params['password'], PASSWORD_DEFAULT);
			$role = $params['role'] ?? 0;

			$created_date = date("Y-m-d H:i:s");
			$query = "
                INSERT INTO $this->table (email, password, role, created_date, updated_date)
				VALUES ('$email', '$password', '$role', '$created_date', '$created_date')
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
	 * Update a user
	 */
	public function update()
	{
		$rawBody = $this->request->getRawBody();
		$params = json_decode($rawBody, true);
		$userId = $params['id'] ?? '';

		// validate isset id
		if (empty($userId)) {
			$this->response->setContent(json_encode(array(
				'status' => Constants::RESPONSE_STATUS_FAIL,
				'data' => [],
				'message' => 'Id is required'
			), JSON_PRETTY_PRINT));
			return;
		}

		// validate exist email
		if (isset($params['email'])) {
			$isExistEmail = self::findByEmail($params['email']);
			if ($isExistEmail) {
				$this->response->setContent(json_encode(array(
					'status' => Constants::RESPONSE_STATUS_FAIL,
					'data' => [],
					'message' => "Email already exists"
				), JSON_PRETTY_PRINT));
				return;
			}
		}

		// validate exist user
		$user = self::findById($userId);
		if (!$user) {
			$this->response->setContent(json_encode(array(
				'status' => Constants::RESPONSE_STATUS_FAIL,
				'data' => [],
				'message' => 'User not exist'
			), JSON_PRETTY_PRINT));
			return;
		}

		// update user
		try {
			$email = $params['email'] ?? $user['email'];
			$password = $params['password'] ? password_hash($params['password'], PASSWORD_DEFAULT) : $user['password'];
			$role = $user['role'];

			// validate user role
			if (isset($params['role'])) {
				if (!in_array($params['role'], Constants::USER_ROLE)) {
					$this->response->setContent(json_encode(array(
						'status' => Constants::RESPONSE_STATUS_FAIL,
						'data' => [],
						'message' => 'Role is wrong'
					), JSON_PRETTY_PRINT));
					return;
				}
				$role = $params['role'];
			}

			$updated_date = date("Y-m-d H:i:s");
			$query = "
                UPDATE $this->table
                SET email= :email,
                    password= :password,
                    role= :role,
                    updated_date= :updated_date
                WHERE id= :id
            ";
			$stmt = $this->pdo->prepare($query);
			// assign params
			$stmt->bindParam(':id', $userId);
			$stmt->bindParam(':email', $email);
			$stmt->bindParam(':password', $password);
			$stmt->bindParam(':role', $role, PDO::PARAM_INT);
			$stmt->bindParam(':updated_date', $updated_date);
			// exec update query
			$stmt->execute();
		} catch (\Exception $exception) {
			parent::setStatusMessages(Constants::RESPONSE_STATUS_FAIL, $exception->getMessage());
		}

		$result = json_encode(array(
			'status' => $this->status,
			'data' => $userId,
			'message' => $this->message
		), JSON_PRETTY_PRINT);
		$this->response->setContent($result);
	}

	/**
	 *  Delete a user
	 */
	public function delete()
	{
		$rawBody = $this->request->getRawBody();
		$params = json_decode($rawBody, true);
		$userId = $params['id'] ?? '';

		// validate isset id
		if (empty($userId)) {
			$this->response->setContent(json_encode(array(
				'status' => Constants::RESPONSE_STATUS_FAIL,
				'data' => [],
				'message' => 'Id is required'
			), JSON_PRETTY_PRINT));
			return;
		}

		// validate exist user
		$user = self::findById($userId);
		if (!$user) {
			$this->response->setContent(json_encode(array(
				'status' => Constants::RESPONSE_STATUS_FAIL,
				'data' => [],
				'message' => 'User not exist'
			), JSON_PRETTY_PRINT));
			return;
		}

		// delete user
		try {
			$query = "DELETE FROM $this->table WHERE id= :id";
			$stmt = $this->pdo->prepare($query);
			$stmt->bindParam(':id', $userId, PDO::PARAM_INT);
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
	 *  Login with user
	 */
	public function login()
	{
		$rawBody = $this->request->getRawBody();
		$params = json_decode($rawBody, true);
		$email = $params['email'] ?? '';
		$password = $params['password'] ?? '';

		if (empty($params['email'])) {
			$this->response->setContent(json_encode(array(
				'status' => Constants::RESPONSE_STATUS_FAIL,
				'data' => [],
				'message' => 'Email is required'
			), JSON_PRETTY_PRINT));
			return;
		}

		if (empty($password)) {
			$this->response->setContent(json_encode(array(
				'status' => Constants::RESPONSE_STATUS_FAIL,
				'data' => [],
				'message' => 'Password is required'
			), JSON_PRETTY_PRINT));
			return;
		}

		$result = [];

		try {
			$user = self::findByEmail($email);
			if (!$user) {
				parent::setStatusMessages(
					Constants::RESPONSE_STATUS_FAIL,
					'Wrong email'
				);
			} else {
				if (password_verify($password, $user['password'])) {
					unset($user['password']);
					$result = $user;
				} else {
					parent::setStatusMessages(
						Constants::RESPONSE_STATUS_FAIL,
						'Wrong password'
					);
				}
			}

		} catch (\Exception $exception) {
			parent::setStatusMessages(
				Constants::RESPONSE_STATUS_FAIL,
				$exception->getMessage()
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
	 * Find user by id
	 * @param $id int
	 * @return false | object user
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
	 * Find user by email
	 * @param $email string
	 * @return false | object user
	 */
	public function findByEmail(string $email)
	{
		$query = "SELECT * FROM $this->table WHERE email= :email";
		$stmt = $this->pdo->prepare($query);
		$stmt->bindParam(':email', $email);
		$stmt->execute();
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}

	private function validate($params): array
	{
		$err = array();
		$utils = new Utils();

		// email
		if (empty($params['email'])) {
			$err['email'] = "Email is required";
		} else {
			if (!$utils->isValidEmail($params['email'])) {
				$err['email'] = "Email is invalid";
			}
		}
		// password
		if (empty($params['password'])) {
			$err['password'] = "Password is required";
		} else {
			if (!$utils->isValidPassword($params['password'])) {
				$err['password'] = "Password is at least 8 character";
			}
		}
		return $err;
	}
}

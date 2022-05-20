<?php

namespace Service;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Src\Utils;

class Auth
{
	/**
	 * Generate JWT
	 * @param array $data
	 * @param int $expire
	 * @return array
	 * @throws \Exception
	 */
	public function generateJWT(array $data, int $expire = 0): array
	{
		$jwt = null;
		// handle access token
		$secretKey = $_ENV['SECRET_KEY'];
		$serverName = Utils::getDomainUrl(); // this can be the servername
		$issueAtTime = time(); // issued at
		$notBeforeTime = $issueAtTime + 10; //not before in seconds
		// jwt valid for 60 days (60 seconds * 60 minutes * 24 hours * 60 days)
		$expireTime = $expire > 0 ? $expire : $issueAtTime + 60 * 60; // expire time in seconds
		$token = array(
			"iss" => $serverName,
			"aud" => $serverName,
			"iat" => $issueAtTime,
			"nbf" => $notBeforeTime,
			"exp" => $expireTime,
			"data" => $data
		);
		try {
			$jwt = JWT::encode($token, $secretKey, 'HS256');
			if ($jwt) {
				return array(
					'token' => $jwt,
					'expired' => $expireTime
				);
			}
		} catch (\Exception $exception) {
			throw new \Exception('Can not creat jwt.');
		}
	}

	/**
	 * Validate jwt token
	 * @return bool
	 * */
	public function validateJWT(): bool
	{
		$jwt = null;
		$secret_key = $_ENV['SECRET_KEY'];
		$jwt = self::getBearerToken();
		if (!$jwt) {
			return false;
		}
		try {
			$decode = JWT::decode($jwt, new Key($secret_key, 'HS256'));
			// Access is granted
			if ($decode) {
				return true;
			}
		} catch (\Exception $exception) {
			return false;
		}
	}

	/**
	 * get access token from header
	 * */
	public function getBearerToken()
	{
		$headers = self::getAuthorizationHeader();
		// HEADER: Get the access token from the header
		if (!empty($headers)) {
			if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
				return $matches[1];
			}
		}
		return null;
	}

	/**
	 * Get header Authorization
	 * */
	public function getAuthorizationHeader(): ?string
	{
		$headers = null;
		if (isset($_SERVER['Authorization'])) {
			$headers = trim($_SERVER["Authorization"]);
		} else {
			if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
				$headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
			} elseif (function_exists('apache_request_headers')) {
				$requestHeaders = apache_request_headers();
				// Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
				$requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)),
					array_values($requestHeaders));
				if (isset($requestHeaders['Authorization'])) {
					$headers = trim($requestHeaders['Authorization']);
				}
			}
		}
		return $headers;
	}

}

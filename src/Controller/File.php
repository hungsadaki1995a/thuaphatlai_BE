<?php

namespace Controller;

use Src\Constants;
use Src\Utils;

class File extends BaseController
{
	/**
	 * Upload file
	 */
	public function upload()
	{
		if (!isset($_FILES['file'])) {
			$this->response->setContent(json_encode(array(
				'status' => Constants::RESPONSE_STATUS_FAIL,
				'data' => [],
				'message' => 'File is not exist'
			), JSON_PRETTY_PRINT));
			return;
		}

		list($success, $error, $message) = self::uploadFile($_FILES['file']);

		$content = json_encode(array(
			'status' => $this->status,
			'data' => array(
				'success' => $success,
				'error' => $error
			),
			'message' => $message
		), JSON_PRETTY_PRINT);

		$this->response->setContent($content);
	}

	/**
	 * Remove file uploaded
	 */
	public function remove()
	{
		$rawBody = $this->request->getRawBody();
		$files = json_decode($rawBody, true);

		if (!isset($files['file_name'])) {
			$this->response->setContent(json_encode(array(
				'status' => Constants::RESPONSE_STATUS_FAIL,
				'data' => [],
				'message' => 'File is required'
			), JSON_PRETTY_PRINT));
			return;
		}

		// handle remove file
		list($success, $error, $message) = self::removeFile($files['file_name']);

		$this->response->setHeader('Content-Type', 'application/json');

		$content = json_encode(array(
			'status' => $this->status,
			'data' => array(
				'success' => $success,
				'error' => $error
			),
			'message' => $message
		), JSON_PRETTY_PRINT);

		$this->response->setContent($content);
	}

	/**
	 * Upload single or multiple file
	 * @param $file
	 * @return array
	 */
	private function uploadFile($file): array
	{
		$success = [];
		$error = [];
		$message = [];
		$fileCount = count($file['name']);

		for ($i = 0; $i < $fileCount; $i++) {
			$fileTmpPath = $file['tmp_name'][$i];
			$fileSize = filesize($fileTmpPath);
			$fileInfo = finfo_open(FILEINFO_MIME_TYPE);
			$fileType = finfo_file($fileInfo, $fileTmpPath);

			// get file name without extension
			$fullFileName = explode('.', $file['name'][$i]);
			$fileName = array_shift($fullFileName);

			// check file size empty
			if ($fileSize === 0) {
				$message[$i] = 'File is empty';
			}
			// check max file size
			if ($fileSize > Constants::MAX_FILE_SIZE_UPLOAD) {
				$message[$i] = 'File is too large. File should be below ' . Constants::MAX_FILE_SIZE_UPLOAD . 'MB';
			}
			// check file type
			if (!in_array($fileType, array_keys(Constants::ALLOW_FILE_TYPE))) {
				$message[$i] = 'File type is not supported';
			}

			// check error and exist
			if (isset($message[$i]) && count($message[$i]) > 0) {
				$error[$i] = $fullFileName;
				continue;
			}

			$extension = Constants::ALLOW_FILE_TYPE[$fileType];
			$targetDirectory = $_SERVER['DOCUMENT_ROOT'] . Constants::UPLOAD_FOLDER;

			// create uniq file name
			$newFileName = $fileName . '_' . Utils::generateUUID() . "." . $extension;
			//$newFileName = $fileName . "." . $extension;

			$newFilePath = $targetDirectory . "/" . $newFileName;

			// copy tmp file to new file
			if (!copy($fileTmpPath, $newFilePath)) {
				$message[$i] = 'Can not save file';
				continue;
			} else {
				$success[$i] = $_SERVER['HTTP_HOST'] . Constants::UPLOAD_FOLDER . $newFileName;
			}
			// delete tmp file
			unlink($fileTmpPath);
		}

		return array($success, $error, $message);
	}

	/**
	 * Remove single or multiple files
	 * @param $fileNames array file name
	 * @return array
	 */
	private function removeFile(array $fileNames): array
	{
		$success = [];
		$errors = [];
		$messages = [];

		foreach ($fileNames as $key => $name) {

			$filePath = $_SERVER['DOCUMENT_ROOT'] . Constants::UPLOAD_FOLDER . $name;

			// check exist file
			if (!file_exists($filePath)) {
				$errors[] = $name;
				$messages[] = 'File ' . $name . ' does not exist.';
				continue;
			}

			// remove file
			if (!unlink($filePath)) {
				$errors[] = $name;
				$messages[] = 'Can not delete file ' . $name;
			} else {
				$success[$key] = $name;
			}
		}

		return array($success, $errors, $messages);
	}
}

<?php

namespace Src;

class Constants
{

	const RESPONSE_STATUS_SUCCESS = 'success';
	const RESPONSE_STATUS_FAIL = 'error';

	const USER_ROLE = [0, 1];

	const SHOW_CAROUSEL = 1;
	const HIDE_CAROUSEL = 0;

	// upload file
	const UPLOAD_FOLDER = '/public/uploads/';
	const ALLOW_FILE_TYPE = [
		'image/png' => 'png',
		'image/jpeg' => 'jpg',
	];
	const MAX_FILE_SIZE_UPLOAD = 3145728; // 3 MB (1 byte * 1024 * 1024 * 3 (for 3 MB))

	// service
	const DEFAULT_SERVICE = 0;
	const OTHER_SERVICE = 1;

	// contact
	const NOT_REPLY = 0;
	const REPLIED = 1;

}

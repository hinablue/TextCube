<?php

	/**
	 * for php-plurk-api config
	 * @package php-plurk-api
	 *
	 */

	define('BASE_PATH', ROOT. DIRECTORY_SEPARATOR . "cache" . DIRECTORY_SEPARATOR );
	define('PLURK_COOKIE_PATH', BASE_PATH . 'cookie.log');
	define('PLURK_LOG_PATH', BASE_PATH . 'plurk.log');

	define('PLURK_NOT_LOGIN', 'You are not login.');
	define('PLURK_AGENT', 'php-plurk-api agent');
?>

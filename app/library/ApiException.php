<?php
class ApiException extends Exception {
	const ERROR_VERSION = 1;
	const ERROR_METHOD = 2;
	const ERROR_PARAM = 3;
	const ERROR_NOLOGIN = 4;
	const ERROR_PHP = 10;
}
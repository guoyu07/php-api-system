<?php
use Ares333\YafLib\Error;
class ApiController extends Yaf_Controller_Abstract {
	function androidAction() {
		$this->process ();
	}
	function iosAction() {
		$this->process ();
	}
	function process() {
		$startTime = microtime ( true );
		$model = ApiModel::getInstance ( $this->getRequest ()->getActionName () );
		try {
			$value = $model->call ();
			$this->getResponse ()->setBody ( $model->getResponse ( 0, null, $value, $startTime ) );
		} catch ( Exception $exception ) {
			if ($exception instanceof ErrorException) {
				$errorCode = ApiException::ERROR_PHP;
			} else {
				$errorCode = $exception->getCode ();
			}
			$this->getResponse ()->setBody ( $model->getResponse ( $errorCode, $exception->getMessage (), null, $startTime ) );
			$errorLog = ini_get ( 'error_log' );
			$displayErrors = ini_get ( 'display_errors' );
			if ($exception instanceof ApiException) {
				ini_set ( 'display_errors', false );
				ini_set ( 'error_log', APP_PATH . '/logs/ApiException.log' );
			}
			ini_set ( 'error_log', $errorLog );
			ini_set ( 'display_errors', $displayErrors );
			Error::catchException ( $exception );
		}
		return false;
	}
}
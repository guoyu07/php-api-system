<?php
/**
 * API controller
 *
 * @author Ares
 *
 */
class ApiModel extends AbstractModel {
	private $api;
	private $module;
	private $noResponse = false;
	function __construct($module) {
		$this->module = $module;
	}

	/**
	 * 当前不响应内容
	 */
	function setNoResponse($bool = true) {
		$this->noResponse = $bool;
	}
	/**
	 * 调用接口
	 */
	function call() {
		header ( 'Content-Type: text/plain' );
		$request = Yaf_Dispatcher::getInstance ()->getRequest ();
		$params = $request->getParams ();
		$className = current ( $params );
		next ( $params );
		$method = key ( $params );
		$this->api = $this->getObject ( 'Api' );
		if (empty ( $className )) {
			throw new ApiException ( 'class is empty', ApiException::ERROR_METHOD );
		}
		if (empty ( $method )) {
			throw new ApiException ( 'method is empty', ApiException::ERROR_METHOD );
		}
		$this->api->validate ();
		$obj = $this->getObject ( $className );
		if (! isset ( $obj )) {
			throw new ApiException ( 'class not found, class=' . $className, ApiException::ERROR_METHOD );
		}
		if (! is_callable ( array (
				$obj,
				$method
		) )) {
			throw new ApiException ( 'method not callable, method=' . $className . ':' . $method, ApiException::ERROR_METHOD );
		}
		return $obj->$method ( $this->getParams () );
	}

	/**
	 *
	 * @param
	 *        	string key
	 * @return mixed
	 */
	private function getParams($key = null) {
		static $params;
		if (! isset ( $params )) {
			$request = Yaf_Dispatcher::getInstance ()->getRequest ();
			$params = array ();
			$params ['request'] = $request->getRequest ();
			$params ['query'] = $request->getQuery ();
			$params ['post'] = $request->getPost ();
			$params ['input'] = file_get_contents ( 'php://input' );
		}
		if (isset ( $key )) {
			if (array_key_exists ( $key, $params )) {
				return $params [$key];
			}
		} else {
			return $params;
		}
	}
	/**
	 * 返回对应版本的API
	 */
	function getApi() {
		return $this->api;
	}
	/**
	 *
	 * @param unknown $errorCode
	 * @param unknown $errorMessage
	 * @param unknown $value
	 * @return string
	 */
	function getResponse($errorCode, $errorMessage, $value, $startTime) {
		if ($this->noResponse) {
			return;
		}
		$res = array (
				'errorCode' => $errorCode,
				'errorMessage' => $errorMessage,
				'value' => $value,
				'timecost' => round ( microtime ( true ) - $startTime, 3 )
		);
		if (isset ( $this->api )) {
			$ext = $this->api->getReponse ();
			$res = array_merge ( $res, $ext );
		}
		return json_encode ( $res );
	}

	/**
	 *
	 * @return string
	 */
	private function getPrefix() {
		return 'Api_' . ucfirst ( $this->module );
	}
	/**
	 * 自动计算可用的版本然后返回实例化的对象
	 *
	 * @param unknown $className
	 * @throws ApiException
	 */
	private function getObject($className) {
		$request = Yaf_Dispatcher::getInstance ()->getRequest ();
		$version = key ( $request->getParams () );
		if (empty ( $version )) {
			throw new ApiException ( 'version can not be empty', ApiException::ERROR_VERSION );
		}
		static $versionList;
		$dir = APP_PATH . '/models/' . str_replace ( '_', '/', $this->getPrefix () );
		if (! isset ( $versionList )) {
			$versionList = [ ];
			if (false !== ($handle = opendir ( $dir ))) {
				while ( false !== ($dirName = readdir ( $handle )) ) {
					if ($dirName != '.' && $dirName != '..') {
						if (is_dir ( $dir . '/' . $dirName )) {
							$versionList [] = str_replace ( 'd', '.', $dirName );
						}
					}
				}
				ksort ( $versionList );
				$versionList = array_reverse ( $versionList );
			}
		}
		if (! in_array ( $version, $versionList )) {
			throw new ApiException ( 'version not found, version=' . $version, ApiException::ERROR_VERSION );
		}
		$isStart = false;
		foreach ( $versionList as $v ) {
			if (false === $isStart && $v == $version) {
				$isStart = true;
			}
			if ($isStart) {
				$versionStr = str_replace ( '.', 'd', $v );
				if ('api' == strtolower ( $className )) {
					$apiName = $this->getPrefix () . '_' . $versionStr . '_ApiModel';
					return $apiName::getInstance ();
				}
				$file = $dir . '/' . str_replace ( '.', 'd', $v ) . '/Fix/' . $className . '.php';
				if (file_exists ( $file )) {
					$apiName = $this->getPrefix () . '_' . $versionStr . '_Fix_' . $className . 'Model';
					return $apiName::getInstance ();
				}
				$file = $dir . '/' . str_replace ( '.', 'd', $v ) . '/Api/' . $className . '.php';
				if (file_exists ( $file )) {
					$apiName = $this->getPrefix () . '_' . $versionStr . '_Api_' . $className . 'Model';
					return $apiName::getInstance ();
				}
			}
		}
	}
}
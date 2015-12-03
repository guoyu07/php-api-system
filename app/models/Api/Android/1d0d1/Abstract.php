<?php
use Ares333\YafLib\Session;
class Api_Spider_1d0d0_AbstractModel extends AbstractModel {
	/**
	 * 用户函数中的异常
	 *
	 * @param unknown $message
	 * @param unknown $code
	 * @throws Api_Abstract_1d0d0Model
	 */
	function methodException($message, $code) {
		if ($code < 0 || $code >= 100) {
			user_error ( 'out of range of errorCode, errorCode=' . $code, E_USER_WARNING );
		}
		$code = 1000 + $code;
		throw new ApiException ( $message, $code );
	}

	/**
	 *
	 * @return Utility_Session2
	 */
	function getSession() {
		ini_set ( 'session.name', '_token' );
		ini_set ( "session.use_cookies", 1 );
		ini_set ( "session.use_only_cookies", 0 );
		$name = explode ( '_', get_class ( $this ) );
		$name = array_pop ( $name );
		return Session::getInstance ( __METHOD__ . '/' . $name );
	}

	/**
	 * 检查登录状态
	 */
	function checkLogin() {
		if (null == ($this->getSession ()->get ( 'isLogin' )) || true !== $this->getSession ()->get ( 'isLogin' )) {
			throw new ApiException ( 'user is not login', ApiException::ERROR_NOLOGIN );
		}
	}
	/**
	 * 检查参数不能为空
	 *
	 * @param string $name
	 */
	function checkParamEmpty($name, &$params) {
		if (empty ( $params [$name] )) {
			throw new ApiException ( "$name can't be empty", ApiException::ERROR_PARAM );
		}
	}
}
<?php
use Ares333\CurlMulti\Core;
class TestController extends Yaf_Controller_Abstract {
	function httpsAction() {
		$curl = new Core();
		$curl->add ( array (
				'url' => 'https://www.zu-ba.com:82',
				'opt' => array (
						CURLOPT_SSL_VERIFYHOST => true,
						CURLOPT_SSL_VERIFYPEER => true
				)
		), function ($r) {
			printr ( $r );
		} )->start ();
		return false;
	}
	function indexAction() {
		return false;
	}
}
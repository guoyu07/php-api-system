<?php
use Ares333\Plugin\Init;
use Ares333\Plugin\Module;
class Bootstrap extends Yaf_Bootstrap_Abstract {
	function _initPlugin(Yaf_Dispatcher $dispatcher) {
		$dispatcher->registerPlugin ( new Init () );
		$dispatcher->registerPlugin ( new Module () );
		$dispatcher->disableView ();
	}
}
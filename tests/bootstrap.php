<?php
define ( 'APP_PATH', dirname ( __DIR__ ) . '/app' );
(new Yaf_Application ( APP_PATH . '/conf/app.ini' ))->bootstrap ();
foreach ( glob ( __DIR__ . '/*.php' ) as $v ) {
	if (substr ( $v, - 13 ) != 'bootstrap.php') {
		include ($v);
	}
}
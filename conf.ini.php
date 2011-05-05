<?php
//force le niveau de remontée des erreurs
error_reporting(E_ALL | E_STRICT);
session_start();

/*
	A mettre dans le virtual host
	SetEnv LOCATION XXX
*/
define('LMS_PATH', dirname(__FILE__));


if( !isset($_SERVER['LOCATION']) || empty($_SERVER['LOCATION']) ){
	define( "SERVER_NAME", 'http://lms.kapok.x10.mx' );

} elseif( strpos('_DEV', $_SERVER['LOCATION']) !== false ){
	define( "SERVER_NAME", 'http://lms.dev' );

} else {
	define( "SERVER_NAME", 'http://lms' );
}

define( "UPLOAD_COVER_PATH", LMS_PATH.'/covers/');
define( "UPLOAD_STORAGE_PATH", LMS_PATH.'/storage/');

date_default_timezone_set('Europe/Zurich');

//stash cache
include(LMS_PATH.'/inc/Stash/Autoloader.class.php');

define('STASH_EXPIRE', 60 * 60 * 24 * 14); //2 weeks
define('STASH_PATH', LMS_PATH.'/stash/');

require( LMS_PATH.'/inc/function_commun.php' );
?>
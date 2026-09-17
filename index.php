
<?php


	ini_set('display_errors', 0);
	error_reporting(E_ALL);



/* ------------
 * PHP API INDEX page
 * @author ZP
 * @date   14.03.2025
 * ------------
 */

 	session_start();

	require_once('lib/apilib.php');
	require_once('lib/apidb.php');


	use apilib\ReqURI; 

	// READ REQUEST URI call
    ReqURI::readURI($_SERVER['REQUEST_URI']) ;
	header('Content-Type: application/json');

	if(ReqURI::isCallPing()){
		include_once(ReqURI::getPingFileName());
	}
	else if (ReqURI::isProtectedCall()){
		include_once('protected.php');
	}
	else  {
		include_once('call.php');
	}


?>
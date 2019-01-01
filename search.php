<?php

require_once('session.php');
require_once('dlm/dlm.php');

session_start();

if($_GET['clear'] == '1'){
	echo 'clear<br />';
	session_destroy();
	session_start();
}

if (!isset($_SESSION['session'])) {
	$session = new UserSession();
	$session->init();
	$_SESSION['session'] = serialize($session);
}
else {
	$session = unserialize($_SESSION['session']);
}

if($_GET['clear'] == '1'){
	print_r($session);
}

session_write_close();

switch($_GET['action']){
	case 'getnav':
		echo 'var data='.file_get_contents('data/en.json');
		exit();
		break;
		
	case 'getdlms':
		echo json_encode($session->getDlms($_GET['category']));
		exit();
		break;
		
	case 'search':
		exit('<div class="page"></page>');
		break;
}


?>
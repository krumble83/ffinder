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
		$data = json_decode(file_get_contents('data/data.json'), true);
		if($_GET['lang']){
			$lang = json_decode(file_get_contents('data/'.$_GET['lang'].'.json'), true);
		}
		if(!isset($lang))
			$lang = json_decode(file_get_contents('data/en.json'), true);
		
		$data = array_merge($data, $lang);			
		header('Content-Type: application/json');
		echo 'var data='.json_encode($data);
		exit();
		break;
		
	case 'getdlms':
		header('Content-Type: application/json');
		echo json_encode($session->getDlms($_GET['category']));
		exit();
		break;
		
	case 'search':
		header('Content-Type: application/json');
		echo json_encode($session->search($_GET['string'], $_GET['dlm']));
		exit();
		break;
		
	case 'getbrowsepage':
		$res = $session->getDlmPage($_GET['dlm'], $_GET['page']);
		exit(json_encode($res));
		break;
}


?>

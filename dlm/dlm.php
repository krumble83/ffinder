
<?php

class Dlm{

	private $name;

	public function __construct($name) {
		$this->init($name);
	}
	
	private function init($name){
		session_start();
		$this->name = $name;
	}
	
	public function doSearch($query, $categories, $dltypes){
		
	}
	
	public function getLink($page){
		
	}
}
d

?>
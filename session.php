<?php

class UserSession {
	private $pagePrefix;
	private $mDlm = array();
	
	public function __construct() {
		//$this->init();
	}
	
	public function init(){
		//echo __DIR__."<br />";
		$this->loadDlms();
		$this->loadHosts();
		//$this->doSearch('bitsoup', 'test', array());
	}
	
	public function getDlms($category){
		$out = array();
		
		foreach($this->mDlm as $k=>$v){
			if($v['ffinder'] && $v['ffinder']['categories']){
				if(!in_array($category, $v['ffinder']['categories']))
					continue;
			}
			if(!isset($out[$v['type']]))
				$out[$v['type']] = array();
			array_push($out[$v['type']], (object) array('name' => $k, 'title' => $v['displayname'], 'version' => $v['version'], 'site' => $v['site']));
		}
		//return $this->mDlm;
		return $out;

	}
	
	public function getDlmByName($name){
		foreach($this->mDlm as $k=>$v){
			if($v['name'] == $name)
				return $v;
		}
		return null;
	}
	
	public function search($string, $dlm){
		$res = array();
		$dlm = $this->getDlmByName($dlm);
		if(!$dlm)
			return $res;
		require_once($dlm['dlmpath'].'/'.$dlm['module']);
		$dlmi = new $dlm['class']();
		$curl = curl_init(); 
		$dlmi->prepare($curl, $string, $_COOKIE["dlm_$dml_username"], $_COOKIE["dlm_$dml_password"]);
		return $res;
	}
	
	public function getbrowsepage($dlm, $page){
		
	}
	
	private function loadDlms(){
		$dir    = 'dlm';
		$files = scandir($dir);
		
		$this->mDlm = array();
		//print_r($files);
		foreach($files as $f){
			if($f == "." OR $f == ".." or !is_dir(__DIR__."/dlm/$f"))
				continue;
			if(file_exists(__DIR__."/dlm/".$f."/INFO")){
				//echo "load $f<br />";
				$nfo = $this->loadINFO(__DIR__."/dlm/$f");
				//print_r($nfo);
				if($nfo !== NULL && $this->checkDlm($nfo, __DIR__."/dlm/".$f) == 0)
					//array_push($this->mDlm, $nfo);
					$nfo['dlmpath'] = __DIR__."/dlm/$f";
					$this->mDlm[$f] = $nfo;
			}
			else
				echo "/".$f."/INFO<br />";
		}
	}
	
	private function checkDlm($nfo, $path){
		$ret = 0;
		
		// check if php file exists
		
		// check if module is a search module (type == "search")
		//if(!array_key_exists('type', $nfo) || $nfo['type'] != 'search')
		//	$ret += 2;
		
		// check if php class not allready exists
		if(!array_key_exists('class', $nfo) || class_exists($nfo['class']))
			$ret += 4;
		
		return $ret;
	}
		
	public function doSearch($dlm, $query, $categories){
		foreach($this->mDlm as $key=> $value){
			if(array_key_exists('categories', $value)){
				echo 'cat found';
			}
			$path = __DIR__."/dlm/$key/".$value['module'];
			include_once($path);
			echo "$path<br />";
		}
	}
	
	private function loadHosts(){
		
	}
	
	private function loadINFO($path){
		$string = file_get_contents("$path/INFO");
		$json = json_decode($string, true);
		if(file_exists("$path/EXTENDED")){
			$string = file_get_contents("$path/EXTENDED");
			$json2 = json_decode($string, true);
			$json['ffinder'] = $json2;
		}
		if(file_exists("$path/BROWSE")){
			$string = file_get_contents("$path/BROWSE");
			$json2 = json_decode($string, true);
			$json['browse'] = $json2;
		}
		return $json;
	}
	
}

?>

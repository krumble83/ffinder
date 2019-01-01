<?php
/* Copyright (c) 2012 Synology Inc. All rights reserved. */


class  {

	public function __construct() {
	}

	public function prepare($curl, $query, $username, $password) {
		$url = $this->qurl . urlencode($query);
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
	}

	public function parse($plugin, $response) {
		$tbody = $this->getTableBody($response);
		$trArray = explode('<tr>', $tbody);

		$count = 0;
		foreach ($trArray as $item) {

		    $info = $this->getInfoFromItem($item);

		    if ($info) {
		    	$count++;
		        $plugin->addResult(
		            $info['title'], $info['download'], $info['size'],
		            $info['datetime'], $info['page'], $info['hash'],
		            $info['seeds'], $info['leechs'], $info['category']
		        );
		    }
		}

		return $count;
	}

}


?>

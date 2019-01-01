<?php
/* Copyright (c) 2012 Synology Inc. All rights reserved. */

if (!class_exists('Common')) {
class Common {
    // return substring that match prefix and suffix
    // returned string contains prefix and suffix
    static function getSubString($string, $prefix, $suffix) {
        $start = strpos($string, $prefix);
        if ($start === FALSE) {
            return $string;
        }

        $end = strpos($string, $suffix, $start);
        if ($end === FALSE) {
            return $string;
        }

        if ($start >= $end) {
            return $string;
        }

        return substr($string, $start, $end - $start + strlen($suffix));
    }

    static function getFirstMatch($string, $pattern) {
        if (1 === preg_match($pattern, $string, $matches)) {
            return $matches[1];
        }
        return FALSE;
    }

    static function convertSize($string) {
        $pattern = '/([0-9\.]+ *([a-zA-Z]*))/';
        $number;
        $unit;
        $unitTable = array('Bytes', 'KB', 'MB', 'GB', 'TB');

        if (1 === preg_match($pattern, $string, $matches)) {
            $number = $matches[1];
            $unit = $matches[2];
        }

        foreach ($unitTable as $idx => $unitStr) {
            if (0 === strcasecmp($unit, $unitStr)) {
                $unitSize = pow(1024, $idx);
                break;
            }
        }

        $size = floatval($number) * $unitSize;

        return round($size);
    }
}

}

class SynoDLMSearchBitSoup {
	private $pagePrefix = 'https://bitsoup.me/';
	private $qurl = 'https://bitsoup.me/browse.php?search=';
	private $loginURL = 'https://www.bitsoup.me/takelogin.php';
	private $COOKIE = '/tmp/bitsoup.cookie';

	public function __construct() {
	}

	public function prepare($curl, $query, $username, $password) {
		$url = $this->qurl . urlencode($query);
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);

		if ($username !== NULL && $password !== NULL) {
			$this->VerifyAccount($username, $password);
			curl_setopt($curl, CURLOPT_COOKIEFILE, $this->COOKIE);
		}
	}

	public function GetCookie()
	{
		return $this->COOKIE;
	}

	public function VerifyAccount($username, $password) {
		$ret = TRUE;

		if (file_exists($this->COOKIE)) {
			unlink($this->COOKIE);
		}
		$option = array('username'=>$username, 'password'=>$password);
		$PostData=http_build_query($option);

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_HEADER, TRUE);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, DOWNLOAD_TIMEOUT);
		curl_setopt($curl, CURLOPT_TIMEOUT, DOWNLOAD_TIMEOUT);
		curl_setopt($curl, CURLOPT_USERAGENT, DOWNLOAD_STATION_USER_AGENT);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($curl, CURLOPT_URL, $this->loginURL);

		curl_setopt($curl, CURLOPT_POST, TRUE);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $PostData);

		curl_setopt($curl, CURLOPT_COOKIEJAR, $this->COOKIE);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
		$Result = curl_exec($curl);
		curl_close($curl);

		if (FALSE !== strpos($Result, 'Login failed!')) {
			$ret = FALSE;
		}

		return $ret;
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

	private function getTableBody($response) {
		$prefix = 'class="koptekst"';
		$suffix = '</table>';
		$tmp = Common::getSubString($response, $prefix, $suffix);

		return $tmp;
	}

	private function getInfoFromItem($item) {

		$info = array();

		if (FALSE === strpos($item, '<td class="rowhead"')) {
		    return FALSE;
		}

		$tdArray = explode('<td ', $item);
		// 0
		// 1: category
		// 2: title & page
		// 3: bookmark (ignore)
		// 4: torrent link
		// 5: files
		// 6: ?? (ignore)
		// 7: time
		// 8: size
		// 9: download count (ignore)
		// 10: seeders
		// 11: leechers
		$title = $this->getTopicTitle($tdArray[2]);
		$info['page'] = $this->pagePrefix . $this->getPageLink($tdArray[2]);
		$info['title'] = $title;
		$info['category'] = "Other";
		$info['hash'] = md5($title);
		$info['download'] = $this->getDownloadLink($tdArray[4]);
		$info['size'] = $this->getSize($tdArray[8]);
		$info['datetime'] = $this->getTime($tdArray[7]);
		$info['seeds'] = $this->getNum($tdArray[10]);
		$info['leechs'] = $this->getNum($tdArray[11]);

		return $info;
    }
    private function getTopicTitle($string) {
    	$pattern = '/<b>([^"]+)<\/b>/';
        return Common::getFirstMatch($string, $pattern);
    }
    private function getPageLink($string) {
        $pattern = '/a href="([^"]+)&amp/';

        return Common::getFirstMatch($string, $pattern);
    }
    private function getDownloadLink($string) {
        $pattern = '/a href="([^"]+)"/';
        $link = $this->pagePrefix . Common::getFirstMatch($string, $pattern);
        return $link;
    }
    private function getHash($string) {
        $pattern = '/id=([^"]+)"/';
        return Common::getFirstMatch($string, $pattern);
    }
    private function getSize($string) {
    	$pattern = '/>(.*)<br>/';
    	$size = Common::getFirstMatch($string, $pattern);

    	$pattern = '/<br>(.*)</';
    	$unit = Common::getFirstMatch($string, $pattern);
    	$string = $size . " " . $unit;
        return Common::convertSize($string);
    }
    private function getNum($string) {
        $pattern = '/>([0-9]+)</';
        return Common::getFirstMatch($string, $pattern);
    }
    private function getTime($string) {
        $pattern = '/(\d{4}-\d{2}-\d{2})/';
        $date = Common::getFirstMatch($string, $pattern);
        $pattern = '/(\d{2}:\d{2}:\d{2})/';
        $time = Common::getFirstMatch($string, $pattern);

        return $date . " " . $time;
    }
}


?>

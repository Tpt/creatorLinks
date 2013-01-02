<?php
class Api {
	const USER_AGENT = 'Creator Links/0.1';
	public $wiki;

	public function __construct( $wiki ) {
		$this->wiki = $wiki;
	}

	/**
	* api query
	* @var $params an associative array for params send to the api
	* @return an array with whe relsult of the api query
	* @throws HttpException
	*/
	public function query( $params ) {
		$params['action'] = 'query';
		return $this->request( $params );
	}

	public function request( $params ) {
		$data = 'format=php';
		foreach($params as $param_name => $param_value) {
			$data .= '&' . $param_name . '=' . urlencode($param_value);
		}
		$url = $this->wiki . '/w/api.php?' . $data;
		$response = self::get($url);
		return unserialize($response);
	}

	public function getContinueParam($result) {
		if(array_key_exists('query-continue', $result)) {
			$keys = array_keys($result['query-continue']);
			$keys2 = array_keys($result['query-continue'][$keys[0]]);
			return array( $keys2[0], $result['query-continue'][$keys[0]][$keys2[0]] );
		} else {
			return null;
		}
	}

	/**
	* @var $url the url
	* @return the file content
	*/
	public static function get($url) {
		$ch = self::getCurl($url);
		$response = curl_exec($ch);
		if(curl_errno($ch)) {
			throw new Exception(curl_error($ch), curl_errno($ch));
		} else if(curl_getinfo($ch, CURLINFO_HTTP_CODE) >= 400) {
			throw new Exception('HTTP error: ' . $url, curl_getinfo($ch, CURLINFO_HTTP_CODE));
		}
		curl_close($ch);
		return $response;
	}

	/**
	* @var $url the url
	* @return curl
	*/
	static function getCurl($url) {
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_USERAGENT, Api::USER_AGENT);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		return $ch;
	}

	/**
	* @return the lang of the Wikisource used
	*/
	public static function getHttpLang() {
		$lang = '';
		if(isset($_GET['lang'])) {
			$lang = $_GET['lang'];
		} else if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
			$langs = explode(',',$_SERVER['HTTP_ACCEPT_LANGUAGE']);
			if(isset($langs[0])) {
				$langs = explode('-', $langs[0]);
				$lang = $langs[0];
			}
		}
		return strtolower($lang);
	}
}

<?php

class EbossAPIClient {

	private $cache;

	private $apiClient;

	private $response;

	private $api_user, $api_key, $api_base;

	static $version = "1";

	function __construct($api_user, $api_key, $api_base = "http://www.eboss.co.nz/api/v2/") {
		$this->cache = new EbossAPIClient_Cache;
		$this->api_user = $api_user;
		$this->api_key = $api_key;
		$this->api_base = $api_base;
	}

	function SendRequest($object, $params = array()) {
		if(isset($_GET['flush'])) $params['flush'] = 1;
		$query = ($params) ? "?".http_build_query($params) : "";
		$url = $this->api_base.$object.$query;
		$user_agent =  "EBOSS API Client v".self::$version;
		$auth = "{$this->api_user}:{$this->api_key}";
		

		if(function_exists("curl_init")) {
			$curl = curl_init();
			curl_setopt_array($curl, array(
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_URL => $url,
				CURLOPT_USERAGENT => $user_agent." CURL",
				CURLOPT_USERPWD => $auth
			));

			$response_raw = curl_exec($curl);
			curl_close($curl);
		} else {
			$context = stream_context_create(array(
				"http" => array(
					"header"  => "Authorization: Basic ".base64_encode($auth)."\r\n",
					"user_agent" => $user_agent." Stream"
				)
			));
			$response_raw = file_get_contents($url, false, $context);
		}

		$response = json_decode($response_raw);
		
		if($response->Status == "error") {
			throw new EbossAPIClient_Exception($response->Message, $response->Code);
			return false;
		}

		$this->response = new EbossAPIClient_Response($response);
		return $this->response;
	}

	function cachedRequest($object, $params = array()) {
		$cacheKey = md5($this->api_base).$object."-".implode("-", $params);
		if(!$data = $this->cache->get($cacheKey)) {
			try {
				$data = $this->SendRequest($object, $params);
				$this->cache->write($cacheKey, $data);
			} catch(Exception $e) {
				throw $e;
			}
		}
		return $data;
		
	}

	/**
	* Returns Brand info for given ID
	* @param int $brandID ID of the brand to retrieve
	* @return EbossAPIClient_Response $response of the data
	**/
	function Brand($brandID) {
		return $this->cachedRequest("Brand", array("BrandID" => $brandID));
	}

	/**
	* Returns Categories for brandID
	* @param int $brandID ID of the brand to retrieve categories for
	* @return EbossAPIClient_Response $response of the data
	**/
	function Categories($brandID) {
		return $this->cachedRequest("Categories", array("BrandID" => $brandID));
	}

	/**
	* Returns Category data for given Brand and category ID
	* @param int $brandID ID of the brand
	* @param int $categoryID the category id to retrieve
	* @return EbossAPIClient_Response $response of the data
	**/
	function Category($brandID, $categoryID) {
		return $this->cachedRequest("Category", array("BrandID" => $brandID, "CategoryID" => $categoryID));
	}

	/**
	* Returns Ranges for given brandID
	* @param int $brandID of the ranges to retrieve
	* @return EbossAPIClient_Response $response of the data
	**/
	function Ranges($brandID) {
		return $this->cachedRequest("Ranges", array("BrandID" => $brandID));
	}

	/**
	* Returns Range info for given Range & Brand ID
	* @param int $brandID
	* @param int $rangeID of the range to retrieve
	* @return EbossAPIClient_Response $response of the data
	**/
	function Range($brandID, $rangeID) {
		return $this->cachedRequest("Range", array("BrandID" => $brandID, "RangeID" => $rangeID));
	}

	/**
	* Returns Products for given brand and / or filters
	* pass in array of filter to filter returned objects by category / range
	* Eg $filter = array("CategoryID" => 12, "RangeID" => 123);
	* @param int $brandID ID of the brand to retrieve
	* @param array $filter array of filters
	* @return EbossAPIClient_Response $response of the data
	**/
	function Products($brandID, $filter = array()) {
		$filter['BrandID'] = $brandID;
		return $this->cachedRequest("Products", $filter);
	}

	/**
	* Returns product info for given brand and product ID
	* The response contains extra information, such as product images and download links
	* @param $brandID of the brand to retrieve
	* @param $productID of the product to retrieve
	* @return EbossAPIClient_Response $response of the data
	**/
	function Product($brandID, $productID) {
		return $this->cachedRequest("Product", array("BrandID" => $brandID, "ProductID" => $productID));
	}

	/**
	* Returns product downloads for $productID
	* The response contains a map of file ids and labels
	* @param $brandID of the brand to retrieve
	* @param $productID of the product to retrieve downloads for
	* @return EbossAPIClient_Response $response of the data
	**/
	function ProductDownloads($brandID, $productID) {
		return $this->cachedRequest("ProductDownloads", array("BrandID" => $brandID, "ProductID" => $productID));
	}


}

class EbossAPIClient_Response extends stdClass implements IteratorAggregate {

	private $reponse;

	function __construct($response) {
		$this->response = $response;

	}

	public function getIterator() {
		if(!$data = $this->Data()) {
			$data = array();
		}
		return new ArrayIterator($data);
	}

	function Data() {
		if(isset($this->response->Data)) {
			return (object)$this->response->Data; 
		} else {
			return false;
		}
	}

	function __get($var) {
		if($data = $this->Data()) {
			return $data->$var;
		}
	}

	function debug() {
		print_r($this->response);
	}
}

class EbossAPIClient_Cache {

	static $ttl = 360;
	static $cache_dir;

	function get($name) {
		$fname = $this->getCacheFilename($name);
		if(isset($_GET['flush'])) {
			return false;
		}
		if(file_exists($fname)) {
			if(filemtime($fname)+self::$ttl > time()) {
				$contents = file_get_contents($fname);
				return unserialize($contents);
			}
		}
	}

	function write($name, $data) {
		$ser_data = serialize($data);
		return file_put_contents($this->getCacheFilename($name), $ser_data);
	}

	function getCacheFilename($name) {
		return $this->getCacheDir()."/eboss_api_cache-".$name.".tmp";
	}

	function getCacheDir() {
		return (self::$cache_dir) ? self::$cache_dir : sys_get_temp_dir();
	}

}

class EbossAPIClient_Exception extends Exception {

}
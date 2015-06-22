<?php

require_once("util.php");

class Curl {

	protected $curl_handle;
	protected $url;
	protected $arguments;
	protected $options;
	protected $response;
	
	function __construct ($url, $arguments=null, $curl_options=null) {
		$this->url = $url;
		$this->curl_handle = curl_init();

		if (!$arguments)
			$arguments = array();

		if (!$curl_options)
			$curl_options = array();

		$this->options = $curl_options + array(
				CURLOPT_URL => $this->url,
				CURLOPT_RETURNTRANSFER => true,
				// TODO: Need to update the CA cert, but don't want to do it now.
				CURLOPT_SSL_VERIFYPEER => false,
  				CURLOPT_SSL_VERIFYHOST => 2
			);

		$this->setup();
	}

	function __destruct () {
		curl_close($this->curl_handle);
	}

	function check_errors () {
		$error = curl_error($this->curl_handle);
		if ($error) {
			throw new Exception($error);
		}
	}

	function execute () {
		$this->response = curl_exec($this->curl_handle);

		$this->check_errors();

		return $this->response;
	}

	function get_response_code () {
		return curl_getinfo($this->curl_handle, CURLINFO_HTTP_CODE);
	}

	function set_headers (array $headers) {
		curl_setopt($this->curl_handle, CURLOPT_HTTPHEADER, $headers);
	}

	function setup () {
		foreach ($this->options AS $option => $value) {
			curl_setopt($this->curl_handle, $option, $value);
		}
	}
}

class CurlPost extends Curl {
	
	function __construct ($url, $arguments=null, $curl_options=null) {
		$curl_options[CURLOPT_POST] = 1;
		if (!$arguments) {
			$arguments = array();
		}
		$curl_options[CURLOPT_POSTFIELDS] = http_build_query($arguments);
		//if ($arguments && count($arguments)) {
		//	$curl_options[CURLOPT_POSTFIELDS] = http_build_query($arguments); // $arguments
		//}
		parent::__construct($url, $arguments, $curl_options);
	}

	function set_json_arguments (array $arguments) {
		curl_setopt($this->curl_handle, CURLOPT_POSTFIELDS, json_encode($arguments));
	}

}

?>
<?php

/**
 * =======================================
 * ###################################
 * SWIFT Framework
 *
 * @package	SWIFT
 * @author	Kayako Infotech Ltd.
 * @copyright	Copyright (c) 2001-2009, Kayako Infotech Ltd.
 * @license	http://www.kayako.com/license
 * @link	http://www.kayako.com
 * @filesource
 * ###################################
 * =======================================
 */
/**
 * Http based on curl
 *
 * @author atul atri
 */
//SWIFT_Loader::LoadLibrary('HTTP:HTTP_Exception');

class SWIFT_HttpCurl extends SWIFT_Library
{

	// singleton instace
	protected static $_Instance;

	// response reponse code and errors
	const SRV_RESPONSE = 0;
	const SRV_CODE = 1;
	const SRV_ERROR_CODE = 3;
	const SRV_ERROR = 4;

	// http types
	const HTTP_METHOD_GET = 'GET';
	const HTTP_METHOD_POST = 'POST';
	const HTTP_METHOD_DELETE = 'DELETE';
	const HTTP_METHOD_PUT = 'PUT';
	const HTTP_METHOD_HEAD = 'HEAD';

	// form content type
	const HTTP_FORM_CONTENT_TYPE_APPLICATION = 0;
	const HTTP_FORM_CONTENT_TYPE_MULTIPART = 1;

	// default request headers
	public $_lastCurl = null;

	// default request headers
	public $_MultiCurlHandles = array();

	// multi curl object
	private $_MultiCurl = null;

	// pointer to the starting of un processed list in $_MultiCurlHandles
	private $_curlCurrentIndex = 0;

	// default options for curl request.
	protected $_defaultReqOpts = array();

	// default request headers
	protected $_defaultReqHeaders = array();

	// list of requests to be executed using multi curl
	protected $_reqQueue = array();

	/**
	 * Constructor
	 *
	 * @author Atul Atri
	 * @return bool "true" on Success, "false" otherwise
	 * @throws SWIFT_API_Exception invalid settings found
	 */
	public function __construct()
	{
		parent::__construct();

		return true;
	}

	/**
	 * Destructor
	 *
	 * @author Atul Atri
	 * @return bool "true" on Success, "false" otherwise
	 */
	public function __destruct()
	{
		parent::__destruct();

		return true;
	}

	public static function GetInstance()
	{
		if (!isset(self::$_Instance) || (get_class(self::$_Instance) != get_called_class())) {
			$_class = get_called_class();
			self::$_Instance = new $_class();
		}

		return self::$_Instance;
	}

	/**
	 * Get request default curl options
	 *
	 * @return array default curl options
	 */
	public function GetDefaultReqOpts()
	{
		return $this->_defaultReqOpts;
	}

	/**
	 * Set request default curl options
	 *
	 * @param array $_defaultReqOpts  default curl options
	 * @return this object
	 */
	public function SetDefaultReqOpts(array $_defaultReqOpts)
	{
		$this->_defaultReqOpts = $_defaultReqOpts;

		return $this;
	}

	/**
	 * Get default request headers
	 *
	 * @return array default request headers
	 */
	public function GetDefaultReqHeaders()
	{
		return $this->_defaultReqHeaders;
	}

	/**
	 * Set default request headers
	 *
	 * @param array $_defaultReqHeaders default request headers
	 * @return this object
	 */
	public function SetDefaultReqHeaders(array $_defaultReqHeaders)
	{
		$this->_defaultReqHeaders = $_defaultReqHeaders;

		return $this;
	}

	/**
	 * Intialise curl and set options
	 *
	 * @author Atul Atri
	 * @param string $_url         url to be executed
	 * @param array  $_postParams  parameters array (e.g. array('username'=>'atul', 'password' => '1234'))to be sent with post or put request
	 * @param string $_method      Request Method. Any of 'GET', 'POST', 'PUT', 'DELETE'
	 * @param string $_method      Headers to be sent with request. e.g. array('Conent-Type' =>'application/json');
	 * @param array  $_reqOptions  Any other curl request options
	 * @param int $_formContentType form content type
	 * @return object curl object
	 */
	public function InitCurl($_url, $_postParams = null, $_method = self::HTTP_METHOD_GET, $_headers = array(), $_reqOptions = array(), $_formContentType = self::HTTP_FORM_CONTENT_TYPE_APPLICATION)
	{
		$_method = strtoupper($_method);

		if ($_method != self::HTTP_METHOD_GET && $_method != self::HTTP_METHOD_POST && $_method != self::HTTP_METHOD_PUT &&
				$_method != self::HTTP_METHOD_DELETE && $_method != self::HTTP_METHOD_HEAD){

			throw new SWIFT_HTTP_Exception("Invalid $_method provided.");
		}

		$_reqHeaders = array();

		if (isset($_reqOptions[CURLOPT_HEADER])) {
			$_reqHeaders = $_reqOptions[CURLOPT_HEADER];
			unset($_reqOptions[CURLOPT_HEADER]);
		}

		$_options = $_reqOptions + array(CURLOPT_CUSTOMREQUEST => $_method, CURLOPT_RETURNTRANSFER => true) + $this->_defaultReqOpts;

		switch ($_method)
		{
			case self::HTTP_METHOD_POST:
				$_options[CURLOPT_POST] = true;
				/* No break */

				case self::HTTP_METHOD_PUT:
					if (is_array($_postParams) && count($_postParams) > 0 && self::HTTP_FORM_CONTENT_TYPE_APPLICATION === $_formContentType) {
						$_postParams = http_build_query($_postParams);
					}

					$_options[CURLOPT_POSTFIELDS] = $_postParams;

				break;
			case self::HTTP_METHOD_HEAD:
				$_options[CURLOPT_NOBODY] = true;
			/* No break */

			case  self::HTTP_METHOD_DELETE:

			case self::HTTP_METHOD_GET:
				if (is_array($_postParams)) {
					$_url .= '?' . http_build_query($_postParams, null, '&');
				} else if ($_postParams) {
					$_url .= '?' . $_postParams;
				}

				break;

			default:
				$_options[CURLOPT_POSTFIELDS] = $_postParams;

				break;
		}

		$_mergedHeaders = $_reqHeaders + $_headers + $this->_defaultReqHeaders;

		if (count($_mergedHeaders) > 0) {
			foreach ($_mergedHeaders as $_key => $_val) {
				$_headersToAdd[] = "$_key: $_val";
			}

			$_options[CURLOPT_HTTPHEADER] = $_headersToAdd;
		}

		$_ch = curl_init($_url);
		curl_setopt_array($_ch, $_options);

		return $_ch;
	}

	/**
	 * Execute request and return response
	 *
	 * @author Atul Atri
	 * @param string $_url         url to be executed
	 * @param array  $_postParams  parameters array (e.g. array('username'=>'atul', 'password' => '1234'))to be sent with post or put request
	 * @param string $_method      Request Method. Any of 'GET', 'POST', 'PUT', 'DELETE'. If wrong method name is given...'GET' will be used
	 * @param string $_headers      Headers to be sent with request. e.g. array('Conent-Type' =>'application/json');
	 * @param array  $_reqOptions  Any other curl request options
	 * @param int $_formContentType form content type
	 * @return array array(self::SRV_CODE => 'http_response_code', self::SRV_RESPONSE => 'response')
	 * @throws SWIFT_API_Exception if curl request failed
	 */
	public function SendRequest($_url, $_postParams = null, $_method = self::HTTP_METHOD_GET, $_headers = array(), $_reqOptions = array(),  $_formContentType = self::HTTP_FORM_CONTENT_TYPE_APPLICATION)
	{
		$_returnMe = array();
		$this->_lastCurl = null;
		$this->_lastCurl = $this->InitCurl($_url, $_postParams, $_method, $_headers, $_reqOptions, $_formContentType);
		//@todo decode contents
		$_returnMe[self::SRV_RESPONSE] = curl_exec($this->_lastCurl);

		$_curlInfo = curl_getinfo($this->_lastCurl);
		$_returnMe[self::SRV_CODE] = $_curlInfo['http_code'];

		$_returnMe[self::SRV_ERROR] = curl_error($this->_lastCurl);
		$_returnMe[self::SRV_ERROR_CODE] = curl_errno($this->_lastCurl);

		if ($_returnMe[self::SRV_ERROR]) {
			throw new SWIFT_HTTP_Exception($this->Language->Get('BC_ERR_INVALID_SETTINGS') . $_returnMe[self::SRV_ERROR]);
		}

		return $_returnMe;
	}

	/**
	 * Init multi curl
	 *
	 * @author Atul Atri
	 * @return void
	 */
	public function InitMultiCurl()
	{
		$this->_MultiCurlHandles = array();
		$this->_MultiCurl = null;
		$this->_curlCurrentIndex = 0;
		$this->_reqQueue = array();
	}

	/**
	 * Add request to multicurl
	 *
	 * @author Atul Atri
	 * @param string $_url         url to be executed
	 * @param array  $_postParams  parameters array (e.g. array('username'=>'atul', 'password' => '1234'))to be sent with post or put request
	 * @param string $_method      Request Method. Any of 'GET', 'POST', 'PUT', 'DELETE'. If wrong method name is given...'GET' will be used
	 * @param string $_headers      Headers to be sent with request. e.g. array('Conent-Type' =>'application/json');
	 * @param array  $_reqOptions  Any other curl request options
	 * @return void
	 */
	public function AddRequest($_url, $_postParams = null, $_method = self::HTTP_METHOD_GET, $_headers = array(), $_reqOptions = array(), $_formContentType = self::HTTP_FORM_CONTENT_TYPE_APPLICATION)
	{
		$_tmp = array();
		$_tmp['url'] = $_url;
		$_tmp['postParams'] = $_postParams;
		$_tmp['method'] = $_method;
		$_tmp['headers'] = $_headers;
		$_tmp['reqOptions'] = $_reqOptions;
		$_tmp['formContentType'] = $_formContentType;
		$this->_reqQueue[] = $_tmp;
	}

	/**
	 * Executes multi curl requests and return response. Response is returned in an array and
	 * result array contains responses in same order in what requests were added. Response array format is
	 * array(array(self::SRV_CODE => 'http_response_code', self::SRV_RESPONSE => 'response',
	 * 	self::SRV_ERROR_CODE => 'any error code', self::SRV_ERROR => 'any error string'), ...)
	 *
	 * @param int $_maxConcurrent Executes this numbers of requests in parallel
	 * @param bool $_isRetry are request being executed again
	 * @author Atul Atri
	 * @return array e.g array()
	 */
	public function ExecuteMultiCurl($_maxConcurrent = 10)
	{
		$this->_curlCurrentIndex = 0;
		$this->_MultiCurlHandles = array();

		foreach ($this->_reqQueue as $_nextReq) {
			$this->_MultiCurlHandles[] = $this->InitCurl($_nextReq['url'], $_nextReq['postParams'], $_nextReq['method'], $_nextReq['headers'], $_nextReq['reqOptions'], $_nextReq['formContentType']);
		}

		$this->_MultiCurl = curl_multi_init();

		$_running = 0;

		do {
			$this->AddHandles(min(array($_maxConcurrent - $_running, $this->MoreCurlToDo())));
			//stay until multicurl is running
			while ($_exec = curl_multi_exec($this->_MultiCurl, $_running) === -1);

			curl_multi_select($this->_MultiCurl);
			//remove completed curl handles
			while ($_multiInfo = curl_multi_info_read($this->_MultiCurl, $_msgs)) {
				curl_multi_remove_handle($this->_MultiCurl, $_multiInfo['handle']);
			}
		} while ($_running || $this->MoreCurlToDo());

		$_multiCurlResponseArr = array();

		foreach ($this->_MultiCurlHandles as $_nextHandle) {
			$_tmp = array();
			//@todo decode contents
			$_tmp[self::SRV_RESPONSE] = curl_multi_getcontent($_nextHandle);
			$_curlInfo = curl_getinfo($_nextHandle);
			$_tmp[self::SRV_CODE] = $_curlInfo['http_code'];
			$_tmp[self::SRV_ERROR] = curl_error($_nextHandle);
			$_tmp[self::SRV_ERROR_CODE] = curl_errno($_nextHandle);
			$_multiCurlResponseArr[] = $_tmp;
		}

		return $_multiCurlResponseArr;
	}

	/**
	 * End multi curl session release all resources
	 *
	 * @author Atul Atri
	 * @return void
	 */
	public function EndMultiCurl()
	{
		foreach ($this->_MultiCurlHandles as $_nextHandle) {
			curl_close($_nextHandle);
		}

		curl_multi_close($this->_MultiCurl);

		$this->_MultiCurlHandles = array();
		$this->_MultiCurl = null;
		$this->_curlCurrentIndex = 0;
		//we are not resetting request array
	}

	/**
	 * Count how many more curls to be executed for current multi curl session
	 *
	 * @author Atul Atri
	 * @return int number of curls more to execute
	 */
	private function MoreCurlToDo()
	{
		return count($this->_MultiCurlHandles) - $this->_curlCurrentIndex;
	}

	/**
	 * Add numbers of request to multicurl
	 *
	 * @param int $_num number of handlers to be added
	 * @author Atul Atri
	 * @return void
	 */
	private function AddHandles($_num)
	{
		while ($_num-- > 0) {
			curl_multi_add_handle($this->_MultiCurl, $this->_MultiCurlHandles[$this->_curlCurrentIndex]);
			$this->_curlCurrentIndex++;
		}
	}

}

?>
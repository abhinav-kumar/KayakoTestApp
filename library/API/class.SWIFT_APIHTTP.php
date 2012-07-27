<?php

/**
 * =======================================
 * ###################################
 * Basecamp App
 *
 * @package	Basecamp
 * @author	Kayako Infotech Ltd.
 * @copyright	Copyright (c) 2001-2009, Kayako Infotech Ltd.
 * @license	http://www.kayako.com/license
 * @link	http://www.kayako.com
 * @filesource
 * ###################################
 * =======================================
 */
/**
 * Handles basic http operations
 *
 * @author Atul Atri
 */
//SWIFT_Loader::LoadLibrary('HTTP:HttpCurl');
//SWIFT_Loader::LoadLibrary('API:APIOauth2');
//SWIFT_Loader::LoadLibrary('API:API_Exception');

class SWIFT_APIHttp extends SWIFT_HttpCurl
{

	//used count the req retry count
	private $_reqRetryCount = 0;

	//max req retires
	private $_maxSendReqRetries = 1;

	/**
	 * Constructor
	 *
	 * @author Atul Atri
	 * @return bool "true" on Success, "false" otherwise
	 */
	public function __construct()
	{
		parent::__construct();

		$_SWIFT = SWIFT::GetInstance();

		//$_SWIFT->Language->LoadApp('basecamp', 'basecamp');

		$_maxConnect = SWIFT_ConfigManager::Get('REQ_MAX_CONNECT');
		$_connectTimeout = SWIFT_ConfigManager::Get('REQ_CONNECT_TIMEOUT');
		$_timeout = $_SWIFT->Settings->Get('bc_req_timeout');
		$_contentType = SWIFT_ConfigManager::Get('REQ_CONTENT_TYPE');
		$_redirection = SWIFT_ConfigManager::Get('REQ_FOLLOW_LOCATION');

		$_bcAppName = $_SWIFT->Settings->Get('bc_app_name');
		$_bcAppEmail = $_SWIFT->Settings->Get('bc_email');

		if (empty($_bcAppName) && !filter_var($_bcAppEmail, FILTER_VALIDATE_EMAIL)) {
			throw new SWIFT_API_Exception($_SWIFT->Language->Get('BC_ERR_INVALID_SETTINGS'));
		}

		$_userAgent = "$_bcAppName ($_bcAppEmail)";

		$this->_defaultReqOpts = array(CURLOPT_MAXCONNECTS => $_maxConnect, CURLOPT_CONNECTTIMEOUT => $_connectTimeout,
			CURLOPT_TIMEOUT => $_timeout, CURLOPT_FOLLOWLOCATION => $_redirection, CURLOPT_USERAGENT => $_userAgent);
		$_shouldDebug = SWIFT_ConfigManager::get('DEBUG_CURL');

		if ($_shouldDebug) {
			$_debug_file = SWIFT_ConfigManager::Get('DEBUG_CURL_FILE');
			$_fp = @fopen($_debug_file, "a");
			$this->_defaultReqOpts[CURLOPT_VERBOSE] = true;
			$this->_defaultReqOpts[CURLOPT_STDERR] = $_fp;
		}

		$this->_defaultReqHeaders['Content-Type'] = $_contentType;
	}

	/**
	 * Executes multi curl requests and return response. Response is returned in an array and
	 * result array contains responses in same order in what requests were added. Response array format is
	 * array(array(self::SRV_CODE => 'http_response_code', self::SRV_RESPONSE => 'response',
	 * 	self::SRV_ERROR_CODE => 'any error code', self::SRV_ERROR => 'any error string'), ...)
	 *
	 * @param int $_maxConcurrent Executes this numbers of requests in parallel
	 * @author Atul Atri
	 * @return array e.g array()
	 */
	public function ExecuteMultiCurl($_maxConcurrent = 10, $_isRetry = false)
	{
		$_multiCurlResponseArr = parent::ExecuteMultiCurl($_maxConcurrent);

		$_isAll401 = true;

		foreach ($_multiCurlResponseArr as $_nextResponse) {

			if ($_nextResponse[self::SRV_CODE] != 401) {
				$_isAll401 = false;
				break;
			}
		}

		if (!$_isRetry && $_isAll401) {
			$this->_reqRetryCount = 0;
		}

		if ($_isAll401 == 401 && $this->_reqRetryCount < $this->_maxSendReqRetries) {
			$this->_CurlCurrentIndex = 0;
			//most probably token expired...ask oauth lib to regenerate token
			$_refreshed = SWIFT_APIOauth2::RefreshToken();

			if ($_refreshed) {
				$this->_reqRetryCount++;
				$this->ModifyAuthHeader();

				return $this->ExecuteMultiCurl($_maxConcurrent, true);
			}
		}

		return $_multiCurlResponseArr;
	}

	/**
	 * This function attaches a authrization header to request and execute it
	 *
	 * @author Atul Atri
	 * @param string $_url         url to be executed
	 * @param array  $_postParams  parameters array (e.g. array('username'=>'atul', 'password' => '1234'))to be sent with post or put request
	 * @param string $_method      Request Method. Any of 'GET', 'POST', 'PUT', 'DELETE'. If wrong method name is given...'GET' will be used
	 * @param string $_method      Headers to be sent with request. e.g. array('Conent-Type' =>'application/json');
	 * @param array  $_reqOptions  Any other curl request options
	 * @param bool  $_isRetry  Is request being retried
	 * @param int $_formContentType form content type
	 * @return array array(self::SRV_CODE => 'http_response_code', self::SRV_RESPONSE => 'response')
	 */
	public function SendSingedRequest($_url, $_postParams = null, $_method = 'GET', $_headers = array(), $_reqOptions = array(),
			$_formContentType = self::HTTP_FORM_CONTENT_TYPE_APPLICATION, $_isRetry = false)
	{
		$_SWIFT = SWIFT::GetInstance();

		$_authHeaderName = SWIFT_ConfigManager::Get('AUTH_HEADER_NAME');
		$_headers[$_authHeaderName] = $_SWIFT->Settings->Get('bc_auth_token');
		$_returnMe = $this->SendRequest($_url, $_postParams, $_method, $_headers, $_reqOptions, $_formContentType);

		if (!$_isRetry) {
			$this->_reqRetryCount = 0;
		}

		if ($_returnMe[SWIFT_APIHttp::SRV_CODE] == 401 && $this->_reqRetryCount < $this->_maxSendReqRetries) {
			//most probably token expired...ask oauth lib to regenerate token
			$_refreshed = SWIFT_APIOauth2::RefreshToken();

			if ($_refreshed) {
				$this->_reqRetryCount++;
				$this->SendSingedRequest($_url, $_postParams, $_method, $_headers, $_reqOptions, $_formContentType, true);
			}
		}

		return $_returnMe;
	}

	/**
	 * This function attaches a authrization header to request and add it to be executed
	 *
	 * @author Atul Atri
	 * @param string $_url         url to be executed
	 * @param array  $_postParams  parameters array (e.g. array('username'=>'atul', 'password' => '1234'))to be sent with post or put request
	 * @param string $_method      Request Method. Any of 'GET', 'POST', 'PUT', 'DELETE'. If wrong method name is given...'GET' will be used
	 * @param string $_method      Headers to be sent with request. e.g. array('Conent-Type' =>'application/json');
	 * @param array  $_reqOptions  Any other curl request options
	 * @param int $_formContentType form content type
	 * @return void
	 */
	public function AddSingedRequest($_url, $_postParams = null, $_method = 'GET', $_headers = array(),
			$_reqOptions = array(), $_formContentType = self::HTTP_FORM_CONTENT_TYPE_APPLICATION)
	{
		$_SWIFT = SWIFT::GetInstance();

		$_authHeaderName = SWIFT_ConfigManager::Get('AUTH_HEADER_NAME');
		$_headers[$_authHeaderName] = $_SWIFT->Settings->Get('bc_auth_token');

		$this->AddRequest($_url, $_postParams, $_method, $_headers, $_reqOptions, $_formContentType);
	}

	/**
	 * Modifies Authentication header in queued requests
	 *
	 * @author Atul Atri
	 * @return void
	 */
	private function ModifyAuthHeader()
	{
		$_SWIFT = SWIFT::GetInstance();
		$_authHeaderName = SWIFT_ConfigManager::Get('AUTH_HEADER_NAME');
		$_authHeaderValue = $_SWIFT->Settings->Get('bc_auth_token');

		foreach ($this->_reqQueue as &$_nextReq) {

			if (isset($_nextReq['headers']) && isset($_nextReq['headers'][$_authHeaderName])) {
				$_nextReq['headers'][$_authHeaderName] = $_authHeaderValue;
			}
		}
	}

}

?>

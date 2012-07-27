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
 * Description of class
 *
 * @author Atul Atri
 */
//SWIFT_Loader::LoadLibrary('API:APIBase');
//SWIFT_Loader::LoadLibrary('API:API_Exception');

class SWIFT_APIMessage extends SWIFT_APIBase
{

	/**
	 * Constructor
	 *
	 * @author Atul Atri
	 * @return bool "true" on Success, "false" otherwise
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

	/**
	 * Post message to basecamp
	 *
	 * @author atul atri
	 * @param string $_project basecamp project id where message needs to be posted
	 * @param string $_subject message subject
	 * @param string $_content message content
	 * @param bool $_isMulti request is part of multiple requests
	 * @return	mixed void id resquest is part of multirequest else json response
	 * @throws SWIFT_API_Exception If curl request is failed or respose code is not 201
	 */
	public function PostMessage($_project, $_subject, $_content, $_isMulti = fasle)
	{
		$_messagePostUrl = SWIFT_ConfigManager::Get('MSG_POST_SUB_URL');
		$_messagePostUrl = sprintf($_messagePostUrl, $_project);
		$_baseUrl = $this->BcApiUrl();
		$_url = $_baseUrl . $_messagePostUrl;

		$_postParamArr = array('subject' => $_subject, 'content' => $_content);
		$_body = json_encode($_postParamArr);

		$_CurlInstance = SWIFT_APIHttp::GetInstance();

		if (!$_isMulti) {
			$_responseArr = $_CurlInstance->SendSingedRequest($_url, $_body, 'POST');
		} else {
			$_CurlInstance->AddSingedRequest($_url, $_body, 'POST');

			return;
		}

		return $this->HandlePostMessageRes($_responseArr);
	}

	/**
	 * Handle reponse returned by PostMessage
	 *
	 * @author Atul Atri
	 * @param array $_responseArr array e.g. array(self::SRV_CODE => 'http_response_code', self::SRV_RESPONSE => 'response',
	 * 								self::SRV_ERROR_CODE => 'any error code', self::SRV_ERROR => 'any error string')
	 * @return String json response from service
	 * @throws SWIFT_API_Exception If curl request is failed or respose code is not 201
	 */
	public function HandlePostMessageRes($_responseArr)
	{
		$_code = $_responseArr[SWIFT_APIHttp::SRV_CODE];
		$_response = $_responseArr[SWIFT_APIHttp::SRV_RESPONSE];

		$_curlError = $_responseArr[SWIFT_APIHttp::SRV_ERROR];

		if ($_curlError) {
			throw new SWIFT_API_Exception($this->Language->Get('BC_ERR_INVALID_SETTINGS') . $_curlError);
		}

		if ($_code != 201) {
			throw new SWIFT_API_Exception($this->Language->Get('BC_ERR_MSG_POST_FAILED') . $_response, $_code);
		}

		return $_response;
	}

}

?>

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
 * Basecamp api client for management of attachments
 *
 * @author Atul Atri
 */
//SWIFT_Loader::LoadLibrary('API:APIBase');
//SWIFT_Loader::LoadLibrary('API:API_Exception');
//SWIFT_Loader::LoadLibrary('API:APIHttp');

class SWIFT_APIAttachments extends SWIFT_APIBase
{

	/**
	 * Constructor
	 *
	 * @author Atul Atri
	 *
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
	 *
	 * @return bool "true" on Success, "false" otherwise
	 */
	public function __destruct()
	{
		parent::__destruct();

		return true;
	}

	/**
	 * Upload a file to basecamp
	 *
	 * @author Atul Atri
	 *
	 * @param string $_filePath file that needs to be uploaded
	 * @param string $_contentType file content type
	 * @param bool $_isMulti true if this request is part of multiple requests
	 *
	 * @return String json response from service or true is $_isMulti was true
	 * @throws SWIFT_API_Exception If curl request is failed or respose code is not 201
	 */
	public function Upload($_filePath, $_contentType, $_isMulti = false)
	{
		$_uploadUrl = SWIFT_ConfigManager::Get('UPLOAD_SUB_URL');

		$_baseUrl = $this->BcApiUrl();
		$_url = $_baseUrl . $_uploadUrl;
		$_CurlInstance = SWIFT_APIHttp::GetInstance();

		//$_postArr[basename($_filePath)] = '@'.$_filePath.';type='.$_contentType;//file_get_contents($_filePath);
		$_postArr = "";
		$_headers = array();
		$_headers['Content-Type'] = $_contentType;
		$_reqOpts[CURLOPT_PUT] = 1;
		$_reqOpts[CURLOPT_INFILE] = fopen($_filePath, "rb");
		$_reqOpts[CURLOPT_INFILESIZE] = filesize($_filePath);

		if (!$_isMulti) {
			$_response = $_CurlInstance->SendSingedRequest($_url, $_postArr, 'POST', $_headers, $_reqOpts, SWIFT_APIHttp::HTTP_FORM_CONTENT_TYPE_MULTIPART);
		} else {
			$_CurlInstance->AddSingedRequest($_url, $_postArr, 'POST', $_headers, $_reqOpts, SWIFT_APIHttp::HTTP_FORM_CONTENT_TYPE_MULTIPART);

			return true;
		}

		return $this->HandleUploadRes($_response);
	}

	/**
	 * Hnadle response returned by PostComment
	 *
	 * @author Atul Atri
	 * @param string $_response json response string
	 *
	 * @return bool "true" on Success, "false" otherwise
	 *
	 * @throws SWIFT_API_Exception If curl request is failed or respose code is not 201
	 */
	public function HandleUploadRes($_response)
	{
		$_code = $_response[SWIFT_APIHttp::SRV_CODE];
		$_responseStr = $_response[SWIFT_APIHttp::SRV_RESPONSE];

		$_curlError = $_response[SWIFT_APIHttp::SRV_ERROR];

		if ($_curlError) {
			throw new SWIFT_API_Exception($this->Language->Get('BC_ERR_INVALID_SETTINGS') . $_curlError);
		}

		if ($_code != 200) {
			throw new SWIFT_API_Exception($this->Language->Get('BC_UPLOAD_ERR'), $_code);
		}

		return $_responseStr;
	}

}

?>

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
 * Basecamp api client for management of comments
 *
 * @author Atul Atri
 */
//SWIFT_Loader::LoadLibrary('API:APIBase');
//SWIFT_Loader::LoadLibrary('API:API_Exception');
//SWIFT_Loader::LoadLibrary('API:APIHttp');

class SWIFT_APIComments extends SWIFT_APIBase
{

	Const SECTION_MESSAGES = 'messages';
	Const SECTION_TODOS = 'todos';

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
	 * Post a comment
	 *
	 * @author Atul Atri
	 * @param int $_bcProjectId project id
	 * @param string  $_section  SWIFT_APIComments::SECTION_MESSAGES or SWIFT_APIComments::SECTION_TODOS
	 * @param int $_sectionId section id
	 * @param String $_content comment content
	 * @param bool $_isMulti true if this request is part of multiple requests
	 * @param array $_attachments attachment array e.g. array('name' => file name, 'token' => file token)
	 * @return String json response from service or true is $_isMulti was true
	 * @throws SWIFT_API_Exception If curl request is failed or respose code is not 201
	 */
	public function PostComment($_bcProjectId, $_section, $_sectionId, $_content, $_isMulti = false, array $_attachments = array())
	{
		$_commentPostUrl = SWIFT_ConfigManager::Get('COMMENT_POST_SUB_URL');
		$_commentPostUrl = sprintf($_commentPostUrl, $_bcProjectId, $_section, $_sectionId);

		$_baseUrl = $this->BcApiUrl();
		$_url = $_baseUrl . $_commentPostUrl;
		$_CurlInstance = SWIFT_APIHttp::GetInstance();

		$_postArr = array('content' => $_content);

		foreach ($_attachments as $_nextAttachment) {

			if (!empty($_nextAttachment['name']) && !empty($_nextAttachment['token'])) {
				$_tmp['name'] = $_nextAttachment['name'];
				$_tmp['token'] = $_nextAttachment['token'];
				$_postArr['attachments'][] = $_tmp;
			}
		}

		$_postStr = json_encode($_postArr);

		if (!$_isMulti) {
			$_response = $_CurlInstance->SendSingedRequest($_url, $_postStr, 'POST');
		} else {
			$_CurlInstance->AddSingedRequest($_url, $_postStr, 'GET');

			return true;
		}

		return $this->HandlePostCommentRes($_response);
	}

	/**
	 * Hnadle response returned by PostComment
	 *
	 * @author Atul Atri
	 * @param string $_response json response string
	 * @return bool "true" on Success, "false" otherwise
	 * @throws SWIFT_API_Exception If curl request is failed or respose code is not 201
	 */
	public function HandlePostCommentRes($_response)
	{
		$_code = $_response[SWIFT_APIHttp::SRV_CODE];
		$_responseStr = $_response[SWIFT_APIHttp::SRV_RESPONSE];

		$_curlError = $_response[SWIFT_APIHttp::SRV_ERROR];

		if ($_curlError) {
			throw new SWIFT_API_Exception($this->Language->Get('BC_ERR_INVALID_SETTINGS') . $_curlError);
		}

		if ($_code != 201) {
			throw new SWIFT_API_Exception($this->Language->Get('BC_TODO_COMMENT_ERR') . $_responseStr, $_code);
		}

		return $_responseStr;
	}
}
?>

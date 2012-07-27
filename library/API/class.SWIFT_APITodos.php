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
 * Basecamp api client for management of todo lists
 *
 * @author Atul Atri
 */
//SWIFT_Loader::LoadLibrary('API:APIBase');
//SWIFT_Loader::LoadLibrary('API:API_Exception');
//SWIFT_Loader::LoadLibrary('API:APIHttp');

class SWIFT_APITodos extends SWIFT_APIBase
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
	 * Post a to do item
	 *
	 * @author Atul Atri
	 * @param int $_bcProjectId project id
	 * @param int $_todolistId todolist id
	 * @param string $_content todo content
	 * @param int $_personId person id if todo is being assigned to some one
	 * @param string $_dueDate todo due date. Date format should be in ISO 8601 format (like "2012-03-27T16:00:00-05:00") otherwise posting will fail
	 * @param bool $_isMulti true if this request is part of multiple requests
	 * @return String json response from service or true is $_isMulti was true
	 * @throws SWIFT_API_Exception If curl request is failed or respose code is not 201
	 */
	public function PostTodo($_bcProjectId, $_todolistId, $_content, $_personId = null, $_dueDate = null, $_isMulti = false)
	{
		$_todoPostUrl = SWIFT_ConfigManager::Get('TODO_POST_SUB_URL');
		$_todoPostUrl = sprintf($_todoPostUrl, $_bcProjectId, $_todolistId);

		$_baseUrl = $this->BcApiUrl();
		$_url = $_baseUrl . $_todoPostUrl;
		$_CurlInstance = SWIFT_APIHttp::GetInstance();

		$_postArr = array('content' => $_content);

		if ($_dueDate) {
			$_postArr['due_at'] = $_dueDate;
		}

		if ($_personId) {
			$_postArr['assignee']['id'] = $_personId;
			$_postArr['assignee']['type'] = 'Person';
		}

		$_postStr = json_encode($_postArr);

		if (!$_isMulti) {
			$_responseArr = $_CurlInstance->SendSingedRequest($_url, $_postStr, 'POST');
		} else {
			$_CurlInstance->AddSingedRequest($_url, $_postStr, 'POST');

			return true;
		}

		return $this->HandlePostTodoRes($_responseArr);
	}

	/**
	 * Hnadle response returned by PostTodo
	 *
	 * @author Atul Atri
	 * @param string $_response json response string
	 * @return bool "true" on Success, "false" otherwise
	 * @throws SWIFT_API_Exception If curl request is failed or respose code is not 201
	 */
	public function HandlePostTodoRes($_response)
	{
		$_code = $_response[SWIFT_APIHttp::SRV_CODE];
		$_responseStr = $_response[SWIFT_APIHttp::SRV_RESPONSE];

		$_curlError = $_response[SWIFT_APIHttp::SRV_ERROR];

		if ($_curlError) {
			throw new SWIFT_API_Exception($this->Language->Get('BC_ERR_INVALID_SETTINGS') . $_curlError);
		}

		if ($_code != 201) {
			throw new SWIFT_API_Exception($this->Language->Get('BC_TODO_POST_ERR') . $_responseStr, $_code);
		}

		return $_responseStr;
	}

}

?>

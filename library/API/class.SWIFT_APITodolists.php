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

class SWIFT_APITodolists extends SWIFT_APIBase
{
	//wrap Todo list name to this length of chacacters in todo list
	Const TODO_MAX_LENGTH = 100;

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
	 * List todos in a basecamp project
	 *
	 * @author atul atri
	 * @param $_bcProjectId basecamp project id
	 * @param $_isMulti is request being made as part of multiple concurrent requests
	 * @return mixed returns json response from service or void if $_isMulti is true
	 * @throws SWIFT_API_Exception If curl request is failed or respose code is not 201
	 */
	public function GetTodolists($_bcProjectId, $_isMulti = false)
	{
		$_todoUrl = SWIFT_ConfigManager::Get('TODOLIST_LIST_SUB_URL');
		$_todoUrl = sprintf($_todoUrl, $_bcProjectId);

		$_baseUrl = $this->BcApiUrl();
		$_url = $_baseUrl . $_todoUrl;
		$_CurlInstance = SWIFT_APIHttp::GetInstance();

		if (!$_isMulti) {
			$_responseArr = $_CurlInstance->SendSingedRequest($_url, null, 'GET');
		} else {
			$_responseArr = $_CurlInstance->AddSingedRequest($_url, null, 'GET');

			return;
		}

		return $this->HandleGetTodolistsRes($_responseArr);
	}

	/**
	 * Handle reponse returned by GetTodolists
	 *
	 * @author Atul Atri
	 * @param array $_responseArr array e.g. array(self::SRV_CODE => 'http_response_code', self::SRV_RESPONSE => 'response',
	 * 								self::SRV_ERROR_CODE => 'any error code', self::SRV_ERROR => 'any error string')
	 * @return String json response from service
	 * @throws SWIFT_API_Exception If curl request is failed or respose code is not 201
	 */
	public function HandleGetTodolistsRes($_responseArr)
	{
		$_code = $_responseArr[SWIFT_APIHttp::SRV_CODE];
		$_response = $_responseArr[SWIFT_APIHttp::SRV_RESPONSE];

		$_curlError = $_responseArr[SWIFT_APIHttp::SRV_ERROR];

		if ($_curlError) {
			throw new SWIFT_API_Exception($this->Language->Get('BC_ERR_INVALID_SETTINGS') . $_curlError);
		}

		if ($_code != 200) {
			throw new SWIFT_API_Exception($this->Language->Get('BC_TODO_LIST_ERR') . $_response, $_code);
		}

		return $_response;
	}

	/**
	 * Returns a list of projects that can be used to render select box
	 *
	 * @author Atul Atri
	 * @param String $_res json people list
	 * @param  int  $_idToSelect todo list id to be selected in options list
	 * @return array list of options
	 */
	public function GetTodolistSelectList($_res, $_idToSelect = null)
	{
		$_todoListArr = json_decode($_res, true);
		$_todolistsOpts = array();

		foreach ($_todoListArr as $_nextList) {
			$_newP['value'] = intval($_nextList['id']);
			$_pName = $_nextList['name'];

			if (strlen($_pName) > self::TODO_MAX_LENGTH) {
				$_pName = wordwrap($_pName, self::TODO_MAX_LENGTH, "...\n", false);
				$_pName = substr($_pName, 0, strpos($_pName, "\n"));
			}

			$_newP['title'] = $_pName;

			if ($_idToSelect == $_newP['value']) {
				$_newP['selected'] = true;
			}

			$_todolistsOpts[] = $_newP;
		}

		return $_todolistsOpts;
	}

}

?>

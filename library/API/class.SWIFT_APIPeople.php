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
 * Basecamp api client for management of people working on basecamp projects
 *
 * @author Atul Atri
 */
//SWIFT_Loader::LoadLibrary('API:APIBase');
//SWIFT_Loader::LoadLibrary('API:API_Exception');
//SWIFT_Loader::LoadLibrary('API:APIHttp');

class SWIFT_APIPeople extends SWIFT_APIBase
{
	//wrap people name to this length of chacacters in beasecamp people list

	Const PEOPLE_MAX_LENGTH = 100;

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
	 * Return list of people working on some project
	 *
	 * @author atul atri
	 * @param $_isMulti is request being made as part of multiple concurrent requests
	 * @return mixed returns json response from service or void if $_isMulti is true
	 * @throws SWIFT_API_Exception If curl request is failed or respose code is not 201
	 */
	public function GetPeople($_isMulti = false)
	{
		$_peopleUrl = SWIFT_ConfigManager::Get("PEOPLE_LIST_SUB_URL");

		$_baseUrl = $this->BcApiUrl();
		$_url = $_baseUrl . $_peopleUrl;
		$_CurlInstance = SWIFT_APIHttp::GetInstance();

		if (!$_isMulti) {
			$_responseArr = $_CurlInstance->SendSingedRequest($_url, null, 'GET');
		} else {
			$_responseArr = $_CurlInstance->AddSingedRequest($_url, null, 'GET');

			return;
		}

		return $this->HandleGetPeopleRes($_responseArr);
	}

	/**
	 * Handle reponse returned by GetPeople
	 *
	 * @author Atul Atri
	 * @param array $_responseArr array e.g. array(self::SRV_CODE => 'http_response_code', self::SRV_RESPONSE => 'response',
	 * 								self::SRV_ERROR_CODE => 'any error code', self::SRV_ERROR => 'any error string')
	 * @return String json response from service
	 * @throws SWIFT_API_Exception If curl request is failed or respose code is not 201
	 */
	public function HandleGetPeopleRes($_responseArr)
	{
		$_code = $_responseArr[SWIFT_APIHttp::SRV_CODE];
		$_response = $_responseArr[SWIFT_APIHttp::SRV_RESPONSE];

		$_curlError = $_responseArr[SWIFT_APIHttp::SRV_ERROR];

		if ($_curlError) {
			throw new SWIFT_API_Exception($this->Language->Get('BC_ERR_INVALID_SETTINGS') . $_curlError);
		}

		if ($_code != 200) {
			throw new SWIFT_API_Exception($this->Language->Get('BC_PEOPLE_LIST_ERR') . $_response, $_code);
		}

		return $_response;
	}

	/**
	 * Returns a list of projects that can be used to render select box
	 *
	 * @author Atul Atri
	 * @param String json people list
	 * @param  int  $_pIdToSelect people id to be selected in options list
	 * @return array list of options
	 */
	public function GetPeopleSelectList($_res, $_pIdToSelect = null)
	{
		$_peopleArr = json_decode($_res, true);
		$_peopleOpts = array();

		foreach ($_peopleArr as $_person) {
			$_newP['value'] = intval($_person['id']);
			$_pName = $_person['name'];

			if (strlen($_pName) > self::PEOPLE_MAX_LENGTH) {
				$_pName = wordwrap($_pName, self::PROJECT_MAX_LENGTH, "...\n", false);
				$_pName = substr($_pName, 0, strpos($_pName, "\n"));
			}

			$_newP['title'] = $_pName;

			if ($_pIdToSelect == $_newP['value']) {
				$_newP['selected'] = true;
			}

			$_peopleOpts[] = $_newP;
		}

		return $_peopleOpts;
	}

}

?>

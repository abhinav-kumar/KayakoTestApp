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
 * Basecamp api client for basecamp projects managemnet
 *
 * @author Atul Atri
 */
//SWIFT_Loader::LoadLibrary('API:APIBase');
//SWIFT_Loader::LoadLibrary('API:API_Exception');
//SWIFT_Loader::LoadLibrary('API:APIHttp');

class SWIFT_APIProject extends SWIFT_APIBase
{
	//wrap project name to this length of chacacters in beasecamp project list
	Const PROJECT_MAX_LENGTH = 100;

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
	 * Get list of projects on basecamp
	 *
	 * @author atul atri
	 * @param $_isMulti is request being made as part of multiple concurrent requests
	 * @return mixed returns json response from service or void if $_isMulti is true
	 * @throws SWIFT_API_Exception If curl request is failed or respose code is not 201
	 */
	public function GetProjects($_isMulti = false)
	{
		$_projectsUrl = SWIFT_ConfigManager::Get('PROJECTS_LIST_SUB_URL');

		$_baseUrl = $this->BcApiUrl();
		$_url = $_baseUrl . $_projectsUrl;
		$_CurlInstance = SWIFT_APIHttp::GetInstance();

		if (!$_isMulti) {
			$_responseArr = $_CurlInstance->SendSingedRequest($_url, null, 'GET');
		} else {
			$_responseArr = $_CurlInstance->AddSingedRequest($_url, null, 'GET');

			return;
		}

		return $this->HandleGetProjectsRes($_responseArr);
	}

	/**
	 * Handle reponse returned by GetProjects
	 *
	 * @author Atul Atri
	 * @param array $_responseArr array e.g. array(self::SRV_CODE => 'http_response_code', self::SRV_RESPONSE => 'response',
	 * 								self::SRV_ERROR_CODE => 'any error code', self::SRV_ERROR => 'any error string')
	 * @return String json response from service
	 * @throws SWIFT_API_Exception If curl request is failed or respose code is not 201
	 */
	public function HandleGetProjectsRes($_responseArr)
	{
		$_code = $_responseArr[SWIFT_APIHttp::SRV_CODE];
		$_response = $_responseArr[SWIFT_APIHttp::SRV_RESPONSE];

		$_curlError = $_responseArr[SWIFT_APIHttp::SRV_ERROR];

		if ($_curlError) {
			throw new SWIFT_API_Exception($this->Language->Get('BC_ERR_INVALID_SETTINGS') . $_curlError);
		}

		if ($_code != 200) {
			throw new SWIFT_API_Exception($this->Language->Get('BC_PROJECT_LIST_ERR'), $_code);
		}

		return $_response;
	}

	/**
	 * Returns a list of projects that can be used to render select box
	 *
	 * @author Atul Atri
	 * @param int $_pIdToSelect project Id to be selected
	 * @param string $_response json response from basecamp project service, if not provided it will get reponse from basecamp service
	 * @return array list of optiosn
	 */
	public function GetProjectSelectList($_pIdToSelect = null, $_response = null)
	{
		$_projects = $_response;

		if (!$_projects) {
			$_projects = $this->GetProjects();
		}

		$_projectsArr = json_decode($_projects, true);
		$_projetcsOpts = array();

		if (count($_projectsArr) > 0) {

			foreach ($_projectsArr as $_newProjetcs) {
				$_newP['value'] = intval($_newProjetcs['id']);
				$_pName = $_newProjetcs['name'];

				if (strlen($_pName) > self::PROJECT_MAX_LENGTH) {
					$_pName = wordwrap($_newProjetcs['name'], self::PROJECT_MAX_LENGTH, "...\n", false);
					$_pName = substr($_pName, 0, strpos($_pName, "\n"));
				}

				$_newP['title'] = $_pName;

				if ($_pIdToSelect == $_newP['value']) {
					$_newP['selected'] = true;
				}

				$_projetcsOpts[] = $_newP;
			}
		}

		return $_projetcsOpts;
	}
}

?>

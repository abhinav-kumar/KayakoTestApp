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
 * Get information about users authorised account
 *
 * @author Atul Atri
 */
//SWIFT_Loader::LoadLibrary('API:APIBase');
//SWIFT_Loader::LoadLibrary('API:API_Exception');
//SWIFT_Loader::LoadLibrary('API:APIHttp');

class SWIFT_APIAuthorization extends SWIFT_APIBase
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
	 * return Authorization information
	 *
	 * @author atul atri
	 * @return mixed returns json response from service
	 * @throws SWIFT_API_Exception If curl request is failed or respose code is not 200
	 */
	public function getAuthorizationInfo()
	{
		$_url = SWIFT_ConfigManager::Get('AUTH_URL');

		$_CurlInstance = SWIFT_APIHttp::GetInstance();
		$_responseArr = $_CurlInstance->SendSingedRequest($_url, null, 'GET');

		$_code = $_responseArr[SWIFT_APIHttp::SRV_CODE];
		$_response = $_responseArr[SWIFT_APIHttp::SRV_RESPONSE];

		$_curlError = $_responseArr[SWIFT_APIHttp::SRV_ERROR];

		if ($_curlError) {
			throw new SWIFT_API_Exception($this->Language->Get('BC_ERR_INVALID_SETTINGS') . $_curlError);
		}

		if ($_code != 200) {
			throw new SWIFT_API_Exception($this->Language->Get('BC_AUTHORIZATION_ERR'), $_code);
		}

		return $_response;
	}

	/**
	 * return basecamp account ids for authorized user
	 *
	 * @author atul atri
	 * @return array basecamp account ids for authorized user
	 * @throws SWIFT_API_Exception If curl request is failed or respose code is not 200
	 */
	public function getBasecampAccountsIds()
	{
		$_infoJsonStr = $this->getAuthorizationInfo();
		$_infoArr = json_decode($_infoJsonStr, true);
		$_baseCampAccounts = array();

		if (isset($_infoArr['accounts']) && isset($_infoArr['accounts'])) {
			$_accountsArr = $_infoArr['accounts'];

			foreach ($_accountsArr as $_nextAccount) {

				if ($_nextAccount['product'] == 'bcx') {
					$_baseCampAccounts[] = $_nextAccount['id'];
				}
			}
		}

		return $_baseCampAccounts;
	}

}

?>

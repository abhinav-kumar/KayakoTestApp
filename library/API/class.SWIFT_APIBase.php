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
 * This is the base class to be extendibale by all basecamp apis. This class will
 * provide abstact functionality that can be used by all basecamp api calls.
 *
 * @author Atul Atri
 */
class SWIFT_APIBase extends SWIFT_Library
{

	//used count the req retry count
	private $_reqRetryCount = 0;
	//max req retires
	private $_maxSendReqRetries = 1;

	/**
	 * Constructor
	 *
	 * @author Atul Atri
	 *
	 * @return bool "true" on Success, "false" otherwise
	 * @throws SWIFT_API_Exception invalid settings found
	 */
	public function __construct()
	{
		parent::__construct();

		//$this->Load->Library('Settings:SettingsManager');
		//$this->Language->Load('basecamp');

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
	 * Gives basecamp base api url
	 *
	 * @author Atul Atri
	 *
	 * @return string basecamp base api url
	 */
	protected function BcApiUrl()
	{
		$_baseUrl = SWIFT_ConfigManager::get('BC_BASE_URL');
		$_accountId = $this->Settings->Get('bc_base_acc_id');
		$_subApiUrl = SWIFT_ConfigManager::get('API_SUB_URL');

		return $_baseUrl . $_accountId . $_subApiUrl;
	}

}

?>

<?php

/**
 * =======================================
 * ###################################
 * Basecamp App
 *
 * @package	Basecamp
 * @author	Kayako Infotech Ltd.
 * @copyright	Copyright (c) 2001-2009, Kayako Infotech Ltd.
 * @license	         http://www.kayako.com/license
 * @link		http://www.kayako.com
 * @filesource
 * ###################################
 * =======================================
 */

/**
 * Manage module configuration
 *
 * @author Atul Atri
 */
class SWIFT_ConfigManager extends SWIFT_Library
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
	 * Get config value for some key
	 *
	 * @author Atul Atri
	 *
	 * @param string $_key configuration key
	 * @param string $_app App name if null is provided current module is used
	 *
	 * @return String value for given key
	 *
	 * @throws SWIFT_Config_Exception if $_app could not be located or
	 *						     if configuration does not exists or
	 *						     if Constant $_key does not exist in configuration file
	 */
	public static function Get($_key, $_app = null)
	{
		$_configurationFile = null;
		$_appObject = null;
		$_SWIFT = SWIFT::GetInstance();

		if (!$_app) {
			$_appObject = $_SWIFT->Router->GetApp();
		} else {
			$_appObject = new SWIFT_App($_app);
		}

		if (!$_appObject) {
			throw new SWIFT_Config_Exception($_app . ' not found');
		}

		$_className = "SWIFT_Config_".$_appObject->GetName();

		if (!class_exists($_className)) {
			$_configurationFile = $_appObject->GetDirectory().DIRECTORY_SEPARATOR.SWIFT_APP::DIRECTORY_CONFIG.DIRECTORY_SEPARATOR."class.".$_className . ".php";

			if (!file_exists($_configurationFile)) {
				throw new SWIFT_Config_Exception($_app . ' : configuration file does not exist.');
			}

			require_once $_configurationFile;

			if (!class_exists($_className)) {
				throw new SWIFT_Config_Exception("$_app : Could not locate  $_className");
			}
		}

		if(!defined("$_className::$_key")){
			throw new SWIFT_Config_Exception("No constant $_key defined in $_className");
		}

		return constant("$_className::$_key");
	}

}
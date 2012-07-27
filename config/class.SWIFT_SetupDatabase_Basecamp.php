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
 * @link		http://www.kayako.com
 * @filesource
 * ###################################
 * =======================================
 */

/**
 * The Basecamp Installer
 *
 * @author Atul Atri
 */
class SWIFT_SetupDatabase_Basecamp extends SWIFT_SetupDatabase
{

	/**
	 * Constructor
	 *
	 * @author Atul Atri
	 * @return bool "true" on Success, "false" otherwise
	 */
	public function __construct()
	{
		parent::__construct('basecamp');

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
	 * Function used to install
	 *
	 * @author Atul Atri
	 * @param int $_pageIndex The Page Index
	 * @return bool "true" on Success, "false" otherwise
	 */
	public function Install($_pageIndex)
	{
		parent::Install($_pageIndex);

		return true;
	}

	/**
	 * Uninstalls the App
	 *
	 * @author Atul Atri
	 * @return bool "true" on Success, "false" otherwise
	 */
	public function Uninstall()
	{
		parent::Uninstall();

		return true;
	}

	/**
	 * Upgrade the App
	 *
	 * @author Atul Atri
	 * @param bool $_isForced
	 * @return bool "true" on Success, "false" otherwise
	 */
	public function Upgrade($_isForced = false)
	{
		parent::Upgrade($_isForced);

		return true;
	}

}

?>
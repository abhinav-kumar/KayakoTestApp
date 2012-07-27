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
 * The OAuth2 exceptions
 *
 * @author Atul Atri
 */
class SWIFT_OAuth2Exception extends SWIFT_Exception
{

	/**
	 * Constructor
	 *
	 * @author Atul Atri
	 * @param string $_errorMessage The Error Message
	 * @param int $_errorCode The Error Code
	 * @return bool "true" on Success, "false" otherwise
	 */
	public function __construct($_errorMessage, $_errorCode = 0)
	{
		parent::__construct($_errorMessage, $_errorCode);

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

}

?>
<?php

/**
 * =======================================
 * ###################################
 * SWIFT Framework
 *
 * @package	SWIFT
 * @author	Kayako Infotech Ltd.
 * @copyright	Copyright (c) 2001-2009, Kayako Infotech Ltd.
 * @license	http://www.kayako.com/license
 * @link	http://www.kayako.com
 * @filesource
 * ###################################
 * =======================================
 */
/**
 * Description of OAuth2CodeGrant code grant
 *
 * @author atul atri
 */
SWIFT_Loader::LoadLibrary('OAuth2:OAUTH2GrantTypeBase');
SWIFT_Loader::LoadLibrary('OAuth2:OAuth2Exception');

class SWIFT_OAuth2ClientGrant extends SWIFT_OAUTH2GrantTypeBase
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
	 * @return bool "true" on Success, "false" otherwise
	 */
	public function __destruct()
	{
		parent::__destruct();

		return true;
	}

	/**
	 * Generate a Authorization Request url
	 *
	 * @param array $_params list of parameters required to build Url
	 * @param array $_params array('response_type' => 'response_type', 'client_id' => 'client_id',
	  'redirect_uri'=>'redirect_uri', 'scope' => 'scope', 'state' => 'state'), only client_id and end_point is required
	 *
	 * @return string Authorization Request url
	 * @throws SWIFT_OAuth2Exception if failed to create url
	 */
	public function GetAuthReqUrl($_endPointURL, array $_params)
	{
		throw new SWIFT_OAuth2Exception('Client Credentials Grant  grant type does not support Authorization Request');
	}

	/**
	 * check  if required parametes are given and modify if necessary
	 *
	 * @author Atul Atri
	 * @param array $_params array of parameters
	 *
	 * @return array $_params modified parameters
	 * @throws SWIFT_OAuth2Exception if invlaid parameters are given
	 */
	protected function CheckTokenRequestParams(array $_params)
	{
		if (!isset($_params['grant_type'])) {
			$_params['grant_type'] = 'client_credentials';
		}

		return $_params;
	}

}

?>

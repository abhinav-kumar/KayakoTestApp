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

class SWIFT_OAuth2CodeGrant extends SWIFT_OAUTH2GrantTypeBase
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
	 * check if required parametes are given
	 *
	 * @author Atul Atri
	 * @param array $_params array of parameters
	 *
	 * @return bool "true" on if all parameters are ok
	 * @throws SWIFT_OAuth2Exception if invlaid parameters are given
	 */
	protected function CheckAuthRequestParams(array $_params)
	{
		if (!isset($_params['client_id'])) {
			throw new SWIFT_OAuth2Exception('client_id is required for Authorization Code Grant type');
		}
		if (!isset($_params['response_type'])) {

			$_params['response_type'] = 'code';
		}

		return $_params;
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
		if (!isset($_params['code'])) {
			throw new SWIFT_OAuth2Exception('Authorization code is required to execute token request query');
		}
		if (!isset($_params['grant_type'])) {
			$_params['grant_type'] = 'authorization_code';
		}

		return $_params;
	}

}

?>

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

class SWIFT_OAuth2TokenGrant extends SWIFT_OAUTH2GrantTypeBase
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
			$_params['response_type'] = 'token';
		}

		return $_params;
	}

	/**
	 * Execute access token request and return response retuned by server
	 *
	 * @param string $_tokenEndPoint token end point url
	 * @param array $_params list of parameters required to execute request
	 * @param int $_clientAuthType client authentication type
	 * @param array any additional header that needs to be sent e.g. array('Conent-Type' =>'application/json');
	 * @param string method method type e.g. 'GET', 'POST', 'DELETE', 'PUT'
	 *
	 * @return mixed response retuned by server
	 * @throws SWIFT_OAuth2Exception if failed to execute request
	 */
	public function AccessTokenRequest($_tokenEndPoint, array $_params, $_clientAuthType = self::AUTH_TYPE_AUTHORIZATION_FORM, array $_headers = array(), $_method = 'POST')
	{
		throw new SWIFT_OAuth2Exception('Token(implicit) grant type does not support Authentication Token Requests');
	}

}

?>

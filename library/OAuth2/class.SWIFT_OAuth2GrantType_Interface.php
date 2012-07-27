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
 * Represents a OAuth2 grant type
 *
 * @author Atul Atri
 */
interface SWIFT_OAuth2GrantType_Interface
{
	//Token server authorization types

	Const AUTH_TYPE_AUTHORIZATION_BASIC = 1;
	Const AUTH_TYPE_AUTHORIZATION_FORM = 2;
	Const AUTH_TYPE_AUTHORIZATION_NONE = 3;

	/**
	 * Get  Authorization Request url
	 *
	 * @param array $_params list of parameters required to build Url
	 * @param string $_endPointURL end point url without trailing slash
	 *
	 * @return string Authorization Request url
	 * @throws SWIFT_OAuth2Exception if failed to create url
	 */
	public function GetAuthReqUrl($_endPointURL, array $_params);

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
	public function AccessTokenRequest($_tokenEndPoint, array $_params, $_clientAuthType = self::AUTH_TYPE_AUTHORIZATION_FORM, array $_headers = array(), $_method = 'POST');

	/**
	 * Modify standard  parameter names
	 *
	 *  @param array $_params list of parameters e.g. array('standard_param_name'=>'modified_param_name')
	 *
	 * @return void
	 */
	public function SetParameterNames(array $_params);
}

?>

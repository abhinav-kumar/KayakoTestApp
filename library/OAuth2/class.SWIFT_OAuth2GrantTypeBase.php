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
 * Base class for oauth2 grant type
 *
 * @author Atul Atri
 */
SWIFT_Loader::LoadInterface('OAuth2:OAuth2GrantType');
SWIFT_Loader::LoadLibrary('OAuth2:OAuth2Exception');
SWIFT_Loader::LoadLibrary('HTTP:HttpCurl');

class SWIFT_OAUTH2GrantTypeBase extends SWIFT_Library implements SWIFT_OAuth2GrantType_Interface
{

	protected $_paramNames = array();

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
		$_params = $this->CheckTokenRequestParams($_params);

		if (($_clientAuthType == self::AUTH_TYPE_AUTHORIZATION_FORM || $_clientAuthType == self::AUTH_TYPE_AUTHORIZATION_BASIC)
				&& (!isset($_params['client_id']) || !isset($_params['client_secret']))) {

			throw new SWIFT_OAuth2Exception('AUTH_TYPE_AUTHORIZATION_FORM requires client_id and client_secret to be given in params');
		}

		if ($_clientAuthType == self::AUTH_TYPE_AUTHORIZATION_BASIC && !isset($_headers['Authorization'])) {
			$_headers['Authorization'] = 'Basic ' . base64_encode($_params['client_id'] . ':' . $_params['client_secret']);
		}

		$_params = $this->ChangeParamNames($_params);

		//execute request
		$_CurlInstance = SWIFT_HttpCurl::GetInstance();
		$_paramsStr = http_build_query($_params);

		$_defaultHeaders['Content-Type'] = 'application/x-www-form-urlencoded';
		$_headers = $_headers + $_defaultHeaders;

		return $_CurlInstance->SendRequest($_tokenEndPoint, $_paramsStr, $_method, $_headers);
	}

	/**
	 * Change parameter names
	 *
	 * @author Atul  Atri
	 * @param array $_params parameters array
	 *
	 * @return array modified parameters
	 */
	protected function ChangeParamNames(array $_params)
	{
		$_newArr = $_params;
		foreach ($_params as $_paramName => $_paramValue) {

			if (isset($this->_paramNames[$_paramName])) {
				unset($_newArr[$_paramName]);
				$_newArr[$this->_paramNames[$_paramName]] = $_paramValue;
			}
		}

		return $_newArr;
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
		$_params = $this->CheckAuthRequestParams($_params);

		//change parameter names
		$_params = $this->ChangeParamNames($_params);

		//constuct and return url
		return $_endPointURL . '?' . http_build_query($_params, null, '&');
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
	protected function CheckAuthRequestParams(array $_params)
	{
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
		return $_params;
	}

	/**
	 * Modify standard  parameter names
	 *
	 *  @param array $_params list of parameters e.g. array('standard_param_name'=>'modified_param_name')
	 *
	 * @return void
	 */
	public function SetParameterNames(array $_params)
	{
		$this->_paramNames = $this->_paramNames + $_params;
	}

}

?>

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
 * Simple OAuth2 client
 *
 * @author Atul Atri
 */
//SWIFT_Loader::LoadInterface('OAuth2:OAuth2GrantType');

class SWIFT_OAuth2Client extends SWIFT_Library
{
	//grant token types
	const RESPONSE_TYPE_CODE = 'code';
	const RESPONSE_TYPE_TOKEN = 'token';

	// grant types
	const GRANT_TYPE_AUTH_CODE = 'authorization_code';
	const GRANT_TYPE_PASSWORD = 'password';
	const GRANT_TYPE_CLIENT_CREDENTIALS = 'client_credentials';
	const GRANT_TYPE_CLIENT_REFRESH_TOKEN = 'refresh_token';

	// grant type class object
	protected $_grantClassObject = null;

	// client id
	protected $_clientId = null;

	// client secret
	protected $_clientSecret = null;

	// redirect url
	protected $_redirectURL = null;

	// url to get request authorization code
	protected $_reqAuthEndPoint = null;

	// url to get authentication token
	protected $_authTokenEndPoint = null;

	// scope
	protected $_scope = null;

	/**
	 * Constructor
	 *
	 * @author Atul Atri
	 *
	 * @param SWIFT_OAuth2GrantType_Interface $_grantClassObject grant type object
	 * @param string $_clientId client id
	 * @param string $_clientSecret client secret
	 * @param string $_redirectURL redirect url
	 * @param string request authorization end point
	 * @param string authentication end point
	 * @param string scop
	 *
	 * @return bool "true" on Success, "false" otherwise
	 */
	public function __construct($_grantClassObject, $_clientId, $_clientSecret, $_redirectURL, $_reqAuthEndPoint, $_authTokenEndPoint, $_scope = null)
	{
		$this->_grantClassObject = $_grantClassObject;
		$this->_clientId = $_clientId;
		$this->_clientSecret = $_clientSecret;
		$this->_redirectURL = $_redirectURL;
		$this->_reqAuthEndPoint = $_reqAuthEndPoint;
		$this->_authTokenEndPoint = $_authTokenEndPoint;
		$this->_scope = $_scope;

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
	 * Get grant class object
	 *
	 * @return object
	 */
	public function GetGrantClassObject()
	{
		return $this->_grantClassObject;
	}

	/**
	 * Set grant type
	 *
	 * @param SWIFT_OAuth2GrantType_Interface $_grantClassObject
	 * @return SWIFT_OAuth2Client this object
	 */
	public function SetGrantClassObject(SWIFT_OAuth2IGrantType_Interface $_grantClassObject)
	{
		$this->_grantClassObject = $_grantClassObject;

		return $this;
	}

	/**
	 * Get Client Id
	 *
	 * @return string client id
	 */
	public function GetClientId()
	{
		return $this->_clientId;
	}

	/**
	 * Set client id
	 *
	 * @param string $_clientId client Id
	 * @return SWIFT_OAuth2Client this object
	 */
	public function SetClientId($_clientId)
	{
		$this->_clientId = $_clientId;

		return $this;
	}

	/**
	 * Get Client secret
	 *
	 * @return string client secret
	 */
	public function GetClientSecret()
	{
		return $this->_clientSecret;
	}

	/**
	 * Set client secret
	 *
	 * @param string $_clientSecret client secret
	 * @return SWIFT_OAuth2Client this object
	 */
	public function SetClientSecret($_clientSecret)
	{
		$this->_clientSecret = $_clientSecret;

		return $this;
	}

	/**
	 * Get Redirect URL
	 *
	 * @return string redirect url
	 */
	public function GetRedirectURL()
	{
		return $this->_redirectURL;
	}

	/**
	 * Redirect URL
	 *
	 * @param string $_redirectURL
	 *
	 * @return SWIFT_OAuth2Client this object
	 */
	public function SetRedirectURL($_redirectURL)
	{
		$this->_redirectURL = $_redirectURL;

		return $this;
	}

	/**
	 * Get Request Authentication end point
	 *
	 * @return string Request Authentication end point
	 */
	public function GetReqAuthEndPoint()
	{
		return $this->_reqAuthEndPoint;
	}

	/**
	 * Set Request Authentication end point
	 *
	 * @param string $_reqAuthEndPoint Request Authentication end point
	 *
	 * @return SWIFT_OAuth2Client this object
	 */
	public function SetReqAuthEndPoint($_reqAuthEndPoint)
	{
		$this->_reqAuthEndPoint = $_reqAuthEndPoint;

		return $this;
	}

	/**
	 * Get Authentication token url
	 *
	 * @return string Authentication token url
	 */
	public function GetAuthTokenEndPoint()
	{
		return $this->_authTokenEndPoint;
	}

	/**
	 * Get Authorization Request URL for
	 *
	 * @author Atul Atri
	 *
	 * @param string $_resposeType grant response type
	 * @param string $_state state
	 * @param array $_extraParams addititional parameters, extra parameters overrids default parameters
	 *
	 * @return string Authorization Request URL
	 */
	public function GetAuthReqUrl($_resposeType = self::GRANT_RESPONSE_TYPE_CODE, $_state = null, array $_extraParams = array())
	{
		$_params['response_type'] = $_resposeType;
		$_params['client_id'] = $this->_clientId;

		if (isset($this->_redirectURL)) {
			$_params['redirect_uri'] = $this->_redirectURL;
		}

		if (isset($this->_scope)) {
			$_params['scope'] = $this->_scope;
		}

		if ($_state) {
			$_params['state'] = $_state;
		}

		$_params = $_extraParams + $_params;

		return $this->_grantClassObject->GetAuthReqUrl($this->_reqAuthEndPoint, $_params);
	}

	/**
	 * Get Access Token for Authorization Code Grant
	 *
	 * @author Atul Atri
	 *
	 * @param string $_code code as returned by authorization request
	 * @param int $_clientAuthType client authentication types
	 * @param array $_extraParams addititional parameters, extra parameters overrids default parameters
	 * @param array $_headers extra headers
	 * @param string method type 'GET', 'POST', 'DELETE', 'PUT'
	 *
	 * @return mixed response retuned by server
	 */
	public function GetCodeGrantAccessToken($_code, array $_extraParams = array(), $_clientAuthType = SWIFT_OAuth2GrantType_Interface::AUTH_TYPE_AUTHORIZATION_FORM, array $_headers = array(), $_method = 'POST')
	{
		$_params['grant_type'] = self::GRANT_TYPE_AUTH_CODE;
		$_params['code'] = $_code;

		if (isset($this->_redirectURL)) {
			$_params['redirect_uri'] = $this->_redirectURL;
		}

		$_authParams = $this->GetAuthParams($_clientAuthType);
		$_params = $_extraParams + $_params + $_authParams;

		return $this->_grantClassObject->AccessTokenRequest($this->_authTokenEndPoint, $_params, $_clientAuthType, $_headers, $_method);
	}

	/**
	 * Get Access Token for Authorization Code Grant
	 *
	 * @author Atul Atri
	 *
	 * @param string $_userName user name
	 * @param string $_userName password
	 * @param int $_clientAuthType client authentication types
	 * @param array $_extraParams addititional parameters, extra parameters overrids default parameters
	 * @param array $_headers extra headers
	 * @param string method type 'GET', 'POST', 'DELETE', 'PUT'
	 *
	 * @return mixed response retuned by server
	 */
	public function GetPasswordGrantAccessToken($_userName, $_password, array $_extraParams = array(), $_clientAuthType = SWIFT_OAuth2GrantType_Interface::AUTH_TYPE_AUTHORIZATION_FORM, array $_headers = array(), $_method = 'POST')
	{
		$_params['grant_type'] = self::GRANT_TYPE_PASSWORD;
		$_params['username'] = $_userName;
		$_params['password'] = $_password;

		if ($this->_scope) {
			$_params['scope'] = $this->_scope;
		}

		$_authParams = $this->GetAuthParams($_clientAuthType);
		$_params = $_extraParams + $_params + $_authParams;

		return $this->_grantClassObject->AccessTokenRequest($this->_authTokenEndPoint, $_params, $_clientAuthType, $_headers, $_method);
	}

	/**
	 * Get Access Token for  Client Credentials Grant
	 *
	 * @author Atul Atri
	 *
	 * @param int $_clientAuthType client authentication types
	 * @param array $_extraParams addititional parameters, extra parameters overrids default parameters
	 * @param array $_headers extra headers
	 * @param string method type 'GET', 'POST', 'DELETE', 'PUT'
	 *
	 * @return mixed response retuned by server
	 */
	public function GetClientGrantAccessToken(array $_extraParams = array(), $_clientAuthType = SWIFT_OAuth2GrantType_Interface::AUTH_TYPE_AUTHORIZATION_FORM, array $_headers = array(), $_method = 'POST')
	{
		$_params['grant_type'] = self::GRANT_TYPE_CLIENT_CREDENTIALS;

		$_authParams = $this->GetAuthParams($_clientAuthType);
		$_params = $_extraParams + $_params + $_authParams;

		return $this->_grantClassObject->AccessTokenRequest($this->_authTokenEndPoint, $_params, $_clientAuthType, $_headers, $_method);
	}

	/**
	 * Refresh Token
	 *
	 * @author Atul Atri
	 *
	 * @param string $_refreshToken refresh token
	 * @param int $_clientAuthType client authentication types
	 * @param array $_extraParams addititional parameters, extra parameters overrids default parameters
	 * @param array $_headers extra headers
	 * @param string method type 'GET', 'POST', 'DELETE', 'PUT'
	 *
	 * @return mixed response retuned by server
	 */
	public function GetRefreshToken($_refreshToken, array $_extraParams = array(), $_clientAuthType = SWIFT_OAuth2GrantType_Interface::AUTH_TYPE_AUTHORIZATION_FORM, array $_headers = array(), $_method = 'POST')
	{
		$_params['grant_type'] = self::GRANT_TYPE_CLIENT_REFRESH_TOKEN;
		$_params['refresh_token'] = $_refreshToken;

		$_authParams = $this->GetAuthParams($_clientAuthType);
		$_params = $_extraParams + $_params + $_authParams;

		return $this->_grantClassObject->AccessTokenRequest($this->_authTokenEndPoint, $_params, $_clientAuthType, $_headers, $_method);
	}

	/**
	 *
	 *
	 * @author Varun Shoor
	 * @param string $_PARAM PARAMDESC
	 * @return bool "true" on Success, "false" otherwise
	 * @throws SWIFT_Exception If the Class is not Loaded
	 */
	private function GetAuthParams($_clientAuthType)
	{
		$_returnMe = array();
		if ($_clientAuthType == SWIFT_OAuth2GrantType_Interface::AUTH_TYPE_AUTHORIZATION_FORM) {
			$_returnMe['client_id'] = $this->_clientId;
			$_returnMe['client_secret'] = $this->_clientSecret;
		}

		return $_returnMe;
	}

}

?>

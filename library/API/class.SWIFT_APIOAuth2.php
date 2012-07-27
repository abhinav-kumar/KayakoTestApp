<?php

/**
 * =======================================
 * ###################################
 * SWIFT Framework
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
 * Basecamp OAuth2
 *
 * @author Atul Atri
 */

//SWIFT_Loader::LoadLibrary('OAuth2:OAuth2Client');
//SWIFT_Loader::LoadLibrary('OAuth2:OAuth2CodeGrant');
//SWIFT_Loader::LoadLibrary('OAuth2:OAuth2RefreshTokenGrant');
//SWIFT_Loader::LoadLibrary('API:API_Exception');

class SWIFT_APIOauth2  extends SWIFT_Library
{

	/**
	 * Get authentication request url
	 *
	 * @return string authentication request url
	 */
	public static function GetAuthReqURL()
	{
		$_grantClassObject = new SWIFT_OAuth2CodeGrant();
		$_grantClassObject->SetParameterNames(array('response_type' => 'type'));

		$_OAuth2Client = self::GetOAuthClient($_grantClassObject);

		return $_OAuth2Client->GetAuthReqUrl('web_server');
	}

	/**
	 *  Create client for OAuth
	 * @param SWIFT_OAuth2GrantType_Interface $_grantClassObject
	 * @return \SWIFT_OAuth2Client
	 */
	private static function GetOAuthClient($_grantClassObject)
	{
		$_SWIFT = SWIFT::GetInstance();
		$_clientId = $_SWIFT->Settings->Get('bc_app_id');
		$_clientSecret = $_SWIFT->Settings->Get('bc_app_secret');
		$_redirectURL = $_SWIFT->Settings->Get('bc_app_redirect_url');
		$_reqAuthEndPoint = SWIFT_ConfigManager::Get('AUTH_URL_NEW');
		$_authTokenEndPoint = SWIFT_ConfigManager::Get('AUTH_TOKEN_URL');
		$_OAuth2Client = new SWIFT_OAuth2Client($_grantClassObject, $_clientId, $_clientSecret, $_redirectURL, $_reqAuthEndPoint, $_authTokenEndPoint);

		return $_OAuth2Client;
	}


	/**
	 * Check if OAuth2 request url returns 3XX
	 * @param string $_clientId
	 * @param string $_clientSecret
	 * @param string $_redirectURL
	 * @return bool true if   if OAuth2 request url returns 200 Ok, false otherwise
	 */
	public static function CheckAuthReqURL($_clientId, $_clientSecret, $_redirectURL)
	{
		$_SWIFT = SWIFT::GetInstance();

		$_grantClassObject = new SWIFT_OAuth2CodeGrant();
		$_grantClassObject->SetParameterNames(array('response_type' => 'type'));
		$_reqAuthEndPoint = SWIFT_ConfigManager::Get('AUTH_URL_NEW');
		$_authTokenEndPoint = SWIFT_ConfigManager::Get('AUTH_TOKEN_URL');
		$_OAuth2Client = new SWIFT_OAuth2Client($_grantClassObject, $_clientId, $_clientSecret, $_redirectURL, $_reqAuthEndPoint, $_authTokenEndPoint);
		$_url =  $_OAuth2Client->GetAuthReqUrl('web_server');

		$_httpInstance = SWIFT_APIHttp::GetInstance();
		$_options = array(CURLOPT_FOLLOWLOCATION => false);
		$_responseArr = $_httpInstance->SendRequest($_url, null, 'GET', array(), $_options);

		$_code = $_responseArr[SWIFT_APIHttp::SRV_CODE];
		$_codeSeries = intval($_code / 100);

		if($_codeSeries == 3){
			return true;
		}

		return false;
	}

	/**
	 * Get Authentication token
	 *
	 * @param string $_authCode Authentication code
	 *
	 * @return array as array(SWIFT_HttpCurl::SRV_CODE => 'http_response_code', SWIFT_HttpCurl::SRV_RESPONSE => 'response',
	 *					SWIFT_HttpCurl::SRV_ERROR_CODE => 'any error code', SWIFT_HttpCurl::SRV_ERROR => 'any error string')
	 *
	 * @thows exception if error is response
	 */
	public static function GetToken($_authCode)
	{
		$_grantClassObject = new SWIFT_OAuth2CodeGrant();
		$_grantClassObject->SetParameterNames(array('grant_type' => 'type'));

		$_OAuth2Client = self::GetOAuthClient($_grantClassObject);

		$_extraParams = array('grant_type' => 'web_server');

		$_responseArr =  $_OAuth2Client->GetCodeGrantAccessToken($_authCode, $_extraParams, SWIFT_OAuth2GrantType_Interface::AUTH_TYPE_AUTHORIZATION_FORM);

		$_code = $_responseArr[SWIFT_APIHttp::SRV_CODE];
		$_response = $_responseArr[SWIFT_APIHttp::SRV_RESPONSE];
		$_curlError = $_responseArr[SWIFT_APIHttp::SRV_ERROR];
		if ($_curlError) {
			throw new SWIFT_API_Exception($this->Language->Get('BC_ERR_INVALID_SETTINGS') . $_curlError);
		}

		if ($_code != 200) {
			throw new SWIFT_API_Exception($this->Language->Get('BC_GET_TOKEN_ERROR'), $_code);
		}
		$_jsonRes = json_decode($_response, true);
		$_accessToken = $_jsonRes['access_token'];
		$_refreshToken = $_jsonRes['refresh_token'];

		$_SWIFT = SWIFT::GetInstance();

		$_issetAcessToken = $_SWIFT->Settings->UpdateKey('settings', 'bc_auth_token', "Bearer $_accessToken");
		$_issetRefreshToken = $_SWIFT->Settings->UpdateKey('settings', 'bc_refresh_token', $_refreshToken);

		return $_issetAcessToken && $_issetRefreshToken;
	}

	/**
	 * Get Authentication token
	 *
	 * @param string $_refreshToken Refresh Token
	 *
	 * @return string json response return by user
	 */
	public static function RefreshToken()
	{
		$_grantClassObject = new SWIFT_OAuth2RefreshTokenGrant();
		$_grantClassObject->SetParameterNames(array('grant_type' => 'type'));

		$_OAuth2Client = self::GetOAuthClient($_grantClassObject);

		$_extraParams = array('grant_type' => 'refresh');

		try{
			$_SWIFT = SWIFT::GetInstance();

			$_refreshToken = $_SWIFT->Settings->Get("bc_refresh_token");
			$_responseArr =  $_OAuth2Client->GetRefreshToken($_refreshToken, $_extraParams, SWIFT_OAuth2GrantType_Interface::AUTH_TYPE_AUTHORIZATION_FORM);

			if($_responseArr[SWIFT_APIHttp::SRV_CODE] == 200){
				$_responseJson = $_responseArr[SWIFT_APIHttp::SRV_RESPONSE];
				$_responseJsonArr = json_decode($_responseJson, true);

				if(isset($_responseJsonArr['access_token'])){
					$_token = trim($_responseJsonArr['access_token']);
					//save token in settings
					$_isset = $_SWIFT->Settings->UpdateKey('settings', 'bc_auth_token', "Bearer $_token");

					return $_isset;
				}
			}
		}  catch (Exception $_e){
			//do nothing
		}

		return false;
	}

}

?>

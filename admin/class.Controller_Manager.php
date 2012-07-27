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
 * @link	http://www.kayako.com
 * @filesource
 * ###################################
 * =======================================
 */
/**
 * This controller to manage basecamp integration
 *
 * @author Atul Atri
 */

class Controller_Manager extends Controller_admin
{

	const MENU_ID = 111;
	const NAV_ID = 1;

	/**
	 * Constructor
	 *
	 * @author Atul Atri
	 * @return bool "true" on Success, "false" otherwise
	 */
	public function __construct()
	{
		parent::__construct();

		$this->Language->Load('basecamp');

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
	 * Render manage form
	 *
	 * @author Atul Atri
	 * @return bool "true" on Success, "false" otherwise
	 */
	public function CodeSubmit()
	{
		if (isset($_POST['basecampcode']) && !empty($_POST['basecampcode'])) {
			$this->HandleCodeSubmit();
		} else {
			//handle new application info
			$this->HandleNewAppSubmit();
		}

		$this->Load->ManageForm();

		return true;
	}

	/**
	 * Handle new application submit
	 *
	 * @author Atul Ati
	 * @return bool "true" on Success, "false" otherwise
	 */
	private function HandleNewAppSubmit()
	{
		if ($this->CheckNewAppSubmit()) {
			$_SWIFT = SWIFT::GetInstance();
			$_redirectUrl = SWIFT::Get('basename') . '/basecamp/Manager/CodeRedirect';

			//check information
			$_isValid = false;
			$_isValid = SWIFT_APIOauth2::CheckAuthReqURL(trim($_POST['bc_app_id']), trim($_POST['bc_app_secret']), $_redirectUrl);

			if ($_isValid == false) {
				$this->UserInterface->Error($this->Language->Get('error_title'), $this->Language->Get('wrong_app_info'));
			} else {
				$_SWIFT->Settings->UpdateKey('settings', 'bc_user_app', 1);
				$_SWIFT->Settings->UpdateKey('settings', 'bc_app_name', trim($_POST['bc_app_name']));
				$_SWIFT->Settings->UpdateKey('settings', 'bc_email', trim($_POST['bc_app_email']));
				$_SWIFT->Settings->UpdateKey('settings', 'bc_app_id', trim($_POST['bc_app_id']));
				$_SWIFT->Settings->UpdateKey('settings', 'bc_app_secret', trim($_POST['bc_app_secret']));
				$_SWIFT->Settings->UpdateKey('settings', 'bc_app_redirect_url', $_redirectUrl);
				//delete old authentication tokens
				$_SWIFT->Settings->UpdateKey('settings', 'bc_base_acc_id', '');
				$_SWIFT->Settings->UpdateKey('settings', 'bc_auth_token', '');
				$_SWIFT->Settings->UpdateKey('settings', 'bc_refresh_token', '');

				$this->Template->Assign('_newAppSaved', true);

				$this->UserInterface->DisplayInfo($this->Language->Get('success'), $this->Language->Get('new_app_saved_success'));
			}
		}

		return true;
	}

	/**
	 * Checks new application submit
	 *
	 * @author Atul Atri
	 * @return bool "true" on Success, "false" otherwise
	 */
	private function CheckNewAppSubmit()
	{
		$_SWIFT = SWIFT::GetInstance();

		if (!SWIFT_Session::CheckCSRFHash($_POST['csrfhash'])) {
			SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

			return false;
		}

		$_appName = trim($_POST['bc_app_name']);
		$_appEmail = trim($_POST['bc_app_email']);
		$_appId = trim($_POST['bc_app_id']);
		$_appSecret = trim($_POST['bc_app_secret']);

		if (empty($_appName)) {
			$this->UserInterface->CheckFields('bc_app_name');
			$this->UserInterface->Error($this->Language->Get('error_title'), $this->Language->Get('empty_bc_app_name'));

			return false;
		}

		if (empty($_appEmail)) {
			$this->UserInterface->CheckFields('bc_app_email');
			$this->UserInterface->Error($this->Language->Get('error_title'), $this->Language->Get('empty_bc_app_email'));

			return false;
		}

		if (!IsEmailValid($_appEmail)) {
			$this->UserInterface->CheckFields('bc_app_email');
			$this->UserInterface->Error($this->Language->Get('error_title'), $this->Language->Get('invalid_bc_app_email'));

			return false;
		}

		if (empty($_appId)) {
			$this->UserInterface->CheckFields('bc_app_id');
			$this->UserInterface->Error($this->Language->Get('error_title'), $this->Language->Get('empty_bc_app_id'));

			return false;
		}

		if (empty($_appSecret)) {
			$this->UserInterface->CheckFields('bc_app_secret');
			$this->UserInterface->Error($this->Language->Get('error_title'), $this->Language->Get('empty_bc_app_secret'));

			return false;
		}

		return true;
	}

	/**
	 * Handle code submit
	 *
	 * @author Atul Ati
	 * @return bool "true" on Success, "false" otherwise
	 */
	private function HandleCodeSubmit()
	{
		$_SWIFT = SWIFT::GetInstance();

		if ($this->CheckCodeSubmit()) {

			try {
				SWIFT_APIOauth2::GetToken($_POST['basecampcode']);

				$_APIAuthorization = new SWIFT_APIAuthorization();

				$_bascampAccounts = $_APIAuthorization->getBasecampAccountsIds();
				$_accountsCounts = count($_bascampAccounts);

				if ($_accountsCounts == 0) {
					throw new Exception($this->Language->Get('no_account'));
				} else if ($_accountsCounts > 1) {
					$this->UserInterface->DisplayAlert($this->Language->Get('warning'), $this->Language->Get('multiple_accounts'));
				}

				$_accountId = $_bascampAccounts[0];

				$_SWIFT->Settings->UpdateKey('settings', 'bc_base_acc_id', $_accountId);
				$this->Template->Assign('_authTokenSuccess', true);

				$this->UserInterface->DisplayInfo($this->Language->Get('success'), $this->Language->Get('get_token_success'));
			} catch (Exception $_e) {
				$_error = $_e->getMessage() . '[' . $_e->getCode() . ']';
				$this->UserInterface->Error($this->Language->Get('error_title'), $_error);
			}
		}

		return true;
	}

	/**
	 * Checks todo export form data
	 *
	 * @author Atul Atri
	 * @return bool "true" on Success, "false" otherwise
	 */
	private function CheckCodeSubmit()
	{
		$_SWIFT = SWIFT::GetInstance();

		if (!SWIFT_Session::CheckCSRFHash($_POST['csrfhash'])) {
			SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

			return false;
		}

		if (empty($_POST['basecampcode'])) {
			return false;
		}

		return true;
	}

	/**
	 * Render manage form
	 *
	 * @author Atul Atri
	 *
	 * @return bool "true" on Success, "false" otherwise
	 */
	public function ManageForm()
	{
		//check if already autherised
		$_SWIFT = SWIFT::GetInstance();

		$_authHeader = $_SWIFT->Settings->Get('bc_auth_token');

		if ($_authHeader) {
			$this->Template->Assign('_alreadyAuthorised', true);
		} else {
			$this->Template->Assign('_alreadyAuthorised', false);
		}

		$_authLink = SWIFT_APIOauth2::GetAuthReqURL();

		$this->Template->Assign('_authLink', $_authLink);
		$this->Template->Assign('_settingsObj', $_SWIFT->Settings);
		$this->Template->Assign('_swiftpath', SWIFT::Get('swiftpath'));
		$this->Template->Assign('_Language', $this->Language);
		$this->Template->Assign('_UserInterface', $this->UserInterface);

		$_formTxt = '';
		$_redirectUrl = SWIFT::Get('basename') . '/basecamp/Manager/CodeRedirect';
		$this->Template->Assign('_redirectUrl', $_redirectUrl);

		//show only if user registered this application
		$_appName = '';
		$_appEmail = '';
		$_appId = '';
		$_appSecret = '';

		if ($this->Settings->Get('bc_user_app')) {
			$_formTxt = $this->Language->Get('user_app_txt');
			$_formTxt = sprintf($_formTxt, '<b>' . $_redirectUrl . '</b>');
			$_buttonTxt = 'update_button';

			$_appName = $this->Settings->Get('bc_app_name');
			$_appEmail = $this->Settings->Get('bc_email');
			$_appId = $this->Settings->Get('bc_app_id');
			$_appSecret = $this->Settings->Get('bc_app_secret');
		} else {
			$_formTxt = $this->Language->Get('system_app_txt');
			$_bcCreatAppUrl = SWIFT_ConfigManager::Get('CREATE_APP_LNK');
			$_clickHere = $this->Language->Get('click_here_lnk');
			$_bcCreateAppHref = "<b><a href='$_bcCreatAppUrl' target='_blank'>$_clickHere</a></b>";
			$_formTxt = sprintf($_formTxt, $_bcCreateAppHref, '<b>' . $_redirectUrl . '</b>');
			$_buttonTxt = 'save_button';
		}

		$this->Template->Assign('_formTxt', $_formTxt);
		$this->Template->Assign('_buttonTxt', $_buttonTxt);
		$this->View->Assign('_buttonTxt', $_buttonTxt);

		$this->View->Assign('_appName', $_appName);
		$this->View->Assign('_appEmail', $_appEmail);
		$this->View->Assign('_appId', $_appId);
		$this->View->Assign('_appSecret', $_appSecret);

		$this->View->ManageForm(self::MENU_ID, self::NAV_ID);

		return true;
	}

	/**
	 * Code redirect page
	 *
	 * @author Atul Atri
	 * @return bool "true" on Success, "false" otherwise
	 */
	public function CodeRedirect()
	{
		if (isset($_GET['error'])) {
			$this->Template->Assign('_error', $_GET['error']);
		}

		if (isset($_GET['code'])) {
			$this->Template->Assign('_code', $_GET['code']);
		}

		$this->View->RenderTplFile(self::MENU_ID, self::NAV_ID, $this->Language->Get('manage_basecamp'), '', false);
	}

}
?>

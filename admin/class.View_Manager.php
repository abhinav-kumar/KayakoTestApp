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
 * Handle view for manager controller
 *
 * @author Atul Atri
 */

class View_Manager extends SWIFT_GeneralViewBase
{

	/**
	 * Constructor
	 *
	 * @author Atul Atri
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
	 * view for manage form action
	 *
	 * @author Atul Atri
	 * @return boolean true
	 */
	public function ManageForm($_menuId = 1, $_navId = 1, $_documentTitle = '', $_customNavHtml = '', $_includeTemplate = true, $_scritpPath = '')
	{
		$this->UserInterface->Start('basecamp_manager', '/basecamp/Manager/CodeSubmit', SWIFT_UserInterface::MODE_EDIT);
		$_GeneralTabObject = $this->UserInterface->AddTab($this->Language->Get('tab_general'), 'icon_form.gif', 'basecamp_tab_auth', true);
		$this->UserInterface->Toolbar->AddButton($this->Language->Get($this->Get('_buttonTxt')), 'icon_check.gif',  "$('#basecampcode').val('');javascript:this.blur(); TabLoading('basecamp_manager', 'basecamp_tab_auth'); $('#basecamp_managerform').submit();", SWIFT_UserInterfaceToolbar::LINK_JAVASCRIPT, 'basecamp_managerform_submit');

		$_GeneralTabObject->Text('bc_app_name', $this->Language->Get('bc_app_name'), '', $this->Get('_appName'), 'text', 60);
		$_GeneralTabObject->Text('bc_app_email', $this->Language->Get('bc_email'), $this->Language->Get('d_bc_email'), $this->Get('_appEmail'), 'text', 60);
		$_GeneralTabObject->Text('bc_app_id', $this->Language->Get('bc_app_id'), '', $this->Get('_appId'), 'text', 60);
		$_GeneralTabObject->Text('bc_app_secret', $this->Language->Get('bc_app_secret'), '', $this->Get('_appSecret'), 'text', 60);

		$this->UserInterface->Hidden('basecampcode', '');

		ob_start();
		$this->RenderTplFile($_menuId, $_navId, $this->Language->Get('manage_basecamp'));
		$_html = ob_get_contents();
		ob_end_clean();

		$_GeneralTabObject->AppendHTML($_html);

		$this->UserInterface->End();

		return true;
	}
}
?>

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
 * View class for todo manager
 *
 * @author Atul Atri
 */
class View_TodoManager extends SWIFT_View
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
		$this->Load->LoadModel('Ticket:Ticket', 'tickets');
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
	 * Render the Action forms
	 *
	 * @author Atul Atri
	 * @param array $_projectsOpts basecamp project list
	 * @param array $_todoProjectList list of todo lists on basecamp in selected project
	 * @param array $_personList list of persons
	 * @param array $_ticketID ticket tid
	 * @param String $_errorTitle Error title if some error needs to be rended
	 * @param String $_errorMsg Error Message if some error needs to be rended
	 * @return bool "true" on Success, "false" otherwise
	 */
	public function RenderTodoExportForm($_projectsOpts, $_todoProjectList, $_personList, $_ticketID, $_errorTitle = '', $_errorMsg = '')
	{
		$this->UserInterface->Header($this->Language->Get('basecamp') . ' > ' . $this->Language->Get('todoexportform'));

		$this->UserInterface->Start(get_class($this), '/basecamp/TodoManager/TodoExportFormSubmit', SWIFT_UserInterface::MODE_EDIT, false, false, false, false, 'todoExportFormResHolder', "SWIFT.Basecamp.AdminObject.RestoreTodoExportForm");
		$this->UserInterface->Toolbar->AddButton($this->Language->Get('export'));

		$_ActionTab = $this->UserInterface->AddTab($this->Language->Get('tab_export'), 'icon_form.gif', 'action', true);

		if ($_errorMsg) {
			if (!$_errorTitle) {
				$_errorTitle = $this->Language->Get('error');
			}

			$_ActionTab->Error($_errorTitle, $_errorMsg, 'bc_todo_list_error');
		}

		$_ActionTab->Select('todoproject', $this->Language->Get('todoproject'), $this->Language->Get('d_todoproject'), $_projectsOpts);
		$_ActionTab->Select('todolist', $this->Language->Get('todlist'), $this->Language->Get('d_todlist'), $_todoProjectList);
		$_ActionTab->Select('assignee', $this->Language->Get('assignee'), $this->Language->Get('d_assignee'), $_personList);
		$_ActionTab->date('duedate', $this->Language->Get('duedate'), $this->Language->Get('d_duedate'));

		$_SWIFT_TicketObject = SWIFT_Ticket::GetObjectOnID($_ticketID);

		$_ticketSub = trim($_SWIFT_TicketObject->subject);

		$_todoItemStr = '[' . $_SWIFT_TicketObject->ticketmaskid . '] ' . $_ticketSub;

		$_ActionTab->Text('todoitem', $this->Language->Get('todoitem_name'), '', $_todoItemStr, 'text', 60);

		if ($_SWIFT_TicketObject->GetProperty('hasattachments')) {
			$_attachments = $_SWIFT_TicketObject->GetAttachmentContainer();
			$_checkBoxContainer = array();

			foreach ($_attachments as $_tmpArr) {

				foreach ($_tmpArr as $_nextAttachment) {
					$_attachementId = $_nextAttachment['attachmentid'];
					$_attachementSize = round($_nextAttachment['filesize'] / 1024, 2);
					$_tmp['value'] = $_attachementId;
					$_tmp['title'] = $_nextAttachment['filename'] . " ($_attachementSize KB)";
					$_checkBoxContainer[$_attachementId] = $_tmp;
				}
			}

			$_ActionTab->CheckBoxList('todo_files', $this->Language->Get('todo_files'), $this->Language->Get('d_todo_files'), $_checkBoxContainer);
		}

		$_ticketPostContainer = $_SWIFT_TicketObject->GetTicketPosts();
		$_ticketPosts = array();

		foreach ($_ticketPostContainer as $_TicketPost) {
			$_creater = ucfirst($_TicketPost->GetProperty('fullname'));
			$_postedOn = SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_TicketPost->GetProperty('dateline'));

			$_subStr = '';

			if ($_TicketPost->GetProperty('creator') == SWIFT_Ticket::CREATOR_STAFF) {
				$_staffDataStore = $this->Staff->GetDataStore();
				$_subStr .= '[' . $_staffDataStore['grouptitle'] . "] $_creater " . $this->Language->Get('bc_wrote_on') . " $_postedOn";
			} else if ($_TicketPost->GetProperty('creator') == SWIFT_Ticket::CREATOR_USER) {
				$_subStr .= '[' . $this->Language->Get('bc_user') . "] $_creater " . $this->Language->Get('bc_wrote_on') . " $_postedOn";
			}


			$_postContent = trim($_TicketPost->GetDisplayContents());
			$_subStrLen = strlen($_subStr);
			$_ticketPosts[] = $_subStr . PHP_EOL . str_repeat('-', $_subStrLen) . PHP_EOL . strip_tags($_postContent);
		}

		$_firstComment = implode(PHP_EOL . PHP_EOL . PHP_EOL, $_ticketPosts);
		$_ActionTab->TextArea('todocomment', $this->Language->Get('todocomment'), $this->Language->Get('d_todocomment'), $_firstComment, 30, 20);
		$this->UserInterface->Hidden('todo_ticketid', $_ticketID);

		$this->UserInterface->End();
		$this->UserInterface->Footer();
	}

	/**
	 * Render the result of AjaxTodoList
	 *
	 * @author Atul Atri
	 *
	 * @param array $_todoOpts todo options list
	 * @param String $_error Error Message if some error needs to be rended
	 *
	 * @return bool "true" on Success, "false" otherwise
	 */
	public function AjaxTodoListView($_todoOpts, $_error)
	{
		$_errorTitle = $this->Language->Get('error');
		$_errorHtml = '';

		if ($_error) {
			$_errorHtml = $this->UserInterface->GetError($_errorTitle, $_error, 'bc_todo_list_error', true);
		}

		$_resArr = array('error' => $_errorHtml, 'todoOptions' => $_todoOpts);

		echo json_encode($_resArr);
	}

	/**
	 * Displayes a messages that todo has been posted
	 *
	 * @author Atul atri
	 *
	 * @return void
	 */
	public function TodoPostSuccess()
	{
		echo "todosuccess";
	}

}

?>

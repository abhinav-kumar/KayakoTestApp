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
 * This controller to exporting of  tickets to todo list in basecamp
 *
 * @author Atul Atri
 */
class Controller_TodoManager extends Controller_Staff
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

		$this->Language->Load('basecamp');
		$this->Load->Library('API:APIProject', false, false);
		$this->Load->Library('API:APIPeople', false, false);
		$this->Load->Library('API:APITodolists', false, false);
		$this->Load->Library('API:APIHttp', false, false);

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
	 * Renders todo export form
	 *
	 * @author Atul Atri
	 *
	 * @param int $_ticketId ticket id
	 * @param int $_bcProjectId selected basecamp project id if null is given first project is selected
	 *
	 * @return bool "true" on Success, "false" otherwise
	 */
	public function TodoExportForm($_ticketId = null, $_bcProjectId = null)
	{
		$_error = '';

		$_projetcsOpts = array(array('value' => 0, 'title' => $this->Language->Get('select')));
		$_todoOpts = array(array('value' => 0, 'title' => $this->Language->Get('select')));
		$_peopleOpts = array(array('value' => 0, 'title' => $this->Language->Get('select')));

		//check if we have authorization token if not, user has not integrated to basecamp
		$_SWIFT = SWIFT::GetInstance();
		$_authToken = $_SWIFT->Settings->Get("bc_auth_token");

		if(!$_authToken){
			$_error = $this->Language->Get('notintegrated');
			$this->View->RenderTodoExportForm($_projetcsOpts, $_todoOpts, $_peopleOpts, $_ticketId, '', $_error);

			return true;
		}


		$_CurlInstance = SWIFT_APIHttp::GetInstance();
		$_CurlInstance->InitMultiCurl();
		$_ProjectsSrv = new SWIFT_APIProject();
		$_ProjectsSrv->GetProjects(true);
		$_PeopleSrv = new SWIFT_APIPeople();
		$_PeopleSrv->GetPeople(true);

		if($_bcProjectId){
			//if project id is given also select list of projects
			$_TodolistsSrv = new SWIFT_APITodolists();
			$_TodolistsSrv->GetTodolists($_bcProjectId, true);
		}

		$_responses = $_CurlInstance->ExecuteMultiCurl();

		try {
			$_projetcsRes = $_ProjectsSrv->HandleGetProjectsRes($_responses[0]);
			$_projetcsOptsTemp = $_ProjectsSrv->GetProjectSelectList($_bcProjectId, $_projetcsRes);

			if (count($_projetcsOptsTemp) == 0) {
				$_error = $this->Language->Get('nobcproject');
			} else {
				$_projetcsOpts = array_merge($_peopleOpts, $_projetcsOptsTemp);

				$_peoples = $_PeopleSrv->HandleGetPeopleRes($_responses[1]);
				$_peopleOptsTemp = $_PeopleSrv->GetPeopleSelectList($_peoples);
				$_peopleOpts = array_merge($_peopleOpts, $_peopleOptsTemp);

				if ($_bcProjectId){
					try {
						//its ok if failed to retive todo list, user can change project in ui and todo list will get loaded
						$_todoListsRes = $_TodolistsSrv->HandleGetTodolistsRes($_responses[2]);
						$_todoOptsTemp = $_TodolistsSrv->GetTodolistSelectList($_todoListsRes);
						$_todoOpts = array_merge($_todoOpts, $_todoOptsTemp);
					} catch (Exception $_e) {
						//ignorre
					}
				}
			}
		} catch (Exception $_e) {
			$_error = $_e->getMessage();
		}

		$_CurlInstance->EndMultiCurl();

		if ($_error) {
			$this->View->RenderTodoExportForm($_projetcsOpts, $_todoOpts, $_peopleOpts, $_ticketId, '', $_error);

			return true;
		}

		$this->View->RenderTodoExportForm($_projetcsOpts, $_todoOpts, $_peopleOpts, $_ticketId, '', $_error);

		return true;
	}

	/**
	 * retuns json response for ajax query fro todo list
	 *
	 * @author Atul Atri
	 * @param int $_bcProjectId selected basecamp project id if null is given first project is selected
	 * @return bool "true" on Success, "false" otherwise
	 */
	public function AjaxTodoList($_bcProjectId)
	{
		$_TodolistsSrv = new SWIFT_APITodolists();
		$_error = '';
		$_todoOpts = array(array('value' => 0, 'title' => $this->Language->Get('select')));

		try {
			$_response = $_TodolistsSrv->GetTodolists($_bcProjectId);
			$_todoOptsTemp = $_TodolistsSrv->GetTodolistSelectList($_response);

			if (count($_todoOptsTemp) == 0) {
				$_error = $this->Language->Get('notolist');
			} else {
				$_todoOpts = array_merge($_todoOpts, $_todoOptsTemp);
			}
		} catch (Exception $_e) {
			$_error = $_e->getMessage();
		}

		$this->View->AjaxTodoListView($_todoOpts, $_error);

		return true;
	}

	/**
	 * Handle todo export form submit
	 *
	 * @author Atul Atri
	 * @return bool "true" on Success, "false" otherwise
	 */
	public function TodoExportFormSubmit()
	{
		$_pId = $_POST['todoproject'];
		$_todolistId = $_POST['todolist'];
		$_assigneeId = $_POST['assignee'];
		$_todo = trim($_POST['todoitem']);
		$_comment = trim($_POST['todocomment']);
		$_ticketId = $_POST['todo_ticketid'];
		$_date = trim($_POST['duedate']);
		$_fileNames = null;

		//if this ticket id is already linked throw exception
		$_todoId = SWIFT_TodoTicketLink::getTodoInfo($_ticketId);

		if($_todoId !== false){
			throw new SWIFT_Exception("TIcket id $_ticketId is already linked to basecamp todo.");
		}

		if(isset($_POST['todo_files'])){
			$_fileNames = $_POST['todo_files'];
		}

		if ($this->CheckTodoExportForm()) {
			$this->Load->Library('API:APITodos', false, false);
			$this->Load->Library('API:APIComments', false, false);

			$_TodoService = new SWIFT_APITodos();

			try{

				if(!empty($_date)){
					$_date = date("c", strtotime($_date));
				}

				$_reponse = $_TodoService->PostTodo($_pId, $_todolistId, $_todo, $_assigneeId, $_date);
				$_reponseArr = json_decode($_reponse, true);
				$_newTodoId = $_reponseArr['id'];

				$_SWIFT_TicketObject = SWIFT_Ticket::GetObjectOnID($_ticketId);

				if(!empty($_comment) || !empty($_fileNames)){
					$_CommentsSrv = new SWIFT_APIComments();

					if(empty($_comment)){
						$_comment = "";
					}

					$_filesToBeUploaded = array();

					if (!empty($_fileNames)) {
						$_attachments = $_SWIFT_TicketObject->GetAttachmentContainer();

						foreach ($_attachments as $_tmpArr) {

							foreach ($_tmpArr as $_nextAttachment) {
								$_fileId = $_nextAttachment['attachmentid'];

								if (in_array($_fileId, $_fileNames)) {
									$_tmp['name'] = $_nextAttachment['filename'];
									$_tmp['path'] = SWIFT_BASEPATH.'/'.SWIFT_BASEDIRECTORY.'/'.SWIFT_FILESDIRECTORY.'/'.$_nextAttachment['storefilename'];
									$_tmp['type'] = $_nextAttachment['filetype'];
									$_fileId = $_nextAttachment['attachmentid'];
									$_filesToBeUploaded[$_fileId] = $_tmp;
								}
							}
						}

						if(count($_filesToBeUploaded) > 0){
							$_CurlInstance = SWIFT_APIHttp::GetInstance();
							$_CurlInstance->InitMultiCurl();
							$_count = 0;

							foreach($_filesToBeUploaded as &$_nextAttachment){
								$this->Load->Library('API:APIAttachments', false, false);
								$_AttachmentsSrv = new SWIFT_APIAttachments();
								$_AttachmentsSrv->Upload($_nextAttachment['path'], $_nextAttachment['type'], true);
								$_nextAttachment['service'] = $_AttachmentsSrv;
								$_nextAttachment['multiCurlIndex'] = $_count;
								$_count++;
							}

							$_responses = $_CurlInstance->ExecuteMultiCurl();
							$_CurlInstance->EndMultiCurl();

							foreach($_filesToBeUploaded as &$_nextAttachment){
								$_srv = $_nextAttachment['service'];
								$_multiCurlIndex = $_nextAttachment['multiCurlIndex'];
								$_res = $_srv->HandleUploadRes($_responses[$_multiCurlIndex]);
								$_resArr = json_decode($_res, true);
								$_nextAttachment['token'] = $_resArr['token'];
							}
						}
					}

					$_CommentsSrv->PostComment($_pId, SWIFT_APIComments::SECTION_TODOS, $_newTodoId, $_comment, false, $_filesToBeUploaded);
				}

				$this->Load->LoadModel('AuditLog:TicketAuditLog');
				SWIFT_TicketAuditLog::AddToLog($_SWIFT_TicketObject, null, SWIFT_TicketAuditLog::ACTION_UPDATESTATUS, $this->Language->Get('audit_todo_posted'), SWIFT_TicketAuditLog::VALUE_NONE, 0, '', 0, '');

				//all well make link in table
				$_dataArray = array();
				$_dataArray['ticketid'] = $_ticketId;
				$_dataArray['todoid'] = $_newTodoId;
				$_dataArray['projectid'] = $_newTodoId;
				$this->Database->AutoExecute(TABLE_PREFIX.'basecamptodoticketlinks', $_dataArray);

				//return success message
				$this->View->TodoPostSuccess();

				return true;
			} catch (Exception $_e) {
				$_error = $_e->getMessage();
				$_SWIFT = SWIFT::GetInstance();
				SWIFT::Error($_SWIFT->Language->Get('error'), $_error);
			}
		}

		$this->Load->TodoExportForm($_ticketId, $_pId);

		return true;
	}

	/**
	 * Checks todo export form data
	 *
	 * @author Atul Atri
	 * @return bool "true" on Success, "false" otherwise
	 */
	private function CheckTodoExportForm()
	{
		$_SWIFT = SWIFT::GetInstance();

		if (!SWIFT_Session::CheckCSRFHash($_POST['csrfhash'])) {
			SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

			return false;
		}

		$_pId = $_POST['todoproject'];
		$_todolistId = $_POST['todolist'];
		$_todo = trim($_POST['todoitem']);
		$_date = trim($_POST['duedate']);

		if (empty($_pId)) {
			$this->UserInterface->CheckFields('todoproject');
			$this->UserInterface->Error($this->Language->Get('error_title'), $this->Language->Get('empty_todoproject'));

			return false;
		}

		if (empty($_todolistId)) {
			$this->UserInterface->CheckFields('todlist');
			$this->UserInterface->Error($this->Language->Get('error_title'), $this->Language->Get('empty_todolist'));

			return false;
		}

		if (empty($_todo)) {
			$this->UserInterface->CheckFields('todoitem');
			$this->UserInterface->Error($this->Language->Get('error_title'), $this->Language->Get('empty_todo'));

			return false;
		}

		if(!empty($_date) && (date('m/d/Y', strtotime($_date)) != $_date)){
			$this->UserInterface->CheckFields('duedate');
			$this->UserInterface->Error($this->Language->Get('error_title'), $this->Language->Get('empty_duedate'));

			return false;
		}

		return true;
	}

}

?>
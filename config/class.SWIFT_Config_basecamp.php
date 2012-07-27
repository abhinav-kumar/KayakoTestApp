<?php
/**
 * =======================================
 * ###################################
 * Basecamp App
 *
 * @package	Basecamp
 * @author	Kayako Infotech Ltd.
 * @copyright	Copyright (c) 2001-2009, Kayako Infotech Ltd.
 * @license	         http://www.kayako.com/license
 * @link		http://www.kayako.com
 * @filesource
 * ###################################
 * =======================================
 */

/**
 * Basecamp configutaion file
 *
 * @author Atul Atri
 */
class SWIFT_Config_basecamp
{
	//Basecamp(37signals.com) Request Authorization URL
	const AUTH_URL_NEW = "https://launchpad.37signals.com/authorization/new";
	//Basecamp(37signals.com) Request Authentication Token URL
	const AUTH_TOKEN_URL = "https://launchpad.37signals.com/authorization/token";
	//Content-type header used to send with 37signals.com API requests
	const REQ_CONTENT_TYPE = "application/json";
	//Automatically follow redirection from 37signals.com API
	const REQ_FOLLOW_LOCATION = false;
	//Header name for Authorization token. It is recommended that you do not change it.
	const AUTH_HEADER_NAME = "Authorization";
	//Basecamp API Base Url
	const BC_BASE_URL = "https://basecamp.com/";
	//Sub-Url to basecamp API
	const API_SUB_URL = "/api/v1";
	//Basecamp API sub-url to create new messages
	const MSG_POST_SUB_URL = "/projects/%s/messages.json";
	//Basecamp API sub-url to list your basecamp projects
	const PROJECTS_LIST_SUB_URL = "/projects.json";
	//Basecamp API sub-url to list people working on basecamp projects
	const PEOPLE_LIST_SUB_URL = "/people.json";
	//Basecamp API sub-url to list todos list in basecamp project
	const TODOLIST_LIST_SUB_URL = "/projects/%s/todolists.json";
	//Basecamp API sub-url to post todo item to basecamp
	const TODO_POST_SUB_URL = "/projects/%s/todolists/%s/todos.json";
	//Basecamp API sub-url to post comment to basecamp
	const COMMENT_POST_SUB_URL = "/projects/%s/%s/%s/comments.json";
	//Basecamp API sub-url to upload files to basecamp
	const UPLOAD_SUB_URL = "/attachments.json";
	//Basecamp URL to get info about the 37signals ID
	const AUTH_URL = "https://launchpad.37signals.com/authorization.json";
	//Debug curl http requests?
	const DEBUG_CURL = false;
	//Basecamp debug file location
	const DEBUG_CURL_FILE = "/tmp/bc_debug.txt";
	//Url to create new 37signals.com application
	const CREATE_APP_LNK = "https://integrate.37signals.com/";
	//Maximum retries to connect to 37signals.com API (in seconds)
	const REQ_MAX_CONNECT = 2;
	//Timeout while connecting to 37signals.com API (in seconds)
	const REQ_CONNECT_TIMEOUT = 5;
	//sub url to todo item page
	const TODO_SUB_URL = "%s/projects/%s/todos/%s";
}
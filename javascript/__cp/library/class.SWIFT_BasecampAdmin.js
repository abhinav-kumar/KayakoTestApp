/**
 * Common functions for basecamp module
 *
 * @author Atul Atri<atul.atri@kayko.com>
 * @class
 * @extends SWIFT_BaseClass
 */
SWIFT.Library.BasecampAdmin = SWIFT.Base.extend({
	//save current window location hash
	hash: window.location.hash,
	//intval to check window location hash
	checkHashTimeOut : 400,

	/**
	 * Check location hash
	 *
	 *  @return SWIFT.Library.BasecampAdmin
	 */
	CheckHash: function(){
		if (window.location.hash != this.hash) {
			this.hash = window.location.hash;
			this.ProcessHash(this.hash);
		}

		if ($('#basecampcode')) {
			setTimeout("SWIFT.Basecamp.AdminObject.CheckHash()", this.checkHashTimeOut);
		}

		return this;
	},

	/**
	 * Process chaned location hash
	 *
	 * @param {String} hash location hash
	 *
	 * @return SWIFT.Library.BasecampAdmin
	 */
	ProcessHash: function(hash){
		var hashArr = hash.split('=');

		if(hashArr.length == 2 && hashArr[0] == '#bs_auth_code'){
			$('#basecampcode').val(hashArr[1]);
			TabLoading('basecamp_manager', 'basecamp_tab_auth');
			$('#basecamp_managerform').submit();
			$('#auth_txt').hide();
			$('#auth_wait').show();
		}

		return this;
	},

	/**
	 * Open authorization window
	 *
	 * @param {String} authLink url for new window
	 *
	 * @return SWIFT.Library.BasecampAdmin
	 */
	OpenAuthWindow: function(authLink){
		var newWIndow = window.open (authLink, 'resizable=1, width=350, height=250');
		newWIndow.focus();

		return this;
	},

	/**
	 * Open Integartion dialog
	 *
	 * @param {String} windowTitle window title
	 *
	 * @return SWIFT.Library.BasecampAdmin
	 */
	IntegrateNowDialog: function(windowTitle){
		var _windowTitle = '<img src="' + themepath + 'images/icon_window.gif" align="absmiddle" border="0" /> ' + windowTitle;

		$('#bc_integrate_now').dialog({
			height: 122,
			width: 500,
			minHeight: 122,
			minWidth: 500,
			modal: true,
			draggable: true,
			resizable: true,
			close: function(event, ui) {
				$(this).dialog('destroy').remove();
			},
			title: _windowTitle,
			open: function() {
				$('.ui-dialog').each(function() {
					$(this).css('overflow','visible');
				});
				$('.ui-dialog-container').each(function() {
					$(this).css('overflow','hidden');
				})
			}
		});

		return SWIFT.Basecamp.AdminObject;
	},

	/**
	 * Handle basecamp project selection
	 *
	 * @return SWIFT.Library.BasecampAdmin
	 */
	TodoProjectSelect: function(){
		SWIFT.Basecamp.AdminObject.AddBcLoader('selecttodlistloader');
		_url =_baseName+ '/basecamp/TodoManager/AjaxTodoList/' + this.value;

		$.getJSON(_url, null, function(response){

			if (response) {

				$.each(response, function(key, val) {

					if (key == 'error') {

						if ( val != "") {

							if ($('#bc_todo_list_error')) {
								$('#bc_todo_list_error').remove();
								//remove default error container
								var dialogerrorcontainer = $('#window_exportbasecamp').children('.dialogerrorcontainer');

								if(dialogerrorcontainer){
									$(dialogerrorcontainer).parent().remove();
								}
							}
							$('#window_exportbasecamp').children(":first") .after(val);
						}else{
							$('#bc_todo_list_error') .remove();
						}
					}

					if (key == 'todoOptions') {
						$('.selecttodlistloader').remove();
						var todoselectlist = $("#selecttodolist");
						todoselectlist.empty();
						$.each(val, function(k, optArr) {
							optKey = optArr['value'];
							optValue = optArr['title'];
							todoselectlist.append($("<option></option>")
								.attr("value", optKey).text(optValue));
						});
					}
				});

			}
		});

		return SWIFT.Basecamp.AdminObject;
	},

	/**
	 * Add loader image basecamp todo selcect box
	 *
	 * @param {String} classname style class name
	 *
	 * @return SWIFT.Library.BasecampAdmin
	 */
	AddBcLoader: function(classname){
		var bcWaittxt = SWIFT.Basecamp.AdminObject.get("bc_wait");

		$("#selecttodolist").after('<div style="display:inline-block; margin-left:5px;" class="'+classname+'">&nbsp;</div>');
		$("."+classname).html('<img src="' + themepath + '/images/loadingcircle.gif"/>');
		$("#selecttodolist").html('<option>'+bcWaittxt+'</option>');

		return SWIFT.Basecamp.AdminObject;
	},

	/**
	 * Restore todo export form once it is destroyed after form submission
	 *
	 *  @return SWIFT.Library.BasecampAdmin
	 */
	RestoreTodoExportForm: function(){
		var _response = $('#todoExportFormResHolder').html();

		//if this is a success show flash message
		if (_response == 'todosuccess') {
			if (window.$UIObject) {
				SWIFT_Notification.Info(SWIFT.Basecamp.AdminObject.get('todo_posted_success'));
			}

			return SWIFT.Basecamp.AdminObject;
		}

		//do not use 'this' in this function this is being called as a callback function from many places
		//first destroy datepicker
		$("#duedate").datepicker('destroy');
		$('#todoExportFormResHolder').html("");

		var _divElement = UICreateWindowStart('exportbasecamp');
		var height = 700;
		$(_divElement).html(_response);

		UICreateWindowEnd(_divElement, 'exportbasecamp', SWIFT.Basecamp.AdminObject.get('basecamptodo'), 800, height, "");
		bindFormSubmit('View_TodoManagerform', 'todoExportFormResHolder', SWIFT.Basecamp.AdminObject.RestoreTodoExportForm);
		//again add date picker
		$('#duedate').datepicker(window.datePickerDefaults);

		return SWIFT.Basecamp.AdminObject;
	},

	/**
	 * Add events to page basecamp todo page
	 *
	 *  @return SWIFT.Library.BasecampAdmin
	 */
	AddtodoEvents: function(){
		$("#selecttodoproject").die();
		$("#selecttodoproject").live('change', SWIFT.Basecamp.AdminObject.TodoProjectSelect);
		//Add a hidden div in dom it will contain data returned by TodoExportForm
		$('#TodoExportFormResHolder').remove();
		$('body').append('<div style="display:none" id="todoExportFormResHolder"></div>');

		return SWIFT.Basecamp.AdminObject;
	},

	/**
	 * Create basecamp menu
	 *
	 * @param {Integer} [ticketId= Null] ticket id
	 * @param {Integer} [todoId= Null] todo id
	 *
	 *  @return SWIFT.Library.BasecampAdmin
	 */
	CreateBasecampMenu: function(ticketId, todoId){
		var basecamptodoMessage = SWIFT.Basecamp.AdminObject.get("basecamptodo");
		var loadingwindowMessage = SWIFT.Basecamp.AdminObject.get("loadingwindow");
		
		if(!todoId){
			$('#basecamp').off('click.openDropDown');
			$('#basecamp').on('click.openTodoForm', function() {
				UICreateWindow(_baseName + "/basecamp/TodoManager/TodoExportForm/" + ticketId, 'exportbasecamp', basecamptodoMessage, loadingwindowMessage, 800, 700, true, window);
			});
		}else{
			$('#basecamp').off('click.openTodoForm');
			var dropImage = $("<img>").attr('src', _swiftPath + "__swift/themes/__cp/images/menudropgray.gif").attr('border', 0).attr('align', "absmiddle");
			$("#basecamp a").append(" ").append(dropImage);
			$('#basecamp').on('click.openDropDown', function(event) {
				UIDropDown('basecamp_menu', event, 'basecamp', 'tabtoolbartable');
			});
		}
	}
});

/**
 *  Basecamp container
 */
SWIFT.Basecamp = {};
/**
 * Basecamp admin object
 */
SWIFT.Basecamp.AdminObject = SWIFT.Library.BasecampAdmin.create();
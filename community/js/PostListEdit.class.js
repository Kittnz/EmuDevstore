/**
 * @author	Marcel Werk
 * @copyright	2001-2007 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 */
function PostListEdit(data, count, page) {
	this.data = data;
	this.count = count;
	this.page = page;
	
	/**
	 * Saves the marked status.
	 */
	this.saveMarkedStatus = function(data) {
		var ajaxRequest = new AjaxRequest();
		ajaxRequest.openPost('index.php?page=PostAction&t='+SECURITY_TOKEN+SID_ARG_2ND, data);
	}
	
	/**
	 * Returns a list of the edit options for the edit menu.
	 */
	this.getEditOptions = function(id) {
		var options = new Array();
		var i = 0;
		var post = this.data.get(id);
		
		if (permissions['canEditPost']) {
			// edit text
			options[i] = new Object();
			options[i]['function'] = 'postListEdit.initPostEdit('+id+');';
			options[i]['text'] = language['wbb.board.threads.button.editPost'];
			i++;
			
			// edit title
			options[i] = new Object();
			options[i]['function'] = 'postListEdit.startTitleEdit('+id+');';
			options[i]['text'] = language['wbb.board.threads.button.editTitle'];
			i++;
		}
		
		// enable / disable
		if (permissions['canEnablePost']) {
			if (post.isDisabled == 1) {
				options[i] = new Object();
				options[i]['function'] = 'postListEdit.enable('+id+');';
				options[i]['text'] = language['wbb.board.threads.button.enable'];
				i++;
			}
			else if (post.isDeleted == 0) {
				options[i] = new Object();
				options[i]['function'] = 'postListEdit.disable('+id+');';
				options[i]['text'] = language['wbb.board.threads.button.disable'];
				i++;
			}
		}
		
		// close / open
		if (permissions['canClosePost']) {
			if (post.isClosed == 1) {
				options[i] = new Object();
				options[i]['function'] = 'postListEdit.open('+id+');';
				options[i]['text'] = language['wbb.board.threads.button.open'];
				i++;
			}
			else {
				options[i] = new Object();
				options[i]['function'] = 'postListEdit.close('+id+');';
				options[i]['text'] = language['wbb.board.threads.button.close'];
				i++;
			}
		}
	
		// delete
		if (permissions['canDeletePost'] && (permissions['canDeletePostCompletely'] || (post.isDeleted == 0 && ENABLE_RECYCLE_BIN))) {
			options[i] = new Object();
			options[i]['function'] = 'postListEdit.remove('+id+');';
			options[i]['text'] = (post.isDeleted == 0 ? language['wcf.global.button.delete'] : language['wcf.global.button.deleteCompletely']);
			i++;
		}
		
		// delete report
		if (this.page == 'reports') {
			options[i] = new Object();
			options[i]['function'] = 'postListEdit.removeReport('+id+');';
			options[i]['text'] = language['wbb.board.threads.button.removeReport'];
			i++;
		}
		
		// recover
		if (post.isDeleted == 1 && permissions['canDeletePostCompletely']) {
			options[i] = new Object();
			options[i]['function'] = 'postListEdit.recover('+id+');';
			options[i]['text'] = language['wbb.board.threads.button.recover'];
			i++;
		}
		
		// merge
		if (this.count > 0 && permissions['canMergePost']) {
			options[i] = new Object();
			options[i]['function'] = 'postListEdit.merge('+id+');';
			options[i]['text'] = language['wbb.board.posts.button.merge'];
			i++;
		}
		
		// marked status
		if (permissions['canMarkPost']) {
			var markedStatus = post ? post.isMarked : false;
			options[i] = new Object();
			options[i]['function'] = 'postListEdit.parentObject.markItem(' + (markedStatus ? 'false' : 'true') + ', '+id+');';
			options[i]['text'] = markedStatus ? language['wcf.global.button.unmark'] : language['wcf.global.button.mark'];
			i++;
		}
		
		return options;
	}
	
	/**
	 * Returns a list of the edit options for the edit marked menu.
	 */
	this.getEditMarkedOptions = function() {
		var options = new Array();
		var i = 0;
		
		if (this.page == 'thread') {
			// move
			if (permissions['canMovePost']) {
				options[i] = new Object();
				options[i]['function'] = "postListEdit.move('move');";
				options[i]['text'] = language['wbb.board.threads.button.move'];
				i++;
			}
			
			// copy
			if (permissions['canCopyPost']) {
				options[i] = new Object();
				options[i]['function'] = "postListEdit.move('copy');";
				options[i]['text'] = language['wbb.board.threads.button.copy'];
				i++;
			}
		}
		
		if (this.page == 'board') {
			// move to new thread
			if (permissions['canMovePost']) {
				options[i] = new Object();
				options[i]['function'] = "threadListEdit.move('moveAndInsert');";
				options[i]['text'] = language['wbb.board.threads.button.moveAndInsert'];
				i++;
			}
			
			// copy to new thread
			if (permissions['canCopyPost']) {
				options[i] = new Object();
				options[i]['function'] = "threadListEdit.move('copyAndInsert');";
				options[i]['text'] = language['wbb.board.threads.button.copyAndInsert'];
				i++;
			}
		}
		
		// delete
		if (permissions['canDeletePost'] && (permissions['canDeletePostCompletely'] || ENABLE_RECYCLE_BIN)) {
			options[i] = new Object();
			options[i]['function'] = 'postListEdit.removeAll();';
			options[i]['text'] = language['wcf.global.button.delete'];
			i++;
		}
		
		// recover
		if (ENABLE_RECYCLE_BIN && permissions['canDeletePostCompletely']) {
			options[i] = new Object();
			options[i]['function'] = 'postListEdit.recoverAll();';
			options[i]['text'] = language['wbb.board.threads.button.recover'];
			i++;
		}
		
		// delete report
		if (this.page == 'reports') {
			options[i] = new Object();
			options[i]['function'] = 'postListEdit.removeReports();';
			options[i]['text'] = language['wbb.board.threads.button.removeReport'];
			i++;
		}
		
		// unmark all
		options[i] = new Object();
		options[i]['function'] = 'postListEdit.unmarkAll();';
		options[i]['text'] = language['wcf.global.button.unmark'];
		i++;
		
		// show marked
		options[i] = new Object();
		options[i]['function'] = 'document.location.href = fixURL("index.php?page=ModerationMarkedPosts'+SID_ARG_2ND+'")';
		options[i]['text'] = language['wbb.board.threads.button.showMarked'];
		i++;
		
		return options;
	}
	
	/**
	 * Returns the title of the edit marked menu.
	 */
	this.getMarkedTitle = function() {
		return eval(language['wbb.board.threads.markedPosts']);
	}
	
	/**
	 * Moves posts.
	 */
	this.move = function(action) {
		document.location.href = fixURL('index.php?page=PostAction&action='+action+(this.page == 'board' ? ('&boardID='+boardID) : '')+(this.page == 'thread' ? ('&threadID='+threadID) : '')+'&url='+encodeURIComponent(url)+'&t='+SECURITY_TOKEN+SID_ARG_2ND);
	}
	
	/**
	 * Deletes a post.
	 */
	this.remove = function(id) {
		var post = this.data.get(id);
		if (post.isDeleted == 0 && ENABLE_RECYCLE_BIN) {
			var promptResult = prompt(language['wbb.board.posts.delete.reason']);
			if (typeof(promptResult) != 'object' && typeof(promptResult) != 'undefined') {
				if (permissions['canReadDeletedPost']) {
					var ajaxRequest = new AjaxRequest();
					ajaxRequest.openGet('index.php?page=PostAction&action=trash&postID='+id+'&reason='+encodeURIComponent(promptResult)+'&t='+SECURITY_TOKEN+SID_ARG_2ND);
					post.isDeleted = 1;
					this.showStatus(id);
				}
				else {
					document.location.href = fixURL('index.php?page=PostAction&action=trash&postID='+id+'&reason='+encodeURIComponent(promptResult)+'&url='+encodeURIComponent(url)+'&t='+SECURITY_TOKEN+SID_ARG_2ND);
				}
			}
		}
		else {
			if (confirm((post.isDeleted == 0 ? language['wbb.board.posts.delete.sure'] : language['wbb.board.posts.deleteCompletely.sure']))) {
				document.location.href = fixURL('index.php?page=PostAction&action=delete&postID='+id+'&url='+encodeURIComponent(url)+'&t='+SECURITY_TOKEN+SID_ARG_2ND);
			}
		}
	}
	
	/**
	 * Deletes the report of a post.
	 */
	this.removeReport = function(id) {
		if (confirm(language['wbb.board.posts.removeReport.sure'])) {
			document.location.href = fixURL('index.php?page=PostAction&action=removeReport&postID='+id+'&url='+encodeURIComponent(url)+'&t='+SECURITY_TOKEN+SID_ARG_2ND);
		}
	}
	
	/**
	 * Merge this post with all marked posts.
	 */
	this.merge = function(id) {
		if (confirm(language['wbb.board.posts.merge.sure'])) {
			document.location.href = fixURL('index.php?page=PostAction&action=merge&postID='+id+'&url='+encodeURIComponent(url)+'&t='+SECURITY_TOKEN+SID_ARG_2ND);
		}
	}
	
	/**
	 * Deletes reports of marked posts.
	 */
	this.removeReports = function() {
		if (confirm(language['wbb.board.posts.removeReports.sure'])) {
			document.location.href = fixURL('index.php?page=PostAction&action=removeReports&url='+encodeURIComponent(url)+'&t='+SECURITY_TOKEN+SID_ARG_2ND);
		}
	}
	
	/**
	 * Deletes the marked posts.
	 */
	this.removeAll = function() {
		if (ENABLE_RECYCLE_BIN) {
			var promptResult = prompt(language['wbb.board.posts.deleteMarked.reason']);
			if (typeof(promptResult) != 'object' && typeof(promptResult) != 'undefined') {
				document.location.href = fixURL('index.php?page=PostAction&action=deleteAll&threadID='+threadID+'&reason='+encodeURIComponent(promptResult)+'&url='+encodeURIComponent(url)+'&t='+SECURITY_TOKEN+SID_ARG_2ND);
			}
		}
		else if (confirm(language['wbb.board.posts.deleteMarked.sure'])) {
			document.location.href = fixURL('index.php?page=PostAction&action=deleteAll&threadID='+threadID+'&url='+encodeURIComponent(url)+'&t='+SECURITY_TOKEN+SID_ARG_2ND);
		}
	}
	
	/**
	 * Recovers the marked posts.
	 */
	this.recoverAll = function() {
		document.location.href = fixURL('index.php?page=PostAction&action=recoverAll&threadID='+threadID+'&url='+encodeURIComponent(url)+'&t='+SECURITY_TOKEN+SID_ARG_2ND);
	}
	
	/**
	 * Ummarked all marked posts.
	 */
	this.unmarkAll = function() {
		var ajaxRequest = new AjaxRequest();
		ajaxRequest.openGet('index.php?page=PostAction&action=unmarkAll&t='+SECURITY_TOKEN+SID_ARG_2ND);
		
		// checkboxes
		this.count = 0;
		var postIDArray = this.data.keys();
		for (var i = 0; i < postIDArray.length; i++) {
			var id = postIDArray[i];
			var post = this.data.get(id);
		
			post.isMarked = 0;
			var checkbox = document.getElementById('postMark' + id);
			if (checkbox) {
				checkbox.checked = false;
			}
			
			this.showStatus(id);
		}
		
		// mark all checkbox
		this.parentObject.checkMarkAll(false);
		
		// edit marked menu
		this.parentObject.showMarked();
	}
	
	/**
	 * Recovers a post.
	 */
	this.recover = function(id) {
		var post = this.data.get(id);
		if (post.isDeleted == 1) {
			var ajaxRequest = new AjaxRequest();
			ajaxRequest.openGet('index.php?page=PostAction&action=recover&postID='+id+'&t='+SECURITY_TOKEN+SID_ARG_2ND);
			post.isDeleted = 0;
			this.showStatus(id);
		}
	}
	
	/**
	 * Enables a post.
	 */
	this.enable = function(id) {
		var post = this.data.get(id);
		if (post.isDisabled == 1) {
			var ajaxRequest = new AjaxRequest();
			ajaxRequest.openGet('index.php?page=PostAction&action=enable&postID='+id+'&t='+SECURITY_TOKEN+SID_ARG_2ND);
			post.isDisabled = 0;
			this.showStatus(id);
		}
	}
	
	/**
	 * Disables a post.
	 */
	this.disable = function(id) {
		var post = this.data.get(id);
		if (post.isDisabled == 0 && post.isDeleted == 0) {
			var ajaxRequest = new AjaxRequest();
			ajaxRequest.openGet('index.php?page=PostAction&action=disable&postID='+id+'&t='+SECURITY_TOKEN+SID_ARG_2ND);
			post.isDisabled = 1;
			this.showStatus(id);
		}
	}
	
	/**
	 * Opens a post.
	 */
	this.open = function(id) {
		var post = this.data.get(id);
		if (post.isClosed == 1) {
			var ajaxRequest = new AjaxRequest();
			ajaxRequest.openGet('index.php?page=PostAction&action=open&postID='+id+'&t='+SECURITY_TOKEN+SID_ARG_2ND);
			post.isClosed = 0;
			this.showStatus(id);
		}
	}
	
	/**
	 * Closes a post.
	 */
	this.close = function(id) {
		var post = this.data.get(id);
		if (post.isClosed == 0) {
			var ajaxRequest = new AjaxRequest();
			ajaxRequest.openGet('index.php?page=PostAction&action=close&postID='+id+'&t='+SECURITY_TOKEN+SID_ARG_2ND);
			post.isClosed = 1;
			this.showStatus(id);
		}
	}
	
	/**
	 * Show the status of a post.
	 */
	this.showStatus = function(id) {
		var post = this.data.get(id);
		
		// get row
		var row = document.getElementById('postRow'+id);
		
		// update css class
		if (row) {
			// get class
			var className = row.className;
		
			// remove all classes
			className = className.replace(/ ?marked/, '');
			className = className.replace(/ ?disabled/, '');
			className = className.replace(/ ?deleted/, '');
			className = className.replace(/ ?closed/, '');

			// marked
			if (post.isMarked) {
				className += ' marked';
			}
			
			// disabled
			if (post.isDisabled) {
				className += ' disabled';
			}
			
			// deleted
			if (post.isDeleted) {
				className += ' deleted';
			}
			
			// closed
			if (post.isClosed) {
				className += ' closed';
			}
			
			row.className = className;
		}
		
		// update icon
		var icon = document.getElementById('postEdit'+id);
		if (icon && icon.src != undefined) {
			// deleted
			if (post.isDeleted) {
				icon.src = post.icon.replace(/[a-z0-9-_]*?(?=(?:Options)?(?:S|M|L|XL)\.png$)/i, 'postTrash');
			}
			else {
				post.icon = post.icon.replace(/postTrash/i, 'post');
				
				// closed
				if (post.isClosed) {
					icon.src = post.icon.replace(/(?:Closed)?(?=(?:Options)?(?:S|M|L|XL)\.png$)/, 'Closed');
				}
				else {
					icon.src = post.icon.replace(/Closed(?=(?:Options)?(?:S|M|L|XL)\.png$)/, '');
				}
			}
		}
	}
	
	/**
	 * Initialises special post options.
	 */
	this.initItem = function(id) {
		// init topic edit
		if (permissions['canEditPost']) {
			var postTopicDiv = document.getElementById('postTopic'+id);
			if (postTopicDiv) {
				postTopicDiv.name = id;
				postTopicDiv.ondblclick = function() { postListEdit.startTitleEdit(this.name); }
			}
		}
	}
	
	/**
	 * Starts the editing of a post title.
	 */
	this.startTitleEdit = function(id) {
		var postTopicDiv = document.getElementById('postTopic'+id);
		if (postTopicDiv) {
			// cancel, if input field does already exist
			var inputs = postTopicDiv.getElementsByTagName('input');
			if (inputs.length > 0) {
				return;
			}
			
			// hide link
			var value = '';
			if (postTopicDiv.firstChild) {
				postTopicDiv.firstChild.style.display = 'none';
				// IE, Opera, Safari, Konqueror
				if (postTopicDiv.firstChild.innerText) {
					value = postTopicDiv.firstChild.innerText;
				}
				// Firefox
				else {
					value = new StringUtil(postTopicDiv.firstChild.innerHTML).decodeHTML();
				}
			}
		
			// show input field
			var inputField = document.createElement('input');
			inputField.type = 'text';
			inputField.className = 'inputText';
			inputField.value = value;
			postTopicDiv.appendChild(inputField);
			
			// add event listeners
			inputField.name = id;
			inputField.onkeydown = function(e) { postListEdit.doTitleEdit(this.name, e); }
			inputField.onblur = function() { postListEdit.abortTitleEdit(this.name); }
			
			// set focus
			inputField.focus();
		}
	}
	
	/**
	 * Aborts the editing of a post title.
	 */
	this.abortTitleEdit = function(id) {
		var postTopicDiv = document.getElementById('postTopic'+id);
		if (postTopicDiv) {
			// remove input field
			var inputs = postTopicDiv.getElementsByTagName('input');
			for (var i = 0; i < inputs.length; i++) {
				postTopicDiv.removeChild(inputs[i]);
			}
			
			// show link
			if (postTopicDiv.firstChild) {
				postTopicDiv.firstChild.style.display = '';
			}
		}
	}
	
	/**
	 * Takes the value of the input-field and creates an ajax-request to save the new title.
	 * enter = save
	 * esc = abort
	 */
	this.doTitleEdit = function(id, e) {
		if (!e) e = window.event;
		
		// get key code
		var keyCode = 0;
		if (e.which) keyCode = e.which;
		else if (e.keyCode) keyCode = e.keyCode;
	
		// get input field
		if (e.target) var inputField = e.target;
		else if (e.srcElement) var inputField = e.srcElement;
		
		// enter
		if (keyCode == '13' && (inputField.value != '')) {
			// set new value
			inputField.value = new StringUtil(inputField.value).trim();
			var postTopicDiv = document.getElementById('postTopic'+id);
			if (postTopicDiv.firstChild) {
				if (postTopicDiv.firstChild.firstChild) postTopicDiv.firstChild.removeChild(postTopicDiv.firstChild.firstChild);
				postTopicDiv.firstChild.appendChild(document.createTextNode(inputField.value));
			}
			
			// save new value
			var ajaxRequest = new AjaxRequest();
			ajaxRequest.openPost('index.php?page=PostAction&action=changeTopic&postID='+id+'&t='+SECURITY_TOKEN+SID_ARG_2ND, 'topic='+encodeURIComponent(inputField.value));
			
			// abort editing
			inputField.blur();
			return false;
		}
		// esc
		else if (keyCode == '27') {
			inputField.blur();
			return false;
		}
	}
	
	/**
	 * Inits the editing of a post text.
	 */
	this.initPostEdit = function(id) {
		if ($('postTextInputDiv'+id)) return;
		
		// request text message
		var ajaxRequest = new AjaxRequest();
		ajaxRequest.openGet('index.php?page=PostMessage&postID='+id+SID_ARG_2ND, function() { postListEdit.startPostEdit(id, ajaxRequest); });
	}
	
	/**
	 * Starts the editing of a post text.
	 */
	this.startPostEdit = function(id, ajaxRequest) {
		if (ajaxRequest && ajaxRequest.xmlHttpRequest.readyState == 4 && ajaxRequest.xmlHttpRequest.status == 200 && ajaxRequest.xmlHttpRequest.responseText) {
			var postTextDiv = $('postText'+id);
			if (postTextDiv) {
				var height = Math.round(postTextDiv.offsetHeight * 0.66);
				if (height < 150) height = 150;
				
				// hide div
				postTextDiv.addClassName('hidden');
				
				// create text area and buttons
				var inputDiv = document.createElement('div');
				inputDiv.className = 'formSubmit';
				postTextDiv.parentNode.insertBefore(inputDiv, postTextDiv);
				inputDiv.id = 'postTextInputDiv'+id;
				
				var textarea = document.createElement('textarea');
				inputDiv.appendChild(textarea);
				textarea.id = 'postTextInput'+id;
				textarea.style.height = height+'px';
				textarea.value = ajaxRequest.xmlHttpRequest.responseText;
				textarea.focus();
				
				// buttons
				var submitButton = document.createElement('input');
				submitButton.type = 'button';
				submitButton.value = language['wcf.global.button.submit'];
				inputDiv.appendChild(submitButton);
				submitButton.onclick = function() { postListEdit.submitPostEdit(id); }
				
				var cancelButton = document.createElement('input');
				cancelButton.type = 'button';
				cancelButton.value = language['wcf.global.button.reset'];
				inputDiv.appendChild(cancelButton);
				cancelButton.onclick = function() { postListEdit.abortPostEdit(id); }
			}
		}
	}
	
	/**
	 * Aborts the editing of a post.
	 */
	this.abortPostEdit = function(id) {
		// remove input div
		var inputDiv = document.getElementById('postTextInputDiv'+id);
		if (inputDiv) inputDiv.parentNode.removeChild(inputDiv);
		
		// show text div
		var postTextDiv = $('postText'+id);
		if (postTextDiv) postTextDiv.removeClassName('hidden');
	}
	
	/**
	 * Saves the new post message.
	 */
	this.submitPostEdit = function(id) {
		var textarea = document.getElementById('postTextInput'+id);
		if (textarea && textarea.value) {
			// save text and request formatted text
			var ajaxRequest = new AjaxRequest();
			ajaxRequest.openPost('index.php?action=PostMessageEdit&postID='+id+'&t='+SECURITY_TOKEN+SID_ARG_2ND, 'text='+encodeURIComponent(textarea.value), function() { postListEdit.finishPostEdit(id, ajaxRequest); });
		}
	}
	
	/**
	 * Finishs the editing of a post.
	 */
	this.finishPostEdit = function(id, ajaxRequest) {
		if (ajaxRequest && ajaxRequest.xmlHttpRequest.readyState == 4) {
			if (ajaxRequest.xmlHttpRequest.status == 200) {
				this.abortPostEdit(id);
				
				var postTextDiv = document.getElementById('postText'+id);
				if (postTextDiv) {
					postTextDiv.innerHTML = ajaxRequest.xmlHttpRequest.responseText;
				}
			}
			else if (ajaxRequest.xmlHttpRequest.status == 403) {
				alert(ajaxRequest.xmlHttpRequest.responseText);
			}
		}
	}
	
	this.parentObject = new InlineListEdit('post', this);
}
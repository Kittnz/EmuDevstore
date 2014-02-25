/**
 * @author	Marcel Werk
 * @copyright	2001-2007 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 */
function ThreadListEdit(data, count, page) {
	this.data = data;
	this.count = count;
	this.page = page;
	this.prefixesCount = 0;
	
	/**
	 * Saves the marked status.
	 */
	this.saveMarkedStatus = function(data) {
		var ajaxRequest = new AjaxRequest();
		ajaxRequest.openPost('index.php?page=ThreadAction&t='+SECURITY_TOKEN+SID_ARG_2ND, data);
	}
	
	/**
	 * Returns a list of the edit options for the edit menu.
	 */
	this.getEditOptions = function(id) {
		var options = new Array();
		var i = 0;
		var thread = this.data.get(id);
		
		if (thread.isMoved) {
			// delete link
			if (permissions['canDeleteThread']) {
				options[i] = new Object();
				options[i]['function'] = 'threadListEdit.removeLink('+thread.isMoved+');';
				options[i]['text'] = language['wbb.board.threads.button.deleteLink'];
				i++;
			}
		}
		else {
			// edit title
			if (permissions['canEditPost']) {
				options[i] = new Object();
				options[i]['function'] = 'threadListEdit.startTitleEdit('+id+');';
				options[i]['text'] = language['wbb.board.threads.button.editTitle'];
				i++;
			}
			
			// edit prefix
			if (permissions['canEditPost'] && this.prefixesCount > 0 && document.getElementById('threadPrefix'+id)) {
				options[i] = new Object();
				options[i]['function'] = 'threadListEdit.startPrefixEdit('+id+');';
				options[i]['text'] = language['wbb.board.threads.button.editPrefix'];
				i++;
			}
			
			// marking as done
			if (enableMarkingAsDone == 1 && permissions['canMarkAsDoneThread']) {
				if (thread.isDone == 1) {
					options[i] = new Object();
					options[i]['function'] = 'threadListEdit.markAsUndone('+id+');';
					options[i]['text'] = language['wbb.board.threads.button.undone'];
					i++;
				}
				else {
					options[i] = new Object();
					options[i]['function'] = 'threadListEdit.markAsDone('+id+');';
					options[i]['text'] = language['wbb.board.threads.button.done'];
					i++;
				}
			}
			
			// make sticky
			if (permissions['canPinThread'] && !thread.isAnnouncement) {
				if (thread.isSticky == 1) {
					options[i] = new Object();
					options[i]['function'] = 'threadListEdit.unstick('+id+');';
					options[i]['text'] = language['wbb.board.threads.button.unstick'];
					i++;
				}
				else {
					options[i] = new Object();
					options[i]['function'] = 'threadListEdit.stick('+id+');';
					options[i]['text'] = language['wbb.board.threads.button.stick'];
					i++;
				}
			}
			
			// enable / disable
			if (permissions['canEnableThread']) {
				if (thread.isDisabled == 1) {
					options[i] = new Object();
					options[i]['function'] = 'threadListEdit.enable('+id+');';
					options[i]['text'] = language['wbb.board.threads.button.enable'];
					i++;
				}
				else if (thread.isDeleted == 0) {
					options[i] = new Object();
					options[i]['function'] = 'threadListEdit.disable('+id+');';
					options[i]['text'] = language['wbb.board.threads.button.disable'];
					i++;
				}
			}
			
			// close / open
			if (permissions['canCloseThread']) {
				if (thread.isClosed == 1) {
					options[i] = new Object();
					options[i]['function'] = 'threadListEdit.open('+id+');';
					options[i]['text'] = language['wbb.board.threads.button.open'];
					i++;
				}
				else {
					options[i] = new Object();
					options[i]['function'] = 'threadListEdit.close('+id+');';
					options[i]['text'] = language['wbb.board.threads.button.close'];
					i++;
				}
			}
		
			// delete
			if (permissions['canDeleteThread'] && (permissions['canDeleteThreadCompletely'] || (thread.isDeleted == 0 && ENABLE_RECYCLE_BIN))) {
				options[i] = new Object();
				options[i]['function'] = 'threadListEdit.remove('+id+');';
				options[i]['text'] = (thread.isDeleted == 0 ? language['wcf.global.button.delete'] : language['wcf.global.button.deleteCompletely']);
				i++;
			}
			
			// recover
			if (thread.isDeleted == 1 && permissions['canDeleteThreadCompletely']) {
				options[i] = new Object();
				options[i]['function'] = 'threadListEdit.recover('+id+');';
				options[i]['text'] = language['wbb.board.threads.button.recover'];
				i++;
			}
			
			// marked status
			if (permissions['canMarkThread']) {
				var markedStatus = thread ? thread.isMarked : false;
				options[i] = new Object();
				options[i]['function'] = 'threadListEdit.parentObject.markItem(' + (markedStatus ? 'false' : 'true') + ', '+id+');';
				options[i]['text'] = markedStatus ? language['wcf.global.button.unmark'] : language['wcf.global.button.mark'];
				i++;
			}
		}
				
		return options;
	}
	
	/**
	 * Returns a list of the edit options for the edit marked menu.
	 */
	this.getEditMarkedOptions = function() {
		var options = new Array();
		var i = 0;
		
		if (this.page == 'board') {
			// move
			if (permissions['canMoveThread']) {
				options[i] = new Object();
				options[i]['function'] = "threadListEdit.move('move');";
				options[i]['text'] = language['wbb.board.threads.button.move'];
				i++;
				
				// move with link
				options[i] = new Object();
				options[i]['function'] = "threadListEdit.move('moveWithLink');";
				options[i]['text'] = language['wbb.board.threads.button.moveWithLink'];
				i++;
			}
			
			// copy
			if (permissions['canCopyThread']) {
				options[i] = new Object();
				options[i]['function'] = "threadListEdit.move('copy');";
				options[i]['text'] = language['wbb.board.threads.button.copy'];
				i++;
			}
			
			// close
			if (permissions['canCloseThread']) {
				options[i] = new Object();
				options[i]['function'] = 'threadListEdit.closeAll();';
				options[i]['text'] = language['wbb.board.threads.button.close'];
				i++;
			}
		}
		
		if (this.page == 'thread') {
			// merge
			if (permissions['canMoveThread']) {
				options[i] = new Object();
				options[i]['function'] = "threadListEdit.move('merge');";
				options[i]['text'] = language['wbb.board.threads.button.merge'];
				i++;
			}
			
			// copy and merge
			if (permissions['canCopyThread']) {
				options[i] = new Object();
				options[i]['function'] = "threadListEdit.move('copyAndMerge');";
				options[i]['text'] = language['wbb.board.threads.button.copyAndMerge'];
				i++;
			}
		}
		
		// delete
		if (permissions['canDeleteThread'] && (permissions['canDeleteThreadCompletely'] || ENABLE_RECYCLE_BIN)) {
			options[i] = new Object();
			options[i]['function'] = 'threadListEdit.removeAll();';
			options[i]['text'] = language['wcf.global.button.delete'];
			i++;
		}
		
		// recover
		if (ENABLE_RECYCLE_BIN && permissions['canDeleteThreadCompletely']) {
			options[i] = new Object();
			options[i]['function'] = 'threadListEdit.recoverAll();';
			options[i]['text'] = language['wbb.board.threads.button.recover'];
			i++;
		}
		
		// unmark all
		options[i] = new Object();
		options[i]['function'] = 'threadListEdit.unmarkAll();';
		options[i]['text'] = language['wcf.global.button.unmark'];
		i++;
		
		// show marked
		options[i] = new Object();
		options[i]['function'] = 'document.location.href = fixURL("index.php?page=ModerationMarkedThreads'+SID_ARG_2ND+'")';
		options[i]['text'] = language['wbb.board.threads.button.showMarked'];
		i++;
		
		return options;
	}
	
	/**
	 * Returns the title of the edit marked menu.
	 */
	this.getMarkedTitle = function() {
		return eval(language['wbb.board.threads.markedThreads']);
	}
	
	/**
	 * Moves threads.
	 */
	this.move = function(action) {
		document.location.href = fixURL('index.php?page=ThreadAction&action='+action+(this.page == 'board' ? ('&boardID='+boardID) : '')+(this.page == 'thread' ? ('&threadID='+threadID) : '')+'&url='+encodeURIComponent(url)+'&t='+SECURITY_TOKEN+SID_ARG_2ND);
	}
	
	/**
	 * Sticks this thread.
	 */
	this.stick = function(id) {
		document.location.href = fixURL('index.php?page=ThreadAction&action=stick&threadID='+id+'&url='+encodeURIComponent(url)+'&t='+SECURITY_TOKEN+SID_ARG_2ND);
	}
	
	/**
	 * Unsticks this thread.
	 */
	this.unstick = function(id) {
		document.location.href = fixURL('index.php?page=ThreadAction&action=unstick&threadID='+id+'&url='+encodeURIComponent(url)+'&t='+SECURITY_TOKEN+SID_ARG_2ND);
	}
	
	/**
	 * Deletes a thread.
	 */
	this.remove = function(id) {
		var thread = this.data.get(id);
		if (thread.isDeleted == 0 && ENABLE_RECYCLE_BIN) {
			var promptResult = prompt(language['wbb.board.threads.delete.reason']);
			if (typeof(promptResult) != 'object' && typeof(promptResult) != 'undefined') {
				if (permissions['canReadDeletedThread']) {
					var ajaxRequest = new AjaxRequest();
					ajaxRequest.openGet('index.php?page=ThreadAction&action=trash&threadID='+id+'&reason='+encodeURIComponent(promptResult)+'&t='+SECURITY_TOKEN+SID_ARG_2ND);
					thread.isDeleted = 1;
					this.showStatus(id);
					// todo: insert real deleteMessage
					$('threadRow' + id).down('.columnTopic').insert('<p class="deleteNote smallFont">' + promptResult.escapeHTML() + '</p>');
				}
				else {
					document.location.href = fixURL('index.php?page=ThreadAction&action=trash&threadID='+id+'&reason='+encodeURIComponent(promptResult)+'&url='+encodeURIComponent(url)+'&t='+SECURITY_TOKEN+SID_ARG_2ND);
				}
			}
		}
		else {
			if (confirm((thread.isDeleted == 0 ? language['wbb.board.threads.delete.sure'] : language['wbb.board.threads.deleteCompletely.sure']))) {
				document.location.href = fixURL('index.php?page=ThreadAction&action=delete&threadID='+id+'&url='+encodeURIComponent(url)+'&t='+SECURITY_TOKEN+SID_ARG_2ND);
			}
		}
	}
	
	/**
	 * Deletes the link to a thread.
	 */
	this.removeLink = function(id) {
		if (confirm(language['wbb.board.threads.deleteLink.sure'])) {
			document.location.href = fixURL('index.php?page=ThreadAction&action=delete&threadID='+id+'&url='+encodeURIComponent(url)+'&t='+SECURITY_TOKEN+SID_ARG_2ND);
		}
	}
	
	/**
	 * Deletes the marked threads.
	 */
	this.removeAll = function() {
		if (ENABLE_RECYCLE_BIN) {
			var promptResult = prompt(language['wbb.board.threads.deleteMarked.reason']);
			if (typeof(promptResult) != 'object' && typeof(promptResult) != 'undefined') {
				document.location.href = fixURL('index.php?page=ThreadAction&action=deleteAll&boardID='+boardID+'&reason='+encodeURIComponent(promptResult)+'&url='+encodeURIComponent(url)+'&t='+SECURITY_TOKEN+SID_ARG_2ND);
			}
		}
		else if (confirm(language['wbb.board.threads.deleteMarked.sure'])) {
			document.location.href = fixURL('index.php?page=ThreadAction&action=deleteAll&boardID='+boardID+'&url='+encodeURIComponent(url)+'&t='+SECURITY_TOKEN+SID_ARG_2ND);
		}
	}
	
	/**
	 * Recovers the marked threads.
	 */
	this.recoverAll = function() {
		document.location.href = fixURL('index.php?page=ThreadAction&action=recoverAll&boardID='+boardID+'&url='+encodeURIComponent(url)+'&t='+SECURITY_TOKEN+SID_ARG_2ND);
	}
	
	/**
	 * Closes the marked threads.
	 */
	this.closeAll = function() {
		document.location.href = fixURL('index.php?page=ThreadAction&action=closeAll&boardID='+boardID+'&url='+encodeURIComponent(url)+'&t='+SECURITY_TOKEN+SID_ARG_2ND);
	}
	
	/**
	 * Ummarked all marked threads.
	 */
	this.unmarkAll = function() {
		var ajaxRequest = new AjaxRequest();
		ajaxRequest.openGet('index.php?page=ThreadAction&action=unmarkAll&t='+SECURITY_TOKEN+SID_ARG_2ND);
		
		// checkboxes
		this.count = 0;
		var threadIDArray = this.data.keys();
		for (var i = 0; i < threadIDArray.length; i++) {
			var id = threadIDArray[i];
			var thread = this.data.get(id);
		
			thread.isMarked = 0;
			var checkbox = document.getElementById('threadMark' + id);
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
	 * Recovers a thread.
	 */
	this.recover = function(id) {
		var thread = this.data.get(id);
		if (thread.isDeleted == 1) {
			var ajaxRequest = new AjaxRequest();
			ajaxRequest.openGet('index.php?page=ThreadAction&action=recover&threadID='+id+'&t='+SECURITY_TOKEN+SID_ARG_2ND);
			thread.isDeleted = 0;
			this.showStatus(id);
			$('threadRow' + id).down('.deleteNote').remove();
		}
	}
	
	/**
	 * Enables a thread.
	 */
	this.enable = function(id) {
		var thread = this.data.get(id);
		if (thread.isDisabled == 1) {
			var ajaxRequest = new AjaxRequest();
			ajaxRequest.openGet('index.php?page=ThreadAction&action=enable&threadID='+id+'&t='+SECURITY_TOKEN+SID_ARG_2ND);
			thread.isDisabled = 0;
			this.showStatus(id);
		}
	}
	
	/**
	 * Disables a thread.
	 */
	this.disable = function(id) {
		var thread = this.data.get(id);
		if (thread.isDisabled == 0 && thread.isDeleted == 0) {
			var ajaxRequest = new AjaxRequest();
			ajaxRequest.openGet('index.php?page=ThreadAction&action=disable&threadID='+id+'&t='+SECURITY_TOKEN+SID_ARG_2ND);
			thread.isDisabled = 1;
			this.showStatus(id);
		}
	}
	
	/**
	 * Opens a thread.
	 */
	this.open = function(id) {
		var thread = this.data.get(id);
		if (thread.isClosed == 1) {
			var ajaxRequest = new AjaxRequest();
			ajaxRequest.openGet('index.php?page=ThreadAction&action=open&threadID='+id+'&t='+SECURITY_TOKEN+SID_ARG_2ND);
			thread.isClosed = 0;
			this.showStatus(id);
		}
	}
	
	/**
	 * Closes a thread.
	 */
	this.close = function(id) {
		var thread = this.data.get(id);
		if (thread.isClosed == 0) {
			var ajaxRequest = new AjaxRequest();
			ajaxRequest.openGet('index.php?page=ThreadAction&action=close&threadID='+id+'&t='+SECURITY_TOKEN+SID_ARG_2ND);
			thread.isClosed = 1;
			this.showStatus(id);
		}
	}
	
	/**
	 * Marks a thread as done.
	 */
	this.markAsDone = function(id) {
		var thread = this.data.get(id);
		if (thread.isDone == 0) {
			var ajaxRequest = new AjaxRequest();
			ajaxRequest.openGet('index.php?page=ThreadAction&action=markAsDone&threadID='+id+'&t='+SECURITY_TOKEN+SID_ARG_2ND);
			thread.isDone = 1;
			this.showStatus(id);
			
			// update event
			var icon = document.getElementById('threadMarking'+id);
			if (icon) {
				icon.ondblclick = function() {
					threadListEdit.markAsUndone.apply(threadListEdit, [id]);
				};
			}
		}
	}
	
	/**
	 * Marks a thread as undone.
	 */
	this.markAsUndone = function(id) {
		var thread = this.data.get(id);
		if (thread.isDone == 1) {
			var ajaxRequest = new AjaxRequest();
			ajaxRequest.openGet('index.php?page=ThreadAction&action=markAsUndone&threadID='+id+'&t='+SECURITY_TOKEN+SID_ARG_2ND);
			thread.isDone = 0;
			this.showStatus(id);
			
			// update event
			var icon = document.getElementById('threadMarking'+id);
			if (icon) {
				icon.ondblclick = function() {
					threadListEdit.markAsDone.apply(threadListEdit, [id]);
				};
			}
		}
	}
	
	/**
	 * Show the status of a thread.
	 */
	this.showStatus = function(id) {
		var thread = this.data.get(id);
		
		// get row
		var row = document.getElementById('threadRow'+id);
		
		// update css class
		if (row) {
			// get class
			var className = row.className;

			// remove all classes except first one
			//className = className.replace(/ .*/, '');
			
			// original className
			if (thread.className != className) {
				className = thread.className;
			}
			
			// disabled
			if (thread.isDisabled) {
				className += ' disabled';
			}
			
			// deleted
			if (thread.isDeleted) {
				className += ' deleted';
			}
			
			// closed
			/*if (thread.isClosed) {
				className = ' closed';
			}*/
			
			// marked
			if (thread.isMarked) {
				className += ' marked';
			}
			
			row.className = className;
		}
		
		// update icon
		var icon = document.getElementById('threadEdit'+id);
		if (icon && icon.src != undefined) {
			// deleted
			if (thread.isDeleted) {
				icon.src = thread.icon.replace(/[a-z0-9-_]*?(?=(?:Options)?(?:S|M|L|XL)\.png$)/i, 'threadTrash');
			}
			else {
				thread.icon = thread.icon.replace(/threadTrash/i, 'thread');
				
				// closed
				if (thread.isClosed) {
					icon.src = thread.icon.replace(/(?:Closed)?(?=(?:Options)?(?:S|M|L|XL)\.png$)/, 'Closed');
				}
				else {
					icon.src = thread.icon.replace(/Closed(?=(?:Options)?(?:S|M|L|XL)\.png$)/, '');
				}
			}
		}
		
		// update marking icon
		var icon = document.getElementById('threadMarking'+id);
		if (icon) {
			if (thread.isDone) {
				icon.src = icon.src.replace(/undone(?=(?:S|M|L|XL)\.png$)/i, 'done');
				icon.title = language['wbb.board.threads.done'];
			}
			else {
				icon.src = icon.src.replace(/\/done(?=(?:S|M|L|XL)\.png$)/i, '/undone');
				icon.title = language['wbb.board.threads.undone'];
			}
		}
	}
	
	/**
	 * Initialises special thread options.
	 */
	this.initItem = function(id) {
		var thread = this.data.get(id);
		if (!thread.isMoved) {
			// init topic edit
			if (permissions['canEditPost']) {
				var threadTopicDiv = document.getElementById('threadTitle'+id);
				if (threadTopicDiv) {
					threadTopicDiv.name = id;
					threadTopicDiv.ondblclick = function(event) { 
						if (!event) event = window.event;
						var target;
						if (event.target) target = event.target;
						else if (event.srcElement) target = event.srcElement;
						if (target.nodeType == 3) {// defeat Safari bug
							target = target.parentNode;
						}
						if (target.parentNode.getAttribute("id") != 'threadPrefix'+id) {
							threadListEdit.startTitleEdit(this.name); 
						}
					}
				}
			}
			
			// init prefix edit
			if (permissions['canEditPost'] && this.prefixesCount > 0) {
				var threadPrefixSpan = document.getElementById('threadPrefix'+id);
				if (threadPrefixSpan) {
					threadPrefixSpan.name = id;
					threadPrefixSpan.ondblclick = function() { threadListEdit.startPrefixEdit(this.name); }
				}
			}
			
			// init markings as done
			if (enableMarkingAsDone == 1 && permissions['canMarkAsDoneThread']) {
				var icon = document.getElementById('threadMarking'+id);
				if (icon) {
					if (thread.isDone == 1) {
						icon.ondblclick = function() {
							threadListEdit.markAsUndone.apply(threadListEdit, [id]);
						};
					}
					else {
						icon.ondblclick = function() {
							threadListEdit.markAsDone.apply(threadListEdit, [id]);
						};
					}
				}
			}
		}
	}
	
	/**
	 * Starts the editing of a thread title.
	 */
	this.startTitleEdit = function(id) {
		var threadTopicDiv = document.getElementById('threadTitle'+id);
		if (threadTopicDiv) {
			// cancel, if input field does already exist
			var inputs = threadTopicDiv.getElementsByTagName('input');
			if (inputs.length > 0) {
				return;
			}
			
			// hide first child
			var value = '';
			var title = threadTopicDiv.getElementsByTagName('a')[0];
			if (title) {
				title.style.display = 'none';
				// IE, Opera, Safari, Konqueror
				if (title.innerText) {
					value = title.innerText;
				}
				// Firefox
				else {
					value = new StringUtil(title.innerHTML).decodeHTML();
				}
			}
		
			// show input field
			var inputField = document.createElement('input');
			inputField.type = 'text';
			inputField.className = 'inputText';
			inputField.value = value;
			threadTopicDiv.appendChild(inputField);
			
			// add event listeners
			inputField.name = id;
			inputField.onkeydown = function(e) { threadListEdit.doTitleEdit(this.name, e); }
			inputField.onblur = function() { threadListEdit.abortTitleEdit(this.name); }
			
			// set focus
			inputField.focus();
		}
	}
	
	/**
	 * Aborts the editing of a thread title.
	 */
	this.abortTitleEdit = function(id) {
		var threadTopicDiv = document.getElementById('threadTitle'+id);
		if (threadTopicDiv) {
			// remove input field
			var inputs = threadTopicDiv.getElementsByTagName('input');
			for (var i = 0; i < inputs.length; i++) {
				threadTopicDiv.removeChild(inputs[i]);
			}
			
			// show first child
			var title = threadTopicDiv.getElementsByTagName('a')[0];
			if (title) {
				title.style.display = '';
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
			var threadTopicDiv = document.getElementById('threadTitle'+id);
			var title = threadTopicDiv.getElementsByTagName('a')[0];
			if (title) {
				if (title.firstChild) title.removeChild(title.firstChild);
				title.appendChild(document.createTextNode(inputField.value));
			}
			
			// save new value
			var ajaxRequest = new AjaxRequest();
			ajaxRequest.openPost('index.php?page=ThreadAction&action=changeTopic&threadID='+id+'&t='+SECURITY_TOKEN+SID_ARG_2ND, 'topic='+encodeURIComponent(inputField.value));
			
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
	 * Starts the editing of a thread prefix.
	 */
	this.startPrefixEdit = function(id) {
		var thread = this.data.get(id);
		var threadPrefixSpan = document.getElementById('threadPrefix'+id);
		if (threadPrefixSpan) {
			// cancel, if select field does already exist
			var selects = threadPrefixSpan.getElementsByTagName('select');
			if (selects.length > 0) {
				return;
			}
			
			// hide span
			threadPrefixSpan.firstChild.style.display = 'none';
			var value = thread.prefix;
			
			// show select field
			var selectField = document.createElement('select');
			threadPrefixSpan.appendChild(selectField);
			var selectedIndex = 0;
			var count = 0;
			
			// add empty option
			if (!prefixRequired) {
				var optionField = document.createElement('option');
				selectField.appendChild(optionField);
				count++;
			}
			
			for (var key in prefixes) {
				var optionField = document.createElement('option');
				optionField.value = key;
				
				if (key == value) {
					
					selectedIndex = count;
				}
				
				selectField.appendChild(optionField);
				optionField.appendChild(document.createTextNode(prefixes[key]));
				count++;
			}
			
			// set selected index
			selectField.selectedIndex = selectedIndex;
			
			// add event listeners
			selectField.id = 'threadPrefixSelect'+id;
			selectField.name = id;
			selectField.onchange = function() { threadListEdit.doPrefixeEdit(this.name, this); }
			selectField.onblur = function() { threadListEdit.abortPrefixEdit(this.name); }
			
			// set focus
			selectField.focus();
		}
	}
	
	/**
	 * Aborts the editing of a thread prefix.
	 */
	this.abortPrefixEdit = function(id) {
		var thread = this.data.get(id);
		if (prefixRequired && !thread.prefix) {
			this.doPrefixeEdit(id, document.getElementById('threadPrefixSelect'+id));
		}
		
		var threadPrefixSpan = document.getElementById('threadPrefix'+id);
		if (threadPrefixSpan) {
			// remove select field
			var selects = threadPrefixSpan.getElementsByTagName('select');
			for (var i = 0; i < selects.length; i++) {
				threadPrefixSpan.removeChild(selects[i]);
			}
			
			// show span
			threadPrefixSpan.firstChild.style.display = '';
		}
	}
	
	/**
	 * Saves the new value of the thread prefix
	 */
	this.doPrefixeEdit = function(id, selectField) {
		var thread = this.data.get(id);
		
		// get new value
		var newPrefix = selectField.options[selectField.selectedIndex].value;
		
		// set new value
		thread.prefix = newPrefix;
		var threadPrefixSpan = document.getElementById('threadPrefix'+id);
		
		var newPrefixValue = (newPrefix != '' ? prefixes[newPrefix] : '');
		if (threadPrefixSpan.firstChild.firstChild) threadPrefixSpan.firstChild.removeChild(threadPrefixSpan.firstChild.firstChild);
		threadPrefixSpan.firstChild.appendChild(document.createTextNode(newPrefixValue));
		
		// save new value
		var ajaxRequest = new AjaxRequest();
		ajaxRequest.openPost('index.php?page=ThreadAction&action=changePrefix&threadID='+id+'&t='+SECURITY_TOKEN+SID_ARG_2ND, 'prefix='+encodeURIComponent(newPrefix));
			
		// abort editing
		selectField.blur();
	}
	
	// count prefixes
	for (var key in prefixes) {
		this.prefixesCount++;
	}
	
	this.parentObject = new InlineListEdit('thread', this);
}
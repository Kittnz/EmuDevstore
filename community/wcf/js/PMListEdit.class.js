/**
 * @author	Marcel Werk
 * @copyright	2001-2007 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 */
function PMListEdit(data, count, folders) {
	this.data = data;
	this.count = count;
	this.folders = folders;
	
	/**
	 * Saves the marked status.
	 */
	this.saveMarkedStatus = function(data) {
		var ajaxRequest = new AjaxRequest();
		ajaxRequest.openPost('index.php?page=PM&t='+SECURITY_TOKEN+SID_ARG_2ND, data);
	}
	
	/**
	 * Returns a list of the edit options for the edit menu.
	 */
	this.getEditOptions = function(id) {
		var options = new Array();
		var i = 0;
		var pm = this.data.get(id);
		
		if (folderID >= 0 || folderID == -3) {
			// reply
			if (pm.canReply) {
				options[i] = new Object();
				options[i]['function'] = "document.location.href=fixURL('index.php?form=PMNew&pmID="+id+"&reply=1"+SID_ARG_2ND+"')";
				options[i]['text'] = language['wcf.pm.button.reply'];
				i++;
			}
			
			// forwarding
			options[i] = new Object();
			options[i]['function'] = "document.location.href=fixURL('index.php?form=PMNew&pmID="+id+"&forwarding=1"+SID_ARG_2ND+"')";
			options[i]['text'] = language['wcf.pm.button.forward'];
			i++;
			
			if (pm.isViewed) {
				// mark as unread
				options[i] = new Object();
				options[i]['function'] = 'pmListEdit.markAsUnread('+id+');';
				options[i]['text'] = language['wcf.pm.button.markAsUnread'];
				i++;
			}
			else {
				// mark as read
				options[i] = new Object();
				options[i]['function'] = 'pmListEdit.markAsRead('+id+');';
				options[i]['text'] = language['wcf.pm.button.markAsRead'];
				i++;
			}
		}
		
		// download
		options[i] = new Object();
		options[i]['function'] = 'pmListEdit.download('+id+');';
		options[i]['text'] = language['wcf.pm.button.download'];
		i++;
		
		// cancelable / edit unread message
		if (pm.isCancelable) {
			options[i] = new Object();
			options[i]['function'] = 'pmListEdit.cancel('+id+');';
			options[i]['text'] = language['wcf.pm.button.cancel'];
			i++;
			
			options[i] = new Object();
			options[i]['function'] = 'pmListEdit.editUnread('+id+');';
			options[i]['text'] = language['wcf.pm.button.edit'];
			i++;
		}
		
		// edit draft
		if (pm.isEditable) {
			options[i] = new Object();
			options[i]['function'] = 'pmListEdit.editDraft('+id+');';
			options[i]['text'] = language['wcf.pm.button.edit'];
			i++;
		}
	
		// delete
		options[i] = new Object();
		options[i]['function'] = 'pmListEdit.remove('+id+');';
		options[i]['text'] = language['wcf.global.button.delete'];
		i++;
		
		// recover
		if (pm.isRecoverable) {
			options[i] = new Object();
			options[i]['function'] = 'pmListEdit.recover('+id+');';
			options[i]['text'] = language['wcf.pm.button.recover'];
			i++;
		}
		
		// marked status
		var markedStatus = pm ? pm.isMarked : false;
		options[i] = new Object();
		options[i]['function'] = 'pmListEdit.parentObject.markItem(' + (markedStatus ? 'false' : 'true') + ', '+id+');';
		options[i]['text'] = markedStatus ? language['wcf.global.button.unmark'] : language['wcf.global.button.mark'];
		i++;
		
		return options;
	}
	
	/**
	 * Returns a list of the edit options for the edit marked menu.
	 */
	this.getEditMarkedOptions = function() {
		var options = new Array();
		var i = 0;
		
		// download
		options[i] = new Object();
		options[i]['function'] = 'pmListEdit.downloadMarked();';
		options[i]['text'] = language['wcf.pm.button.download'];
		i++;
		
		// move to ...
		if (this.folders.keys().length > 1) {
			var ids = this.folders.keys();
			for (var j = 0; j < ids.length; j++) {
				var folderID = ids[j];
				var title = this.folders.get(folderID);
				options[i] = new Object();
				options[i]['function'] = 'pmListEdit.moveMarkedTo('+folderID+');';
				options[i]['text'] = new StringUtil(language['wcf.pm.button.moveTo']).replace('{$folder}', title);
				i++;
			}
		}
		
		// cancel
		if (folderID == -1) {
			options[i] = new Object();
			options[i]['function'] = 'pmListEdit.cancelMarked();';
			options[i]['text'] = language['wcf.pm.button.cancel'];
			i++;
		}
		
		// delete
		options[i] = new Object();
		options[i]['function'] = 'pmListEdit.removeMarked();';
		options[i]['text'] = language['wcf.global.button.delete'];
		i++;
		
		// unmark all
		options[i] = new Object();
		options[i]['function'] = 'pmListEdit.unmarkAll();';
		options[i]['text'] = language['wcf.global.button.unmark'];
		i++;
		
		return options;
	}
	
	/**
	 * Ummarked all marked pms.
	 */
	this.unmarkAll = function() {
		var ajaxRequest = new AjaxRequest();
		ajaxRequest.openGet('index.php?page=PM&action=unmarkAll&t='+SECURITY_TOKEN+SID_ARG_2ND);
		
		// checkboxes
		this.count = 0;
		var pmDArray = this.data.keys();
		for (var i = 0; i < pmDArray.length; i++) {
			var id = pmDArray[i];
			var pm = this.data.get(id);
		
			pm.isMarked = 0;
			var checkbox = document.getElementById('pmMark' + id);
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
	 * Returns the title for the marked message box.
	 */
	this.getMarkedTitle = function() {
		return eval(language['wcf.pm.markedMessages']);
	}
	
	/**
	 * Deletes a message.
	 */
	this.remove = function(id) {
		if (confirm(language['wcf.pm.delete.sure'])) {
			document.location.href = fixURL('index.php?page=PM&action=delete&pmID='+id+'&folderID='+folderID+'&t='+SECURITY_TOKEN+SID_ARG_2ND);
		}
	}
	
	/**
	 * Deletes the marked messages.
	 */
	this.removeMarked = function() {
		if (confirm(language['wcf.pm.deleteMarked.sure'])) {
			document.location.href = fixURL('index.php?page=PM&action=deleteMarked'+'&folderID='+folderID+'&t='+SECURITY_TOKEN+SID_ARG_2ND);
		}
	}
	
	/**
	 * Starts the download of a message.
	 */
	this.download = function(id) {
		document.location.href = fixURL('index.php?page=PM&action=download&pmID='+id+'&t='+SECURITY_TOKEN+SID_ARG_2ND);
	}
	
	/**
	 * Starts the download of the marked messages.
	 */
	this.downloadMarked = function() {
		document.location.href = fixURL('index.php?page=PM&action=downloadMarked&t='+SECURITY_TOKEN+SID_ARG_2ND);
	}
	
	/**
	 * Moves a message to the specified folder.
	 */
	this.moveTo = function(id, folderID) {
		document.location.href = fixURL('index.php?page=PM&action=moveTo&pmID='+id+'&folderID='+folderID+'&t='+SECURITY_TOKEN+SID_ARG_2ND);
	}
	
	/**
	 * Moves the marked messages to the specified folder.
	 */
	this.moveMarkedTo = function(folderID) {
		document.location.href = fixURL('index.php?page=PM&action=moveMarkedTo&folderID='+folderID+'&t='+SECURITY_TOKEN+SID_ARG_2ND);
	}
	
	/**
	 * Forwards to the edit of a draft.
	 */
	this.editDraft = function(id) {
		document.location.href = fixURL('index.php?form=PMNew&pmID='+id+SID_ARG_2ND);
	}
	
	/**
	 * Forwards to the edit of an unread message.
	 */
	this.editUnread = function(id) {
		document.location.href = fixURL('index.php?page=PM&action=edit&pmID='+id+'&t='+SECURITY_TOKEN+SID_ARG_2ND);
	}
	
	/**
	 * Cancels a message.
	 */
	this.cancel = function(id) {
		if (confirm(language['wcf.pm.cancel.sure'])) {
			document.location.href = fixURL('index.php?page=PM&action=cancel&pmID='+id+'&folderID='+folderID+'&t='+SECURITY_TOKEN+SID_ARG_2ND);
		}
	}
	
	/**
	 * Cancels all marked messages.
	 */
	this.cancelMarked = function() {
		if (confirm(language['wcf.pm.cancelMarked.sure'])) {
			document.location.href = fixURL('index.php?page=PM&action=cancelMarked'+'&folderID='+folderID+'&t='+SECURITY_TOKEN+SID_ARG_2ND);
		}
	}
	
	/**
	 * Recovers a message.
	 */
	this.recover = function(id) {
		document.location.href = fixURL('index.php?page=PM&action=recover&pmID='+id+'&folderID='+folderID+'&t='+SECURITY_TOKEN+SID_ARG_2ND);
	}
	
	/**
	 * Marks a message as read.
	 */
	this.markAsRead = function(id) {
		var pm = this.data.get(id);
		var ajaxRequest = new AjaxRequest();
		ajaxRequest.openGet('index.php?page=PM&action=markAsRead&pmID='+id+'&t='+SECURITY_TOKEN+SID_ARG_2ND);
		pm.isViewed = 1;
		
		// show status
		var icon = document.getElementById('pmEdit'+id);
		if (icon) {
			icon.src = icon.src.replace(/Unread/, 'Read');
			icon.name = icon.name.replace(/Unread/, 'Read');
			icon.title = '';
		}
		
		var column = document.getElementById('pmColumn'+id);
		if (column) column.className = column.className.replace(/ new/, '');
	}
	
	/**
	 * Marks a message as unread.
	 */
	this.markAsUnread = function(id) {
		var pm = this.data.get(id);
		var ajaxRequest = new AjaxRequest();
		ajaxRequest.openGet('index.php?page=PM&action=markAsUnread&pmID='+id+'&t='+SECURITY_TOKEN+SID_ARG_2ND);
		pm.isViewed = 0;
		
		// show status
		var icon = document.getElementById('pmEdit'+id);
		if (icon) {
			icon.src = icon.src.replace(/Read/, 'Unread');
			icon.name = icon.name.replace(/Read/, 'Unread');	
		}
		
		var column = document.getElementById('pmColumn'+id);
		if (column) column.className += ' new';
	}
	
	/**
	 * Shows the status of a message.
	 */
	this.showStatus = function(id) {
		var pm = this.data.get(id);
		
		// get row
		var row = document.getElementById('pmRow'+id);
		
		// update css class
		if (row) {
			// get class
			var className = row.className;
		
			// original className
			if (pm.className != className) {
				className = pm.className;
			}
			
			// marked
			if (pm.isMarked) {
				// add marked class
				className += ' marked';
			}
			
			row.className = className;
		}
	}
	
	/**
	 * Initialises special pm options.
	 */
	this.initItem = function(id) {
		var pm = this.data.get(id);
		
		// init mark as read on double click
		if ((folderID >= 0 || folderID == -3) && !pm.isViewed) {
			var icon = document.getElementById('pmEdit'+id);
			if (icon) {
				icon.pmID = id;
				icon.ondblclick = function() { pmListEdit.markAsRead(this.pmID); }
			}
		}
	}
	
	this.parentObject = new InlineListEdit('pm', this);
}
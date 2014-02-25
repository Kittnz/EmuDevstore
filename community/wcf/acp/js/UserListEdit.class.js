/**
 * @author	Marcel Werk
 * @copyright	2001-2007 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
function UserListEdit(data, count, additionalUserOptions, additionalMarkedOptions) {
	this.data = data;
	this.count = count;
	this.additionalUserOptions = additionalUserOptions;
	this.additionalMarkedOptions = additionalMarkedOptions;
	
	/**
	 * Saves the marked status.
	 */
	this.saveMarkedStatus = function(data) {
		var ajaxRequest = new AjaxRequest();
		ajaxRequest.openPost('index.php?page=UserAction'+SID_ARG_2ND, data);
	}
	
	/**
	 * Returns a list of the edit options for the edit menu.
	 */
	this.getEditOptions = function(id) {
		var options = new Array();
		var i = 0;
		var user = this.data.get(id);
		
		// additional options
		for (var j = 0; j < this.additionalUserOptions.length; j++) {
			options[i] = new Object();
			options[i]['function'] = this.additionalUserOptions[j]['function'].replace(/%s/, id);
			options[i]['text'] = this.additionalUserOptions[j]['text'];
			if (this.additionalUserOptions[j]['className']) options[i]['className'] = this.additionalUserOptions[j]['className'];
			i++;
		}
		
		if (permissions['canDeleteUser']) {
			if (i > 0) {
				options[(i - 1)]['className'] = 'bottomSeparator';
			}
			
			options[i] = new Object();
			options[i]['function'] = 'userListEdit.remove('+id+');';
			options[i]['text'] = language['wcf.global.button.delete'];
			i++;
		}
		
		if (i > 0) {
			options[(i - 1)]['className'] = 'bottomSeparator';
		}
		
		// marked status
		var markedStatus = user ? user.isMarked : false;
		options[i] = new Object();
		options[i]['function'] = 'userListEdit.parentObject.markItem(' + (markedStatus ? 'false' : 'true') + ', '+id+');';
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
		
		// send mail
		if (permissions['canMailUser']) {
			options[i] = new Object();
			options[i]['function'] = 'userListEdit.sendMail();';
			options[i]['text'] = language['wcf.acp.user.button.sendMail'];
			i++;
		
			// export mail address
			options[i] = new Object();
			options[i]['function'] = 'userListEdit.exportMailAddress();';
			options[i]['text'] = language['wcf.acp.user.button.exportMail'];
			i++;
		}
		
		if (permissions['canEditUser']) {
			// assign to user group
			options[i] = new Object();
			options[i]['function'] = 'userListEdit.assignToGroup();';
			options[i]['text'] = language['wcf.acp.user.button.assignGroup'];
			i++;
		}
		
		if (permissions['canDeleteUser']) {
			// delete
			options[i] = new Object();
			options[i]['function'] = 'userListEdit.removeMarked();';
			options[i]['text'] = language['wcf.global.button.delete'];
			i++;
		}
		
		if (i > 0) {
			options[(i - 1)]['className'] = 'bottomSeparator';
		}
		
		// additional options
		for (var j = 0; j < this.additionalMarkedOptions.length; j++) {
			options[i] = new Object();
			options[i]['function'] = this.additionalMarkedOptions[j]['function'];
			options[i]['text'] = this.additionalMarkedOptions[j]['text'];
			if (this.additionalMarkedOptions[j]['className']) options[i]['className'] = this.additionalMarkedOptions[j]['className'];
			i++;
		}
		
		// unmark all
		options[i] = new Object();
		options[i]['function'] = 'userListEdit.unmarkAll();';
		options[i]['text'] = language['wcf.global.button.unmark'];
		i++;
		
		return options;
	}
	
	/**
	 * Ummarked all marked users.
	 */
	this.unmarkAll = function() {
		var ajaxRequest = new AjaxRequest();
		ajaxRequest.openGet('index.php?page=UserAction&action=unmarkAll&packageID='+PACKAGE_ID+SID_ARG_2ND);
		
		// checkboxes
		this.count = 0;
		var userDArray = this.data.keys();
		for (var i = 0; i < userDArray.length; i++) {
			var id = userDArray[i];
			var user = this.data.get(id);
		
			user.isMarked = 0;
			var checkbox = document.getElementById('userMark' + id);
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
	
	this.exportMailAddress = function() {
		document.location.href = fixURL('index.php?form=UserEmailAddressExport&packageID='+PACKAGE_ID+SID_ARG_2ND);
	}
	
	this.sendMail = function() {
		document.location.href = fixURL('index.php?form=UserMail&packageID='+PACKAGE_ID+SID_ARG_2ND);
	}
	
	this.assignToGroup = function() {
		document.location.href = fixURL('index.php?form=UserAssignToGroup&packageID='+PACKAGE_ID+SID_ARG_2ND);
	}
	
	/**
	 * Deletes an user.
	 */
	this.remove = function(id) {
		if (confirm(language['wcf.acp.user.delete.sure'])) {
			document.location.href = fixURL('index.php?action=UserDelete&userID='+id+'&packageID='+PACKAGE_ID+SID_ARG_2ND); // &url='+encodeURIComponent(url)+'
		}
	}
	
	/**
	 * Deletes the marked users.
	 */
	this.removeMarked = function() {
		if (confirm(language['wcf.acp.user.deleteMarked.sure'])) {
			document.location.href = fixURL('index.php?page=UserAction&action=deleteMarked&packageID='+PACKAGE_ID+SID_ARG_2ND);
		}
	}
	
	/**
	 * Returns the title for the marked message box.
	 */
	this.getMarkedTitle = function() {
		return eval(language['wcf.acp.user.markedUsers']);
	}
	
	/**
	 * Shows the status of an user.
	 */
	this.showStatus = function(id) {
		var user = this.data.get(id);
		
		// get row
		var row = document.getElementById('userRow'+id);
		
		// update css class
		if (row) {
			// get class
			var className = row.className;
		
			// original className
			if (user.className != className) {
				className = user.className;
			}
			
			// marked
			if (user.isMarked) {
				// add marked class
				className += ' marked';
			}
			
			row.className = className;
		}
	}
	
	this.parentObject = new InlineListEdit('user', this);
}
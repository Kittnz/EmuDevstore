/**
 * @author	Marcel Werk
 * @copyright	2001-2007 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 */
function SubscribedThreadListEdit(data, count) {
	this.data = data;
	this.count = count;
	
	/**
	 * Saves the marked status.
	 */
	this.saveMarkedStatus = function(data) {
		var ajaxRequest = new AjaxRequest();
		ajaxRequest.openPost('index.php?page=Subscriptions&t='+SECURITY_TOKEN+SID_ARG_2ND, data);
	}
	
	/**
	 * Returns a list of the edit options for the edit menu.
	 */
	this.getEditOptions = function(id) {
		var options = new Array();
		var i = 0;
		var thread = this.data.get(id);
		
		// unsubscribe
		options[i] = new Object();
		options[i]['function'] = 'threadListEdit.unsubcribe('+id+');';
		options[i]['text'] = language['wbb.user.subscriptions.unsubscribe'];
		i++;
		
		// marked status
		var markedStatus = thread ? thread.isMarked : false;
		options[i] = new Object();
		options[i]['function'] = 'threadListEdit.parentObject.markItem(' + (markedStatus ? 'false' : 'true') + ', '+id+');';
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
		
		// unsubscribe all
		options[i] = new Object();
		options[i]['function'] = 'threadListEdit.unsubcribeAll();';
		options[i]['text'] = language['wbb.user.subscriptions.unsubscribe'];
		i++;
		
		// unmark all
		options[i] = new Object();
		options[i]['function'] = 'threadListEdit.unmarkAll();';
		options[i]['text'] = language['wcf.global.button.unmark'];
		i++;
		
		return options;
	}
	
	/**
	 * Returns the title of the edit marked menu.
	 */
	this.getMarkedTitle = function() {
		return eval(language['wbb.user.subscriptions.markedThreads']);
	}
	
	/**
	 * Unsubcribes a thread.
	 */
	this.unsubcribe = function(id) {
		document.location.href = fixURL('index.php?page=Subscriptions&action=unsubscribeThread&threadID='+id+'&url='+encodeURIComponent(url)+'&t='+SECURITY_TOKEN+SID_ARG_2ND);
	}
	
	/**
	 * Unsubcribes all threads.
	 */
	this.unsubcribeAll = function(id) {
		document.location.href = fixURL('index.php?page=Subscriptions&action=unsubscribeMarkedThreads&url='+encodeURIComponent(url)+'&t='+SECURITY_TOKEN+SID_ARG_2ND);
	}
	
	/**
	 * Ummarked all marked threads.
	 */
	this.unmarkAll = function() {
		var ajaxRequest = new AjaxRequest();
		ajaxRequest.openGet('index.php?page=Subscriptions&action=unmarkAll&t='+SECURITY_TOKEN+SID_ARG_2ND);
		
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
			className = className.replace(/ .*/, '');
		
			// marked
			if (thread.isMarked) {
				className += ' marked';
			}
			
			row.className = className;
		}
	}
	
	/**
	 * Does nothing.
	 */
	this.initItem = function(id) {}
	
	this.parentObject = new InlineListEdit('thread', this);
}
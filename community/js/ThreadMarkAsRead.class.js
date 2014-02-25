/**
 * @author	Marcel Werk
 * @copyright	2001-2007 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 */
function ThreadMarkAsRead() {
	this.boardIDs = new Array();
	
	/**
	 * Initialises a board.
	 */
	this.init = function(threadID) {
		// get icon element
		var icon = document.getElementById('threadEdit' + threadID);
		if (icon) {
			// add information
			icon.threadID = threadID;
			
			// add event listener
			icon.ondblclick = function() { threadMarkAsRead.markAsRead(parseInt(this.threadID)); }
		}
	}
	
	/**
	 * Marks the thread as read.
	 */
	this.markAsRead = function(threadID) {
		// get icon element
		var icon = document.getElementById('threadEdit' + threadID);
		
		// mark thread as read
		var ajaxRequest = new AjaxRequest();
		if (ajaxRequest.openGet('index.php?action=ThreadMarkAsRead&threadID='+threadID+'&t='+SECURITY_TOKEN+'&ajax=1'+SID_ARG_2ND)) {
			// change icon
			icon.src = icon.src.replace(/New/, '');
			if (icon.name) icon.name = icon.name.replace(/New/, '');
			// change icon and class name in inline edit data
			if (typeof(threadListEdit) != 'undefined' && threadListEdit.data[threadID]) {
				threadListEdit.data[threadID]['icon'] = threadListEdit.data[threadID]['icon'].replace(/New/, '');
				threadListEdit.data[threadID]['class'] = threadListEdit.data[threadID]['icon'].replace(/ new/, '');
			}
			
			// clear title tag
			icon.title = '';
			
			// get div
			var div = document.getElementById('thread' + threadID);
			if (div) {
				// change class
				div.className = div.className.replace(/ new/, '');
				
				// remove go to first new post link
				var link = document.getElementById('gotoFirstNewPost' + threadID);
				if (link) {
					div.removeChild(link);
				}
			}
		}
	
		// remove event listener
		if (icon) {
			icon.ondblclick = '';
		}
	}
}

var threadMarkAsRead = new ThreadMarkAsRead();
/**
 * @author	Marcel Werk
 * @copyright	2001-2008 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
function SmileyListEdit(data, count, categories) {
	this.data = data;
	this.count = count;
	this.categories = categories;
	
	/**
	 * Saves the marked status.
	 */
	this.saveMarkedStatus = function(data) {
		var ajaxRequest = new AjaxRequest();
		ajaxRequest.openPost('index.php?action=SmileyMark'+SID_ARG_2ND, data);
	}
	
	/**
	 * Returns a list of the edit options for the edit menu.
	 */
	this.getEditOptions = function(id) {
		return new Array();
	}
	
	/**
	 * Returns a list of the edit options for the edit marked menu.
	 */
	this.getEditMarkedOptions = function() {
		var options = new Array();
		var i = 0;
		
		// move
		if (permissions['canEditSmiley'] && this.categories.keys().length > 1) {
			var ids = this.categories.keys();
			for (var j = 0; j < ids.length; j++) {
				var categoryID = ids[j];
				var title = this.categories.get(categoryID);
				options[i] = new Object();
				options[i]['function'] = 'smileyListEdit.moveMarkedTo('+categoryID+');';
				options[i]['text'] = new StringUtil(language['wcf.acp.smiley.button.moveTo']).replace('{$category}', title);
				i++;
			}
		}
		
		// delete
		if (permissions['canDeleteSmiley']) {
			options[i] = new Object();
			options[i]['function'] = 'smileyListEdit.removeMarked();';
			options[i]['text'] = language['wcf.global.button.delete'];
			i++;
		}
		
		if (i > 0) {
			options[(i - 1)]['className'] = 'bottomSeparator';
		}
		
		// unmark all
		options[i] = new Object();
		options[i]['function'] = 'smileyListEdit.unmarkAll();';
		options[i]['text'] = language['wcf.global.button.unmark'];
		i++;
		
		return options;
	}
	
	/**
	 * Ummarked all marked users.
	 */
	this.unmarkAll = function() {
		var ajaxRequest = new AjaxRequest();
		ajaxRequest.openGet('index.php?action=SmileyUnmarkAll&packageID='+PACKAGE_ID+SID_ARG_2ND);
		
		// checkboxes
		this.count = 0;
		var smileyDArray = this.data.keys();
		for (var i = 0; i < smileyDArray.length; i++) {
			var id = smileyDArray[i];
			var smiley = this.data.get(id);
		
			smiley.isMarked = 0;
			var checkbox = document.getElementById('smileyMark' + id);
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
	 * Moves a smiley to the specified category.
	 */
	this.moveTo = function(id, categoryID) {
		document.location.href = fixURL('index.php?action=SmileyMove&smileyID='+id+'&smileyCategoryID='+categoryID+'&packageID='+PACKAGE_ID+SID_ARG_2ND);
	}
	
	/**
	 * Moves the marked smileys to the specified category.
	 */
	this.moveMarkedTo = function(categoryID) {
		document.location.href = fixURL('index.php?action=SmileyMoveMarked&smileyCategoryID='+categoryID+'&packageID='+PACKAGE_ID+SID_ARG_2ND);
	}
	
	/**
	 * Deletes a smiley.
	 */
	this.remove = function(id) {
		if (confirm(language['wcf.acp.smiley.delete.sure'])) {
			document.location.href = fixURL('index.php?action=SmileyDelete&smileyID='+id+'&packageID='+PACKAGE_ID+SID_ARG_2ND);
		}
	}
	
	/**
	 * Deletes the marked smileys.
	 */
	this.removeMarked = function() {
		if (confirm(language['wcf.acp.smiley.deleteMarked.sure'])) {
			document.location.href = fixURL('index.php?action=SmileyDeleteMarked&packageID='+PACKAGE_ID+SID_ARG_2ND);
		}
	}
	
	/**
	 * Returns the title for the marked message box.
	 */
	this.getMarkedTitle = function() {
		return eval(language['wcf.acp.smiley.markedSmileys']);
	}
	
	/**
	 * Shows the status of an user.
	 */
	this.showStatus = function(id) {
		var smiley = this.data.get(id);
		
		// get row
		var row = document.getElementById('smileyRow_'+id);
		
		// update css class
		if (row) {
			// get class
			var className = row.className;
		
			// marked
			if (smiley.isMarked) {
				// add marked class
				className += ' marked';
			}
			else {
				className = className.replace(/ marked/, '');
			}
			
			row.className = className;
		}
	}
	
	// init parent object
	this.parentObject = new InlineListEdit('smiley', this);
}
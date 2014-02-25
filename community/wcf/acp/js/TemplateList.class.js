/**
 * Handles options in template list.
 *
 * @author	Marcel Werk
 * @copyright	2001-2007 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 */
function TemplateList() {
	this.searchField = null;
	this.listField = null;
	this.editButton = null;
	this.deleteButton = null;
	this.lastValue = '';
	
	/**
	 * Initialises the template list options.
	 */
	this.init = function(listField, searchField, editButton, deleteButton, allowMultipleSelection) {
		this.searchField = document.getElementById(searchField);
		this.listField = document.getElementById(listField);
		this.editButton = document.getElementById(editButton);
		this.deleteButton = document.getElementById(deleteButton);
		
		// add event listener
		// search input
		this.searchField.tpl = this;
		this.searchField.onkeyup = function(e) { this.tpl.search(e); };
		
		// list double click
		this.listField.tpl = this;
		if (this.editButton) this.listField.ondblclick = function() { this.tpl.edit(); };
		// list change
		this.listField.onchange = function() { this.tpl.showStatus(); };
		
		// edit button
		if (this.editButton) this.editButton.disabled = true;
		
		// delete button
		if (this.deleteButton) this.deleteButton.disabled = true;
		
		// opera bug fix
		if (allowMultipleSelection) this.listField.setAttribute('multiple', true);
	}
	
	/**
	 * Shows the status of selected templates.
	 */
	this.showStatus = function() {
		var count = 0;
		for (var i = 0; i < this.listField.options.length; i++) {
			if (this.listField.options[i].selected) count++;
		}
		
		// enable / disable edit button
		if (this.editButton) this.editButton.disabled = (count != 1);
		
		// enable / disable delete button
		if (this.deleteButton) this.deleteButton.disabled = (count == 0);
	}
	
	/**
	 * Edits a template.
	 */
	this.edit = function() {
		document.location.href = fixURL('index.php?form=TemplateEdit&templateID='+this.listField.options[this.listField.selectedIndex].value+'&packageID='+PACKAGE_ID+SID_ARG_2ND);
	}
	
	/**
	 * Deletes templates.
	 */
	this.remove = function() {
		this.listField.form.onsubmit = '';
		this.listField.form.submit();
	}
	
	/**
	 * Searches templates.
	 */
	this.search = function(event) {
		if (!event) event = window.event;
		
		// get key code
		if (event.which) {
			var keyCode = event.which;
		}
		else if (event.keyCode) {
			var keyCode = event.keyCode;
		}
		
		// get search query
		var q = this.searchField.value.toLowerCase();
		
		if (q != '' && q != this.lastValue && keyCode != 8 && keyCode != 46) {
			// get results
			var results = new Array();
			for (var i = 0; i < this.listField.options.length; i++) {
				if (this.listField.options[i].text.substr(0, q.length).toLowerCase() == q) {
					if (results.length == 0) this.listField.options[i].selected = true;
					results[results.length] = this.listField.options[i].text;
				}
				else this.listField.options[i].selected = false;
			}
		
			// set last value
			this.lastValue = q;
			
			// do auto complete
			if (results.length > 0) this.autoComplete(results);
		}
		
		this.showStatus();
	}
	
	/**
	 * Auto completes the search input.
	 */
	this.autoComplete = function(results) {
		// calculate new value
		var first = results[0];
		var length = first.length;
		for (var i = 1; i < results.length; i++) {
			while (length > 0) {
				if (first.toLowerCase().substr(0, length) == results[i].toLowerCase().substr(0, length)) break;
				else length--;
			}
			if (length <= 1) break;
		}
	   	var newValue = first.substr(0, length);
	   	
		// set new value
		var oldValueLength = this.searchField.value.length;
	   	this.searchField.value = newValue;
	   	this.searchField.select();
	   	
		// select changed range
	   	if (document.selection) { // internet explorer, opera
			var tplRange = document.selection.createRange();
	   		tplRange.moveStart('character', oldValueLength);
	   		tplRange.select();
		}
		else if (window.getSelection) { // mozilla (gecko), safari (khtml)
			this.searchField.selectionStart = oldValueLength;
		}
	}
}
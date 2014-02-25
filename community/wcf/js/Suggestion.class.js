/**
 * @author	Marcel Werk
 * @copyright	2001-2007 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
function Suggestion() {
	this.inputFields = new Object();
	this.suggestions = new Array();
	this.activeTarget = null;
	this.selectedIndex = -1;
	this.insertAutomatically = false;
	this.forceSuggestion = true;
	this.ajaxRequest;
	this.source = 'index.php?page=UserSuggest'+SID_ARG_2ND;
	this.showIcon = false;
	this.multiple = true;
	this.separator = ',';
	
	/**
	 * Initialises a new suggestion popup.
	 */
	this.init = function(inputFieldID) {
		if (!this.inputFields[inputFieldID]) {
			this.inputFields[inputFieldID] = inputFieldID;
			
			// get input selement
			var element = document.getElementById(inputFieldID);
			
			// set display=block for ie and safari
			if (IS_IE || IS_SAFARI) {
				element.style.display = 'block';
			}
			
			// set autocomplete off
			// TODO: does not work in safari
			element.form.setAttribute('autocomplete', 'off');
			
			// disable submit on return
			element.form.onsubmit = function() { if (suggestion.selectedIndex != -1) return false; };
			
			// create suggestion list div
			var newDiv = document.createElement('div');
			newDiv.id = 'option' + inputFieldID;
			newDiv.className = 'hidden';
			
			// insert new div
			if (element.nextSibling) {
				element.parentNode.insertBefore(newDiv, element.nextSibling);
			}
			else {
				element.parentNode.appendChild(newDiv);
			}
			
			// add event listeners
			element.onkeyup = function(e) { return suggestion.handleInput(e); };
			element.onkeydown = function(e) { return suggestion.handleBeforeInput(e); };
			element.onclick = function(e) { return suggestion.handleClick(e); };
			element.onfocus = function(e) { return suggestion.handleClick(e); };
			element.onblur = function(e) { return suggestion.closeList(); }
		}
	}
	
	/**
	 * By default the user is forced to use a suggestion
	 * By passing false you can disable this behaviour
	 * @param	boolean	forceSuggestion
	 */
	this.setForceSuggestion = function(forceSuggestion) {
		this.forceSuggestion = forceSuggestion;	
	}
	
	/**
	 * Closes all suggestion popups.
	 */
	this.closeAllLists = function(event) {
		// get event
		if (!event) event = window.event;
		
		// get target
		var target = this.getEventTarget(event);
		if (target.type == 'submit') return;
		
		for (var inputFieldID in this.inputFields) {
			if (target.id.indexOf(inputFieldID) == -1) {
				var activeTarget = document.getElementById(inputFieldID);
				this.closeList(activeTarget);
			}
		}
	}
	
	/**
	 * Return the target of a given event.
	 */
	this.getEventTarget = function(event) {
		if (event.target) return event.target;
		else if (event.srcElement) return event.srcElement;
	}
	
	/**
	 * Handles the input event on key down.
	 */
	this.handleBeforeInput = function(event) {
		// get event
		if (!event) event = window.event;
		
		// get key code
		var keyCode = 0;
		if (event.which) keyCode = event.which;
		else if (event.keyCode) keyCode = event.keyCode;
		
		// handle special keys
		// array down
		if (keyCode == 40) return this.moveList('next');
		// array up
		if (keyCode == 38) return this.moveList('previous');
		// escape
		if (keyCode == 27) return this.closeList();
		// return
		if (IS_IE && keyCode == 13) {
			if (this.selectedIndex != -1) {
				this.insertSelectedOption();
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * Inserts the selected suggestion list option.
	 */
	this.insertSelectedOption = function(index, target) {
		if (index == undefined) {
			index = this.selectedIndex;
		}
		
		if (!target) {
			target = this.activeTarget;
		}
	
		if (this.suggestions.length > index && this.suggestions[index]) {
			var cursorStart = this.getCursorPosition(target);
			var start = 0;
			var end = target.value.length;
			
			if (this.multiple) {
				end = target.value.indexOf(this.separator, cursorStart);
				if (end == -1) end = target.value.length;
				
				// search last comma
				for (var i = cursorStart; i >= 0; i--) {
					if (target.value.charAt(i) == this.separator) {
						start = i;
						break;
					}
				}
			}
			
			// search last trailing space
			for (var i = start + 1; i < target.value.length; i++) {
				if (target.value.charAt(i) != ' ') {
					break;
				}
				
				start++;
			}
			
			var newValue = '';
			if (start > 0) newValue += target.value.substring(0, start + 1);
			newValue += this.suggestions[index]['name'];
			newValue += target.value.substring(end);
			target.value = newValue;
			target.select();
			
			// select changed range
		   	if (document.selection) { // internet explorer, opera
				var range = target.createTextRange();
				range.moveStart('character', cursorStart);
				range.moveEnd('character', (start > 0 ? start + 1 : 0) + this.suggestions[index]['name'].length);
				range.select();
			}
			else if (window.getSelection) { // mozilla (gecko), safari (khtml)
				target.selectionStart = cursorStart;
			}
		}
		
		this.closeList(target);
		return false;
	}
	
	/**
	 * Handles the input event on key up.
	 */
	this.handleInput = function(event) {
		// get event
		if (!event) event = window.event;
		
		// get key code
		var keyCode = 0;
		if (event.which) keyCode = event.which;
		else if (event.keyCode) keyCode = event.keyCode;
		
		// get target
		var target = this.getEventTarget(event);
		if (this.activeTarget != target) this.ajaxRequest = null;
		this.activeTarget = target;
		
		// handle special keys
		// array down
		if (keyCode == 40) return false;
		// array up
		if (keyCode == 38) return false;
		// escape
		if (keyCode == 27) return false;
		// backspace
		if (keyCode == 8) return false;
		// return
		if (keyCode == 13) {
			if (this.selectedIndex != -1) {
				this.insertSelectedOption();
				return false;
			}
			
			return true;
		}
		
		// arrow left, arrow right, 
		if (keyCode == 37 || keyCode == 39 || !this.forceSuggestion/* || keyCode == 0*/) {
			this.insertAutomatically = false;
		}
		else {
			this.insertAutomatically = true;
		}
		
		this.getSuggestList(target);
	}
	
	/**
	 * Sets the selected index.
	 */
	this.setSelectedIndex = function(selectedIndex) {
		// remove old selection
		if (this.selectedIndex != -1) {
			var oldElement = document.getElementById('optionList'+this.activeTarget.id+'Element'+this.selectedIndex);
			if (oldElement) oldElement.className = "";
		}
		
		// new selection
		this.selectedIndex = selectedIndex;
		var element = document.getElementById('optionList'+this.activeTarget.id+'Element'+this.selectedIndex);
		if (element) element.className = "active";
	}
	
	/**
	 * Moves to the next or previous element in the suggestion list.
	 */
	this.moveList = function(direction) {
		if (direction == 'next') {
			if (this.selectedIndex + 1 < this.suggestions.length) {
				this.setSelectedIndex(this.selectedIndex + 1);
			}
		}
		else {
			if (this.selectedIndex > 0) {
				this.setSelectedIndex(this.selectedIndex - 1);
			}
		}
		
		return false;
	} 
	
	/**
	 * Handles the mouse click event in the input field.
	 */
	this.handleClick = function(event) {
		this.closeAllLists(event);
		
		// get event
		if (!event) event = window.event;
		
		// get target
		var target = this.getEventTarget(event);
		if (this.activeTarget != target) this.ajaxRequest = null;
		this.activeTarget = target;
		
		this.insertAutomatically = false;
		this.getSuggestList(target);
	}
	
	/**
	 * Opens a new ajax request to get a new suggestion list.
	 */
	this.getSuggestList = function(target) {
		// get active string
		var string = this.getActiveString(target);
		
		// send request
		if (string != '') {
			this.ajaxRequest = new AjaxRequest();
			this.ajaxRequest.openPost(this.source, 'query='+encodeURIComponent(string), function() { suggestion.receiveResponse(); } );
		}
		else {
			this.closeList();
		}
	}
	
	/**
	 * Receives the response of an opened ajax request.
	 */
	this.receiveResponse = function() {
		if (this.ajaxRequest && this.ajaxRequest.xmlHttpRequest.readyState == 4 && this.ajaxRequest.xmlHttpRequest.status == 200 && this.ajaxRequest.xmlHttpRequest.responseXML) {
			this.suggestions = new Array();
			var suggestions = this.ajaxRequest.xmlHttpRequest.responseXML.getElementsByTagName('suggestions');
			if (suggestions.length > 0) {
				for (var i = 0; i < suggestions[0].childNodes.length; i++) {
					if (suggestions[0].childNodes[i].childNodes.length > 0) {
						var id = this.suggestions.length;
						this.suggestions[id] = new Object();
						this.suggestions[id]['name'] = suggestions[0].childNodes[i].childNodes[0].nodeValue;
						this.suggestions[id]['type'] = suggestions[0].childNodes[i].nodeName;
					}
				}
			}
		
			this.showList();
			this.ajaxRequest.xmlHttpRequest.abort();
		}
	}
	
	/**
	 * Shows the suggestion list.
	 */
	this.showList = function() {
		this.closeList();
		
		if (this.suggestions.length > 0 && this.activeTarget) {
			if (this.suggestions.length == 1 && this.insertAutomatically) {
				this.setSelectedIndex(0);
				this.insertSelectedOption();
			}
			else {
				// get option div
				var optionDiv = document.getElementById('option'+this.activeTarget.id);
				if (optionDiv) {
					optionDiv.className = 'pageMenu popupMenu';
					
					// create option list
					var optionList = document.createElement('ul');
					optionList.id = 'optionList'+this.activeTarget.id
					optionDiv.appendChild(optionList);
				
					optionList = document.getElementById('optionList'+this.activeTarget.id);
				
					// add list elements
					for (var i = 0; i < this.suggestions.length; ++i) {
						// create li element
						var optionListElement = document.createElement('li');
						optionListElement.id = 'optionList'+this.activeTarget.id+'Element'+i;
						optionList.appendChild(optionListElement);
						
						// create a element
						var optionListLink = document.createElement('a');
						optionListLink.name = i;
						optionListLink.onmousedown = function() { suggestion.insertSelectedOption(this.name); };
						document.getElementById('optionList'+this.activeTarget.id+'Element'+i).appendChild(optionListLink);
						
						// create icon
						if (this.showIcon) {
							var icon = document.createElement('img');
							icon.src = RELATIVE_WCF_DIR + 'icon/' + this.suggestions[i]['type'] + 'S.png';
							optionListLink.appendChild(icon);
						}
						
						// create text node
						var name = document.createTextNode((this.showIcon ? ' ' : '') + this.suggestions[i]['name']);
						optionListLink.appendChild(name);
					}
				}
				
				this.setSelectedIndex(0);
			}
		}
	}
	
	/**
	 * Closes active suggestion list.
	 */
	this.closeList = function(target) {
		this.selectedIndex = -1;
		
		if (!target) {
			target = this.activeTarget;
		}
		
		if (target) {
			var optionDiv = document.getElementById('option'+target.id);
			if (optionDiv) {
				// remove children
				var optionList = document.getElementById('optionList'+target.id);
				if (optionList) {
					optionDiv.removeChild(optionList);
				}
			
				// change class to hidden
				optionDiv.className = 'hidden';
			}
		}
	}
	
	/**
	 * Returns the active string.
	 */
	this.getActiveString = function(target) {
		var cursorPosition = this.getCursorPosition(target);
		
		var string = target.value.substring(0, cursorPosition);
		
		if (this.multiple) {
			var parts = string.split(this.separator);
			if (parts.length > 0) {
				string = parts[parts.length - 1];
				
			}
			else string = '';
		}
		
		// trim string
		string = string.replace(/^\s+/, '');
		string = string.replace(/\s+$/, '');
		
		return string;
	}
	
	/**
	 * Returns the current cursor position.
	 */
	this.getCursorPosition = function(target) {
		var cursorPosition = target.value.length;
		
		if (typeof target.selectionStart == 'number') {
			cursorPosition = target.selectionStart;
		}
		else if (typeof document.selection.createRange() == 'object') {
			var range = document.selection.createRange();
			range.moveEnd('textedit', 1);
			var last = String(range.text);
			cursorPosition = cursorPosition - last.length;
		}
		
		return cursorPosition;
	}
	
	/**
	 * Sets the source.
	 */
	this.setSource = function(newSource) {
		this.source = newSource;
	}
	
	/**
	 * Enables the display of suggestion icons.
	 */
	this.enableIcon = function(enable) {
		this.showIcon = enable;
	}
	
	/**
	 * Enables the input of multiple (by default komma separated) items.
	 */
	this.enableMultiple = function(enable) {
		this.multiple = enable;
	}
	
	/**
	 * Sets the separator for multiple inputs.
	 */
	this.setSeparator = function(separator) {
		this.separator = separator;
	}
}

var suggestion = new Suggestion();
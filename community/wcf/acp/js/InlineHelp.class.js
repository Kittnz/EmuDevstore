/**
 * @author	Marcel Werk
 * @copyright	2001-2007 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
function InlineHelp() {
	this.helpMessages = new Array();
	this.interactiveMessage = false;
	this.status = '';
	
	this.register = function() {
		for (var i = 0; i < arguments.length; i++) {
			this.initMessage(arguments[i]);
			this.helpMessages[this.helpMessages.length] = arguments[i];
		}
	}
	
	this.initMessage = function(id) {
		// add event listener
		// input field
		var input = document.getElementById(id);
		if (input) {
			if (true) {
				input.onfocus = function() {
					inlineHelp.showInteractiveMessage(this.id, true);
				}
				input.onblur = function() {
					inlineHelp.showInteractiveMessage(this.id, false);
				}
			}
		}
		
		// input div
		var inputDiv = document.getElementById(id+'Div');
		if (inputDiv) {
			inputDiv.name = id;
			inputDiv.onmouseover = function() {
				inlineHelp.showInteractiveMessage(this.name, true);
			}
			inputDiv.onmouseout = function() {
				inlineHelp.showInteractiveMessage(this.name, false);
			}
		}
	}
	
	this.showInteractiveMessage = function(id, show) {
		if (this.interactiveMessage) {
			this.showMessage(id, show);
		}
	}
	
	this.showMessage = function(id, show) {
		var div = document.getElementById(id+'HelpMessage');
		if (div) {
			div.className = show ? 'formFieldDesc' : 'hidden';
		}
	}
	
	this.showAllMessages = function(show) {
		for (var i = 0; i < this.helpMessages.length; i++) {
			this.showMessage(this.helpMessages[i], show);
		}
	}
	
	this.enableInteractiveHelp = function() {
		if (this.status == 'interactive') return;
		
		this.showAllMessages(false);
		this.interactiveMessage = true;
		var linkInteractive = document.getElementById('helpLinkInteractive');
		if (linkInteractive) linkInteractive.className = 'active';
		var linkDisable = document.getElementById('helpLinkDisable');
		if (linkDisable) linkDisable.className = '';
		var linkComplete = document.getElementById('helpLinkComplete');
		if (linkComplete) linkComplete.className = '';
		
		this.saveStatus('interactive');
	}
	
	this.disableHelp = function() {
		if (this.status == 'disable') return;
		
		this.showAllMessages(false);
		this.interactiveMessage = false;
		var linkInteractive = document.getElementById('helpLinkInteractive');
		if (linkInteractive) linkInteractive.className = '';
		var linkDisable = document.getElementById('helpLinkDisable');
		if (linkDisable) linkDisable.className = 'active';
		var linkComplete = document.getElementById('helpLinkComplete');
		if (linkComplete) linkComplete.className = '';
		
		this.saveStatus('disable');
	}
	
	this.enableHelp = function() {
		if (this.status == 'all') return;
		
		this.showAllMessages(true);
		this.interactiveMessage = false;
		var linkInteractive = document.getElementById('helpLinkInteractive');
		if (linkInteractive) linkInteractive.className = '';
		var linkDisable = document.getElementById('helpLinkDisable');
		if (linkDisable) linkDisable.className = '';
		var linkComplete = document.getElementById('helpLinkComplete');
		if (linkComplete) linkComplete.className = 'active';
		
		this.saveStatus('all');
	}
	
	this.saveStatus = function(status) {
		if (this.status != '') saveStatus('inlineHelpStatus', status);
		this.status = status;
	}
	
	this.setStatus = function(newStatus) {
		switch (newStatus) {
			case 'interactive':
				this.enableInteractiveHelp();
				break;
			case 'disable':
				this.disableHelp();
				break;
			default:
				this.enableHelp();
		}		
	}
}

var inlineHelp = new InlineHelp();
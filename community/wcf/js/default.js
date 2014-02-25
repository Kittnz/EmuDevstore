/**
 * @author	Marcel Werk
 * @copyright	2001-2007 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
// define some global constants
// browser types
var USER_AGENT 		= navigator.userAgent.toLowerCase();
var IS_OPERA 		= (USER_AGENT.indexOf('opera') != -1);
var IS_SAFARI 		= ((USER_AGENT.indexOf('applewebkit') != -1) || (navigator.vendor == 'Apple Computer, Inc.'));
var IS_SAFARI_MOBILE	= (IS_SAFARI && ((USER_AGENT.indexOf('iphone') != -1) || (USER_AGENT.indexOf('ipod') != -1)));
var IS_IE		= ((USER_AGENT.indexOf('msie') != -1) && !IS_OPERA && !IS_SAFARI);
var IS_IE7		= false;
var IS_IE6		= false;
if (IS_IE) {
	if (!IS_OPERA && window.XMLHttpRequest) IS_IE7 = true;
	else IS_IE6 = true;
}
var IS_MOZILLA		= ((navigator.product == 'Gecko') && !IS_SAFARI);
var IS_KONQUEROR	= (USER_AGENT.indexOf('konqueror') != -1);

/**
 * Handle multiple onload events
 * usage: onloadEvents.push(function() {...});
 */
var onloadEvents = new Array();
window.onload = function() {
	for (var i = 0; i < onloadEvents.length; i++)
		onloadEvents[i]();
}

// add method for strings
Object.extend(String.prototype, {
	firstCharToUpperCase: function() {
		return this.slice(0, 1).toUpperCase() + this.slice(1, this.length);
	}
});


/**
 * Fixes a prototype bug
 */
Object.extend(Element.Methods,{
	 getDimensions: function(element) {
		element = $(element);
		var display = element.getStyle('display');
		if (display != 'none' && display != null) // Safari bug
			return {width: element.offsetWidth, height: element.offsetHeight};
  
			// All *Width and *Height properties give 0 on elements with display none,
			// so enable the element temporarily
			var els = element.style;
			var originalVisibility = els.visibility;
			var originalDisplay = els.display;
			els.visibility = 'hidden';
			els.display = 'block';
			var originalWidth = element.clientWidth;
			var originalHeight = element.clientHeight;
			els.display = originalDisplay;
			els.visibility = originalVisibility;
			return {width: originalWidth, height: originalHeight}; 
	 }
});

Element.addMethods();

/**
 * Enables options in form dialog.
 */
function enableOptions() {
	for (var i = 0; i < arguments.length; i++) {
		var element = document.getElementById(arguments[i] + 'Div');
			
		if (element) {
			// enable form elements 
			enableFormElements(element, true);
			
			// change class
			var className = element.className;
			className = className.replace(/ ?disabled/, '');
			className = className.replace(/disabled ?/, '');
			
			element.className = className;
		}
	}
	
	return true;
}

/**
 * Enables form elements of an option.
 */
function enableFormElements(parent, enable) {
	if (!parent.childNodes) {
		return;
	}
	
	for (var i = 0; i < parent.childNodes.length; i++) {
		var name = parent.childNodes[i].nodeName;
		if (name) name = name.toLowerCase();
		if (name == 'textarea' || name == 'input' || name == 'select') {
			if (name == 'select' || parent.childNodes[i].type == 'checkbox' || parent.childNodes[i].type == 'radio') {
				parent.childNodes[i].disabled = !enable;
			}
			else {
				if (enable) parent.childNodes[i].removeAttribute('readonly');
				else parent.childNodes[i].setAttribute('readonly', true);
			}
		}
		enableFormElements(parent.childNodes[i], enable);
	}
}

/**
 * Disables options in form dialog.
 */
function disableOptions() {
	for (var i = 0; i < arguments.length; i++) {
		var element = document.getElementById(arguments[i] + 'Div');
		
		if (element) {
			// disable form elements 
			enableFormElements(element, false);
			
			// change class
			var className = element.className;
			
			if (className.indexOf('disabled') == -1) {
				if (className != '') {
					className += ' ';
				}
				className += 'disabled';
				element.className = className;
			}
		}
	}
	
	return true;
}

var animatingList = false;

/**
 * Opens or closes a html list of elements. By default the current status 
 * (open/close) will be inverted, use the setVisible option to prevent that
 *
 * @param	string		listName
 * @param	array		options		optional parameters
 *		boolean		save		should the status be saved via ajax
 *		string		openTitle	the title of an open image
 *		string		closeTitle	the title of a close image
 *		boolean		setVisible	if this is set the list keeps its status
 *		function	afterOpen	callback function thats executed after opening the list
 *		function	afterClose	callback function thats executed after closing the list
 */
function openList(listName, options) {
	var options = Object.extend({
		save: false,
		openTitle: null,
		closeTitle: null,
		setVisible: null,
		afterOpen: null,
		afterClose: null
	}, arguments[1] || { });
	
	if (animatingList) {
		return false;
	}
	else {
		var element = $(listName);
		if (element == null) {
			return false;
		}
		
		if (Prototype.Browser.IE) {
			element.setStyle({ zoom: 1 });	
		}
		
		var status = 0;
		var elementHeight = element.getHeight();
		if (element.visible()) {
			if (options.setVisible == null || (options.setVisible != null && options.setVisible == false)) {
				// close list
				element.blindUp({
					beforeStart: function() {
						animatingList = true;
					},
					afterFinish: function() {
						var image = $(listName + 'Image');
						if (image) {
							image.src = image.src.replace(/minus(S|M|L)/, 'plus$1');
							if (options.openTitle) image.title = options.openTitle;
						}
						animatingList = false;
						if (options.afterClose) {
							options.afterClose();	
						}
					},
					duration: Math.sqrt(elementHeight)/40
				});
			}	
		}
		else {
			if (options.setVisible == null || (options.setVisible != null && options.setVisible == true)) {
				// open list
				status = 1;
				element.blindDown({
					beforeStart: function() {
						animatingList = true;
					},
					afterFinish: function() {
						var image = $(listName + 'Image');
						if (image) {
							image.src = image.src.replace(/plus(S|M|L)/, 'minus$1');
							if (options.closeTitle) image.title = options.closeTitle;
						}
						animatingList = false;
						if (options.afterOpen) {
							options.afterOpen();	
						}
					},
					duration: Math.sqrt(elementHeight)/40
				});
			}
		}
		
		// save new status in session (use ajax)
		if (options.save) saveStatus(listName, status);
		
		return true;
	}
}

/**
 * Saves the status of a hidden user option.
 */
function saveStatus(varname, value) {
	var ajaxRequest = new AjaxRequest();
	ajaxRequest.openPost('index.php?action=StatusSave'+SID_ARG_2ND, 'name='+encodeURIComponent(varname)+'&status='+encodeURIComponent(value));
}

function initList(listName, status) {
	if (!status) {
		var element = document.getElementById(listName);
		element.style.display = 'none';
		document.getElementById(listName + 'Image').src = document.getElementById(listName + 'Image').src.replace(/minus(S|M|L)/, 'plus$1');
	}
}

/**
 * Shows a hidden content box.
 */
function showContent(content, link) {
	document.getElementById(link).style.display = 'none';
	document.getElementById(content).style.display = '';
	
	return;
}

/**
 * Fixes a browser bug with relative URLs ignoring the <base> tag.
 * Thanks to microsoft and opera ;)
 */
function fixURL(url) {
	if (IS_IE || IS_OPERA) {
		if (url.indexOf("/") == -1 && document.getElementsByTagName('base').length > 0) {
			return document.getElementsByTagName('base')[0].href + url;
		}
	}
	
	return url;
}

/**
* Wrapper for Editor function call to avoid js errors when no editor exists
*/
function WysiwygInsert(type, value, title, code) {
	if (typeof(tinyMCE) != 'undefined') {
		if (!tinyMCE.initialized) {
			window.setTimeout(function() {
				WysiwygInsert(type, value, title, code);
			}, 500);
			return;
		}
		
		if (type == 'smiley') tinyMCE.insertSmiley(value, title, code);
		else if (type == 'attachment') tinyMCE.insertAttachment(value);
		else if (type == 'text') tinyMCE.insertText(value);
		else if (type == 'bbcode') tinyMCE.insertBBCodes(value);
	}
	else {
		// TODO: insert plain text !? (to do this the function "insertCode" got to be in this default.js)
	}
}

/**
 * Inline delete can be used to execute a delete action directly in place without an extra page load (using ajax)
 * The element that should be deleted needs to have a class name "deletable"
 * The element should include a button <a href="url2deleteAction" class="deleteButton"><img src="" longdesc="confirmMessage" ...
 */
function inlineDelete(deleteButton) {
	// if it is not a normal element, it must be an event object
	if (!(!!deleteButton && deleteButton.nodeType === 1)) {
		var a = deleteButton.findElement('a');
		var evt = deleteButton;
	}
	else {
		var a = deleteButton;
	}
	var img = a.down('img');
	var desc = img.readAttribute('longdesc');
	if (desc == null) {
		desc = LANG_DELETE_CONFIRM;
	}
	if (desc == "") {
		desc = null;
	}
	
	if (a && desc) {
		if (confirm(desc)) {
			a.up('.deletable').fade({ 
				duration: 0.5, 
				afterFinish: function() { 
					a.up('.deletable').remove();
					document.fire('wcf:inlineDelete');
				} 
			});
			var ajaxRequest = new AjaxRequest();
			ajaxRequest.openGet(a.href.gsub('&nbsp;', '&') + '&ajax=1');
		}
		else {
			document.fire('wcf:inlineDelete');
		}
	} 
	else if (a) {
		a.up('.deletable').fade({ 
				duration: 0.5, 
				afterFinish: function() { 
					a.up('.deletable').remove();
					document.fire('wcf:inlineDelete');
				} 
			});
		var ajaxRequest = new AjaxRequest();
		ajaxRequest.openGet(a.href.gsub('&nbsp;', '&') + '&ajax=1');
	}
	else {
		document.fire('wcf:inlineDelete');
	}
	if (evt) evt.stop();
}

// add functions or listeners that should be loaded after the dom has been loaded
document.observe("dom:loaded", function() {				
	$$('.deletable .deleteButton').invoke('observe', 'click', inlineDelete);
});
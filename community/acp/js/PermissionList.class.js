/**
 * @author	Marcel Werk
 * @copyright	2001-2007 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 */
function PermissionList(key, data, settings) {
	this.key = key;
	this.data = data;
	this.settings = settings;
	this.selectedIndex = -1;
	this.ajaxRequest;
	this.inputHasFocus = false;
	
	/**
	 * Initialises the permission list.
	 */
	this.init = function() {
		// add button listener
		var button = $(this.key + 'AddButton');
		if (button) {
			button.observe('click', function(event) {
  				this.add();
  			}.bind(this));
		}
		
		// add input listener
		var input = $(this.key + 'AddInput');
		if (input) {
			input.observe('focus', function(event) {
  				this.inputHasFocus = true;
  			}.bind(this));
  			input.observe('blur', function(event) {
  				this.inputHasFocus = false;
  			}.bind(this));
  			input.observe('keyup', function(event) {
				if (!event) event = window.event;
			
				// get key code
				var keyCode = 0;
				if (event.which) keyCode = event.which;
				else if (event.keyCode) keyCode = event.keyCode;
				
				// return
				if (keyCode == 13) {
					this.add();
				}
			}.bind(this));
		}
		
		// refresh data list
		this.refresh();
	}
	
	/**
	 * Refreshes the complete list.
	 */
	this.refresh = function() {
		// get data div
		var dataDiv = document.getElementById(this.key);
		if (dataDiv) {
			// remove old content
			while (dataDiv.childNodes.length > 0) {
				dataDiv.removeChild(dataDiv.childNodes[0]);
			}
			
			// create list
			if (this.data.length > 0) {
				// create ul
				var ul = document.createElement('ul');
				dataDiv.appendChild(ul);
				
				for (var i = 0; i < this.data.length; i++) {
					// create li
					var li = document.createElement('li');
					li.id = this.key + i;
					if (i == this.selectedIndex) li.className = 'selected';
					ul.appendChild(li);
					
					// create remove link
					var removeLink = new Element('a', { 'class': 'remove' });
					removeLink.observe('click', function(i, event) {
						this.remove(i);
					}.bind(this, i));
					li.appendChild(removeLink);
					
					// create remove link image
					var removeImage = document.createElement('img');
					removeImage.src = RELATIVE_WCF_DIR + 'icon/deleteS.png';
					removeLink.appendChild(removeImage);
					
					// create a
					var a = new Element('a');
					a.observe('click', function(i, event) {
						this.setSelectedIndex(i);
					}.bind(this, i));
					li.appendChild(a);
					
					// create image
					var img = document.createElement('img');
					img.src = RELATIVE_WCF_DIR + 'icon/'+this.data[i]['type']+'S.png';
					a.appendChild(img);
					
					// create title
					var title = document.createTextNode(this.data[i]['name']);
					a.appendChild(title);
				}
			}
		}
	}
	
	/**
	 * Refreshes the checkboxes.
	 */
	this.refreshSettings = function() {
		var settingsDiv = document.getElementById(this.key + 'Settings');
		if (settingsDiv) {
			// remove old content
			while (settingsDiv.childNodes.length > 0) {
				settingsDiv.removeChild(settingsDiv.childNodes[0]);
			}
			
			// create ul
			var ul = document.createElement('ul');
			settingsDiv.appendChild(ul);
				
			for (var setting in this.data[this.selectedIndex]['settings']) {
				// create li
				var li = document.createElement('li');
				ul.appendChild(li);
				
				// create a
				var a = document.createElement('a');
				li.appendChild(a);
				
				// checkbox (deny)
				// label
				var labelDeny = document.createElement('label');
				labelDeny.className = 'deny';
				a.appendChild(labelDeny);
				
				var checkboxDeny = new Element('input', { 'type': 'checkbox', 'id': this.key + 'Setting' + setting + 'Deny' });
				checkboxDeny.observe('click', function(setting, event) {
					this.deny(setting, event.findElement().checked);
				}.bind(this, setting));
				labelDeny.appendChild(checkboxDeny);
				if (this.data[this.selectedIndex]['settings'][setting] == 0) checkboxDeny.checked = true;

				// checkbox (allow)
				// label
				var labelAllow = document.createElement('label');
				labelAllow.className = 'allow';
				a.appendChild(labelAllow);
				
				var checkboxAllow = new Element('input', { 'type': 'checkbox', 'id': this.key + 'Setting' + setting + 'Allow' });
				checkboxAllow.observe('click', function(setting, event) {
					this.allow(setting, event.findElement().checked);
				}.bind(this, setting));
				labelAllow.appendChild(checkboxAllow);
				if (this.data[this.selectedIndex]['settings'][setting] == 1) checkboxAllow.checked = true;

				// create span
				var span = new Element('span');
				span.observe('mouseup', function(id, event) {
					$(id).focus();
				}.bind(this, this.key + 'Setting' + setting + 'Allow'));
				a.appendChild(span);
				
				// title
				var title = document.createTextNode(language['wbb.acp.board.permissions.'+setting]);
				span.appendChild(title);
			}

			this.checkFullControl();
		}
	}
	
	/**
	 * Removes an user or a group from the list.
	 */
	this.remove = function(index) {
		this.data.splice(index, 1);
		this.refresh();
		
		if (this.selectedIndex == index) this.setSelectedIndex(-1);
		else if (this.selectedIndex > index) this.setSelectedIndex(this.selectedIndex - 1);
	}
	
	/**
	 * Receives a click on a allow full control checkbox.
	 */
	this.allowFullControl = function(checked) {
		for (var setting in this.data[this.selectedIndex]['settings']) {
			if (setting == 'fullControl') continue;
			this.data[this.selectedIndex]['settings'][setting] = (checked ? 1 : -1);
		}
	}
	
	/**
	 * Receives a click on a deny full control checkbox.
	 */
	this.denyFullControl = function(checked) {
		for (var setting in this.data[this.selectedIndex]['settings']) {
			if (setting == 'fullControl') continue;
			this.data[this.selectedIndex]['settings'][setting] = (checked ? 0 : -1);
		}
	}
	
	/**
	 * Receives a click on a allow checkbox.
	 */
	this.allow = function(setting, checked) {
		if (setting == 'fullControl') this.allowFullControl(checked);
		else this.data[this.selectedIndex]['settings'][setting] = (checked ? 1 : -1);
			
		this.refreshSettings();
	}
	
	/**
	 * Receives a click on a deny checkbox.
	 */
	this.deny = function(setting, checked) {
		if (setting == 'fullControl') this.denyFullControl(checked);
		else this.data[this.selectedIndex]['settings'][setting] = (checked ? 0 : -1);
		
		this.refreshSettings();
	}
	
	/**
	 * Checks whether all allow or deny boxes are selected.
	 */
	this.checkFullControl = function() {
		var value = undefined;
		
		for (var setting in this.data[this.selectedIndex]['settings']) {
			if (setting == 'fullControl') continue;
			if (value == undefined) value = this.data[this.selectedIndex]['settings'][setting];
			else {
				if (value != this.data[this.selectedIndex]['settings'][setting]) {
					value = -1; break;
				}
			}
		}
		
		document.getElementById(this.key + 'SettingfullControlAllow').checked = (value == 1);
		document.getElementById(this.key + 'SettingfullControlDeny').checked = (value == 0);
	}
	
	/**
	 * Sets the selected list index.
	 */
	this.setSelectedIndex = function(index) {
		if (this.selectedIndex != -1) {
			// disable selected item
			var li = document.getElementById(this.key + this.selectedIndex);
			if (li) li.className = '';
		}
		
		this.selectedIndex = index;
		if (this.selectedIndex == -1) {
			this.showSettings(false);
		}
		else {
			var li = document.getElementById(this.key + this.selectedIndex);
			if (li) li.className = 'selected';
			
			// draw title
			var h3 = document.getElementById(this.key + 'SettingsTitle');
			if (h3) {
				// remove old content
				while (h3.childNodes.length > 0) {
					h3.removeChild(h3.childNodes[0]);
				}
				
				var title = document.createTextNode(language['wbb.acp.board.permissions.permissionsFor'].replace(/\{\$name\}/, this.data[this.selectedIndex]['name']));
				h3.appendChild(title);
			}
			
			// draw settings
			this.refreshSettings();
			this.showSettings(true);
		}
	}
	
	/**
	 * Shows/Hides the permission list.
	 */
	this.showSettings = function(show) {
		document.getElementById(this.key + 'Settings').parentNode.parentNode.style.display = (show ? '' : 'none');
	}
	
	/**
	 * Adds a new user or a new group to the list.
	 */
	this.add = function() {
		var query = new StringUtil(document.getElementById(this.key + 'AddInput').value).trim();
		
		if (query) {
			var activePermissionList = this;
			this.ajaxRequest = new AjaxRequest();
			this.ajaxRequest.openPost('index.php?page=BoardPermissionsObjects'+SID_ARG_2ND, 'query='+encodeURIComponent(query), function() { activePermissionList.receiveResponse(); });
		}
	}
	
	/**
	 * Receives the response of an opened ajax request.
	 */
	this.receiveResponse = function() {
		if (this.ajaxRequest && this.ajaxRequest.xmlHttpRequest.readyState == 4 && this.ajaxRequest.xmlHttpRequest.status == 200 && this.ajaxRequest.xmlHttpRequest.responseXML) {
			var objects = this.ajaxRequest.xmlHttpRequest.responseXML.getElementsByTagName('objects');
			if (objects.length > 0) {
				var firstNewKey = -1;
				for (var i = 0; i < objects[0].childNodes.length; i++) {
					// get name
					var name = objects[0].childNodes[i].childNodes[0].childNodes[0].nodeValue;
					
					// get type
					var type = objects[0].childNodes[i].childNodes[1].childNodes[0].nodeValue;  
					
					// get id
					var id = objects[0].childNodes[i].childNodes[2].childNodes[0].nodeValue;  
					
					var doBreak = false;
					for (var j = 0; j < this.data.length; j++) {
						if (this.data[j]['id'] == id && this.data[j]['type'] == type) doBreak = true;
					}
					
					if (doBreak) continue;
					
					var key = this.data.length;
					if (firstNewKey == -1) firstNewKey = key;
					this.data[key] = new Object();
					this.data[key]['name'] = name;
					this.data[key]['type'] = type;
					this.data[key]['id'] = id;
					this.data[key]['settings'] = new Object();
					this.data[key]['settings']['fullControl'] = -1;
					
					for (var j = 0; j < this.settings.length; j++) {
						this.data[key]['settings'][this.settings[j]] = -1;
					}
				}
				
				document.getElementById(this.key + 'AddInput').value = '';
				this.refresh();
				
				// select first new item automatically
				if (this.selectedIndex == -1 && firstNewKey != -1) {
					this.setSelectedIndex(firstNewKey);
				}
			}
		
			this.ajaxRequest.xmlHttpRequest.abort();
		}
	}
	
	/**
	 * Saves the selected permissions in hidden input fields.
	 */
	this.submit = function(form) {
		for (var i = 0; i < this.data.length; i++) {
			// general
			var typeField = document.createElement('input');
			typeField.type = 'hidden';
			typeField.name = this.key + '[' + i + '][type]';
			typeField.value = this.data[i]['type'];
			form.appendChild(typeField);
			
			var idField = document.createElement('input');
			idField.type = 'hidden';
			idField.name = this.key + '[' + i + '][id]';
			idField.value = this.data[i]['id'];
			form.appendChild(idField);
			
			var nameField = document.createElement('input');
			nameField.type = 'hidden';
			nameField.name = this.key + '[' + i + '][name]';
			nameField.value = this.data[i]['name'];
			form.appendChild(nameField);
			
			// settings
			for (var setting in this.data[i]['settings']) {
				if (setting == 'fullControl') continue;
				var settingField = document.createElement('input');
				settingField.type = 'hidden';
				settingField.name = this.key + '[' + i + '][settings][' + setting + ']';
				settingField.value = this.data[i]['settings'][setting];
				form.appendChild(settingField);
			}
		}
	}

	this.init();
}
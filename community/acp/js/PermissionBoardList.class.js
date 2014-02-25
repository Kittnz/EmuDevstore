/**
 * @author	Marcel Werk
 * @copyright	2001-2007 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 */
function PermissionBoardList(boardStructure, globalPermissions, boardPermissions, type) {
	this.boardStructure = boardStructure;
	this.globalPermissions = globalPermissions;
	this.boardPermissions = boardPermissions;
	this.activePermission = '';
	this.type = type;
	
	/**
	 * Initialises the board list.
	 */
	this.init = function() {
		// add onchange listener
		var permissionSelect = $('permissionName');
		permissionSelect.observe('change', function(event) {
			this.setActivePermission(event.findElement().options[event.findElement().selectedIndex].value);
		}.bind(this));
		
		// add onclick listener
		this.addOnclickListener(0);
	}
	
	/**
	 * Sets the name of the selected permission.
	 */
	this.setActivePermission = function(permissionName) {
		if (permissionName) {
			this.activePermission = permissionName;
			
			// refresh checkbox status
			this.refreshSettings(permissionName, 0);
			
			// refresh status icon
			this.refreshStatus(permissionName, 0, -1);
			
			// make visible
			this.showBoardList(true);
		}
		else {
			this.activePermission = '';
			this.showBoardList(false);
		}
	}
	
	/**
	 * Refreshes the checkboxes.
	 */
	this.refreshSettings = function(permissionName, parentID) {
		if (!this.boardStructure[parentID]) return;
		
		for (var i = 0; i < this.boardStructure[parentID].length; i++) {
			var boardID = this.boardStructure[parentID][i];
			
			// get setting
			var value = this.getBoardPermission(permissionName, boardID);
			
			// show setting
			document.getElementById('allow'+boardID).checked = (value == 1 ? true : false);
			document.getElementById('deny'+boardID).checked = (value == 0 ? true : false);
			
			// refresh children
			this.refreshSettings(permissionName, boardID);
		}
	}
	
	/**
	 * Refreshes the status icon.
	 */
	this.refreshStatus = function(permissionName, parentID, parentValue) {
		if (!this.boardStructure[parentID]) return;
		
		for (var i = 0; i < this.boardStructure[parentID].length; i++) {
			var boardID = this.boardStructure[parentID][i];
			
			// get setting
			var value = this.getBoardPermission(permissionName, boardID);
			var inheritValue = value;
			
			// take parent
			if (value == -1) {
				if (parentValue != -1) {
					value = parentValue;
					inheritValue = value;
				}
				else {
					value = this.getGlobalPermission(permissionName, boardID);
				}
			}
			
			// show status
			document.getElementById('status'+boardID).src = (value == 1 ? RELATIVE_WCF_DIR + 'icon/enabledS.png' : RELATIVE_WCF_DIR + 'icon/disabledS.png');
			
			// refresh children
			this.refreshStatus(permissionName, boardID, inheritValue);
		}
	}
	
	/**
	 * Returns the value of a board permission.
	 */
	this.getBoardPermission = function(permissionName, boardID) {
		var value = -1;
		if (permissionName == 'fullControl') {
			var globalPermissions = this.getGlobalPermissions(boardID);
			for (permission in globalPermissions) {
				if (this.boardPermissions[boardID] && this.boardPermissions[boardID][permission] == 1) var newValue = 1;
				else if (this.boardPermissions[boardID] && this.boardPermissions[boardID][permission] == 0) var newValue = 0;
				else {
					value = -1;
					break;
				}
				
				if (value == -1) value = newValue;
				else if (value != newValue) {
					value = -1;
					break;
				}
			}
		}
		else {
			if (this.boardPermissions[boardID] && this.boardPermissions[boardID][permissionName] == 1) value = 1;
			else if (this.boardPermissions[boardID] && this.boardPermissions[boardID][permissionName] == 0) value = 0;
		}
		
		return value;
	}
	
	/**
	 * Adds the onclick listener to the checkboxes.
	 */
	this.addOnclickListener = function(parentID) {
		if (!this.boardStructure[parentID]) return;
		
		for (var i = 0; i < this.boardStructure[parentID].length; i++) {
			var boardID = this.boardStructure[parentID][i];
			
			// add listener
			var allow = $('allow'+boardID);
			allow.observe('click', function(boardID, event) {
				this.allow(boardID, event.findElement().checked);
			}.bind(this, boardID));
			
			var deny = $('deny'+boardID);
			deny.observe('click', function(boardID, event) {
				this.deny(boardID, event.findElement().checked);
			}.bind(this, boardID));
			
			// refresh children
			this.addOnclickListener(boardID);
		}
	}
	
	/**
	 * Receives a click on a allow checkbox.
	 */
	this.allow = function(boardID, checked) {
		if (!this.boardPermissions[boardID]) this.boardPermissions[boardID] = new Object();
		if (this.activePermission == 'fullControl') {
			this.allowFullControl(boardID, checked);
		}
		else {
			this.boardPermissions[boardID][this.activePermission] = (checked ? 1 : -1);
		}
		this.refresh();
	}
	
	/**
	 * Allows all permissions for a board.
	 */
	this.allowFullControl = function(boardID, checked) {
		var globalPermissions = this.getGlobalPermissions(boardID);
		for (permission in globalPermissions) {
			this.boardPermissions[boardID][permission] = (checked ? 1 : -1);
		}
	}
	
	/**
	 * Denies all permissions for a board.
	 */
	this.denyFullControl = function(boardID, checked) {
		var globalPermissions = this.getGlobalPermissions(boardID);
		for (permission in globalPermissions) {
			this.boardPermissions[boardID][permission] = (checked ? 0 : -1);
		}
	}
	
	/**
	 * Receives a click on a deny checkbox.
	 */
	this.deny = function(boardID, checked) {
		if (!this.boardPermissions[boardID]) this.boardPermissions[boardID] = new Object();
		if (this.activePermission == 'fullControl') {
			this.denyFullControl(boardID, checked);
		}
		else {
			this.boardPermissions[boardID][this.activePermission] = (checked ? 0 : -1);
		}
		this.refresh();
	}
	
	/**
	 * Refreshes the complete list.
	 */
	this.refresh = function() {
		this.setActivePermission(this.activePermission);
	}
	
	/**
	 * Makes the board list visible.
	 */
	this.showBoardList = function(show) {
		document.getElementById('boardList').style.display = (show ? '' : 'none');
	}
	
	/**
	 * Saves the selected permissions in hidden input fields.
	 */
	this.submit = function(form) {
		for (var boardID in this.boardPermissions) {
			for (var permissionName in this.boardPermissions[boardID]) {
				var typeField = document.createElement('input');
				typeField.type = 'hidden';
				typeField.name = 'boardPermissions[' + boardID + '][' + permissionName + ']';
				typeField.value = this.boardPermissions[boardID][permissionName];
				form.appendChild(typeField);
			}
		}
	}
	
	/**
	 * Returns the list of global permissions.
	 */
	this.getGlobalPermissions = function(boardID) {
		if (type == 'group') return this.globalPermissions;
		else return this.globalPermissions[boardID];
	}
	
	/**
	 * Returns the value of a global permission.
	 */
	this.getGlobalPermission = function(permissionName, boardID) {
		var globalPermissions = this.getGlobalPermissions(boardID);
		var value = -1;
		
		if (permissionName == 'fullControl') {
			for (permission in globalPermissions) {
				if (globalPermissions[permission] == 1) var newValue = 1;
				else if (globalPermissions[permission] == 0) var newValue = 0;
				else {
					value = -1;
					break;
				}
				
				if (value == -1) value = newValue;
				else if (value != newValue) {
					value = -1;
					break;
				}
			}
		}
		else {
			value = globalPermissions[permissionName];
		}
		
		return value;
	}
	
	this.init();
}
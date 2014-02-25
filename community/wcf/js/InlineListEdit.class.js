/**
 * @author	Marcel Werk
 * @copyright	2001-2007 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
function InlineListEdit(key, callbackObject) {
	this.key = key;
	this.callbackObject = callbackObject;
	
	/**
	 * Initialises the inline list edit.
	 */
	this.init = function() {
		
		// init mark all checkbox
		var markAllCheckboxes = $$('input[name="' + this.key + 'MarkAll' + '"]');
		var markAll = this.areAllMarked();
		markAllCheckboxes.each(function(checkbox) {
			checkbox.editor = this;
			checkbox.observe('click', function() { this.editor.markAll(this.checked); });
			checkbox.checked = markAll;
		}, this);
		
		// mark checkboxes
		var idArray = this.callbackObject.data.keys();
		for (var i = 0; i < idArray.length; i++) {
			var id = idArray[i];
			var dataObject = this.callbackObject.data.get(id);
			
			// handle mark checkbox
			var checkbox = document.getElementById(this.key + 'Mark' + id);
			if (checkbox) {
				checkbox.value = id;
				checkbox.editor = this;
				checkbox.checked = this.isMarked(id);
				checkbox.onclick = function() { this.editor.mark(this); }
			}
							
			// edit icon
			var icon = document.getElementById(this.key + 'Edit' + id);
			if (icon) {
				icon.editor = this;
				icon.onclick = function(e) { this.editor.edit(e, this); }
				
				// update icon
				if (icon.src != undefined) {
					// add options tag
					icon.src = icon.src.replace(/(?=(?:S|M|L|XL)\.png$)/, 'Options');
					
					// add hover 
					dataObject.icon = icon.src;
					icon.name = icon.src.replace(/[a-z0-9-_]*(?=(?:S|M|L|XL)\.png$)/i, this.key + 'EditOptions');
					icon.onmouseover = function() {
						this.style.cursor = 'pointer';
						var tempSrc = this.src;
						this.src = this.name;
						this.name = tempSrc;
					}
					icon.onmouseout = function() {
						var tempSrc = this.src;
						this.src = this.name;
						this.name = tempSrc;
					}
				}
				
				// create popup menu div
				var menuDiv = document.createElement('div');
				menuDiv.id = this.key + 'Edit' + id + 'Menu';
				menuDiv.className = 'hidden';
				icon.parentNode.appendChild(menuDiv);
				popupMenuList.register(this.key + 'Edit' + id);
			}
			
			// init item
			if (this.callbackObject.initItem) this.callbackObject.initItem(id);
			
			// show status
			if (this.callbackObject.showStatus) this.callbackObject.showStatus(id);
		}
		
		// edit marked items icon
		this.showMarked();
	}
	
	/**
	 * Shows the edit menu for the marked items.
	 */
	this.showMarked = function() {
		var showDiv = document.getElementById(this.key + 'EditMarked');
		
		if (showDiv) {
			if (showDiv.firstChild) {
				showDiv.removeChild(showDiv.firstChild);
			}
			
			if (this.callbackObject.count > 0) {
				// menu ul
				var menuUL = document.createElement('ul');
				showDiv.appendChild(menuUL);
				
				// menu li
				var menuLI = document.createElement('li');
				menuLI.className = 'options';
				menuUL.appendChild(menuLI);
				
				// menu a
				var menuA = document.createElement('a');
				menuA.editor = this;
				menuA.onclick = function(e) { this.editor.editMarked(e, this); }
				menuA.id = this.key + 'EditMarkedLink';
				menuLI.appendChild(menuA);
				
				// menu span
				var menuSpan = document.createElement('span');
				menuA.appendChild(menuSpan);
				menuSpan.appendChild(document.createTextNode(this.callbackObject.getMarkedTitle()));
				
				// space
				menuA.appendChild(document.createTextNode(' '));
				
				// drop down image
				var menuImg = document.createElement('img');
				menuImg.src = RELATIVE_WCF_DIR + 'icon/dropDownMenuS.png';
				menuA.appendChild(menuImg);
				
				// drop down menu div
				var menuDiv = document.createElement('div');
				menuDiv.id = this.key + 'EditMarkedLinkMenu';
				menuDiv.className = 'hidden';
				menuLI.appendChild(menuDiv);
				popupMenuList.register(this.key + 'EditMarkedLink');
			}
		}
	}
	
	
	/**
	 * Marks/Unmarks all list items on the current page.
	 */
	this.markAll = function(checked) {
		var ids = new Array();
		
		var j = 0;
		var idArray = this.callbackObject.data.keys();
		for (var i = 0; i < idArray.length; i++) {
			var id = idArray[i];
			var checkbox = document.getElementById(this.key + 'Mark' + id);
			if (checkbox) {
				checkbox.checked = checked;
				ids[j] = id;
				j++;
			}
		}
		
		this.checkMarkAll(checked);
		this.setMarked(checked, ids);
	}
	
	/**
	 * Sets the status of the mark all checkboxes.
	 */
	this.checkMarkAll = function(checked) {
		var markAllCheckboxes = document.getElementsByName(this.key + 'MarkAll');
		for (var i = 0; i < markAllCheckboxes.length; i++) {
			markAllCheckboxes[i].checked = checked;
		}
	}
	
	/**
	 * Marks/Unmarks one list item.
	 */
	this.markItem = function(mark, id, ignoreCheckbox) {
		if (mark) {
			this.setMarked(mark, id);
			
			// check mark all checkbox if necessary
			if (this.areAllMarked()) {
				this.checkMarkAll(true);
			}
		}
		else {
			this.setMarked(mark, id);
			
			// uncheck mark all checkbox if necessary
			this.checkMarkAll(false);
		}
		
		if (!ignoreCheckbox) {
			var checkbox = document.getElementById(this.key + 'Mark' + id);
			if (checkbox) {
				checkbox.checked = mark;
			}
		}
	}
	
	/**
	 * Marks/Unmarks one list item by checkbox click.
	 */
	this.mark = function(checkbox) {
		this.markItem(checkbox.checked, checkbox.value, true);
	}
	
	/**
	 * Returns true, if the given item is marked.
	 */
	this.isMarked = function(id) {
		var dataObject = this.callbackObject.data.get(id);
		if (dataObject) {
			return dataObject.isMarked;
		}
		
		return false;
	}
	
	/**
	 * Sets the marked status.
	 */
	this.setMarked = function(mark, ids) {
		if (isFinite(ids)) {
			this.setMarkedStatus(mark, ids);
		}
		else {
			for (var i = 0; i < ids.length; i++) {
				this.setMarkedStatus(mark, ids[i]);
			}
		}
		
		this.saveMarkedStatus(mark, ids);
		this.showMarked();
	}
	
	/**
	 * Saves the marked status by ajax.
	 */
	this.saveMarkedStatus = function(mark, ids) {
		var data = 'action='+(mark ? 'mark' : 'unmark');
		if (isFinite(ids)) {
			data += '&'+this.key+'ID='+ids;
		}
		else {
			for (var i = 0; i < ids.length; i++) {
				data += '&'+this.key+'ID[]='+ids[i];
			}
		}
		
		this.callbackObject.saveMarkedStatus(data);
	}
	
	/**
	 * Sets the marked status for one item.
	 */
	this.setMarkedStatus = function(mark, id) {
		var dataObject = this.callbackObject.data.get(id);
		if (dataObject) {
			if (mark && !dataObject.isMarked) {
				dataObject.isMarked = 1;
				this.callbackObject.count++;
			}
			else if (!mark && dataObject.isMarked) {
				dataObject.isMarked = 0;
				this.callbackObject.count--;
			}
			
			this.callbackObject.showStatus(id);
		}
	}
	
	/**
	 * Returns true, if all items on the current page are marked.
	 */
	this.areAllMarked = function() {
		var idArray = this.callbackObject.data.keys();
		if (idArray.length == 0) {
			return false;
		}
		
		for (var i = 0; i < idArray.length; i++) {
			var id = idArray[i];
			var dataObject = this.callbackObject.data.get(id);
			if (!dataObject.isMarked) {
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * Shows the edit menu for one list item.
	 */
	this.edit = function(event, element) {
		var id = element.id.replace(/[^\d]+/, '');
		var options = this.callbackObject.getEditOptions(id);
		this.createMenu(options, element, this.key + 'Edit' + id + 'Menu');
	}
	
	/**
	 * Shows the edit menu for the marked items.
	 */
	this.editMarked = function(event, element) {
		var options = this.callbackObject.getEditMarkedOptions();
		this.createMenu(options, element, this.key + 'EditMarkedLinkMenu');
	}
	
	/**
	 * Creates a new edit menu.
	 */
	this.createMenu = function(options, parentElement, id) {
		// get menu div
		var menuDiv = document.getElementById(id);
		
		// remove old elements
		while (menuDiv.hasChildNodes()) {
			menuDiv.removeChild(menuDiv.firstChild);
		}
		
		// menu ul
		var menuUL = document.createElement('ul');
		menuDiv.appendChild(menuUL);
		
		// menu elements
		for (var i = 0; i < options.length; i++) {
			var menuLI = document.createElement('li');
			menuUL.appendChild(menuLI);
			if (options[i]['className']) menuLI.className = options[i]['className'];
			
			var menuA = document.createElement('a');
			menuA.href = 'javascript:'+options[i]['function'];
			menuLI.appendChild(menuA);
			menuA.appendChild(document.createTextNode(options[i]['text']));
		}
	}
	
	this.init();
}
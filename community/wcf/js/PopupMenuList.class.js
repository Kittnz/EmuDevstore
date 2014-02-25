/**
 * @author	Marcel Werk
 * @copyright	2001-2007 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
function PopupMenuList() {
	this.menus = new Array();
	this.menuBarOpen = false;
	this.hiddenSelects = new Array();
	
	// set event listeners
	window.onresize = function() { popupMenuList.closeAllMenus() }
	document.onclick = function(e) { return popupMenuList.handleClickEvents(e); }
	
	
	/**
	 * Registers a new popup menu.
	 */
	this.register = function(id) {
		for (var i = 0; i < this.menus.length; i++) {
			if (this.menus[i] == id) {
				return false;
			}
		}

		this.menus.push(id);
		return true;
	}
	
	/**
	 * Closes all popup menus.
	 */
	this.closeAllMenus = function() {
		for (var i = 0; i < this.menus.length; i++) {
			var popupMenu = document.getElementById(this.menus[i] + "Menu");
			if (!popupMenu) {
				this.menus.slice(i, 1);
				continue;
			}
			
			popupMenu.className = "hidden";
		}
		
		this.menuBarOpen = false;
	}
	
	/**
	 * Listens to all mouse click events.
	 */
	this.handleClickEvents = function(event) {
		if (!event) event = window.event;
		var target;
		
		if (event.target) target = event.target;
		else if (event.srcElement) target = event.srcElement;
		if (target.nodeType == 3) {// defeat Safari bug
			target = target.parentNode;
		}
	
		var id = target.getAttribute("id");
		if ((id == null || !id) && target.parentNode && target.parentNode.getAttribute)  {
			id = target.parentNode.getAttribute("id");
		}
		
		this.enable(id);
	}
	
	/**
	 * Returns the offset of an element.
	 */
	this.getOffset = function(element) {
		var offsetLeft = element.offsetLeft;
		var offsetTop = element.offsetTop;
		
		while ((element = element.offsetParent) != null) {
			offsetLeft += element.offsetLeft;
			offsetTop += element.offsetTop;
		}
		
		return {'left' : offsetLeft, 'top' : offsetTop};
	}
	
	/**
	 * Enables a popup menu.
	 */
	this.enable = function(id, mouseOver) {
		this.showSelects();
		this.menuBarOpen = false;
		for (var i = 0; i < this.menus.length; i++) {
			var popupMenu = document.getElementById(this.menus[i] + "Menu");
			if (!popupMenu) {
				this.menus.slice(i, 1);
				continue;
			}
			
			if (this.menus[i] == id) {
				if (popupMenu.className != 'popupMenu pageMenu') {
					// get parent height
					var parentHeight = popupMenu.parentNode ? popupMenu.parentNode.offsetHeight : 0;
					
					// get inner window height
					var windowInnerHeight = window.innerHeight ? window.innerHeight : (document.documentElement.clientHeight ? document.documentElement.clientHeight : document.body.clientHeight);
					
					// get inner window width
					var windowInnerWidth = window.innerWidth ? window.innerWidth : (document.documentElement.clientWidth ? document.documentElement.clientWidth : document.body.clientWidth);
										
					// init sliding
					// no sliding in opera because of bad performance :(
					var slidingDirection = 'down';
					if (!mouseOver && !IS_OPERA) {
						popupMenu.style.clip = 'rect(auto, auto, 0, auto)';
					}
					else {
						popupMenu.style.clip = 'rect(auto, auto, auto, auto)';
					}
					
					// make popup visible
					popupMenu.className = "popupMenu pageMenu";
					
					// add some IE specials ;)
					if (IS_IE) {
						popupMenu.style.filter = "progid:DXImageTransform.Microsoft.alpha(enabled=1,opacity=90)";
						popupMenu.style.filter += "progid:DXImageTransform.Microsoft.shadow(direction=135,color=#8e8e8e,strength=3)";
					}
					
					// set menu bar status
					if (id.indexOf('menuBar') != -1) this.menuBarOpen = true;
					
					// check top offset
					popupMenu.style.marginTop = '';
					var scrollBottom = (window.pageYOffset ? window.pageYOffset : (document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop)) + windowInnerHeight;
					var offset = this.getOffset(popupMenu);
					if ((offset['top'] - popupMenu.offsetHeight - parentHeight) >= 0 && (offset['top'] + popupMenu.offsetHeight >= scrollBottom || (IS_IE && parentHeight > 0 && id.indexOf('menuBar') == -1 && offset['top'] - popupMenu.offsetHeight > 0))) {
					//if (offset['top'] + popupMenu.offsetHeight >= scrollBottom) {
						popupMenu.style.marginTop = (-popupMenu.offsetHeight - parentHeight)+'px';
						
						// change sliding direction
						slidingDirection = 'up';
						if (!mouseOver && !IS_OPERA) {
							popupMenu.style.clip = 'rect('+popupMenu.offsetHeight+'px, auto, auto, auto)';
						}
					}
					
					// hide overlaped selects in IE
					this.hideSelects(popupMenu);
					
					// check left offset
					if (offset['left'] + popupMenu.offsetWidth >= windowInnerWidth) {
						popupMenu.style.right = '0';
					}
					
					// start sliding
					if (!mouseOver && !IS_OPERA) {
						var interval = parseInt(popupMenu.offsetHeight / 8);
						this.slide(this.menus[i] + "Menu", 0, interval, slidingDirection);
					}
					
					continue;
				}
				else if (mouseOver) {
					if (id.indexOf('menuBar') != -1) this.menuBarOpen = true;
					continue;
				}
			}
			
			// close popup
			if (popupMenu.className != "hidden") popupMenu.className = "hidden";
		}
	}
	
	/**
	 * Slides a popup menu.
	 */
	this.slide = function(id, size, interval, direction) {
		var popupMenu = document.getElementById(id);
		
		if (size > popupMenu.offsetHeight) {
			size = popupMenu.offsetHeight;
		}
		
		if (direction == 'down') {
			popupMenu.style.clip = 'rect(auto, auto, '+size+'px, auto)';
		}
		else {
			popupMenu.style.clip = 'rect('+(popupMenu.offsetHeight - size)+'px, auto, auto, auto)';
		}
		
		if (size >= popupMenu.offsetHeight) {
			return;
		}
		
		size += interval;
		setTimeout("popupMenuList.slide('"+id+"', "+size+", "+interval+", '"+direction+"')", 10);
	}
	
	/**
	 * Returns true, if the menu element overlaps the select element.
	 */
	this.overlaps = function(menuPosition, selectPosition) {
		if (	selectPosition['left'] > menuPosition['right']
			|| selectPosition['right'] < menuPosition['left']
			|| selectPosition['top'] > menuPosition['bottom']
			|| selectPosition['bottom'] < menuPosition['top']) {
				return false;
		}
		return true;
	}
	
	/**
	 * Returns the position of an element.
	 */
	this.getPositionArray = function(element) {
		var offset = this.getOffset(element);
		offset['right'] = offset['left'] + element.offsetWidth;
		offset['bottom'] = offset['top'] + element.offsetHeight;
		
		return offset;
	}
	
	/**
	 * Hides by a popup menu overlaped selects in IE.
	 */
	this.hideSelects = function(popupMenu) {
		if (!IS_IE6) {
			return;
		}
		
		var selects = document.getElementsByTagName('select');
		if (selects.length > 0) {
			var menuPosition = this.getPositionArray(popupMenu);
			for (var i = 0; i < selects.length; i++) {
				var selectPosition = this.getPositionArray(selects[i]);
				if (this.overlaps(menuPosition, selectPosition)) {
					selects[i].style.visibility = 'hidden';
					this.hiddenSelects.push(selects[i]);
				}
			}
		}
	}
	
	/**
	 * Shows hidden selects.
	 */
	this.showSelects = function() {
		if (!IS_IE) {
			return;
		}
	
		while (this.hiddenSelects.length > 0) {
			var select = this.hiddenSelects.shift();
			if (select) {
				select.style.visibility = 'visible';
			}
		}
	}
}

var popupMenuList = new PopupMenuList();
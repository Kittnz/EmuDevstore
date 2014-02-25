/**
 * @author	Marcel Werk
 * @copyright	2001-2007 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
function ACPMenu() {
	this.menuItems = new Object();
	this.activeMenuItems;
	this.sitemapOpen = false;
	
	/**
	 * Initialises the acp menu.
	 */ 
	this.init = function(menuItemData, activeMenuItems) {
		// handle menu data
		this.activeMenuItems = activeMenuItems;
		
		for (var i = 0; i < menuItemData.length; i++) {
			if (!this.menuItems[menuItemData[i][0]]) {
				this.menuItems[menuItemData[i][0]] = new Array();
			}
			
			var menuObject = new Object();
			menuObject['menuItem'] = menuItemData[i][1];
			menuObject['menuItemName'] = menuItemData[i][2];
			menuObject['menuItemLink'] = menuItemData[i][3];
			menuObject['menuItemIcon'] = menuItemData[i][4];
			
			this.menuItems[menuItemData[i][0]][this.menuItems[menuItemData[i][0]].length] = menuObject;
		}
		
		// set event listeners
		onloadEvents.push(function() {
			acpMenu.showActiveMenu();
			
			var sitemapButton = document.getElementById('sitemapButton');
			if (sitemapButton) {
				sitemapButton.onclick = function() { 
					if (!acpMenu.sitemapOpen) {
						acpMenu.openSitemap();
						sitemapButton.className = 'menuActive';
					} else {
						acpMenu.closeSitemap();
						sitemapButton.className = '';
					}
				}
			}
		});
	}

	/**
	 * Renders a menu bar.
	 *
	 * @param	string	parentMenuItem
	 */
	this.showMenuBar = function(parentMenuItem) {
		// set tab
		this.activeTab(parentMenuItem);
		
		// get menu bar node
		var ul = document.getElementById('menuBar').firstChild.firstChild;
		
		// remove obsolute children
		while (ul.childNodes.length > 0) {
			ul.removeChild(ul.childNodes[0]);
		}
		
		// build menu bar
		if (this.menuItems[parentMenuItem]) {
			for (var i = 0; i < this.menuItems[parentMenuItem].length; i++) {
				// li
				var li = document.createElement('li');
				ul.appendChild(li);
				if (this.isActiveMenuItem(this.menuItems[parentMenuItem][i]['menuItem'])) li.className = 'active';
				
				// a
				var a = document.createElement('a');
				li.appendChild(a);
				a.id = 'menuBar'+this.menuItems[parentMenuItem][i]['menuItem'];
				if (this.menuItems[parentMenuItem][i]['menuItemLink'] != '') {
					a.href = this.menuItems[parentMenuItem][i]['menuItemLink'];
				}
				else {
					a.onmouseover = function() {
						if (popupMenuList.menuBarOpen) popupMenuList.enable(this.id, true);
					};
				}
				
				// icon
				if (this.menuItems[parentMenuItem][i]['menuItemIcon'] != '') {
					var icon = document.createElement('img');
					a.appendChild(icon);
					a.appendChild(document.createTextNode(' '));
					icon.src = this.menuItems[parentMenuItem][i]['menuItemIcon'];
				}
				
				// span
				var span = document.createElement('span');
				a.appendChild(span);
				if (this.menuItems[parentMenuItem][i]['menuItemIcon'] != '') span.className = 'menuBarImage';
				span.appendChild(document.createTextNode((new StringUtil(this.menuItems[parentMenuItem][i]['menuItemName'])).decodeHTML()));

				// add children
				if (this.menuItems[parentMenuItem][i]['menuItemLink'] == '') {
					var div = document.createElement('div');
					div.className = 'hidden';
					li.appendChild(div);
					div.id = 'menuBar'+this.menuItems[parentMenuItem][i]['menuItem']+'Menu';
					
					// register popup
					popupMenuList.register('menuBar'+this.menuItems[parentMenuItem][i]['menuItem']);
					
					// create popup item
					this.createMenuPopup(this.menuItems[parentMenuItem][i]['menuItem'], div);
					
					
				}
			}
		}
		
		return;
	}
	
	/**
	 * Creates a new menu popup.
	 *
	 * @param	string	parentMenuItem
	 * @return	string	html code of the popup
	 */
	this.createMenuPopup = function(parentMenuItem, parentNode) {
		var ul = document.createElement('ul');
		parentNode.appendChild(ul);
		var separatorBefore = true;
		
		if (this.menuItems[parentMenuItem]) {
			for (var i = 0; i < this.menuItems[parentMenuItem].length; i++) {
				if (this.menuItems[this.menuItems[parentMenuItem][i]['menuItem']]) {
					var separator = !separatorBefore ? 'topSeparator' : '';
					
					for (var j = 0; j < this.menuItems[this.menuItems[parentMenuItem][i]['menuItem']].length; j++) {
						if (j + 1 == this.menuItems[this.menuItems[parentMenuItem][i]['menuItem']].length && i + 1 < this.menuItems[parentMenuItem].length) {
							if (separator != '') separator += ' ';
							separator += 'bottomSeparator';
						}
						
						this.createMenuPopupLink(this.menuItems[this.menuItems[parentMenuItem][i]['menuItem']][j], separator, ul);
						if (separator != '') separatorBefore = true;
						separator = '';
					}
				}
				else {
					this.createMenuPopupLink(this.menuItems[parentMenuItem][i], '', ul);
					separatorBefore = false;
				}
			}
		}
	}
	
	/**
	 * Creates a menu popup link.
	 *
	 * @param	string	menuItem
	 * @param	string	separator
	 * @return	string	html code of the link
	 */
	this.createMenuPopupLink = function(menuItem, separator, parentNode) {
		var className = separator;
		if (this.isActiveMenuItem(menuItem['menuItem'])) {
			if (className != '') className += ' ';
			className += 'active';
		}
		
		// li
		var li = document.createElement('li');
		parentNode.appendChild(li);
		if (className != '') li.className = className;
		
		// a
		var a = document.createElement('a');
		li.appendChild(a);
		a.href = menuItem['menuItemLink'];
		
		// icon
		if (menuItem['menuItemIcon'] != '') {
			var icon = document.createElement('img');
			a.appendChild(icon);
			icon.src = menuItem['menuItemIcon'];
			a.appendChild(document.createTextNode(' '));
		}
		
		// span
		var span = document.createElement('span');
		a.appendChild(span);
		span.appendChild(document.createTextNode(new StringUtil(menuItem['menuItemName']).decodeHTML()));
	}
	
	/**
	 * Returns true, if the given menu item is active.
	 *
	 * @return	boolean
	 */
	this.isActiveMenuItem = function(menuItem) {
		for (var i = 0; i < activeMenuItems.length; i++) {
			if (menuItem == activeMenuItems[i]) {
				return true;
			}
		}
	}
	
	/**
	 * Shows the active menu bar.
	 */
	this.showActiveMenu = function() {
		if (activeMenuItems.length > 0) {
			var topMenuItem = activeMenuItems[activeMenuItems.length - 1];
			this.showMenuBar(topMenuItem);
		}
	}
	
	/**
	 * Enables the menu tab with the given name.
	 *
	 * @param	string	menuItem
	 */
	this.activeTab = function(menuItem) {
		if (this.menuItems['']) {
			for (var i = 0; i < this.menuItems[''].length; i++) {
				var menuElement = document.getElementById('tab' + this.menuItems[''][i]['menuItem']);
				if (this.menuItems[''][i]['menuItem'] == menuItem) {
					menuElement.className = 'active';
				}
				else {
					menuElement.className = '';
				}
			}
		}
	}
	
	/**
	 * Opens the sitemap.
	 */
	this.openSitemap = function() {
		this.sitemapOpen = true;
		
		// create div
		var div = document.createElement('div');
		div.id = 'sitemap';
		div.className = 'sitemap';
		var contentDiv = document.getElementById('content');
		if (contentDiv) {
			contentDiv.parentNode.appendChild(div);
			contentDiv.className = 'hidden';
			this.makeSitemap(div, '', 0);
		}
	}
	
	/**
	 * Closes the sitemap.
	 */
	this.closeSitemap = function() {
		var sitemapDiv = document.getElementById('sitemap');
		if (sitemapDiv) {
			sitemapDiv.parentNode.removeChild(sitemapDiv);
			var contentDiv = document.getElementById('content');
			if (contentDiv) contentDiv.className = '';
		}
		this.sitemapOpen = false;
	}
	
	/**
	 * Makes the nodes of the sitemap.
	 * 
	 * @param	node		parent node
	 * @param	string		parent item
	 * @return	integer		depth of list
	 */
	this.makeSitemap = function(parentNode, parentItem, depth) {
		if (!this.menuItems[parentItem]) return;
		
		var ul = document.createElement('ul');
		
		if (depth == 2) {
			ul.className = 'sitemapDepth-' + depth + ' container-1';
		}
		else {
			ul.className = 'sitemapDepth-' + depth;
		}
		parentNode.appendChild(ul);
		for (var i = 0; i < this.menuItems[parentItem].length; i++) {
			if (depth == 1 && i > 0 && i % 3 == 0) {
				var div = document.createElement('div');
				div.className = 'clear';
				ul.appendChild(div);
			}
			this.makeSitemapItem(ul, this.menuItems[parentItem][i], depth);
		}
	}
	
	/**
	 * Makes the node of a sitemap item.
	 * 
	 * @param	node		parent node
	 * @param			item
	 * @return	integer		level, depth of list
	 */
	this.makeSitemapItem = function(parentNode, item, depth) {
		if (item['menuItemLink'] != '' || depth < 2) {
			var itemTitle = (new StringUtil(item['menuItemName'])).decodeHTML();
			var li = document.createElement('li');
			parentNode.appendChild(li);
			parentNode = li;
			
			var headline = document.createElement('h' + (depth + 2));
			if (depth == 1) {
				var headlineContainer = document.createElement('div');
				headlineContainer.className = 'containerHead';
				headlineContainer.appendChild(headline);
				li.appendChild(headlineContainer);
				li.className += ' border';
			}
			else {
				li.appendChild(headline);
			}
			
			// icon
			if (item['menuItemIcon'] != '') {
				var img = document.createElement('img');
				img.src = item['menuItemIcon'];
				headline.appendChild(img);
			}
			
			// link
			if (item['menuItemLink'] != '') {
				var a = document.createElement('a');
				a.href = item['menuItemLink'];
				headline.appendChild(a);
				a.appendChild(document.createTextNode(itemTitle));
			}
			else {
				var span = document.createElement('span');
				headline.appendChild(span);
				span.appendChild(document.createTextNode(itemTitle));
			}
		}
		
		this.makeSitemap(parentNode, item['menuItem'], depth + 1);
	}
}

var acpMenu = new ACPMenu();
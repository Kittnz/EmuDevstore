/**
 * @author	Marcel Werk, Arian Glander
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
var TabMenu = Class.create({
	/**
	 * Init TabMenu
	 * @param array	options	optional array holding the paths to icon files (only needed if there are more tabs then tabmenu can display)
	 */
	initialize: function() {
		this.options = Object.extend({
			imgPrevious : 		'',
			imgPreviousDisabled :	'',
			imgNext : 		'',
			imgNextDisabled : 	''
		}, arguments[0] || { });
		
		this.activeTabMenuItem = '';
		this.activeSubTabMenuItem = '';
		this.activeSubTabMenuItems = new Object();
	},
	
	/**
	 * Check the width of the tabmenu to find out if there is enough space to display all tabs
	 * Quite dirty solution -> can be rewritten in 1.2
	 */
	checkWidth: function() {
		var menu = $(this.activeTabMenuItem).up('.tabMenu');
		var tabWidth = 0;
		var tabs = new Array();
		var tooWide = false;
		var activeIndex = 0;

		// save all tabs and their width in an array
		// find out if there are to many tabs to display
		// get index of currently active tab
		menu.select('li').each(function(li, i) {
			var width = li.getWidth() + (li.getStyle('margin-left') ? parseInt(li.getStyle('margin-left').gsub('px', '')) : 0) + (li.getStyle('margin-right') ? parseInt(li.getStyle('margin-right').gsub('px', '')) : 0);
			tabs.push([li, width]);
			if (menu.getWidth() > tabWidth + width + 50) {
				tabWidth += width;
			} 
			else {
				tooWide = true;
			}
			if (li.hasClassName('activeTabMenu')) {
				activeIndex = i;
			}
		}, this);
		
		// if there is not enough space
		if (tooWide) {
			var tabWidth = 0;
			var showTabsLeft = true;
			var showTabsRight = true;
			
			// go through all tabs and hide those that are not near by active tab
			for (var i = 0; i <= tabs.length; i++) {
				var tab = null;
				// show left
				if (tabs[activeIndex - i]) {
					tab = tabs[activeIndex - i];
					if (showTabsLeft && menu.getWidth() > tabWidth + tab[1] + 50) {
						tabWidth += tab[1];
						tab[0].show();
					}
					else {
						showTabsLeft = false;
						tab[0].hide();
					}
				}
				// show right
				if (tabs[activeIndex + i] && tabs[activeIndex + i] != tab) {
					tab = tabs[activeIndex + i];
					if (showTabsRight && menu.getWidth() > tabWidth + tab[1] + 50) {
						tabWidth += tab[1];
						tab[0].show();
					}
					else {
						showTabsRight = false;
						tab[0].hide();
					}
				}
			}
			
			// insert tab navigation
			if (!menu.down('.tabNavigation')) {
				menu.insert(new Element('div').addClassName('tabNavigation'));	
			}
			
			// insert link to show tabs on the left side
			if (menu.down('.showTabsLeft')) {
				var a = menu.down('.showTabsLeft');
				if (tabs[activeIndex - 1]) {
					a.href = "javascript:tabMenu.showSubTabMenu('" + tabs[activeIndex - 1][0].identify() + "')";
					a.down('img').src = this.options.imgPrevious;
				}
				else {
					a.href = '#';
					a.down('img').src = this.options.imgPreviousDisabled;
				}
			}
			else {
				var a = new Element('a');
				a.addClassName('showTabsLeft');
				if (tabs[activeIndex - 1]) {
					a.href = "javascript:tabMenu.showSubTabMenu('" + tabs[activeIndex - 1][0].identify() + "')";
					a.insert(new Element('img', { src: this.options.imgPrevious}));
				}
				else {
					a.href = '#';
					a.insert(new Element('img', { src: this.options.imgPreviousDisabled}));
				}
				menu.down('.tabNavigation').insert(a);
			}
			
			// insert link to show tabs on the right side
			if (menu.down('.showTabsRight')) {
				var a = menu.down('.showTabsRight');
				if (tabs[activeIndex + 1]) {
					a.href = "javascript:tabMenu.showSubTabMenu('" + tabs[activeIndex + 1][0].identify() + "')";
					a.down('img').src = this.options.imgNext;
				}
				else {
					a.href = '#';
					a.down('img').src = this.options.imgNextDisabled;
				}
			}
			else {
				var a = new Element('a');
				a.href = "javascript:tabMenu.showSubTabMenu('" + tabs[activeIndex + 1][0].identify() + "')";
				a.addClassName('showTabsRight');
				if (tabs[activeIndex + 1]) {
					a.href = "javascript:tabMenu.showSubTabMenu('" + tabs[activeIndex + 1][0].identify() + "')";
					a.insert(new Element('img', { src: this.options.imgNext}));
				}
				else {
					a.href = '#';
					a.insert(new Element('img', { src: this.options.imgNextDisabled}));
				}
				menu.down('.tabNavigation').insert(a);	
			}
		}
	},

	showSubTabMenu: function(tabMenuItem, subTabMenuItem) {
		if (this.activeTabMenuItem == tabMenuItem) return;
			
		if (this.activeTabMenuItem) {
			this.hideSubTabMenu(this.activeTabMenuItem);
			this.setActiveTabMenuItem('');
		}
		// enable menu item
		var tabMenuItemLI = document.getElementById(tabMenuItem);
		if (tabMenuItemLI) tabMenuItemLI.className = 'activeTabMenu';
		
		// show sub categories
		var tabMenuItemUL = document.getElementById(tabMenuItem + '-categories');
		if (tabMenuItemUL) {
			tabMenuItemUL.className = '';
			
			if (!subTabMenuItem && this.activeSubTabMenuItems[tabMenuItem]) subTabMenuItem = this.activeSubTabMenuItems[tabMenuItem];
			if (!subTabMenuItem) {
				// get first sub category
				for (var i = 0; i < tabMenuItemUL.childNodes.length; i++) {
					if (tabMenuItemUL.childNodes[i].nodeName.toLowerCase() == 'li') {
						subTabMenuItem = tabMenuItemUL.childNodes[i].id;
						break;
					}
				}
			}
				
			this.showTabMenuContent(subTabMenuItem);
		}
		else {
			if (this.activeSubTabMenuItem != '') {
				this.hideTabMenuContent(this.activeSubTabMenuItem);
				this.setActiveSubTabMenuItem('');
			}
			if (document.getElementById(tabMenuItem + '-content')) {
				document.getElementById(tabMenuItem + '-content').className = document.getElementById(tabMenuItem + '-content').className.replace(/ hidden/, '');
			}
		}
		
		this.setActiveTabMenuItem(tabMenuItem);
	},
	
	hideSubTabMenu: function(tabMenuItem) {
		// disable menu item
		var activeTabMenuItemLI = document.getElementById(tabMenuItem);
		if (activeTabMenuItemLI) activeTabMenuItemLI.className = '';
		
		// hide sub categories
		var activeTabMenuItemUL = document.getElementById(tabMenuItem + '-categories');
		if (activeTabMenuItemUL) activeTabMenuItemUL.className = 'hidden';
		else {
			if (document.getElementById(tabMenuItem + '-content')) document.getElementById(tabMenuItem + '-content').className += ' hidden';
		}
	},
	
	hideTabMenuContent: function (subTabMenuItem) {
		document.getElementById(subTabMenuItem).className = '';
		document.getElementById(subTabMenuItem + '-content').className += ' hidden';
	},
	
	showTabMenuContent: function(subTabMenuItem) {
		if (this.activeSubTabMenuItem == subTabMenuItem) return;
		
		if (this.activeSubTabMenuItem) {
			this.hideTabMenuContent(this.activeSubTabMenuItem);
			this.setActiveSubTabMenuItem('');
		}
		document.getElementById(subTabMenuItem).className = 'activeSubTabMenu';
		document.getElementById(subTabMenuItem + '-content').className = document.getElementById(subTabMenuItem + '-content').className.replace(/ hidden/, '');
		this.setActiveSubTabMenuItem(subTabMenuItem);
	},
	
	setActiveTabMenuItem: function(activeTabMenuItem) {
		this.activeTabMenuItem = activeTabMenuItem;
		var hidden = document.getElementById('activeTabMenuItem');
		if (hidden) {
			hidden.value = activeTabMenuItem;
		}
		if (activeTabMenuItem != '' && this.options.imgNext != '') {
			this.checkWidth();
		}
	},
	
	setActiveSubTabMenuItem: function(activeSubTabMenuItem) {
		this.activeSubTabMenuItems[this.activeTabMenuItem] = activeSubTabMenuItem;
		this.activeSubTabMenuItem = activeSubTabMenuItem;
		var hidden = document.getElementById('activeSubTabMenuItem');
		if (hidden) {
			hidden.value = activeSubTabMenuItem;
		}
	}
});
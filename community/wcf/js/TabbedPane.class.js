/**
 * @author	Marcel Werk
 * @copyright	2001-2007 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
function TabbedPane() {
	this.tabs = new Array();
	this.erroneousTabs = new Array();
	
	this.addTab = function(name, hasError) {
		this.tabs.push(name);
		this.erroneousTabs.push(hasError);
	}
	
	this.init = function(activeTab) {
		document.getElementById('tabMenu').className = "tabMenu";
		document.getElementById('subTabMenu').className = "subTabMenu";
		document.getElementById('tabContent').className = "border";
		
		if (this.tabs.length > 0) {
			// hide tab headers
			for (var i = 0; i < this.tabs.length; i++) {
				var element = document.getElementById(this.tabs[i]);
				if (element) {
					var elementTitle = element.getElementsByTagName("h2")[0];
					if (elementTitle) {
						elementTitle.style.display = "none";
					}
				}
			}
			
			// open first erroneous tab
			for (var i = 0; i < this.erroneousTabs.length; i++) {
				if (this.erroneousTabs[i]) {
					this.openTab(this.tabs[i]);
					return;
				}
			}
			
			// open default tab
			this.openTab(activeTab ? activeTab : this.tabs[0]);
		}
	}
	
	this.openTab = function(name) {
		for (var i = 0; i < this.tabs.length; i++) {
			var pane = $(this.tabs[i]);
			var tab = $(this.tabs[i] + 'Tab');
			
			if (pane && tab) {
				// remove focus
				tab.firstChild.blur();
			
				if (this.tabs[i] == name && this.erroneousTabs[i]) {
					pane.className = "tabMenuContent container-1";
					pane.show();
					tab.className = "formError activeTabMenu";
				}
				else if (this.tabs[i] == name) {
					pane.className = "tabMenuContent container-1";
					pane.show();
					tab.className = "activeTabMenu";
				}
				else if (this.erroneousTabs[i]){
					pane.hide();
					tab.className = "formError";
				} else {
					pane.hide();
					tab.removeClassName('activeTabMenu');
				}
				
				// save active tab
				if (this.tabs[i] == name) {
					document.getElementById('activeTab').value = name;
				}
			}
		}
	}
}

// create default instance
var tabbedPane = new TabbedPane();

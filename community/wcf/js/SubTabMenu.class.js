/**
 * @author	Alexander Ebert
 * @copyright	2001-2010 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
var SubTabMenu = Class.create({
	/**
	 * Sets class specific variables
	 */
	initialize: function() {
		this.activeTabMenuItem = '';
		this.container = null;
		this.containerID = '';
		this.initialized = false;
		this.isActive = false;
	},
	/**
	 * Shows the requested content div using fancy effects
	 */
	showTabMenuContent: function(id) {
		var contentID = id+'-content';
		
		// ignore clicks on the active menu item
		if (this.activeTabMenuItem == id) {
			return;
		}
		
		// handle first time call
		if (!this.initialized) {
			this.prepareContent(contentID);
			
			// mark item as active
			this.setActiveTabMenuItem(id);
			
			// first time setup completed
			this.initialized = true;
			return;
		}
		
		// ignore action while class is active
		if (this.isActive) {
			return;
		}
		
		// mark class as active
		this.isActive = true;
		
		// handle content toggling
		var previousItemID = this.activeTabMenuItem+'-content';
		var targetItem = $(contentID);
		
		// adjust size for surrounding container
		var height = this.container.getHeight();
		this.container.setStyle({ height: height+'px', overflow: 'hidden' });
		
		// make previous item absolute positioned
		var previousItem = $(previousItemID).absolutize();
		
		// get height of target item (add one pixel to defeat rounding errors)
		height = (targetItem.show().getHeight() + 1);
		
		// hide target item again and make it absolute positioned
		targetItem.absolutize().hide();
		
		// blend over
		new Effect.Parallel([
			new Effect.Appear(contentID, { sync: true }),
			new Effect.Fade(previousItemID, { sync: true }),
			new Effect.Morph(this.containerID, { sync: true, style: 'height: '+height+'px' })
		], {
			duration: 0.75,
			afterFinish: function() {
				// reset position attribute
				targetItem.relativize();
				previousItem.relativize();
				
				// reset style attributes for surrounding container
				this.container.setStyle({ height: 'auto', overflow: 'visible' });
				
				// allow further actions
				this.isActive = false;
			}.bind(this)
		});
		
		// save new tab menu item
		this.setActiveTabMenuItem(id);
	},
	/**
	 * Hides all elements with class "tabMenuContent" and moves
	 * them into a new div container.
 	 */
	prepareContent: function(contentID) {
		var activeElement = $(contentID);
		
		// get parent container
		var container = activeElement.up();
		
		// create tab menu container
		this.container = new Element('div').setStyle({ position: 'relative' });
		
		// hide all child elements and move them into container div
		container.childElements().each(function(childElement) {
			if (childElement.hasClassName('tabMenuContent')) {
				this.container.insert(childElement.hide().remove());
			}
		}.bind(this));
		
		// insert container
		container.insert(this.container);
		
		// save container ID
		this.containerID = this.container.identify();
		
		/**
		 * TODO: Toggle display of sub tab menu via global.css
		 */
		
		// show sub tab menu
		container.down('.subTabMenu').show();
		
		// show active element
		activeElement.show();
	},
	/**
	 * Handles active tab menu marking
	 */
	setActiveTabMenuItem: function(id) {
		if (this.activeTabMenuItem != '') {
			$(this.activeTabMenuItem).removeClassName('activeSubTabMenu');
		}
		
		$(id).addClassName('activeSubTabMenu');
		this.activeTabMenuItem = id;
	}
});
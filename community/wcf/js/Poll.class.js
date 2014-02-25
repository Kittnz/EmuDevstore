/**
 * @author	Marcel Werk
 * @copyright	2001-2007 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
 
var Poll = Class.create({
	
	initialize: function(pollID) {
		this.pollID = pollID;
		
		this.options = Object.extend({
			choiceCount		: 1,
			pollOptionIDs		: [],
			checkboxesDisabled	: false
		}, arguments[1] || { });
		
		// change view buttons
		$$('.pollChangeButton').invoke('observe', 'click', this.changeView);
		$$('.pollShowButton').invoke('observe', 'click', this.changeView);
		
		// event listener
		if (this.options.choiceCount > 1) {
			$('poll' + this.pollID).select('input[type=checkbox]').invoke('observe', 'click', this.checkboxClicked.bind(this));
			this.checkboxClicked();
		}
	},
	
	changeView: function(evt) {
		var button = evt.findElement();
		if (button.up('.pollShowForm')) {
			var pollForm = button.up('.pollShowForm');
			var pollResults = button.up('.poll').down('.pollShowResults');
		}
		else {
			var pollForm = button.up('.poll').down('.pollShowForm');
			var pollResults = button.up('.pollShowResults');
		}
		
		pollForm.toggleClassName('hidden');
		pollResults.toggleClassName('hidden');
		evt.stop();
	},
	
	checkboxClicked: function() {
		// count checked checkboxes
		var count = 0;
		for (var i = 0; i < this.options.pollOptionIDs.length; i++) {
			var checkbox = document.getElementById('pollOption'+this.options.pollOptionIDs[i]);
			if (checkbox.checked) count++;
		}
		
		if (count >= this.options.choiceCount) {
			if (this.options.checkboxesDisabled) return;
			
			for (var i = 0; i < this.options.pollOptionIDs.length; i++) {
				var checkbox = document.getElementById('pollOption'+this.options.pollOptionIDs[i]);
				if (!checkbox.checked) checkbox.disabled = true;	
			}
			
			this.options.checkboxesDisabled = true;
		}
		else if (this.options.checkboxesDisabled) {
			for (var i = 0; i < this.options.pollOptionIDs.length; i++) {
				var checkbox = document.getElementById('pollOption'+this.options.pollOptionIDs[i]);
				checkbox.disabled = false;	
			}
			
			this.options.checkboxesDisabled = false;
		}
	}
});
/**
 * @author	Marcel Werk
 * @copyright	2001-2008 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 */
var ThreadQuickReply = Class.create({
	
	initialize: function(id) {
		this.id = id;
		
		// show container
		var container = $('quickReplyContainer-' + this.id);
		if (container) {
			container.removeClassName('hidden');
		}
		
		var input = $('quickReplyInput-' + this.id);
		if (input) {
			input.hide();
			input.removeClassName('hidden');
		}
		
		var textarea = $('text');
		if (textarea) textarea.setStyle({'width' : '98%'});
		
		// add event listener
		var link = $('quickReplyLink-' + this.id);
		if (link) {
			link.observe('click', this.enable.bind(this));
		}
	},
	
	enable: function() {
		openList('quickReplyInput-' + this.id, {
			afterOpen: function() {
				var buttons = $('quickReplyButtons-' + this.id);
				if (buttons) {
					buttons.removeClassName('hidden');
				}
				var textarea = $('text');
				if (textarea) textarea.focus();
			}.bind(this),
			afterClose: function() {
				var buttons = $('quickReplyButtons-' + this.id);
				if (buttons) {
					buttons.addClassName('hidden');
				}
			}.bind(this)
		});
	}
});
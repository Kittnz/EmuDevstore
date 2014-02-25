/**
 * Functions to mark a board as read inline.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 */
var BoardMarkAsRead = Class.create({
	/**
	 * Initialises BoardMarkAsRead.
	 */
	initialize: function(boards) {
		// store the data
		this.boards = boards;
		
		// init given objects
		var boardMarkAsRead = this;
		this.boards.each(function(pair) {
			boardMarkAsRead.initializeBoard.apply(boardMarkAsRead, [pair.value.boardNo]);
		});
	},
	
	/**
	 * Initialises a board.
	 */
	initializeBoard: function(boardNo) {
		// get icon element
		var icon = $('boardIcon' + boardNo);
		if (icon) {
			// add event listener
			var boardMarkAsRead = this;
			icon.ondblclick = function() {
				boardMarkAsRead.markAsRead.apply(boardMarkAsRead, [boardNo]);
			}
		}
	},
	
	/**
	 * Marks a board as read.
	 */
	markAsRead: function(boardNo) {
		// get icon element
		var icon = $('boardIcon' + boardNo);
		
		// get board
		var board = this.boards.get(boardNo);
		
		// mark board as read
		var ajaxRequest = new AjaxRequest();
		if (ajaxRequest.openGet('index.php?action=BoardMarkAsRead&boardID='+board.boardID+'&t='+SECURITY_TOKEN+'&ajax=1'+SID_ARG_2ND)) {
			// update icon
			if (icon) {
				// change icon
				icon.src = board.icon;
				
				// clear title tag
				icon.title = '';
			}
			
			// get board link
			var link = $('boardLink' + boardNo);
			if (link) {
				// remove 'new' class
				link.removeClassName('new');
				
				// get unread threads span
				var spans = link.getElementsByTagName('span');
				if (spans.length > 0) {
					link.removeChild(spans[0]);
				}
			}
		}
	
		// remove event listener
		if (icon) {
			icon.ondblclick = '';
		}
	}
});
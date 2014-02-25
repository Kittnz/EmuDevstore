/**
 * @author	Marcel Werk
 * @copyright	2001-2007 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 */
function Rating(elementName, currentRating) {
	this.elementName = elementName;
	this.currentRating = currentRating;

	/**
	 * Initialises a new rating option.
	 */
	this.init = function() {
		var span = document.getElementById(this.elementName + 'Span');
		if (span) {
			// add stars
			for (var i = 1; i <= 5; i++) {
				var star = document.createElement('img');
				star.src = RELATIVE_WBB_DIR+'icon/noRatingS.png';
				star.alt = '';
				star.rating = this;
				star.name = i;
				star.onmouseover = function() { this.style.cursor = 'pointer'; this.rating.showRating(parseInt(this.name)); };
				star.onclick = function() { this.rating.submitRating(parseInt(this.name)); };
				span.appendChild(star);
			}
		
			// add listener
			span.rating = this;
			span.onmouseout = function() { this.rating.showCurrentRating(); };
			
			// set visible
			span.className = '';
		}
		
		if (this.currentRating > 0) {
			this.showCurrentRating();
		}
	}
	
	/**
	 * Shows the current user rating.
	 */
	this.showCurrentRating = function() {
		this.showRating(this.currentRating);
	}
	
	/**
	 * Shows a selected rating.
	 */
	this.showRating = function(rating) {
		var span = document.getElementById(this.elementName + 'Span');
		if (span) {
			for (var i = 1; i <= rating; i++) {
				if (span.childNodes[i - 1]) {
					span.childNodes[i - 1].src = RELATIVE_WBB_DIR+'icon/ratingS.png';
				}
			}
			
			
			for (var i = rating + 1; i <= 5; i++) {
				if (span.childNodes[i - 1]) {
					span.childNodes[i - 1].src = RELATIVE_WBB_DIR+'icon/noRatingS.png';
				}
			}
		}
	}
	
	/**
	 * Submits a selected rating.
	 */
	this.submitRating = function(rating) {
		var element = document.getElementById(elementName);
		var select = document.getElementById(elementName + 'Select');
		if (element) {
			this.currentRating = rating;
			element.value = rating;
			
			if (select) {
				select.selectedIndex = rating - 1;
			}
			
			element.form.submit();
		}
	}
	
	this.init();
}
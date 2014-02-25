/**
 * @author	Marcel Werk
 * @copyright	2001-2007 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
function MultiPagesLinks() {
	this.pageLink = '';
	
	this.setPageLink = function(pageLink) {
		this.pageLink = pageLink;
	}
	
	this.startPageNumberInput = function(element) {
		element.style.display = 'none';
	
		element.nextSibling.style.display = 'block';
		element.nextSibling.value = '';
		element.nextSibling.onkeydown = function(e) { return multiPagesLinks.handlePageNumberInput(e); };
		element.nextSibling.onblur = function() { multiPagesLinks.stopPageNumberInput(this); };
		element.nextSibling.focus();
	}
	
	this.handlePageNumberInput = function(event) {
		if (!event) event = window.event;
	
		// get key code
		if (event.which) {
			var keyCode = event.which;
		}
		else if (event.keyCode) {
			var keyCode = event.keyCode;
		}
	
		// get target
		var target;
		if (event.target) target = event.target;
		else if (event.srcElement) target = event.srcElement;
		
		// enter
		if (keyCode == 13) {
			document.location.href = fixURL(this.pageLink.replace(/%d/, parseInt(target.value)));
		}
	
		// enter and esc
		if (keyCode == 13 || keyCode == 27) {
			this.stopPageNumberInput(target);
		}
		
		return true;
	}
	
	this.stopPageNumberInput = function(element) {
		element.style.display = 'none';
	
		element.previousSibling.style.display = 'block';
	}
}

var multiPagesLinks = new MultiPagesLinks();
/**
 * @author	Marcel Werk
 * @copyright	2001-2007 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 */
function AlignmentPreview() {
	/**
	 * Initializes a new alignment preview icon.
	 */
	this.init = function(id, skipEventListener) {
		var select = document.getElementById(id);
		
		// create image
		var image = document.createElement('img');
		image.id = id + '-alignment-preview';
		select.parentNode.appendChild(image);
		
		// refresh icon
		this.refresh(id);
		
		// add onchange listener
		if (!skipEventListener) {
			select.onchange = function() { alignmentPreview.refresh(this.id); };
		}
	}
	
	/**
	 * Refreshes the alignment preview icon.
	 */
	this.refresh = function(id) {
		var select = document.getElementById(id);
		var image = document.getElementById(id + '-alignment-preview');
		var imageSrc = RELATIVE_WCF_DIR + 'icon/alignment';
		var value = select.options[select.selectedIndex].value;
		
		if (value == 'top' || id.indexOf('vertical') != -1) {
			imageSrc += 'V';
		}
		else {
			imageSrc += 'H';
		}
		
		imageSrc += value.substring(0, 1).toUpperCase() + value.substring(1);
		
		image.src = imageSrc + 'M.png';
		image.title = select.options[select.selectedIndex].text;
	}
}

var alignmentPreview = new AlignmentPreview();
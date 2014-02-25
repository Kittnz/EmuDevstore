/**
 * @author	Marcel Werk
 * @copyright	2001-2007 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 */
function ColorChooser() {
	this.activeField = '';
	this.activeImage = null;
	this.created = false;
	
	/**
	 * Initializes a new color chooser icon.
	 */
	this.init = function(field) {
		var input = document.getElementById(field);
		
		// create image
		var image = document.createElement('img');
		image.id = field + '-chooser-image';
		image.name = field;
		image.onclick = function() { colorChooser.open(this, this.name); };
		input.parentNode.appendChild(image);
		
		// refresh icon
		this.refresh(image, input);
		
		// add keyup listener
		input.onkeyup = function() { colorChooser.refresh(document.getElementById(this.id + '-chooser-image'), this); };
	}
	
	/**
	 * Refreshes the color chooser icon.
	 */
	this.refresh = function(image, input) {
		// set icon
		image.src = (input.value != '' ? RELATIVE_WCF_DIR + 'icon/colorPickerM.png' : RELATIVE_WCF_DIR + 'icon/colorPickerEmptyM.png');
		
		// set value
		var oldColor = image.style.backgroundColor;
		try {
			image.style.backgroundColor = (input.value != '' ? input.value : 'transparent');
		}
		catch (e) {
			image.style.backgroundColor = oldColor;
		}
	}
	
	/**
	 * Opens the color chooser.
	 */
	this.open = function(image, field) {
		if (this.activeField == field) {
			this.close();
		}
		else {
			if (this.activeField != '') {
				this.close();
			}
			
			if (!this.created) this.create();
			
			this.activeField = field;
			this.activeImage = image;
			
			var div = document.createElement('div');
			div.id = this.activeField + 'Div';
			this.activeImage.parentNode.appendChild(div);
			div.innerHTML = document.getElementById('colorChooser').innerHTML;
			
			if (IS_IE6) {
				var parentHeight = div.parentNode ? div.parentNode.offsetHeight : 0;
				div.style.marginTop = (-document.getElementById('colorChooser').firstChild.offsetHeight - parentHeight)+'px';
				popupMenuList.hideSelects(div.firstChild);
			}
		}
	}
	
	/**
	 * Creates the html code of the color chooser.
	 */
	this.create = function() {
		this.created = true;
		
		// create div
		var div = document.createElement('div');
		div.id = 'colorChooser';
		div.className = 'hidden';
		document.body.appendChild(div);
		
		var innerDiv = document.createElement('div');
		innerDiv.className = 'colors';
		div.appendChild(innerDiv);
		
		// create ul for grey scale
		var ulGrey = document.createElement('ul');
		ulGrey.className = 'greyColumn';
		innerDiv.appendChild(ulGrey);
		
		for (var i = 0; i < 256; i += 51) {
			var li = document.createElement('li');
			
			var color = i.toString(16);
			if (color.length > 1) color = color.substr(0, 1);
			
			li.style.backgroundColor = '#'+color+color+color;
			li.title = '#'+color+color+color;
			ulGrey.appendChild(li);
			
			var a = document.createElement('a');
			a.href = "javascript:colorChooser.select('#"+color+color+color+"');";
			a.style.borderColor = '#'+color+color+color;
			li.appendChild(a);
		}
		
		// create "transparent" color
		var liTransparent = document.createElement('li');
		liTransparent.style.backgroundImage = 'url(' + RELATIVE_WCF_DIR + 'icon/colorPickerEmptyS.png)';
		liTransparent.title = 'transparent';
		ulGrey.appendChild(liTransparent);
		
		var aTransparent = document.createElement('a');
		aTransparent.href = "javascript:colorChooser.select('');";
		liTransparent.appendChild(aTransparent);
		
		// create ul
		var ulColor = document.createElement('ul');
		ulColor.className = 'colorColumn';
		innerDiv.appendChild(ulColor);
		
		var i = 0;
		for (var r = 0; r < 256; r += 51) { // red
			for (var g = 0; g < 256; g += 51) { // green
				for (var b = 0; b < 256; b += 51) { // blue
					i++;
					if (i == 73 || i == 145) { // break
						ulColor = document.createElement('ul');
						ulColor.className = 'colorColumn';
						innerDiv.appendChild(ulColor);
					}
					
					var li = document.createElement('li');
					var red = r.toString(16);
					if (red.length > 1) red = red.substr(0, 1);
					var green = g.toString(16);
					if (green.length > 1) green = green.substr(0, 1);
					var blue = b.toString(16);
					if (blue.length > 1) blue = blue.substr(0, 1);
					
					li.style.backgroundColor = '#'+red+green+blue;
					li.title = '#'+red+green+blue;
					ulColor.appendChild(li);
					
					var a = document.createElement('a');
					a.href = "javascript:colorChooser.select('#"+red+green+blue+"');";
					a.style.borderColor = '#'+red+green+blue;
					li.appendChild(a);
				}
			}
		}
	}
	
	/**
	 * Closes the color chooser.
	 */
	this.close = function() {
		var colors = document.getElementById(this.activeField + 'Div');
		if (colors) {
			colors.parentNode.removeChild(colors);
		}
		
		this.activeField = '';
		this.activeImage = null;
	}
	
	/**
	 * Selects a color from the color chooser.
	 */
	this.select = function(color) {
		var input = document.getElementById(this.activeField);
		if (input) {
			input.value = color;
			if (this.activeImage) {
				this.refresh(this.activeImage, input);
			}
		}
		
		this.close();
	}
}

var colorChooser = new ColorChooser();
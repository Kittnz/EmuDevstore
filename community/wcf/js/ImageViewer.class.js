/**
 * ImageViewer can be used to show a gallery of images in a "lightbox" 
 */
var ImageViewer = Class.create({
	/**
	 * Init ImagePanel
	 */
	initialize: function(links) {
		
		this.options = Object.extend({
			langCaption		: '',
			langPrevious		: '',
			langNext		: '',
			langPlay		: '',
			langPause		: '',
			langEnlarge		: '',
			langClose		: '',
			imgBlankSrc		: '',
			imgMenuSrc		: '',
			imgPlaySrc		: '',
			imgPreviousSrc		: '',
			imgNextSrc		: '',
			imgEnlargeSrc		: '',
			imgPauseSrc		: '',
			imgCloseSrc		: '',
			imgPlayHoverSrc		: '',
			imgPreviousHoverSrc	: '',
			imgNextHoverSrc		: '',
			imgEnlargeHoverSrc	: '',
			imgPauseHoverSrc	: '',
			imgCloseHoverSrc	: '',
			slideShowDuration	: 5
		}, arguments[1] || { });
		
		// store the links
		this.links = links;
		// init values
		this.currentIndex = 0;
		this.maxWidth = document.viewport.getWidth() - 60;
		this.maxHeight = document.viewport.getHeight() - 60;
		this.isOpen = false;
		this.isPlaying = false;
		this.isOverMenu = false;
		this.currentImage = null;
		this.menuIsAppearing = false;
		// create the frame
		this.createFrame();
		// add click handler for the links
		links.invoke('observe', 'click', this.open.bindAsEventListener(this));
		// cache the event bounding functions
		this.cacheBoundingFunctions();
	},
	
	/**
	 * Open the ImageViewer
	 * @param 	Event 	evt	the event object when the user opened the viewer
	 */
	open: function(evt) {
		// get element where the user started the opening
		element = evt.findElement();
		// if element was a link
		if (element.nodeName.toLowerCase() == 'a') {
			this.currentLink = element;
		}
		// if element was a span
		else {
			this.currentLink = element.parentNode;
		}
		// find the position of that link in the list of links
		this.links.each(function(link, index) {
			if (this.currentLink == link) {
				this.currentIndex = index;	
			}
		}.bind(this));
		this.show(true);
		evt.stop();
	},
	
	/**
	 * Shows the ImageViewer
	 * @param: if the param is given the imageviewer will be opened by moving the clicked thumbnail image to the center
	 */
	show: function(startFromCurrentLink) {
		
		var startFromCurrentLink = arguments[0] ? arguments[0] : false;
		
		// show background
		this.background.appear({duration: 0.2, to: 0.6});
		// hide drop down boxes in IE6
		if (IS_IE6) {
			$$('select').invoke('setStyle', 'visibility: hidden');
		}
		
		if (startFromCurrentLink && this.currentLink.down('img') && !IS_IE6 && !IS_SAFARI_MOBILE) {
			// create current image
			var left = this.currentLink.down('img').viewportOffset()['left'];
			var top = this.currentLink.down('img').viewportOffset()['top'];
			if (IS_OPERA) {
				// remove scroll offset
				var topOffset = document.viewport.getScrollOffsets();
				top -= topOffset.top;
			}
			this.currentImage = new Element('img', {
				'src' 	: this.options.imgBlankSrc,
				'style' : 'left: ' + left + 'px; top: ' + top + 'px; width: ' + this.currentLink.down('img').getWidth() + 'px; height: ' + this.currentLink.down('img').getHeight() + 'px;'	
			}).addClassName('imageViewerCurrentImage');
		}
		else {
			// create current image
			var left = document.viewport.getWidth()/2 - 200;
			var top = document.viewport.getHeight()/2 - 100 + (IS_IE6 || IS_SAFARI_MOBILE ? document.viewport.getScrollOffsets().top : 0);
			this.currentImage = new Element('img', {
				'src' 	: this.options.imgBlankSrc,
				'style' : 'left: ' + left + 'px; top: ' + top + 'px;'	
			}).addClassName('imageViewerCurrentImage');
		}
		// insert current image in the document
		$$('body')[0].insert(this.currentImage);
		//set isOpen true
		this.isOpen = true;
		// show menu
		this.showMenu();
		// show information
		this.writeInfoLine();
		// register event handlers
		this.registerEventHandlers();
		// load the current image
		this.loadImage(this.currentLink.href);
	},
	
	/**
	 * Close the ImageViewer
	 * @param 	Event 	evt	the event object when the user closed the viewer
	 */
	close: function(evt) {
		// if user pressed a key different than ESC or BACKSPACE don't execute close
		if (evt != null && evt.type == 'keyup' && evt.keyCode != Event.KEY_ESC && evt.keyCode != Event.KEY_BACKSPACE) {
			return;
		}
		// set isOpen false
		this.isOpen = false;
		// unregister the event handlers
		this.unregisterEventHandlers();
		if (this.menuTimer) {
			this.menuTimer.stop();
		}
		// hide the menu
		this.menu.hide();
		// remove the current image
		this.currentImage.remove();
		// stop playing (if it is playing)
		if (this.isPlaying) {
			this.play();
		}
		
		this.resetButtons();
		
		// fade out the background
		this.background.fade({duration: 0.2});
		// show drop down boxes in ie 6
		if (IS_IE6) {
			$$('select').invoke('setStyle', 'visibility: visible');
		}
		evt.stop();
	},
	
	/**
	 * Remember that bindAsEventListener returns a fresh anonymous function that wraps your method. 
	 * This means that every call to it returns a new function. Therefore, the code above requests 
	 * stopping on another function than was used when setting up observation. No match is found, 
	 * and the original observer is left untroubled.
	 */
	cacheBoundingFunctions: function() {
		this._changeWindow = this.changeWindow.bindAsEventListener(this);
		this._showMenu = this.showMenu.bindAsEventListener(this);
		this._close = this.close.bindAsEventListener(this);
		this._enlarge = this.enlarge.bindAsEventListener(this);
		this._hoverMenu = this.hoverMenu.bindAsEventListener(this);
		this._hoverCloseButton = this.hoverCloseButton.bindAsEventListener(this);
		this._hoverEnlargeButton = this.hoverEnlargeButton.bindAsEventListener(this);
		this._hoverPlayButton = this.hoverPlayButton.bindAsEventListener(this);
		this._hoverPreviousButton = this.hoverPreviousButton.bindAsEventListener(this);
		this._hoverNextButton = this.hoverNextButton.bindAsEventListener(this);
		this._previous = this.previous.bindAsEventListener(this);
		this._play = this.play.bindAsEventListener(this);
		this._next = this.next.bindAsEventListener(this);
	},
	
	/**
	 * Register the event handlers
	 */
	registerEventHandlers: function() {
		// document
		Event.observe(window, 'resize', this._changeWindow);
		document.observe('mousemove', this._showMenu);
		document.observe('keyup', this._close);
		// menu
		this.menu.observe('mouseover', this._hoverMenu);
		this.menu.observe('mouseout', this._hoverMenu);
		this.closeButton.observe('click', this._close);
		this.closeButton.observe('mouseover', this._hoverCloseButton);
		this.closeButton.observe('mouseout', this._hoverCloseButton);
		this.enlargeButton.observe('click', this._enlarge);
		this.enlargeButton.observe('mouseover', this._hoverEnlargeButton);
		this.enlargeButton.observe('mouseout', this._hoverEnlargeButton);
		// background
		this.background.observe('click', this._close);
		
		if (this.links.length > 1) {
			// buttons
			document.observe('keyup', this._next);
			document.observe('keyup', this._previous);
			document.observe('keydown', this._play);
			this.previousButton.observe('click', this._previous);
			this.previousButton.observe('mouseover', this._hoverPreviousButton);
			this.previousButton.observe('mouseout', this._hoverPreviousButton);
			this.playButton.observe('click', this._play);
			this.playButton.observe('mouseover', this._hoverPlayButton);
			this.playButton.observe('mouseout', this._hoverPlayButton);
			this.nextButton.observe('click', this._next);
			this.nextButton.observe('mouseover', this._hoverNextButton);
			this.nextButton.observe('mouseout', this._hoverNextButton);
			// image
			this.currentImage.observe('click', this._next);
		}
	},
	
	/**
	 * Unregister the event handlers
	 */
	unregisterEventHandlers: function() {
		// document
		Event.stopObserving(window, 'resize', this._changeWindow);
		document.stopObserving('mousemove', this._showMenu);
		document.stopObserving('keyup', this._close);
		// menu
		this.menu.stopObserving();
		this.closeButton.stopObserving();
		this.enlargeButton.stopObserving();
		// background
		this.background.stopObserving();
		
		if (this.links.length > 1) {
			// buttons
			document.stopObserving('keyup', this._next);
			document.stopObserving('keyup', this._previous);
			document.stopObserving('keydown', this._play);
			this.previousButton.stopObserving();
			this.playButton.stopObserving();
			this.nextButton.stopObserving();
			// image
			this.currentImage.stopObserving();
		}
	},
	
	/**
	 * Opens the image in a new page
	 */
	enlarge: function() {
		location.href = this.currentLink.href;
	},
	
	/**
	 * Loads an image from an url and calls this.exchangeImage() if loaded
	 * @param	String	url	url of image to load
	 */
	loadImage: function(url) {
		// load in the new image
		this.currentImage.addClassName('imageViewerLoading');
		this.newImage = new Image();
		
		// add load event handler
		this.newImage.onload = function() {
			this.exchangeImage();
		}.bindAsEventListener(this);
		
		// add error event handler
		this.newImage.onerror = function() {
			// show thumbnail
			if (this.currentLink.down('img')) {
				this.newImage.src = this.currentLink.down('img').src;
			}
			// or close icon
			else {
				this.newImage.src= this.options.imgCloseSrc;
			}
			this.exchangeImage();
		}.bindAsEventListener(this);
		this.newImage.src = url;
	},
	
	/**
	 * Writes the content of the image caption (info line)
	 */
	writeInfoLine: function() {
		this.caption.update(eval(this.options.langCaption));	
	},
	
	/**
	 * Exchange the current image with the new image
	 */
	exchangeImage: function() {
		// get the size of the new image
		var size = this.calculateSize(this.newImage);
		// do the exchange with a morph effect
		var left = document.viewport.getWidth()/2 - size['width']/2;
		var top = document.viewport.getHeight()/2 - size['height']/2;
		if (IS_IE) {
			this.caption.setStyle('top: ' + (document.viewport.getScrollOffsets().top + 5) + 'px; width: ' + $$('body')[0].getWidth() + 'px;');
		}
		new Effect.Morph(this.currentImage, {
			duration: 0.5,
			style: 'height: ' + size['height'] + 'px; width: ' + size['width'] + 'px; left: ' + left + 'px; top: ' + (IS_IE6 || IS_SAFARI_MOBILE ? document.viewport.getScrollOffsets().top + top : top) + 'px;',
			afterFinish: function() {
				this.currentImage.src = this.newImage.src;
				this.currentImage.removeClassName('imageViewerLoading');
				if (this.isPlaying) this.playingTimer = window.setTimeout(this.next.bind(this), this.options.slideShowDuration * 1000);
				this.changeWindow();
			}.bind(this)	
		});
		
	},
	
	/**
	 * Calculate the size of an image - it shouldn't exceed the maximum width and height
	 * @param	Image	image	the image to calculate the size on
	 * @return	Array		Associative array holding the width and height of the image
	 */
	calculateSize: function(image) {
		// if the new image is exceeding the max width and height
		if (image.width > this.maxWidth || image.height > this.maxHeight) {
			// scale it down
			if (this.maxWidth / image.width < this.maxHeight / image.height) {
				width = this.maxWidth;
				height = Math.round(image.height * (width / image.width));
			}
			else {
				height = this.maxHeight;	
				width = Math.round(image.width * (height / image.height));
			}
		}
		else {
			// or just take the current width and height
			width = image.width;
			height = image.height;	
		}
		return {
			'width' : width,
			'height' : height	
		};
	},
	
	/**
	 * Create the visual frame of the viewer including the background and menu
	 */
	createFrame: function() {
		// background
		this.background = new Element('div').addClassName('imageViewerBackground');
		if (IS_IE6 || IS_SAFARI_MOBILE) {
			this.background.setStyle('height: ' + $$('body')[0].getHeight() + 'px; width: ' + $$('body')[0].getWidth() + 'px;');
		}
		this.background.hide();
		$$('body')[0].insert(this.background);
		
		// caption
		this.caption = new Element('p').addClassName('imageViewerCaption');
		
		this.background.insert(this.caption);
		
		// menu
		this.menu = new Element('div', {
			'style' : 'left: ' + (document.viewport.getWidth()/2 - 200/2) + 'px;'
		}).addClassName('imageViewerMenu');
		this.menu.hide();
		
		// previous button
		this.previousButton = new Element('img', {
			'id'	: 'previousImage',
			'src' 	: this.options.imgPreviousSrc,
			'style' : 'margin: 0 10px; cursor: pointer',
			'title'	: this.options.langPrevious	
		});
		if (this.links.length == 1) {
			this.previousButton.setOpacity(0.2);
			this.previousButton.setStyle({cursor: 'default'});
			this.previousButton.title = '';
		}
		this.menu.insert(this.previousButton);
		
		// play button
		this.playButton = new Element('img', {
			'id'	: 'playImage',
			'src' 	: this.options.imgPlaySrc,
			'style' : 'margin: 0 10px; cursor: pointer',
			'title'	: this.options.langPlay
		});
		if (this.links.length == 1) {
			this.playButton.setOpacity(0.2);
			this.playButton.setStyle({cursor: 'default'});
			this.playButton.title = '';
		}
		this.menu.insert(this.playButton);
					
		// next button
		this.nextButton = new Element('img', {
			'id'	: 'nextImage',
			'src' 	: this.options.imgNextSrc,
			'style' : 'margin: 0 10px; cursor: pointer',
			'title'	: this.options.langNext	
		});
		if (this.links.length == 1) {
			this.nextButton.setOpacity(0.2);
			this.nextButton.setStyle({cursor: 'default'});
			this.nextButton.title = '';
		}
		this.menu.insert(this.nextButton);
		
		// enlarge button
		this.enlargeButton = new Element('img', {
			'id'	: 'enlargeButton',
			'src' 	: this.options.imgEnlargeSrc,
			'style' : 'margin: 0 10px; cursor: pointer',
			'title'	: this.options.langEnlarge
		});
		this.menu.insert(this.enlargeButton);
		
		// close button
		this.closeButton = new Element('img', {
			'id'	: 'closeImageViewer',
			'src' 	: this.options.imgCloseSrc,
			'style' : 'margin: 0 10px; cursor: pointer',
			'title'	: this.options.langClose	
		});
		this.menu.insert(this.closeButton);
		
		$$('body')[0].insert(this.menu);
	},
	
	/**
	 * Reset buttons to their default state
	 */
	resetButtons: function() {
		if (this.links.length > 1) {
			this.previousButton.src = this.options.imgPreviousSrc;
			this.playButton.src = this.options.imgPlaySrc;
			this.nextButton.src = this.options.imgNextSrc;	
		}
		this.enlargeButton.src = this.options.imgEnlargeSrc;
		this.closeButton.src =  this.options.imgCloseSrc;
	},
	
	/**
	 * Toggeling the state of this.isOverMenu (depending if the mouse is currently over the menu)
	 * @param	Event	evt	event object
	 */
	hoverMenu: function(evt) {
		if (evt.type == 'mouseover') {
			this.isOverMenu = true;
		} 
		else {
			this.isOverMenu = false;	
		}
	},
	
	/**
	 * Toggeling the close button hover/out
	 * @param	Event	evt	event object
	 */
	hoverCloseButton: function(evt) {
		this.closeButton.src = evt.type == 'mouseout' ? this.options.imgCloseSrc : this.options.imgCloseHoverSrc;
	},
	
	/**
	 * Toggeling the enlarge button hover/out
	 * @param	Event	evt	event object
	 */
	hoverEnlargeButton: function(evt) {
		this.enlargeButton.src = evt.type == 'mouseout' ? this.options.imgEnlargeSrc : this.options.imgEnlargeHoverSrc;
	},
	
	/**
	 * Toggeling the previous button hover/out
	 * @param	Event	evt	event object
	 */
	hoverPreviousButton: function(evt) {
		this.previousButton.src = evt.type == 'mouseout' ? this.options.imgPreviousSrc : this.options.imgPreviousHoverSrc;
	},
	
	/**
	 * Toggeling the next button hover/out
	 * @param	Event	evt	event object
	 */
	hoverNextButton: function(evt) {
		this.nextButton.src = evt.type == 'mouseout' ? this.options.imgNextSrc : this.options.imgNextHoverSrc;
	},
	
	/**
	 * Toggeling the play button hover/out
	 * @param	Event	evt	event object
	 */
	hoverPlayButton: function(evt) {
		if (this.isPlaying) {
			this.playButton.src = evt.type == 'mouseout' ? this.options.imgPauseSrc : this.options.imgPauseHoverSrc;
		}
		else {
			this.playButton.src = evt.type == 'mouseout' ? this.options.imgPlaySrc : this.options.imgPlayHoverSrc;
		}
	},
	
	/**
	 * Play/stop playing (automatically changing the images)
	 */
	play: function(evt) {
		// if user pressed a key different from SPACE don't execute play
		if (evt != null && evt.type == 'keydown' && evt.keyCode != 32) {
			return;
		}
		// if it is currently playing
		if (this.isPlaying) {
			// stop playing
			window.clearTimeout(this.playingTimer);
			// changing the button to play
			if (evt != null && evt.type == 'keydown') {
				this.showMenu();
				setTimeout(function() {
					this.playButton.src = this.options.imgPauseHoverSrc;
					setTimeout(function() {
						this.playButton.src = this.options.imgPlayHoverSrc;
						setTimeout(function() {
							this.playButton.src = this.options.imgPlaySrc;
						}.bind(this), 100);
					}.bind(this), 100);
				}.bind(this), 100);
			}
			else {
				this.playButton.src = this.options.imgPlayHoverSrc;
			}
			this.playButton.title = this.options.langPlay;
			// set isPlaying false
			this.isPlaying = false;
		}
		else {
			// start playing (exchanging pictures every 5 seconds
			this.playingTimer = window.setTimeout(this.next.bind(this), this.options.slideShowDuration * 1000);
			// changing the button to pause
			if (evt != null && evt.type == 'keydown') {
				this.showMenu();
				setTimeout(function() {
					this.playButton.src = this.options.imgPlayHoverSrc;
					setTimeout(function() {
						this.playButton.src = this.options.imgPauseHoverSrc;
						setTimeout(function() {
							this.playButton.src = this.options.imgPauseSrc;
						}.bind(this), 100);
					}.bind(this), 100);
				}.bind(this), 100);
			}
			else {
				if (this.isOverMenu) {
					this.playButton.src = this.options.imgPauseHoverSrc;
				}
				else {
					this.playButton.src = this.options.imgPauseSrc;
				}
			}
			this.playButton.title = this.options.langPause;
			// set isPlaying true
			this.isPlaying = true;	
		}
		if (evt != null && evt.type == 'keydown') evt.stop();
	},
	
	/**
	 * Show the menu
	 */
	showMenu: function() {
		// only do this, when the viewer is open and the menu is not already appearing
		if (this.isOpen && !this.menuIsAppearing) {
			// let the menu appear
			this.menu.appear({
				duration: 0.1, 
				to: 0.6,
				beforeStart: function() { this.menuIsAppearing = true; }.bind(this),
				afterFinish: function() { 
					this.menuIsAppearing = false;
					// if the viewer has already been closed, hide the menu
					if (!this.isOpen) {
						this.hideMenu();
					}
				}.bind(this)
			});
			// stop the menuTimer (if one exist)
			if (this.menuTimer != null) {
				this.menuTimer.stop();
			}
			// if the mouse is currently not over the menu let the menu hide in 3 seconds
			if (!this.isOverMenu) {
				this.menuTimer = new PeriodicalExecuter(this.hideMenu.bind(this), 2);
			}
		}
	},
	
	/**
	 * Hide the menu
	 */
	hideMenu: function() {
		// fade out the menu
		if (this.menu && !IS_SAFARI_MOBILE) {
			this.menu.fade({duration: 0.1});
		}
		// stop the timer
		if (this.menuTimer) {
			this.menuTimer.stop();
		}
	},
	
	/**
	 * Show the next image
	 */
	next: function(evt) {
		// if user pressed a key different from RIGHT don't execute next
		if (evt != null && evt.type == 'keyup' && evt.keyCode != Event.KEY_RIGHT) {
			return;
		}
		// clear timeout
		window.clearTimeout(this.playingTimer);
		
		//animate next button
		if (evt != null && evt.type == 'keyup') {
			this.showMenu();
			setTimeout(function() {
				this.nextButton.src = this.options.imgNextHoverSrc;
				setTimeout(function() {
					this.nextButton.src = this.options.imgNextSrc;
				}.bind(this), 100);
			}.bind(this), 100);
		}
		
		// showing a blank image
		this.currentImage.src = this.options.imgBlankSrc;
		// getting the next index
		if (this.currentIndex + 1 == this.links.size()) {
			this.currentIndex = 0;
		}
		else {
			this.currentIndex++;
		}
		// getting the current link
		this.currentLink = this.links[this.currentIndex];
		// scroll this link in the background
		this.scrollToCurrentImage();
		// write current information
		this.writeInfoLine();
		// load the image
		this.loadImage(this.currentLink.href);
	},
	
	/**
	 * Show the previous image
	 */
	previous: function(evt) {
		// if user pressed a key different from LEFT don't execute next
		if (evt != null && evt.type == 'keyup' && evt.keyCode != Event.KEY_LEFT) {
			return;
		}
		
		//animate previous button
		if (evt != null && evt.type == 'keyup') {
			this.showMenu();
			setTimeout(function() {
				this.previousButton.src = this.options.imgPreviousHoverSrc;
				setTimeout(function() {
					this.previousButton.src = this.options.imgPreviousSrc;
				}.bind(this), 100);
			}.bind(this), 100);
		}
		
		// showing a blank image
		this.currentImage.src = this.options.imgBlankSrc;
		// getting the previous index
		if (this.currentIndex == 0) {
			this.currentIndex = this.links.size() - 1;
		}
		else {
			this.currentIndex--;
		}
		// getting the current link
		this.currentLink = this.links[this.currentIndex];
		// scroll this link in the background
		this.scrollToCurrentImage();
		// write current information
		this.writeInfoLine();
		// load the image
		this.loadImage(this.currentLink.href);
	},
	
	/**
	 * Scrolls the background to the current image
	 */
	scrollToCurrentImage: function() {
		var linkPos1 = this.currentLink.cumulativeOffset()[1];
		var linkPos2 = linkPos1 + this.currentLink.getHeight();
		var viewportPos1 = document.viewport.getScrollOffsets()[1];
		var viewportPos2 = viewportPos1 + document.viewport.getHeight();
		
		// if link is not within viewport scroll the background (don't do it in IE 6)
		if (!IS_IE6 && !IS_SAFARI_MOBILE && !(linkPos1 > viewportPos1 && linkPos1 < viewportPos2 || linkPos2 > viewportPos1 && linkPos2 < viewportPos2)) {
			this.currentLink.scrollTo();
		}

		
	},
	
	/**
	 * Change the size of the window and reposition menu and image
	 */
	changeWindow: function() {
		// get the current max width
		this.maxWidth = document.viewport.getWidth() - 60;
		// get the current max height
		this.maxHeight = document.viewport.getHeight() - 60;
		// get the new size of the image
		var size = this.calculateSize(this.newImage);
		var left = document.viewport.getWidth()/2 - size['width']/2;
		var top = document.viewport.getHeight()/2 - size['height']/2 +  + (IS_IE6 || IS_SAFARI_MOBILE ? document.viewport.getScrollOffsets().top : 0);
		// resize and position the image
		this.currentImage.setStyle('height: ' + size['height'] + 'px; width: ' + size['width'] + 'px; left: ' + left + 'px; top: ' + top + 'px;');
		// reposition the menu
		this.menu.setStyle('left: ' + (document.viewport.getWidth()/2 - this.menu.getWidth()/2) + 'px;');
		if (IS_SAFARI_MOBILE) this.menu.setStyle('top: ' + (document.viewport.getScrollOffsets().top + document.viewport.getHeight() - this.menu.getHeight() - 20) + 'px;');
	}
});
/**
 * @author	Alexander Ebert
 * @copyright	2001-2010 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
var Slideshow = Class.create(ImageViewer, {
	/**
	 * Calculates position data for preview images
	 */
	initialize: function($super, links) {
		// initialize image viewer
		$super(links, arguments[2]);
		
		// get offsets for preview images
		var dimensions = document.viewport.getDimensions();
		this.previewOffsets = $H();
		this.previewOffsets.set('left', [ 60, ((dimensions.height - 95) / 2) ]);
		this.previewOffsets.set('right', [ (dimensions.width - 135), ((dimensions.height - 95) / 2) ]);
		
		this.arrowOffsets = $H();
		this.arrowOffsets.set('left', [ 94, this.previewOffsets.get('left')[1] + 36 ]);
		this.arrowOffsets.set('right', [ this.previewOffsets.get('right')[0] + 36, this.previewOffsets.get('right')[1] + 36 ]);
		this.navigationArrows = [];
		this.previewImages = [];
		this.isVisible = true;
		
		this.initializeNavigationArrows();
	},
	/**
	 * Initializes arrow images on preview images
	 */
	initializeNavigationArrows: function() {
		// set left arrow
		var leftArrow = new Element('img', { src: this.options.imgPreviousSrc }).setStyle({
			height: '24px',
			left: this.arrowOffsets.get('left')[0]+'px',
			position: 'fixed',
			top: this.arrowOffsets.get('left')[1]+'px',
			width: '24px',
			zIndex: 120
		}).addClassName('pointer').hide();
		$$('body')[0].insert(leftArrow);
		
		// set right arrow
		var rightArrow = new Element('img', { src: this.options.imgNextSrc }).setStyle({
			height: '24px',
			left: this.arrowOffsets.get('right')[0]+'px',
			position: 'fixed',
			top: this.arrowOffsets.get('right')[1]+'px',
			width: '24px',
			zIndex: 120
		}).addClassName('pointer').hide();
		$$('body')[0].insert(rightArrow);
		
		this.navigationArrows = [ leftArrow.identify(), rightArrow.identify() ];
		
		// bind event listener
		leftArrow.observe('click', this.previous.bind(this));
		rightArrow.observe('click', this.next.bind(this));
	},
	/**
	 * Adjusts position for preview images on window resize
	 */
	changeWindow: function($super) {
		if (this.previewImages.length == 2) {
			var dimensions = document.viewport.getDimensions();
			
			// adjust position
			this.previewOffsets.set('left', [ 60, ((dimensions.height - 95) / 2) ]);
			this.previewOffsets.set('right', [ (dimensions.width - 135), ((dimensions.height - 95) / 2) ]);
			this.arrowOffsets.set('left', [ 94, this.previewOffsets.get('left')[1] + 36 ]);
			this.arrowOffsets.set('right', [ this.previewOffsets.get('right')[0] + 36, this.previewOffsets.get('right')[1] + 36 ]);
			
			// move preview images
			$(this.previewImages[1]).setStyle({ left: this.previewOffsets.get('left')[0]+'px', top: this.previewOffsets.get('left')[1]+'px' });
			$(this.previewImages[0]).setStyle({ left: this.previewOffsets.get('right')[0]+'px', top: this.previewOffsets.get('right')[1]+'px' });
			
			// move navigation arrows
			$(this.navigationArrows[0]).setStyle({ left: this.arrowOffsets.get('left')[0]+'px', top: this.arrowOffsets.get('left')[1]+'px' });
			$(this.navigationArrows[1]).setStyle({ left: this.arrowOffsets.get('right')[0]+'px', top: this.arrowOffsets.get('right')[1]+'px' });
		}
		
		$super();
	},
	/**
	 * Removes preview images if image viewer is closed
	 */
	close: function($super, event) {
		// if user pressed a key different than ESC or BACKSPACE don't execute close
		if (event != null && event.type == 'keyup' && event.keyCode != Event.KEY_ESC && event.keyCode != Event.KEY_BACKSPACE) {
			return;
		}
		
		// remove images
		$(this.previewImages[0]).remove();
		$(this.previewImages[1]).remove();
		
		// hide navigation arrows
		$(this.navigationArrows[0]).hide();
		$(this.navigationArrows[1]).hide();
		
		// unset image IDs
		this.previewImages = new Array();
		
		// call image viewer method
		$super(event);
	},
	/**
	 * Updates preview images
	 */
	next: function($super, event) {
		$super(event);
		
		window.setTimeout(this.showPreviewImages.bind(this), 500);
	},
	/**
	 * Updates preview images
	 */
	previous: function($super, event) {
		$super(event);
		
		window.setTimeout(this.showPreviewImages.bind(this), 500);
	},
	/**
	 * Fades out preview image if menu disappears
	 */
	hideMenu: function($super) {
		$super();
		
		if (this.previewImages.length == 2) {
			// fade out preview images
			new Effect.Parallel([
				new Effect.Fade(this.previewImages[0], { sync: true }),
				new Effect.Fade(this.previewImages[1], { sync: true }),
				new Effect.Fade(this.navigationArrows[0], { sync: true }),
				new Effect.Fade(this.navigationArrows[1], { sync: true })
			], { duration: 0.1 });
		}
		
		this.isVisible = false;
	},
	/**
	 * Fades in preview images if menu appeared
	 */
	showMenu: function($super) {
		if (this.isOpen && !this.menuIsAppearing) {
			$super();
			
			if (this.previewImages.length == 2) {
				// fade in preview images
				new Effect.Parallel([
					new Effect.Appear(this.previewImages[0], { sync: true }),
					new Effect.Appear(this.previewImages[1], { sync: true }),
					new Effect.Appear(this.navigationArrows[0], { sync: true }),
					new Effect.Appear(this.navigationArrows[1], { sync: true })
				], { duration: 0.1 });
			}
			
			this.isVisible = true;
		}
	},
	/**
	 * Fetches preview images and displays them if applicable
	 */
	showPreviewImages: function() {
		// prevent ghost actions if image viewer is closed
		if (!this.isOpen) {
			return;
		}
		
		// show quadratic thumbnail of next image
		var index = this.currentIndex + 1;
		if (index == this.links.length) {
			index = 0;
		}
		
		// get image
		var image = this.links[index];
		var imagePath = image.readAttribute('thumbnail');
		
		// create image
		var nextImage = new Element('img', { src: imagePath }).setStyle({
			height: '75px',
			left: this.previewOffsets.get('right')[0]+'px',
			position: 'fixed',
			top: this.previewOffsets.get('right')[1]+'px',
			width: '75px'
		}).addClassName('imageViewerCurrentImage imageViewerNavigation pointer').hide();
		var nextImageID = nextImage.identify();
		
		$$('body')[0].insert(nextImage);
		
		// show quadratic thumbnail of previous image
		index = this.currentIndex - 1;
		if (index == -1) {
			index = this.links.length - 1;
		}
		
		// get image
		image = this.links[index];
		imagePath = image.readAttribute('thumbnail');
		
		// create image
		var previousImage = new Element('img', { src: imagePath }).setStyle({
			height: '75px',
			left: this.previewOffsets.get('left')[0]+'px',
			position: 'fixed',
			top: this.previewOffsets.get('left')[1]+'px',
			width: '75px'
		}).addClassName('imageViewerCurrentImage imageViewerNavigation pointer').hide();
		var previousImageID = previousImage.identify();
		
		$$('body')[0].insert(previousImage);
		
		// show images
		if (this.isVisible) {
			// display preview images on first call
			if (this.previewImages.length == 0) {
				nextImage.appear();
				previousImage.appear();
				
				// display arrows
				$(this.navigationArrows[0]).appear();
				$(this.navigationArrows[1]).appear();
				
				this.previewImages = [ nextImageID, previousImageID ];
			}
			else {
				// exchange preview images
				new Effect.Parallel([
					new Effect.Appear(nextImageID, { sync: true }),
					new Effect.Appear(previousImageID, { sync: true }),
					new Effect.Fade(this.previewImages[0], { sync: true }),
					new Effect.Fade(this.previewImages[1], { sync: true })
				], {
					duration: 0.5,
					afterFinish: function() {
						// remove old images
						$(this.previewImages[0]).remove();
						$(this.previewImages[1]).remove();
						
						this.previewImages = [ nextImageID, previousImageID ];
					}.bind(this)
				});
			}
		}
		else {
			// preview images are not visible, just replace images
			if (this.previewImages.length == 2) {
				$(this.previewImages[0]).remove();
				$(this.previewImages[1]).remove();
			}
			
			this.previewImages = [ nextImageID, previousImageID ];
		}
		
		// bind event listener
		nextImage.observe('click', this.next.bind(this));
		previousImage.observe('click', this.previous.bind(this));
	},
	/**
	 * Initializes preview images on first call
	 */
	show: function($super, startFromCurrentLink) {
		$super(startFromCurrentLink);
		
		window.setTimeout(this.showPreviewImages.bind(this), 500);
	}
});
<?php
// define default values
if (!defined('CAPTCHA_RANDOM_FONT_SIZE')) define('CAPTCHA_RANDOM_FONT_SIZE', false);
if (!defined('CAPTCHA_RANDOM_FONT_FACE')) define('CAPTCHA_RANDOM_FONT_FACE', true);
if (!defined('CAPTCHA_RANDOM_BACKGROUND')) define('CAPTCHA_RANDOM_BACKGROUND', true);
if (!defined('CAPTCHA_RANDOM_FONT_COLOR')) define('CAPTCHA_RANDOM_FONT_COLOR', true);
if (!defined('CAPTCHA_FONT_MORPH')) define('CAPTCHA_FONT_MORPH', true);
if (!defined('CAPTCHA_RANDOM_LINES')) define('CAPTCHA_RANDOM_LINES', false);

/**
 * Generates the captcha image.
 * 
 * @author	Michael Schaefer
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.image.captcha
 * @subpackage	data.image.captcha
 * @category 	Community Framework
 */
class CaptchaImage {
	/**
	 * rgb default.
	 * 
	 * @var	array<string>
	 */
	protected $rgb = array('red', 'green', 'blue');
	
	/**
	 * char images
	 * 
	 * @var	resource 
	 */
	protected $chars;
	
	/**
	 * image width
	 * 
	 * @var	integer
	 */
	protected $captchaWidth;
	
	/**
	 * image height
	 * 
	 * @var	integer
	 */
	protected $captchaHeight;
	
	/**
	 * color gradient
	 * 
	 * @var	array
	 */
	protected $gradient;
	
	/**
	 * color gradient border
	 * 
	 * @var	integer
	 */
	protected $gradientBorder;
	
	/**
	 * font size
	 * 
	 * @var	integer
	 */
	protected $fontSize;
	
	/**
	 * path to true type font file.
	 * 
	 * @var	string
	 */
	protected $fontFace;
	
	/**
	 * captcha code word
	 * 
	 * @var	string
	 */
	protected $codeWord;
	
	/**
	 * background color one
	 * 
	 * @var	array<integer>
	 */
	protected $color1;
	
	/**
	 * background color two
	 * 
	 * @var	array<integer>
	 */
	protected $color2;
	
	/**
	 * font color (rgb)
	 * 
	 * @var	array<integer>
	 */
	protected $fontColor;
	
	/**
	 * char width
	 * 
	 * @var	array<integer>
	 */
	protected $charWidth;
	
	/**
	 * number of lines.
	 * 
	 * @var integer
	 */
	protected $lines = 0;
	
	/**
	 * Creates a new CaptchaImage object.
	 * 
	 * @param	string		$code		captcha string
	 */
	public function __construct($code) {
		// set background colors
		$this->setBackgroundColors();
		
		// set code
		$this->codeWord = $code;	
				
		// set font size 
		$this->setFontSize();
		
		// set font face
		$this->setFontFace();
		
		// set background options
		if (CAPTCHA_RANDOM_BACKGROUND) {
			$this->elements	= mt_rand(2, 5);
			$this->setGradientInfo();
		}
		
		// set font color
		if (CAPTCHA_RANDOM_FONT_COLOR) {
			if (CAPTCHA_RANDOM_BACKGROUND) {
				$this->setFontColor();
			}
			else {
				$this->fontColor = array('red' => mt_rand(0, 255), 'green' => mt_rand(0, 255), 'blue' => mt_rand(0, 255));
			}
		}
		else {
			$this->fontColor = array('red' => 2, 'green' => 2, 'blue' => 2);
		}

		// set random lines
		if (CAPTCHA_RANDOM_LINES) {
			$this->lines = mt_rand(1, 10);
		}
		
		// set default captcha size
		$this->captchaWidth	= 0;
		$this->captchaHeight 	= 120;
		
		
		$this->createCharImages();
		$this->createImage();
	}
	
	/**
	 * Sets the background colors.
	 */
	protected function setBackgroundColors() {
		$this->color1 = array('red' => mt_rand(0, 255), 'green' => mt_rand(0, 255), 'blue' => mt_rand(0, 255));
		$this->color2 = array('red' => mt_rand(0, 255), 'green' => mt_rand(0, 255), 'blue' => mt_rand(0, 255));
	}
	
	/**
	 * Sets the font size.
	 */
	protected function setFontSize() {
		if (CAPTCHA_RANDOM_FONT_SIZE) $this->fontSize = mt_rand(36, 60);
		else $this->fontSize = 48;
	}
	
	/**
	 * Sets the font face.
	 * Reads all fonts from 'fonts' folder.
	 */
	protected function setFontFace() {
		$fontFaces =  glob(WCF_DIR.'fonts/*.ttf');
		if (CAPTCHA_RANDOM_FONT_FACE) {
			$n = mt_rand(0, count($fontFaces) - 1);
			$this->fontFace = $fontFaces[$n];
		}
		else $this->fontFace = $fontFaces[0];
	}
	
	/**
	 * Creates a gradient between the 2 generated colors for image background.
	 */
	protected function setGradientInfo() {
		$this->gradient = array('red' => array(), 'green' => array(), 'blue' => array());
		
		for ($i = 0; $i < 3; $i++) {
			$this->gradient[$this->rgb[$i]]['interval'] = abs($this->color1[$this->rgb[$i]] - $this->color2[$this->rgb[$i]]);
			
			if ($this->color1[$this->rgb[$i]] <= $this->color2[$this->rgb[$i]]) $this->gradient[$this->rgb[$i]]['direction'] = 'down';
			else $this->gradient[$this->rgb[$i]]['direction'] = 'up';
		}
		
		$tempArray = array($this->gradient['red']['interval'], $this->gradient['green']['interval'], $this->gradient['blue']['interval']);
		$this->gradientBorder = max($tempArray);
	}
	
	/**
	 * Generates a random font color.
	 */
	protected function setFontColor() {
		$this->fontColor = array('red' => 0, 'green' => 0, 'blue' => 0);
		$this->fontColor['red']  = mt_rand((round((255 - $this->color1['red'] + 255 - $this->color2['red']) / 2) % 256), 256);
		$this->fontColor['green'] = mt_rand((round((255 - $this->color1['green'] + 255 - $this->color2['green']) / 2) % 256), 256);
		$this->fontColor['blue']  = mt_rand((round((255 - $this->color1['blue'] + 255 - $this->color2['blue']) / 2) % 256), 256);
		
		if ((abs($this->color1['red'] - $this->fontColor['red']) <= 50 || abs($this->color2['red'] - $this->fontColor['red']) <= 50)) {
			$this->setFontColor();
		}
	}
	
	/**
	 * Creates an image for every code character.
	 */
	protected function createCharImages() {
		$counter = 0;
		
		for ($i = 0, $j = strlen($this->codeWord); $i < $j; $i++) {
			$char = $this->codeWord[$i];
				
			$tempImageWidth =  $this->fontSize * 2;
			$tempImageHeight = $this->fontSize + 40;
			
			// create image
			$tempImage = imageCreate($tempImageWidth, $tempImageHeight);
			$tempColor = imageColorAllocate($tempImage, $this->color2['red'], $this->color2['green'], $this->color2['blue']);
			imageColorTransparent($tempImage, $tempColor);		
			
			// set font color
			$fontColor = imageColorAllocate($tempImage, $this->fontColor['red'], $this->fontColor['green'], $this->fontColor['blue']);
			
			// write text
			imageTtfText($tempImage, $this->fontSize, 0, 10, $this->fontSize + mt_rand(25, 30), $fontColor, $this->fontFace, $char);
				
			// morph text		
			if (CAPTCHA_FONT_MORPH) {
				$tempImageHeight = 120;
				$tempImage2 = imageCreate($tempImageWidth, 120);
				$tempColor = imageColorAllocate($tempImage2, $this->color2['red'], $this->color2['green'], $this->color2['blue']);
				imageColorTransparent($tempImage2, $tempColor);
			
				$divisor = mt_rand(6, 7);
				$quotient = mt_rand(4, 6);
				$method = mt_rand(0, 1);
			
				// morph text on x-axis
				if ($method == 0) {
					for ($y = 1; $y <= $tempImageHeight; $y++) {
						$posX =  sin($y / $divisor) * $quotient;
						imageCopyMerge($tempImage2, $tempImage, $posX, $y, 0, $y, $tempImageWidth, 1, 100);
					}
				}
			
				// morph text on y-axis
				if ($method == 1) {
					for ($x = 1; $x <= $tempImageWidth; $x++) {
						$posY =  sin($x / $divisor) * $quotient;
						imageCopyMerge($tempImage2, $tempImage, $x, $posY, $x, 0, 1, $tempImageHeight, 100);
					}
				}
			
				$image = $tempImage2;
			}
			else {
				$image = $tempImage;
			}

			// get text width and height
			$positionX = 0;
	
			for ($x = ($tempImageWidth-1); $x > 0; $x--) {
				for ($y = $tempImageHeight - 1; $y > 0; $y--) {
					$color = imageColorAt($image, $x, $y);
					$colorArray = imageColorsForIndex($image, $color);
					if ($colorArray['red'] == $this->fontColor['red'] && $colorArray['green'] == $this->fontColor['green'] && $colorArray['blue'] == $this->fontColor['blue']) {
						$positionX = $x;
						$x = 0;
						$y = 0;
						break;
					}
				}
			}
			
			$width 	= $positionX + 10;
			$height = 100;
			
			// create final char image
			$this->chars[$counter] = imageCreate($width, $height);
			
			$color2 = imageColorAllocate($this->chars[$counter], $this->color2['red'], $this->color2['green'], $this->color2['blue']);
			imageColorTransparent($this->chars[$counter], $color2);
			imageCopyMerge($this->chars[$counter], $image, 5, 5,  0,  0, $width, $tempImageHeight, 100);
			$this->charWidth[$counter] = $width;
			
			// destroy temp images
			imageDestroy($tempImage);
			if (CAPTCHA_FONT_MORPH)	imageDestroy($tempImage2);
			
			
			$counter++;
		}
	}
	
	/**
	 * Creates and outputs the final image. 
	 */
	protected function createImage() {
		// calculate image width
		foreach ($this->charWidth as $width) {
			$this->captchaWidth += $width;
		}
		
		if ($this->captchaWidth + (strlen($this->codeWord) * 10) <= 300) {
			$this->captchaWidth = 300;
		}
		
		// create final image
		$captcha = imageCreate($this->captchaWidth + 40, $this->captchaHeight);
		$min = 0;
		
		// insert background
		if (CAPTCHA_RANDOM_BACKGROUND) {
			for ($y = 1; $y <= $this->elements; $y++) {
				$size = $elementsArr[$y]['size'] = mt_rand(150, 250);
				$elementsArr[$y]['pos']  = array(mt_rand($min, $min+$size), mt_rand(20, 80));
				$min = $min + $size + 50;
			}
			
			$tempRed = $this->color2['red'];
			$tempYellow = $this->color2['green'];
			$tempBlue = $this->color2['blue'];
			
			$counter = 0;
			
			for ($x = $this->gradientBorder; $x > 0; $x--) {
				
				if ($tempRed != $this->color1['red']) {
					if ($this->gradient['red']['direction'] == 'up') $tempRed++;
					else $tempRed--;
				}
				
				if ($tempYellow != $this->color1['green']) {
					if ($this->gradient['green']['direction'] == 'up') $tempYellow++;
					else $tempYellow--;
				}
	
				if ($tempBlue != $this->color1['blue']) {
					if ($this->gradient['blue']['direction'] == 'up') $tempBlue++;
					else $tempBlue--;
				}
				
				$color = imageColorAllocate($captcha, $tempRed, $tempYellow, $tempBlue);
				
				foreach ($elementsArr as $element) {
					imageFilledEllipse($captcha, $element['pos'][0], $element['pos'][1], $element['size'] - $counter,  $element['size'] - $counter, $color);
				}
				$counter++;
			}
		}
		else {
			$tempColor  = imageColorAllocate($captcha, 255, 255, 255);
		}
		
		// insert character images
		$counter = $y = 0;
		foreach ($this->chars as $char) {
			imageCopy($captcha, $char, $y, mt_rand(0, 30), 0, 0, $this->charWidth[$counter], $this->captchaHeight);
			imageDestroy($char);
			$y = $y + $this->charWidth[$counter] + mt_rand(0, 10);
			$counter++;
		}
		
		// insert random lines
		if (CAPTCHA_RANDOM_LINES) {
			for ($x = 1; $x <= $this->lines; $x++) {
				imageSetThickness($captcha, mt_rand(1, 3));
				$pos1 = array('x' => mt_rand(2, $this->captchaWidth - 2), 'y' => mt_rand(2, $this->captchaHeight - 2));
				$pos2 = array('x' => mt_rand(2, $this->captchaWidth - 2), 'y' => mt_rand(2, $this->captchaHeight - 2));
				$lineColor = imageColorAllocate($captcha, $this->fontColor['red'], $this->fontColor['green'], $this->fontColor['blue']);
				imageLine($captcha, $pos1['x'], $pos1['y'], $pos2['x'], $pos2['y'], $lineColor);
			}
		}
		
		// output image
		imagePng($captcha);
		
		// destroy image
		imageDestroy($captcha);
	}
}
?>
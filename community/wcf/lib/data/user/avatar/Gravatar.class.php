<?php
// wcf imports
require_once(WCF_DIR.'lib/data/user/avatar/DisplayableAvatar.class.php');

/**
 * Represents a user gravatar.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.page.user.profile
 * @subpackage	data.user.avatar
 * @category 	Community Framework
 * @see		http://www.gravatar.com
 */
class Gravatar implements DisplayableAvatar {
	const GRAVATAR_BASE = 'http://gravatar.com/avatar/%s?&s=%s&r=g&d=%s';
	const GRAVATAR_CACHE_LOCATION = 'images/avatars/gravatars/%s-%s.jpg';
	const GRAVATAR_CACHE_EXPIRE = 7;

	/**
	 * gravatar e-mail address.
	 * 
	 * @var	string
	 */
	public $gravatar = '';

	/**
	 * size of the gravatar
	 *
	 * @var integer
	 */
	public $size = 150;
	
	/**
	 * url of this gravatar.
	 *
	 * @var string
	 */
	protected $url = null;
	
	/**
	 * Creates a new Gravatar object.
	 * 
	 * @param	string		$gravtar
	 */
	public function __construct($gravatar) {
		$this->gravatar = $gravatar;
	}
	
	/**
	 * @see	DisplayableAvatar::getURL()
	 */
	public function getURL() {
		if ($this->url === null) {
			// try to use cached gravatar
			$cachedFilename = sprintf(self::GRAVATAR_CACHE_LOCATION, md5($this->gravatar), $this->size);
			if (file_exists(WCF_DIR.$cachedFilename) && filemtime(WCF_DIR.$cachedFilename) > (TIME_NOW - (self::GRAVATAR_CACHE_EXPIRE * 86400))) {
				$this->url = RELATIVE_WCF_DIR.$cachedFilename;
			}
			else {
				$gravatarURL = sprintf(self::GRAVATAR_BASE, md5($this->gravatar), $this->size, rawurlencode(RELATIVE_WCF_DIR . 'images/avatars/avatar-default.png'));
				try {
					$tmpFile = FileUtil::downloadFileFromHttp($gravatarURL, 'gravatar');
					copy($tmpFile, WCF_DIR.$cachedFilename);
					@unlink($tmpFile);
					@chmod(WCF_DIR.$cachedFilename, 0777);
					$this->url = RELATIVE_WCF_DIR.$cachedFilename;
				}
				catch (SystemException $e) {
					$this->url = RELATIVE_WCF_DIR . 'images/avatars/avatar-default.png';
				}
			}
		}
		
		return $this->url;
	}
	
	/**
	 * @see	DisplayableAvatar::__toString()
	 */
	public function __toString() {
		return '<img src="'.$this->getURL().'" style="width: '.$this->getWidth().'px; height: '.$this->getHeight().'px" alt="" />';
	}
	
	/**
	 * @see	DisplayableAvatar::setMaxHeight()
	 */
	public function setMaxHeight($maxHeight) {
		if ($maxHeight < $this->size) {
			$this->size = $maxHeight;
			return true;
		}
		
		return false;
	}
	
	/**
	 * @see	DisplayableAvatar::setMaxSize()
	 */
	public function setMaxSize($maxWidth, $maxHeight) {
		$max = max($maxWidth, $maxHeight);
		if ($max < $this->size) {
			$this->size = $max;
			return true;
		}
		
		return false;
	}
	
	/**
	 * @see	DisplayableAvatar::getWidth()
	 */
	public function getWidth() {
		return $this->size;
	}
	
	/**
	 * @see	DisplayableAvatar::getHeight()
	 */
	public function getHeight() {
		return $this->size;
	}
}
?>
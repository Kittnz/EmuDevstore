<?php
// wcf imports
require_once(WCF_DIR.'lib/data/DatabaseObject.class.php');
require_once(WCF_DIR.'lib/data/user/avatar/DisplayableAvatar.class.php');

/**
 * Represents a user avatar.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.page.user.profile
 * @subpackage	data.user.avatar
 * @category 	Community Framework
 */
class Avatar extends DatabaseObject implements DisplayableAvatar {
	/**
	 * Creates a new Avatar object.
	 * 
	 * @param	array		$row
	 * @param	integer		$avatarID
	 */
	public function __construct($avatarID, $row = null) {
		if ($avatarID !== null) {
			$sql = "SELECT	*
				FROM	wcf".WCF_N."_avatar
				WHERE	avatarID = ".$avatarID;
			$row = WCF::getDB()->getFirstRow($sql);
		}
		
		parent::__construct($row);
	}
	
	/**
	 * @see	DisplayableAvatar::getURL()
	 */
	public function getURL() {
		return RELATIVE_WCF_DIR . 'images/avatars/avatar-' . $this->avatarID . '.' . StringUtil::encodeHTML($this->avatarExtension);
	}
	
	/**
	 * @see	DisplayableAvatar::__toString()
	 */
	public function __toString() {
		return '<img src="'.$this->getURL().'" style="width: '.$this->width.'px; height: '.$this->height.'px" alt="" />';
	}
	
	/**
	 * @see	DisplayableAvatar::setMaxHeight()
	 */
	public function setMaxHeight($maxHeight) {
		if ($this->height > $maxHeight) {
			$this->data['width'] = round($this->width * $maxHeight / $this->height, 0);
			$this->data['height'] = $maxHeight;
			return true;
		}
		
		return false;
	}
	
	/**
	 * @see	DisplayableAvatar::setMaxSize()
	 */
	public function setMaxSize($maxWidth, $maxHeight) {
		if ($this->width > $maxWidth || $this->height > $maxHeight) {
			$widthFactor = $maxWidth / $this->width;
			$heightFactor = $maxHeight / $this->height;
			
			if ($widthFactor < $heightFactor) {
				$this->data['width'] = $maxWidth;
				$this->data['height'] = round($this->height * $widthFactor, 0);
			}
			else {
				$this->data['width'] = round($this->width * $heightFactor, 0);
				$this->data['height'] = $maxHeight;
			}
			
			return true;
		}
		
		return false;
	}
	
	/**
	 * @see	DisplayableAvatar::getWidth()
	 */
	public function getWidth() {
		return $this->width;
	}
	
	/**
	 * @see	DisplayableAvatar::getHeight()
	 */
	public function getHeight() {
		return $this->height;
	}
}
?>
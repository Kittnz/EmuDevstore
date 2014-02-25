<?php
// wcf imports
require_once(WCF_DIR.'lib/data/user/avatar/Avatar.class.php');
require_once(WCF_DIR.'lib/data/image/Thumbnail.class.php');

/**
 * Provides functions to create or delete avatars.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.page.user.profile
 * @subpackage	data.user.avatar
 * @category 	Community Framework
 */
class AvatarEditor extends Avatar {
	private static $allowedFileExtensions = null;
	private static $illegalFileExtensions = array('php', 'php3', 'php4', 'php5', 'phtml');
	
	/**
	 * Deletes this avatar.
	 */
	public function delete() {
		// delete database entry
		$sql = "DELETE FROM	wcf".WCF_N."_avatar
			WHERE		avatarID = ".$this->avatarID;
		WCF::getDB()->sendQuery($sql);
		
		// delete file
		@unlink(WCF_DIR . 'images/avatars/avatar-' . $this->avatarID . '.' . $this->avatarExtension);
	}
	
	/**
	 * Updates the data of this avatar.
	 */
	public function update($data) {
		$updates = '';
		foreach ($data as $key => $value) {
			if (!empty($updates)) $updates .= ',';
			$updates .= $key . " = '" . $value . "'";
		}
		
		if (!empty($updates)) {
			$sql = "UPDATE	wcf".WCF_N."_avatar
				SET	".$updates."
				WHERE	avatarID = ".$this->avatarID;
			WCF::getDB()->sendQuery($sql);
		}
	}
	
	/**
	 * Creates a new avatar.
	 * 
	 * @param	string		$tmpName
	 * @param	string		$name
	 * @param	string		$field
	 * @return	integer		avatar id
	 */
	public static function create($tmpName, $name, $field, $userID = 0, $groupID = 0, $neededPoints = 0, $avatarCategoryID = 0) {
		// check avatar content
		if (!ImageUtil::checkImageContent($tmpName)) {
			throw new UserInputException($field, 'badAvatar');
		}
		
		// get file extension
		/*$fileExtension = '';
		if (!empty($name) && StringUtil::indexOf($name, '.') !== false) {
			$fileExtension = StringUtil::toLowerCase(StringUtil::substring($name, StringUtil::lastIndexOf($name, '.') + 1));
		}*/
		
		// get image data
		if (($imageData = @getImageSize($tmpName)) === false) {
			throw new UserInputException($field, 'badAvatar');
		}
		
		// get file extension by mime
		$fileExtension = ImageUtil::getExtensionByMimeType($imageData['mime']);
		
		// check file extension
		if (!in_array($fileExtension, self::getAllowedFileExtensions())) {
			throw new UserInputException($field, 'notAllowedExtension');
		}
		
		// get avatar size
		$width = $imageData[0];
		$height = $imageData[1];
		if (!$width || !$height) {
			throw new UserInputException($field, 'badAvatar');
		}
		
		$size = @filesize($tmpName);
		
		// generate thumbnail if necessary
		if ($width > WCF::getUser()->getPermission('user.profile.avatar.maxWidth') || $height > WCF::getUser()->getPermission('user.profile.avatar.maxHeight')) {
			$thumbnail = new Thumbnail($tmpName, WCF::getUser()->getPermission('user.profile.avatar.maxWidth'), WCF::getUser()->getPermission('user.profile.avatar.maxHeight'));
			$thumbnailSrc = $thumbnail->makeThumbnail();
			
			if ($thumbnailSrc) {
				$file = new File($tmpName);
				$file->write($thumbnailSrc);
				$file->close();
				
				// refresh avatar size
				list($width, $height,) = @getImageSize($tmpName);
				clearstatcache();
				$size = @filesize($tmpName);
				
				// get new file extension
				$fileExtension = ImageUtil::getExtensionByMimeType($thumbnail->getMimeType());
			}
			
			unset($thumbnail, $thumbnailSrc);
		}
		
		// check size again
		if ($width > WCF::getUser()->getPermission('user.profile.avatar.maxWidth') || $height > WCF::getUser()->getPermission('user.profile.avatar.maxHeight') || $size > WCF::getUser()->getPermission('user.profile.avatar.maxSize')) {
			throw new UserInputException($field, 'tooLarge');
		}
		
		// create avatar
		$avatarID = self::insert(basename($name), array(
			'avatarExtension' => $fileExtension,
			'width' => $width,
			'height' => $height,
			'userID' => $userID,
			'groupID' => $groupID,
			'neededPoints' => $neededPoints,
			'avatarCategoryID' => $avatarCategoryID
		));
		
		// copy avatar to avatar folder
		if (!@copy($tmpName, WCF_DIR.'images/avatars/avatar-'.$avatarID.'.'.$fileExtension)) {
			// copy failed
			// delete avatar
			@unlink($tmpName);
			$sql = "DELETE FROM	wcf".WCF_N."_avatar
				WHERE		avatarID = ".$avatarID;
			WCF::getDB()->sendQuery($sql);
			throw new UserInputException($field, 'copyFailed');
		}
		// set permissions
		@chmod(WCF_DIR.'images/avatars/avatar-'.$avatarID.'.'.$fileExtension, 0666);
		
		return $avatarID;
	}
	
	/**
	 * Creates the avatar row in database table.
	 *
	 * @param 	string 		$avatarName
	 * @param 	array		$additionalFields
	 * @return	integer		new avatar id
	 */
	public static function insert($avatarName, $additionalFields = array()){ 
		$keys = $values = '';
		foreach ($additionalFields as $key => $value) {
			$keys .= ','.$key;
			$values .= ",'".escapeString($value)."'";
		}
		
		$sql = "INSERT INTO	wcf".WCF_N."_avatar
					(avatarName
					".$keys.")
			VALUES		('".escapeString($avatarName)."'
					".$values.")";
		WCF::getDB()->sendQuery($sql);
		return WCF::getDB()->getInsertID();
	}
	
	/**
	 * Returns a list of allowed avatar file extensions.
	 * 
	 * @return	array<string>
	 */
	public static function getAllowedFileExtensions() {
		if (self::$allowedFileExtensions === null) {
			self::$allowedFileExtensions = array();
			self::$allowedFileExtensions = array_unique(explode("\n", StringUtil::unifyNewlines(WCF::getUser()->getPermission('user.profile.avatar.allowedFileExtensions'))));
			self::$allowedFileExtensions = array_diff(self::$allowedFileExtensions, self::$illegalFileExtensions);
		}
		
		return self::$allowedFileExtensions;
	}
}
?>
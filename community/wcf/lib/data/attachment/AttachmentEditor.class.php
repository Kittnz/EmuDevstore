<?php
// wcf imports
require_once(WCF_DIR.'lib/data/attachment/Attachment.class.php');
require_once(WCF_DIR.'lib/data/image/Thumbnail.class.php');

/**
 * Provides functions to manage attachments.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.message.attachment
 * @subpackage	data.attachment
 * @category 	Community Framework
 */
class AttachmentEditor extends Attachment {
	/**
	 * Creates a new attachments.
	 *
	 * @param	string		$file
	 * @param	array		$attachmentData
	 * @return	AttachmentEditor
	 */
	public static function create($file, $attachmentData) {
		$attachmentData['isBinary'] = 1;
		if (!$attachmentData['isImage']) {
			$attachmentData['isBinary'] = (int) FileUtil::isBinary($file);
		}
		
		// insert attachment
		$attachmentID = self::insert($attachmentData);
		$attachmentData['attachmentID'] = $attachmentID;
		$attachment = new AttachmentEditor(null, $attachmentData);
		
		// copy tmp file to /attachments folders
		try {
			$path = WCF_DIR.'attachments/attachment-'.$attachmentID;
			if (!@move_uploaded_file($file, $path)) {
				throw new SystemException();
			}
			@chmod($path, 0777);
		}
		catch (SystemException $e) {
			// could not copy uploaded file, rollback insert statement
			$attachment->delete();
			return null;
		}
		
		return $attachment;
	}
	
	/**
	 * Creates the attachment row in database table.
	 *
	 * @param 	array		$data
	 * @return	integer		new attachment id
	 */
	public static function insert($data){ 
		$keys = $values = '';
		foreach ($data as $key => $value) {
			if (!empty($keys)) $keys .= ',';
			$keys .= $key;
			if (!empty($values)) $values .= ',';
			$values .= "'".escapeString($value)."'";
		}
		
		$sql = "INSERT INTO	wcf".WCF_N."_attachment
					(".$keys.")
			VALUES		(".$values.")";
		WCF::getDB()->sendQuery($sql);
		return WCF::getDB()->getInsertID();
	}

	/**
	 * Creates and saves a thumbnail picture.
	 */
	public function createThumbnail($thumbnailWidth = ATTACHMENT_THUMBNAIL_WIDTH, $thumbnailHeight = ATTACHMENT_THUMBNAIL_HEIGHT, $addSourceInfo = ATTACHMENT_THUMBNAIL_ADD_SOURCE_INFO, $useEmbedded = ATTACHMENT_THUMBNAIL_USE_EMBEDDED) {
		// make thumbnail
		$sourceFile = WCF_DIR.'attachments/attachment-'.$this->attachmentID;
		$targetFile = WCF_DIR.'attachments/thumbnail-'.$this->attachmentID;
		$thumbnail = new Thumbnail($sourceFile, $thumbnailWidth, $thumbnailHeight, $addSourceInfo, $this->attachmentName, $useEmbedded);
		
		// get thumbnail
		try {
			if (($thumbnailData = $thumbnail->makeThumbnail())) {
				// save thumbnail
				$file = new File($targetFile);
				$file->write($thumbnailData);
				unset($thumbnailData);
				$file->close();
				
				// update database entry
				$thumbnailSize = intval(filesize($targetFile));
				list($thumbnailWidth, $thumbnailHeight,) = @getImagesize($targetFile);
				$sql = "UPDATE	wcf".WCF_N."_attachment
					SET 	thumbnailType = '".escapeString($thumbnail->getMimeType())."',
						thumbnailSize = ".$thumbnailSize.",
						thumbnailWidth = ".$thumbnailWidth.",
						thumbnailHeight = ".$thumbnailHeight."
					WHERE 	attachmentID = ".$this->attachmentID;
				WCF::getDB()->registerShutdownUpdate($sql);
				// update data
				$this->data['thumbnailType'] = $thumbnail->getMimeType();
				$this->data['thumbnailSize'] = $thumbnailSize;
			}
		}
		catch (Exception $e) {}
	}
	
	/**
	 * Deletes this attachment.
	 */
	public function delete() {
		// delete database entry
		$sql = "DELETE FROM	wcf".WCF_N."_attachment
			WHERE		attachmentID = ".$this->attachmentID;
		WCF::getDB()->registerShutdownUpdate($sql);
		
		// delete file
		$this->deleteFile();
	}
	
	/**
	 * Deletes the file of this attachment.
	 */
	public function deleteFile() {
		// delete attachment file
		if (file_exists(WCF_DIR.'attachments/attachment-'.$this->attachmentID)) @unlink(WCF_DIR.'attachments/attachment-'.$this->attachmentID);
		
		// delete thumbnail, if exists
		if (file_exists(WCF_DIR.'attachments/thumbnail-'.$this->attachmentID)) @unlink(WCF_DIR.'attachments/thumbnail-'.$this->attachmentID);
	}
	
	/**
	 * Sets the position of an attachment.
	 *
	 * @param	integer		$newPosition
	 */
	public function setShowOrder($newPosition) {
		$sql = "UPDATE	wcf".WCF_N."_attachment
			SET	showOrder = ".$newPosition."
			WHERE	attachmentID = ".$this->attachmentID;
		WCF::getDB()->registerShutdownUpdate($sql);
		$this->data['showOrder'] = $newPosition;
	}
}
?>
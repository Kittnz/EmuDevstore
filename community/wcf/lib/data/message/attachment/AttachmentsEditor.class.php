<?php
// wcf imports
require_once(WCF_DIR.'lib/data/message/attachment/Attachments.class.php');
require_once(WCF_DIR.'lib/system/io/File.class.php');
require_once(WCF_DIR.'lib/data/image/Thumbnail.class.php');

/**
 * The AttachmentsEditor class provides functions to upload and delete attachments.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.message.attachment
 * @subpackage	data.message.attachment
 * @category 	Community Framework
 * @deprecated 	1.1
 */
class AttachmentsEditor extends Attachments {
	protected $messageID = 0;
	protected $idHash = '';
	protected $maxFileSize = 0;
	protected $maxUploads = 0;
	protected $allowedExtensions = '';
	protected $allowedExtensionsDesc = '';
	protected $thumbnailWidth, $thumbnailHeight, $addSourceInfo;
	
	protected $errors = array();
	protected $attachmentHashes = array();
	
	/**
	 * Creates a new AttachmentsEditor object.
	 * 
	 * @param	integer		$messageID
	 * @param	string		$isHash
	 * @param	string		$messageType
	 * @param	integer		$maxFileSize
	 * @param	string		$allowedExtensions
	 * @param	integer		$maxUploads
	 */
	public function __construct($messageID = 0, $messageType = 'post', $maxFileSize = 2000000, $allowedExtensions = "gif\njpg\njpeg\npng\nbmp\nzip\ntxt", $maxUploads = 5, $packageID = PACKAGE_ID, $thumbnailWidth = ATTACHMENT_THUMBNAIL_WIDTH, $thumbnailHeight = ATTACHMENT_THUMBNAIL_HEIGHT, $addSourceInfo = ATTACHMENT_THUMBNAIL_ADD_SOURCE_INFO, $useEmbedded = ATTACHMENT_THUMBNAIL_USE_EMBEDDED) {
		$this->messageID = $messageID;
		$this->thumbnailWidth = $thumbnailWidth;
		$this->thumbnailHeight = $thumbnailHeight;
		$this->addSourceInfo = $addSourceInfo;
		$this->useEmbedded = $useEmbedded;
		if (empty($this->messageID)) $this->getIDHash();
		
		// call parent constructor
		parent::__construct($messageID ? $messageID : null, $messageType, $this->idHash, $packageID);
		
		$this->maxFileSize = $maxFileSize;
		$this->maxUploads = $maxUploads;
		$allowedExtensions = StringUtil::unifyNewlines($allowedExtensions);
		$allowedExtensions = implode("\n", array_unique(explode("\n", $allowedExtensions)));
		
		$this->allowedExtensions = '/^('.StringUtil::replace("\n", "|", StringUtil::replace('\*', '.*', preg_quote($allowedExtensions, '/'))).')$/i';
		$this->allowedExtensionsDesc = self::formatAllowedExtensions($allowedExtensions);
		
		$this->getAttachmentHashes();
		
		$this->assign();
	}
	
	/**
	 * Formats allowed file extensions.
	 * 
	 * @param	string		$allowedExtensions
	 * @return	string
	 */
	protected static function formatAllowedExtensions($allowedExtensions) {
		// explode to array
		$extensions = explode("\n", $allowedExtensions);

		// sort
		sort($extensions);
		
		// check wildcards
		for ($i = 0, $j = count($extensions); $i < $j; $i++) {
			if (strpos($extensions[$i], '*') !== false) {
				for ($k = $j - 1; $k > $i; $k--) {
					if (preg_match('/^'.str_replace('\*', '.*', preg_quote($extensions[$i], '/')).'$/i', $extensions[$k])) {
						array_splice($extensions, $k, 1);
						$j--;
					}
				}
			}
		}
		
		// implode to string
		return implode(", ", $extensions);
	}
	
	/**
	 * Gets the value of the id hash.
	 */
	protected function getIDHash() {
		if (isset($_REQUEST['idHash'])) {
			$this->idHash = StringUtil::trim($_REQUEST['idHash']);
		}
		if (empty($this->idHash)) {
			$this->idHash = StringUtil::getRandomID();
		}
	}
	
	/**
	 * Updates the message id of the active attachment.
	 * 
	 * @param	integer		$messageID 
	 */	
	public function updateMessageID($messageID) {
		$this->messageID = $messageID;
		
		if (count($this->getAttachments()) > 0) {
			$sql = "UPDATE 	wcf".WCF_N."_attachment
				SET	containerID = ".$messageID.",
					idHash = ''
				WHERE 	packageID = ".$this->packageID."
					AND idHash = '".escapeString($this->idHash)."'";
			WCF::getDB()->sendQuery($sql);
		}
	}
	
	/**
	 * Handles a request on the attachment edit form.
	 * Deletes old or uploads new attachments.
	 */
	public function handleRequest() {
		// delete uploaded attachments
		if (isset($_POST['delete']) && is_array($_POST['delete']) && count($_POST['delete'])) {
			// delete selected attachments
			$keys = array_keys($_POST['delete']);
			$this->delete(intval(array_shift($keys)));
		}
		
		// move uploaded attachments
		if (isset($_POST['attachmentListPositions']) && is_array($_POST['attachmentListPositions'])) {
			$positionChanged = false;
			$positions = ArrayUtil::toIntegerArray($_POST['attachmentListPositions']);
			foreach ($positions as $attachmentID => $position) {
				$attachmentID = intval($attachmentID);
				if (isset($this->attachments[$this->messageID][$attachmentID]) && $this->attachments[$this->messageID][$attachmentID]['showOrder'] != $position) {
					$this->attachments[$this->messageID][$attachmentID]['showOrder'] = $position;
					$sql = "UPDATE	wcf".WCF_N."_attachment
						SET	showOrder = ".$position."
						WHERE	attachmentID = ".$attachmentID;
					WCF::getDB()->registerShutdownUpdate($sql);
					$positionChanged = true;
				}
			}
			
			if ($positionChanged) {
				uasort($this->attachments[$this->messageID], array('self', 'compareAttachments'));
			}
		}
		
		// upload new attachments
		if (isset($_FILES) && count($_FILES) && isset($_FILES['upload'])) {
			// upload new attachments
			for ($x = 0, $y = count($_FILES['upload']['name']); $x < $y; $x++) {
				$attachment = array();
				$attachment['attachmentName'] = $_FILES['upload']['name'][$x];
				
				if ($attachment['attachmentName']) {
					$attachment['attachment'] = $_FILES['upload']['tmp_name'][$x];
					$attachment['attachmentSize'] = $_FILES['upload']['size'][$x];
					$attachment['sha1Hash'] = sha1_file($attachment['attachment']);
					$attachment['attachmentExtension'] = StringUtil::toLowerCase(StringUtil::substring($attachment['attachmentName'], StringUtil::lastIndexOf($attachment['attachmentName'], '.') + 1));
					$attachment['fileType'] = $_FILES['upload']['type'][$x];
					$attachment['isImage'] = 0;
					if (strchr($attachment['fileType'], 'image')) {
						// check mime
						$attachment['fileType'] = 'application/octet-stream';
						if (($imageData = @getImageSize($attachment['attachment'])) !== false) {
							if (strchr($imageData['mime'], 'image')) {
								$attachment['fileType'] = $imageData['mime'];
								if ($attachment['fileType'] == 'image/bmp') $attachment['fileType'] = 'image/x-ms-bmp';
								$attachment['isImage'] = 1;
							}
						}
					}
					$attachment['showOrder'] = (isset($this->attachments[$this->messageID]) ? count($this->attachments[$this->messageID]) : 0) + 1;
					
					if ($this->checkAttachment($attachment['attachment'], $attachment['attachmentName'].':'.$attachment['sha1Hash'], $attachment['attachmentName'], $attachment['attachmentSize'], $attachment['attachmentExtension'], $attachment['isImage'])) {
						$attachment['messageID'] = $this->messageID;
						$attachment['idHash'] = $this->idHash;
						$attachment['userID'] = WCF::getUser()->userID;
						$attachment['uploadTime'] = TIME_NOW;
						$attachment['thumbnailType'] = '';
						
						if ($this->setAttachment($attachment)) {
							$this->attachmentHashes[count($this->attachmentHashes)] = $attachment['attachmentName'].':'.$attachment['sha1Hash'];
							$attachment['fileTypeIcon'] = $this->getFileTypeIcon($attachment);
							$this->attachments[$this->messageID][$attachment['attachmentID']] = $attachment;
						}
					}
				}
			}
		}
		
		$this->assign();
		
		if (count($this->errors)) {
			// throw user exception 
			throw new UserInputException('attachments', $this->errors);
		}
	}
	
	/**
	 * Checks the validity of the given attachments.
	 * Creates a user input error, if validation fails.
	 * Returns true, if the given attachment is valid.
	 * 
	 * @param	string		$tmpName	path to attachment
	 * @param	string		$fileHash	sha1 hash of attachment
	 * @param	string		$name		original name of attachment
	 * @param	integer		$size		size of attachment
	 * @param	string		$extension	file extension of attachment
	 * @return	boolean				true, if the given attachment is valid
	 */
	protected function checkAttachment($tmpName, $fileHash, $name, $size, $extension, $isImage) {
		$i = count($this->errors);
		
		if (isset($this->attachments[$this->messageID]) && count($this->attachments[$this->messageID]) > $this->maxUploads) {
			return false;
		}
		
		if (!$tmpName) {
			// php upload filesize limit reached
			$this->errors[$i]['attachmentName'] = $name;
			$this->errors[$i]['errorType'] = "fileSizePHP";
			return false;
		}
		
		if (in_array($fileHash, $this->attachmentHashes)) {
			$this->errors[$i]['attachmentName'] = $name;
			$this->errors[$i]['errorType'] = "doubleUpload";
			return false;
		}
		
		if ($size == 0 || $size > $this->maxFileSize) {
			$this->errors[$i]['attachmentName'] = $name;
			$this->errors[$i]['errorType'] = "fileSize";
			return false;
		}
		
		if (!preg_match($this->allowedExtensions, $extension)) {
			$this->errors[$i]['attachmentName'] = $name;
			$this->errors[$i]['errorType'] = "illegalExtension";
			return false;
		}
		
		if ($isImage && !ImageUtil::checkImageContent($tmpName)) {
			$this->errors[$i]['attachmentName'] = $name;
			$this->errors[$i]['errorType'] = "badImage";
			return false;
		}
		
		return true;
	}
	
	/**
	 * Creates a list with the sha1 hashes of the uploaded attachments.
	 */
	protected function getAttachmentHashes() {
		if (isset($this->attachments[$this->messageID])) {
			foreach ($this->attachments[$this->messageID] as $attachment) {
				$this->attachmentHashes[] = $attachment['attachmentName'].':'.$attachment['sha1Hash'];
			}
		}
	}
	
	/**
	 * Deletes an attachment with given attachment id.
	 * 
	 * @param	integer		$attachmentID
	 */
	public function delete($attachmentID) {
		foreach ($this->attachments as $messageID => $attachments) {
			if (isset($attachments[$attachmentID])) {
				unset($this->attachments[$messageID][$attachmentID]);
				if (!count($this->attachments[$messageID])) unset($this->attachments[$messageID]);
				
				// delete database entry
				$sql = "DELETE FROM	wcf".WCF_N."_attachment
					WHERE		attachmentID = ".$attachmentID;
				WCF::getDB()->registerShutdownUpdate($sql);
				
				// delete file
				$this->deleteFile($attachmentID);
				break;
			}
		}
	}
	
	/**
	 * Deletes the files of the attachment with the given id.
	 * 
	 * @param	integer		$attachmentID
	 */
	public static function deleteFile($attachmentID) {
		// delete attachment file
		if (file_exists(WCF_DIR.'attachments/attachment-'.$attachmentID)) @unlink(WCF_DIR.'attachments/attachment-'.$attachmentID);
		
		// delete thumbnail, if exists
		if (file_exists(WCF_DIR.'attachments/thumbnail-'.$attachmentID)) @unlink(WCF_DIR.'attachments/thumbnail-'.$attachmentID);
	}
	
	/**
	 * Deletes all loaded attachments.
	 */
	public function deleteAll() {
		// delete files
		foreach ($this->attachments as $attachments) {
			foreach ($attachments as $attachment) {
				$this->deleteFile($attachment['attachmentID']);
			}
		}
		
		// delete sql data
		if (!empty($this->messageID)) {
			$sql = "DELETE FROM	wcf".WCF_N."_attachment
				WHERE 		packageID = ".$this->packageID."
						AND containerID IN (".$this->messageID.")
						AND containerType = '".escapeString($this->messageType)."'";
			WCF::getDB()->registerShutdownUpdate($sql);
		}
		else if (!empty($this->idHash)) {
			$sql = "DELETE FROM	wcf".WCF_N."_attachment
				WHERE 		packageID = ".$this->packageID."
						AND idHash = '".escapeString($this->idHash)."'
						AND containerType = '".escapeString($this->messageType)."'";
			WCF::getDB()->registerShutdownUpdate($sql);
		}
		
		$this->attachments = array();
	}
	
	/**
	 * Saves an attachment in database and in the folder /attachments.
	 * Creates a thumbnail, if necessary.
	 * 
	 * @param	array		$attachment
	 * @return	boolean		false, if storage fails 
	 */
	public function setAttachment(&$attachment) {
		$attachment['isBinary'] = 1;
		if (!$attachment['isImage']) {
			$attachment['isBinary'] = (int) FileUtil::isBinary($attachment['attachment']);
		}
		
		// insert attachment
		$attachmentID = self::insert($attachment['attachmentName'], array(
			'packageID' => $this->packageID,
			'containerID' => $attachment['messageID'],
			'containerType' => $this->messageType,
			'userID' => $attachment['userID'],
			'attachmentSize' => $attachment['attachmentSize'],
			'isImage' => $attachment['isImage'],
			'sha1Hash' => $attachment['sha1Hash'],
			'idHash' => $attachment['idHash'],
			'uploadTime' => $attachment['uploadTime'],
			'fileType' => $attachment['fileType'],
			'isBinary' => $attachment['isBinary'],
			'showOrder' => $attachment['showOrder']
		));
		$attachment['attachmentID'] = $attachmentID;
		
		// copy tmp file to /attachments folders
		try {
			$path = WCF_DIR.'attachments/attachment-'.$attachmentID;
			if (!@move_uploaded_file($attachment['attachment'], $path)) {
				throw new SystemException();
			}
			else {
				// change attachment file path
				$attachment['attachment'] = $path;
				@chmod($path, 0777);
			}
		}
		catch (SystemException $e) {
			// could not copy uploaded file, rollback insert statement
			$this->delete($attachmentID);
			return false;
		}
		
		// create thumbnail
		if (ATTACHMENT_ENABLE_THUMBNAILS && $attachment['isImage']) {
			$this->saveThumbnail($attachment, $this->thumbnailWidth, $this->thumbnailHeight, $this->addSourceInfo, $this->useEmbedded);
		}
		
		return true;
	}
	
	/**
	 * Creates the attachment row in database table.
	 *
	 * @param 	string 		$attachmentName
	 * @param 	array		$additionalFields
	 * @return	integer		new attachment id
	 */
	public static function insert($attachmentName, $additionalFields = array()){ 
		$keys = $values = '';
		foreach ($additionalFields as $key => $value) {
			$keys .= ','.$key;
			$values .= ",'".escapeString($value)."'";
		}
		
		$sql = "INSERT INTO	wcf".WCF_N."_attachment
					(attachmentName
					".$keys.")
			VALUES		('".escapeString($attachmentName)."'
					".$values.")";
		WCF::getDB()->sendQuery($sql);
		return WCF::getDB()->getInsertID();
	}
	
	/**
	 * Creates and saves a thumbnail picture
	 * 
	 * @param	array		$attachment
	 */
	public static function saveThumbnail(&$attachment, $thumbnailWidth = ATTACHMENT_THUMBNAIL_WIDTH, $thumbnailHeight = ATTACHMENT_THUMBNAIL_HEIGHT, $addSourceInfo = ATTACHMENT_THUMBNAIL_ADD_SOURCE_INFO, $useEmbedded = ATTACHMENT_THUMBNAIL_USE_EMBEDDED) {
		// make thumbnail
		$targetFile = WCF_DIR.'attachments/thumbnail-'.$attachment['attachmentID'];
		$thumbnail = new Thumbnail($attachment['attachment'], $thumbnailWidth, $thumbnailHeight, $addSourceInfo, $attachment['attachmentName'], $useEmbedded);
		
		// get thumbnail
		try {
			if (($thumbnailData = $thumbnail->makeThumbnail())) {
				// save thumbnail
				$file = new File($targetFile);
				$file->write($thumbnailData);
				unset($thumbnailData);
				$file->close();
				
				// update database entry
				$sql = "UPDATE	wcf".WCF_N."_attachment
					SET 	thumbnailType = '".escapeString($thumbnail->getMimeType())."',
						thumbnailSize = ".intval(filesize($targetFile))."
					WHERE 	attachmentID = ".$attachment['attachmentID'];
				WCF::getDB()->registerShutdownUpdate($sql);
			}
		}
		catch (Exception $e) {}
	}
	
	/**
	 * Returns the number of shown upload fields in attachment upload form.
	 * 
	 * @return	integer
	 */
	protected function getMaxUploadFields() {
		if (isset($this->attachments[$this->messageID])) $uploads = count($this->attachments[$this->messageID]);
		else $uploads = 0;
		
		return $this->maxUploads - $uploads;
	}
	
	/**
	 * Assigns attachment variables to the template engine.
	 */
	protected function assign() {
		WCF::getTPL()->assign(array(
			'attachments' => $this->attachments,
			'maxUploadFields' => $this->getMaxUploadFields(),
			'messageID' => $this->messageID,
			'maxUploads' => $this->maxUploads,
			'maxFileSize' => $this->maxFileSize,
			'allowedExtensions' => $this->allowedExtensionsDesc,
			'idHash' => $this->idHash
		));
	}
	
	/**
	 * Copies the selected attachments.
	 * 
	 * @param	array		$messageMapping
	 * @return	array		$attachmentMapping
	 */
	public function copyAll(&$messageMapping) {
		$attachmentMapping = array();
		
		foreach($this->attachments as $oldMessageID => $attachments) {
			foreach ($attachments as $attachment) {
				$newAttachmentID = self::insert($attachment['attachmentName'], array(
					'packageID' => $this->packageID,
					'containerID' => $messageMapping[$oldMessageID],
					'containerType' => $this->messageType,
					'userID' => $attachment['userID'],
					'attachmentSize' => $attachment['attachmentSize'],
					'isImage' => $attachment['isImage'],
					'thumbnailType' => $attachment['thumbnailType'],
					'thumbnailSize' => $attachment['thumbnailSize'],
					'downloads' => $attachment['downloads'],
					'sha1Hash' => $attachment['sha1Hash'],
					'uploadTime' => $attachment['uploadTime'],
					'fileType' => $attachment['fileType'],
					'isBinary' => $attachment['isBinary'],
					'lastDownloadTime' => $attachment['lastDownloadTime'],
					'embedded' => $attachment['embedded']
				));

				if (!isset($attachmentMapping[$messageMapping[$oldMessageID]])) $attachmentMapping[$messageMapping[$oldMessageID]] = array();
				$attachmentMapping[$messageMapping[$oldMessageID]][$attachment['attachmentID']] = $newAttachmentID;
				@copy(WCF_DIR.'attachments/attachment-'.$attachment['attachmentID'], WCF_DIR.'attachments/attachment-'.$newAttachmentID);	
				
				if (!empty($attachment['thumbnailType'])) {
					@copy(WCF_DIR.'attachments/thumbnail-'.$attachment['attachmentID'], WCF_DIR.'attachments/thumbnail-'.$newAttachmentID);
				}
			}
		}
		
		return $attachmentMapping;
	}
	
	/**
	 * Finds embedded attachments in the given message and flags the attachments in database.
	 * 
	 * @param	string		$message
	 * @return	array		embedded attachments
	 */
	public function findEmbeddedAttachments($message) {
		if ($this->messageID) {
			$sql = "UPDATE	wcf".WCF_N."_attachment
				SET	embedded = 0
				WHERE	packageID = ".$this->packageID."
					AND containerID = ".$this->messageID."
					AND containerType = '".escapeString($this->messageType)."'";
			WCF::getDB()->registerShutdownUpdate($sql);
		}
		
		$attachmentIDs = array();
		if (preg_match_all('!\[attach\](\d+)\[/attach\]!i', $message, $matches)) {
			$attachmentIDs = $matches[1];
		}
		if (preg_match_all('!\[attach=(\d+)(?:,(?:left|right))?\]!i', $message, $matches)) {
			$attachmentIDs = array_merge($attachmentIDs, $matches[1]);
		}
		
		$flagAttachmentIDs = array();
		foreach ($attachmentIDs as $attachmentID) {
			foreach ($this->attachments as $attachments) {
				if (isset($attachments[$attachmentID])) {
					$flagAttachmentIDs[] = $attachmentID;
				}
			}
		}
		
		if (count($flagAttachmentIDs) > 0) {
			$flagAttachmentIDs = array_unique($flagAttachmentIDs);
			$sql = "UPDATE	wcf".WCF_N."_attachment
				SET	embedded = 1
				WHERE	attachmentID IN (".implode(',', $flagAttachmentIDs).")";
			WCF::getDB()->registerShutdownUpdate($sql);
		}
		
		return $flagAttachmentIDs;
	}
	
	private static function compareAttachments($attachmentA, $attachmentB) {
		if ($attachmentA['showOrder'] < $attachmentB['showOrder']) return -1;
		else if ($attachmentA['showOrder'] > $attachmentB['showOrder']) return 1;
		return 0;
	}
}
?>
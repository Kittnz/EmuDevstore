<?php
// wcf imports
require_once(WCF_DIR.'lib/data/attachment/MessageAttachmentList.class.php');
require_once(WCF_DIR.'lib/data/attachment/AttachmentEditor.class.php');

/**
 * Provides functions to manage a list of attachment.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.message.attachment
 * @subpackage	data.attachment
 * @category 	Community Framework
 */
class MessageAttachmentListEditor extends MessageAttachmentList {
	protected $idHash = '';
	protected $maxFileSize = 0;
	protected $maxUploads = 0;
	protected $allowedExtensions = '';
	protected $allowedExtensionsDesc = '';
	protected $thumbnailWidth = 0, $thumbnailHeight = 0, $addSourceInfo = 0, $useEmbedded = 0;
	protected $errors = array();
	protected $attachmentHashes = array();

	/**
	 * class name for attachment objects
	 * 
	 * @var	string
	 */
	public $className = 'AttachmentEditor';
	
	/**
	 * Creates a new MessageAttachmentListEditor object.
	 * 
	 * @param	array<integer>	$containerIDArray
	 * @param	string		$containerType
	 * @param	integer		$packageID
	 * @param	integer		$maxFileSize
	 * @param	string		$allowedExtensions
	 * @param	integer		$maxUploads
	 * @param	integer		$thumbnailWidth
	 * @param	integer		$thumbnailHeight
	 * @param	boolean		$addSourceInfo
	 * @param	boolean		$useEmbedded
	 */
	public function __construct($containerIDArray = array(), $containerType = 'post', $packageID = PACKAGE_ID, $maxFileSize = 2000000, $allowedExtensions = "gif\njpg\njpeg\npng\nbmp\nzip\ntxt", $maxUploads = 5, $thumbnailWidth = ATTACHMENT_THUMBNAIL_WIDTH, $thumbnailHeight = ATTACHMENT_THUMBNAIL_HEIGHT, $addSourceInfo = ATTACHMENT_THUMBNAIL_ADD_SOURCE_INFO, $useEmbedded = ATTACHMENT_THUMBNAIL_USE_EMBEDDED) {
		if (!is_array($containerIDArray)) $containerIDArray = array($containerIDArray);
		$this->thumbnailWidth = $thumbnailWidth;
		$this->thumbnailHeight = $thumbnailHeight;
		$this->addSourceInfo = $addSourceInfo;
		$this->useEmbedded = $useEmbedded;
		if (!count($containerIDArray)) $this->getIDHash();
		
		// call parent constructor
		parent::__construct($containerIDArray, $containerType, $this->idHash, $packageID);
		// read attachments
		$this->readObjects();
		
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
	 * Updates the container id of the active attachment.
	 * 
	 * @param	integer		$containerID 
	 */	
	public function updateContainerID($containerID) {
		$this->containerIDArray = array($containerID);
		
		if (count($this->attachments) > 0) {
			if (empty($this->idHash)) {
				throw new SystemException('missing argument idHash.');
			}
			
			$sql = "UPDATE 	wcf".WCF_N."_attachment
				SET	containerID = ".$containerID.",
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
			$tmpContainerIDArray = (count($this->containerIDArray) ? $this->containerIDArray : array(0));
			$positionChanged = false;
			$positions = ArrayUtil::toIntegerArray($_POST['attachmentListPositions']);
			foreach ($positions as $attachmentID => $position) {
				$attachmentID = intval($attachmentID);
				foreach ($tmpContainerIDArray as $containerID) {
					if (isset($this->attachments[$containerID][$attachmentID]) && $this->attachments[$containerID][$attachmentID]->showOrder != $position) {
						$this->attachments[$containerID][$attachmentID]->setShowOrder($position);
						$positionChanged = true;
					}
				}
			}
			
			if ($positionChanged) {
				foreach ($tmpContainerIDArray as $containerID) {
					uasort($this->attachments[$containerID], array('self', 'compareAttachments'));
				}
			}
		}
		
		// upload new attachments
		$containerID = (count($this->containerIDArray) ? reset($this->containerIDArray) : 0);
		if (isset($_FILES) && count($_FILES) && isset($_FILES['upload'])) {
			// upload new attachments
			for ($x = 0, $y = count($_FILES['upload']['name']); $x < $y; $x++) {
				$attachmentData = array();
				$attachmentData['attachmentName'] = $_FILES['upload']['name'][$x];
				
				if ($attachmentData['attachmentName']) {
					$tmpFile = $_FILES['upload']['tmp_name'][$x];
					$attachmentData['attachmentSize'] = $_FILES['upload']['size'][$x];
					$attachmentData['sha1Hash'] = @sha1_file($tmpFile);
					$fileExtension = StringUtil::toLowerCase(StringUtil::substring($attachmentData['attachmentName'], StringUtil::lastIndexOf($attachmentData['attachmentName'], '.') + 1));
					$attachmentData['fileType'] = $_FILES['upload']['type'][$x];
					$attachmentData['isImage'] = 0;
					if (strchr($attachmentData['fileType'], 'image')) {
						// check mime
						$attachmentData['fileType'] = 'application/octet-stream';
						if (($imageData = @getImageSize($tmpFile)) !== false) {
							if (strchr($imageData['mime'], 'image')) {
								$attachmentData['fileType'] = $imageData['mime'];
								if ($attachmentData['fileType'] == 'image/bmp') $attachmentData['fileType'] = 'image/x-ms-bmp';
								$attachmentData['isImage'] = 1;
							}
						}
					}
					$attachmentData['showOrder'] = (isset($this->attachments[$containerID]) ? count($this->attachments[$containerID]) : 0) + 1;
					
					if ($this->checkAttachment($tmpFile, $attachmentData['attachmentName'].':'.$attachmentData['sha1Hash'], $attachmentData['attachmentName'], $attachmentData['attachmentSize'], $fileExtension, $attachmentData['isImage'])) {
						$attachmentData['packageID'] = $this->packageID;
						$attachmentData['containerID'] = $containerID;
						$attachmentData['containerType'] = $this->containerType;
						$attachmentData['idHash'] = $this->idHash;
						$attachmentData['userID'] = WCF::getUser()->userID;
						$attachmentData['uploadTime'] = TIME_NOW;
						$attachmentData['thumbnailType'] = '';
						$attachmentData['width'] = $attachmentData['height'] = 0;
						if ($attachmentData['isImage']) {
							list($width, $height,) = @getImagesize($tmpFile);
							$attachmentData['width'] = $width;
							$attachmentData['height'] = $height;
						}
						
						// save attachment
						if ($attachment = AttachmentEditor::create($tmpFile, $attachmentData)) {
							$this->attachmentHashes[count($this->attachmentHashes)] = $attachmentData['attachmentName'].':'.$attachmentData['sha1Hash'];
							$this->attachments[$containerID][$attachment->attachmentID] = $attachment;
							// save thumbnails
							if (ATTACHMENT_ENABLE_THUMBNAILS && $attachment->isImage) {
								$attachment->createThumbnail($this->thumbnailWidth, $this->thumbnailHeight, $this->addSourceInfo, $this->useEmbedded);
							}
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
		
		// check max uploads
		if (isset($this->attachments[reset($this->containerIDArray)]) && count($this->attachments[reset($this->containerIDArray)]) > $this->maxUploads) {
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
		foreach ($this->containerIDArray as $containerID) {
			if (isset($this->attachments[$containerID])) {
				foreach ($this->attachments[$containerID] as $attachment) {
					$this->attachmentHashes[] = $attachment->attachmentName.':'.$attachment->sha1Hash;
				}
			}
		}
	}
	
	/**
	 * Deletes an attachment with given attachment id.
	 * 
	 * @param	integer		$attachmentID
	 */
	public function delete($attachmentID) {
		foreach ($this->attachments as $containerID => $attachments) {
			if (isset($attachments[$attachmentID])) {
				// delete attachment
				$attachments[$attachmentID]->delete();
				unset($this->attachments[$containerID][$attachmentID]);
				if (!count($this->attachments[$containerID])) unset($this->attachments[$containerID]);
			}
		}
	}
	
	/**
	 * Deletes all loaded attachments.
	 */
	public function deleteAll() {
		// delete files
		foreach ($this->attachments as $attachments) {
			foreach ($attachments as $attachment) {
				$attachment->delete();
			}
		}

		$this->attachments = array();
	}
	
	/**
	 * Returns the number of shown upload fields in attachment upload form.
	 * 
	 * @return	integer
	 */
	protected function getMaxUploadFields() {
		if (isset($this->attachments[reset($this->containerIDArray)])) $uploads = count($this->attachments[reset($this->containerIDArray)]);
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
			'containerID' => (count($this->containerIDArray) ? reset($this->containerIDArray) : 0),
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
		
		foreach ($this->attachments as $oldContainerID => $attachments) {
			foreach ($attachments as $attachment) {
				$newAttachment = AttachmentEditor::create(array(
					'attachmentName' => $attachment->attachmentName,
					'packageID' => $this->packageID,
					'containerID' => $messageMapping[$oldContainerID],
					'containerType' => $this->containerType,
					'userID' => $attachment->userID,
					'attachmentSize' => $attachment->attachmentSize,
					'isImage' => $attachment->isImage,
					'thumbnailType' => $attachment->thumbnailType,
					'thumbnailSize' => $attachment->thumbnailSize,
					'downloads' => $attachment->downloads,
					'sha1Hash' => $attachment->sha1Hash,
					'uploadTime' => $attachment->uploadTime,
					'fileType' => $attachment->fileType,
					'isBinary' => $attachment->isBinary,
					'lastDownloadTime' => $attachment->lastDownloadTime,
					'embedded' => $attachment->embedded,
					'width' => $attachment->width,
					'height' => $attachment->height
				));

				if (!isset($attachmentMapping[$messageMapping[$oldContainerID]])) $attachmentMapping[$messageMapping[$oldContainerID]] = array();
				$attachmentMapping[$messageMapping[$oldContainerID]][$attachment->attachmentID] = $newAttachment->attachmentID;
				@copy(WCF_DIR.'attachments/attachment-'.$attachment->attachmentID, WCF_DIR.'attachments/attachment-'.$newAttachment->attachmentID);	
				
				if (!empty($attachment->thumbnailType)) {
					@copy(WCF_DIR.'attachments/thumbnail-'.$attachment->attachmentID, WCF_DIR.'attachments/thumbnail-'.$newAttachment->attachmentID);
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
		if (count($this->containerIDArray)) {
			$sql = "UPDATE	wcf".WCF_N."_attachment
				SET	embedded = 0
				WHERE	packageID = ".$this->packageID."
					AND containerID = ".implode(',', $this->containerIDArray)."
					AND containerType = '".escapeString($this->containerType)."'";
			WCF::getDB()->registerShutdownUpdate($sql);
		}
		
		$attachmentIDs = array();
		if (preg_match_all('!\[attach\](\d+)\[/attach\]!i', $message, $matches)) {
			$attachmentIDs = $matches[1];
		}
		if (preg_match_all('!\[attach=(\d+)(?:,\'?(?:left|right)\'?)?\]!i', $message, $matches)) {
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
		if ($attachmentA->showOrder < $attachmentB->showOrder) return -1;
		else if ($attachmentA->showOrder > $attachmentB->showOrder) return 1;
		return 0;
	}
}
?>
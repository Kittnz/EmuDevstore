<?php
// wcf imports
require_once(WCF_DIR.'lib/system/io/File.class.php');

/**
 * The Attachments class provides functions to select and show attachments.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.message.attachment
 * @subpackage	data.message.attachment
 * @category 	Community Framework
 * @deprecated 	1.1
 */
class Attachments {
	protected $attachments = array();
	protected $messageType = '';
	public $packageID;
	
	protected static $fileTypeGroups = array(
		'Music' => array('Aif', 'Mid', 'Mp3', 'Ogg', 'Wav', 'Aiff', 'M4a'),
		'System' => array('Bat', 'Dll'),
		'Web' => array('Css', 'Js'),
		'Database' => array('Db'),
		'Image' => array('Dmg', 'Img', 'Iso'),
		'TextDocument' => array('Doc', 'Docx', 'Sxw', 'Odt', 'Swd'),
		'Picture' => array('Png', 'Gif', 'Jpg', 'Jpeg', 'Tif', 'Tiff', 'Tga', 'Bmp', 'Psd'),
		'Html' => array('Html', 'Htm', 'Shtml', 'Tpl', 'Mht'),
		'Text' => array('Txt', 'Log', 'Sql', 'Rtf', 'Wri', 'Diff'),
		'Font' => array('Otf', 'Ttf', 'Fon', 'Dfont'),
		'Archive' => array('Zip', 'Rar', 'Ace', '7z', 'Tar', 'Gz', 'Gzip', 'Tgz', 'Bz2', 'Sit', 'Sitx'),
		'SpreadSheet' => array('Xls', 'Xlsx', 'Sxc', 'Ods', 'Csv'),
		//'Video' => array('Mpeg', 'Avi', 'Wma', 'Mpg'),
		//'Xml' => array('Xml', 'Dtd'),
		//'Flash' => array('Swf', 'Fla'),
		'Java' => array('Jar', 'Java', 'Class')//,
		//'Php' => array('Php', 'Php3', 'Php4', 'Php5', 'Phtml')
	);
	
	/**
	 * Creates a new Attachments object.
	 *
	 * @param	mixed		$messageIDs 
	 * @param	string		$messageType
	 * @param	string		$idHash
	 */
	public function __construct($messageIDs = null, $messageType = 'post', $idHash = '', $packageID = PACKAGE_ID) {
		$this->messageType = $messageType;
		$this->packageID = $packageID;
		
		// build sql
		$sql = '';
		if ($messageIDs !== null && $messageIDs !== 0) {
			$sql = "SELECT		attachment.*, containerID AS messageID, containerType AS messageType
				FROM		wcf".WCF_N."_attachment attachment
				WHERE		packageID = ".$this->packageID."
						AND containerID IN (".$messageIDs.")
						AND containerType = '".escapeString($this->messageType)."'
				ORDER BY 	showOrder, uploadTime, attachmentID";
		}
		else if (!empty($idHash)) {
			$sql = "SELECT		attachment.*, containerID AS messageID, containerType AS messageType
				FROM 		wcf".WCF_N."_attachment attachment
				WHERE 		packageID = ".$this->packageID."
						AND idHash = '".escapeString($idHash)."'
						AND containerType = '".escapeString($this->messageType)."'
				ORDER BY 	showOrder, uploadTime, attachmentID";
		}
		
		if (!empty($sql)) {
			$result = WCF::getDB()->sendQuery($sql);
			while ($row = WCF::getDB()->fetchArray($result)) {
 				// get file type icon
 				$row['fileTypeIcon'] = $this->getFileTypeIcon($row);
 				$row['contentPreview'] = $this->getContentPreview($row);
 				
 				// add attachment to list	
 				$this->attachments[$row['messageID']][$row['attachmentID']] = $row;
			}
		}
	}
	
	/**
	 * Returns an abstract of the file content.
	 * 
	 * @param	array		$data
	 * @return	string
	 */
	protected static function getContentPreview($data) {
		if (!ATTACHMENT_ENABLE_CONTENT_PREVIEW || $data['isBinary'] || $data['attachmentSize'] == 0) {
			return '';
		}
		
		$content = '';
		try {
			$file = new File(WCF_DIR.'attachments/attachment-'.$data['attachmentID'], 'rb');
			$content = $file->read(2003);
			$file->close();
			
			if (CHARSET == 'UTF-8') {
				if (!StringUtil::isASCII($content) && !StringUtil::isUTF8($content)) {
					$content = StringUtil::convertEncoding('ISO-8859-1', CHARSET, $content);
				}
			
				$content = StringUtil::substring($content, 0, 500);
				if (strlen($content) < $file->filesize()) $content .= '...';
			}
			else {
				if (StringUtil::isUTF8($content)) {
					return '';
				}
				
				$content = StringUtil::substring($content, 0, 500);
				if ($file->filesize() > 500) $content .= '...';
			}
		}
		catch (Exception $e) {} // ignore errors
		
		return $content;
	}
	
	/**
	 * Returns the right file type icon for the given attachment.
	 * 
	 * @param	array		$data
	 * @return	string
	 */
	protected static function getFileTypeIcon($data) {
		// get file extension
		$extension = StringUtil::firstCharToUpperCase(StringUtil::toLowerCase(StringUtil::substring($data['attachmentName'], StringUtil::lastIndexOf($data['attachmentName'], '.') + 1)));
		
		// get file type icon
		if (file_exists(WCF_DIR.'icon/fileTypeIcon'.$extension.'M.png')) {
			return StyleManager::getStyle()->getIconPath('fileTypeIcon'.$extension.'M.png');
		}
		else {
			foreach (self::$fileTypeGroups as $key => $group) {
				if (in_array($extension, $group)) return StyleManager::getStyle()->getIconPath('fileTypeIcon'.$key.'M.png'); 
			}
			
			return StyleManager::getStyle()->getIconPath('fileTypeIconDefaultM.png');
		}
	}

	/**
	 * Returns the attachments with the given message id.
	 *
	 * @param	integer		$messageID
	 * @return	array		attachments with the given message id.
	 */
	public function getAttachments($messageID = 0) {
		if (isset($this->attachments[$messageID])) return $this->attachments[$messageID];
		if ($messageID === null) return $this->attachments;
		return array();
	}
	
	/**
	 * Returns all selected attachments sorted by file type.
	 *
	 * @return	array
	 */
	public function getSortedAttachments($showPreview = true) {
		$result = array();
		
		foreach ($this->attachments as $messageID => $attachments) {
			if (!isset($result[$messageID])) {
				$result[$messageID] = array();
				$result[$messageID]['files'] = array();
				$result[$messageID]['images'] = array();
			}
		
			foreach ($attachments as $attachment) {
				if ($attachment['isImage'] && $showPreview) {
					if (!empty($attachment['thumbnailType'])) {
						list($width, $height, ) = @getImageSize(WCF_DIR.'attachments/thumbnail-'.$attachment['attachmentID']);
						$attachment['width'] = $width;
						$attachment['height'] = $height;
						$result[$messageID]['images'][$attachment['attachmentID']] = $attachment;
						continue;
					}
					else {
						// check image size
						if (file_exists(WCF_DIR.'attachments/attachment-'.$attachment['attachmentID'])) {
							list($width, $height, ) = @getImageSize(WCF_DIR.'attachments/attachment-'.$attachment['attachmentID']);
							if ($width > 0 && $height > 0 && $width <= ATTACHMENT_THUMBNAIL_WIDTH && $height <= ATTACHMENT_THUMBNAIL_HEIGHT) {
								$attachment['width'] = $width;
								$attachment['height'] = $height;
								$result[$messageID]['images'][$attachment['attachmentID']] = $attachment;
								continue;
							}
						}
					}
				}
			
				if (!$showPreview) {
					$row['contentPreview'] = '';
				}
				$result[$messageID]['files'][$attachment['attachmentID']] = $attachment;
			}
		}
		
		return $result;
	}
	
	/**
	 * Removes the embedded attachments in the sorted attachments list.
	 * 
	 * @param	array		$sortedAttachments
	 */
	public static function removeEmbeddedAttachments(&$sortedAttachments) {
		foreach ($sortedAttachments as $messageID => $types) {
			foreach ($types as $type => $attachments) {
				foreach ($attachments as $attachmentID => $attachment) {
					if ($attachment['embedded']) {
						unset($sortedAttachments[$messageID][$type][$attachmentID]);
					}
				}
			}
		}
	}
}
?>
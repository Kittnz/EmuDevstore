<?php
// wcf imports
require_once(WCF_DIR.'lib/data/DatabaseObject.class.php');
require_once(WCF_DIR.'lib/data/attachment/AttachmentContainerType.class.php');

/**
 * Represents an attachment.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.message.attachment
 * @subpackage	data.attachment
 * @category 	Community Framework
 */
class Attachment extends DatabaseObject {
	/**
	 * list of the file types
	 * 
	 * @var	array
	 */
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
		'SpreadSheet' => array('Xls', 'Xlsx','Sxc', 'Ods', 'Csv'),
		//'Video' => array('Mpeg', 'Avi', 'Wma', 'Mpg'),
		//'Xml' => array('Xml', 'Dtd'),
		//'Flash' => array('Swf', 'Fla'),
		'Java' => array('Jar', 'Java', 'Class')//,
		//'Php' => array('Php', 'Php3', 'Php4', 'Php5', 'Phtml')
	);
	
	/**
	 * cache of the container types
	 * 
	 * @var	array
	 */
	protected static $containerTypeCache = null;
	
	/**
	 * preview of file contents
	 * 
	 * @var	string
	 */
	protected $contentPreview = null;
	
	/**
	 * path to file type icon
	 * 
	 * @var	string
	 */
	protected $fileTypeIcon = null;
	
	/**
	 * Creates a new Attachment object.
	 *
	 * @param	integer		$attachmentID
	 * @param	array<mixed>	$row
	 */
	public function __construct($attachmentID, $row = null) {
		if ($attachmentID !== null) {
			$sql = "SELECT	*
				FROM	wcf".WCF_N."_attachment
				WHERE	attachmentID = ".$attachmentID;
			$row = WCF::getDB()->getFirstRow($sql);
		}
		
		parent::__construct($row);
	}
	
	/**
	 * Returns an abstract of the file content.
	 * 
	 * @return	string
	 */
	public function getContentPreview() {
		if ($this->contentPreview === null) {
			$this->contentPreview = '';
			
			if (ATTACHMENT_ENABLE_CONTENT_PREVIEW && !$this->isBinary && $this->attachmentSize != 0) {
				try {
					$file = new File(WCF_DIR.'attachments/attachment-'.$this->attachmentID, 'rb');
					$this->contentPreview = $file->read(2003);
					$file->close();
					
					if (CHARSET == 'UTF-8') {
						if (!StringUtil::isASCII($this->contentPreview) && !StringUtil::isUTF8($this->contentPreview)) {
							$this->contentPreview = StringUtil::convertEncoding('ISO-8859-1', CHARSET, $this->contentPreview);
						}
					
						$this->contentPreview = StringUtil::substring($this->contentPreview, 0, 500);
						if (strlen($this->contentPreview) < $file->filesize()) $this->contentPreview .= '...';
					}
					else {
						if (StringUtil::isUTF8($this->contentPreview)) {
							$this->contentPreview = '';
						}
						else {
							$this->contentPreview = StringUtil::substring($this->contentPreview, 0, 500);
							if ($file->filesize() > 500) $this->contentPreview .= '...';
						}
					}
				}
				catch (Exception $e) {} // ignore errors
			}
		}
		
		return $this->contentPreview;
	}
	
	/**
	 * Returns the right file type icon for the given attachment.
	 * 
	 * @return	string
	 */
	public function getFileTypeIcon() {
		if ($this->fileTypeIcon === null) {
			$this->fileTypeIcon = '';
			
			// get file extension
			$extension = StringUtil::firstCharToUpperCase(StringUtil::toLowerCase(StringUtil::substring($this->attachmentName, StringUtil::lastIndexOf($this->attachmentName, '.') + 1)));
			
			// get file type icon
			if (file_exists(WCF_DIR.'icon/fileTypeIcon'.$extension.'M.png')) {
				$this->fileTypeIcon = 'fileTypeIcon'.$extension.'M.png';
			}
			else {
				foreach (self::$fileTypeGroups as $key => $group) {
					if (in_array($extension, $group)) {
						$this->fileTypeIcon = 'fileTypeIcon'.$key.'M.png';
						break;
					} 
				}
				
				if (empty($this->fileTypeIcon)) {
					$this->fileTypeIcon = 'fileTypeIconDefaultM.png';
				}
			}
		}
		
		if (!class_exists('StyleManager')) return RELATIVE_WCF_DIR.'icon/'.$this->fileTypeIcon;
		else return StyleManager::getStyle()->getIconPath($this->fileTypeIcon);
	}
	
	/**
	 * Returns the url to the object that contains this attachment.
	 * 
	 * return	string
	 */
	public function getContainerURL() {
		if (self::$containerTypeCache === null) {
			// load cache
			WCF::getCache()->addResource('act-'.PACKAGE_ID, WCF_DIR.'cache/cache.act-'.PACKAGE_ID.'.php', WCF_DIR.'lib/system/cache/CacheBuilderAttachmentContainerType.class.php');
			self::$containerTypeCache = WCF::getCache()->get('act-'.PACKAGE_ID);
		}
		
		if (isset(self::$containerTypeCache[$this->containerType]) && self::$containerTypeCache[$this->containerType]->url && $this->containerID) {
			$occurrences = StringUtil::countSubstring(self::$containerTypeCache[$this->containerType]->url, '%s');
			if ($occurrences == 1) {
				return sprintf(self::$containerTypeCache[$this->containerType]->url, $this->containerID).SID_ARG_2ND_NOT_ENCODED;
			}
			else if ($occurrences == 2) {
				return sprintf(self::$containerTypeCache[$this->containerType]->url, $this->containerID, SID_ARG_2ND_NOT_ENCODED);
			}
			else {
				return sprintf(self::$containerTypeCache[$this->containerType]->url, $this->containerID, SID_ARG_2ND_NOT_ENCODED, $this->containerID);
			}
		}
		
		return '';
	}
	
	public function getWidth() {
		if (empty($this->data['width'])) {
			list($this->data['width'], $this->data['height'], ) = @getImageSize(WCF_DIR.'attachments/attachment-'.$this->attachmentID);
		}
		
		return $this->data['width'];
	}
	
	public function getHeight() {
		if (empty($this->data['height'])) {
			list($this->data['width'], $this->data['height'], ) = @getImageSize(WCF_DIR.'attachments/attachment-'.$this->attachmentID);
		}
		
		return $this->data['height'];
	}
	
	public function getThumbnailWidth() {
		if (empty($this->data['thumbnailWidth'])) {
			list($this->data['thumbnailWidth'], $this->data['thumbnailHeight'], ) = @getImageSize(WCF_DIR.'attachments/thumbnail-'.$this->attachmentID);
		}
		
		return $this->data['thumbnailWidth'];
	}
	
	public function getThumbnailHeight() {
		if (empty($this->data['thumbnailHeight'])) {
			list($this->data['thumbnailWidth'], $this->data['thumbnailHeight'], ) = @getImageSize(WCF_DIR.'attachments/thumbnail-'.$this->attachmentID);
		}
		
		return $this->data['thumbnailHeight'];
	}
}
?>
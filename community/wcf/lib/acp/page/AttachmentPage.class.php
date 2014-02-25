<?php
// wcf imports
require_once(WCF_DIR.'lib/page/AbstractPage.class.php');

/**
 * Shows an attachment.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.message.attachment
 * @subpackage	acp.page
 * @category 	Community Framework
 */
class AttachmentPage extends AbstractPage {
	public $attachmentID = 0;
	public $thumbnail = 0;
	public static $inlineMimeTypes = array('image/gif', 'image/jpeg', 'image/png', 'application/pdf', 'image/pjpeg', 'image/x-png');
	public $attachment = array();
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['attachmentID'])) $this->attachmentID = intval($_REQUEST['attachmentID']);
		if (isset($_REQUEST['thumbnail'])) $this->thumbnail = intval($_REQUEST['thumbnail']);
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		try {
			// get attachment from database
			$sql = "SELECT		container_type.*, attachment.*
				FROM 		wcf".WCF_N."_attachment attachment
				LEFT JOIN	wcf".WCF_N."_attachment_container_type container_type
				ON		(container_type.packageID = attachment.packageID AND container_type.containerType = attachment.containerType)
				WHERE 		attachment.attachmentID = ".$this->attachmentID."
						AND attachment.packageID IN (
							SELECT	dependency
							FROM	wcf".WCF_N."_package_dependency
							WHERE	packageID = ".PACKAGE_ID."
						)";
			$this->attachment = WCF::getDB()->getFirstRow($sql);
			
			// attachment exists?
			if (!isset($this->attachment['attachmentID']) || $this->attachment['isPrivate']) {
				throw new IllegalLinkException();
			}
			
			// attachment has thumbnail?
			if ($this->thumbnail && !$this->attachment['thumbnailType']) {
				throw new IllegalLinkException();
			}
			
			// check permission
			WCF::getUser()->checkPermission('admin.attachment.canDeleteAttachment');
			
			parent::show();
			
			// send headers
			// file type
			$mimeType = ($this->thumbnail ? $this->attachment['thumbnailType'] : $this->attachment['fileType']);
			if ($mimeType == 'image/x-png') $mimeType = 'image/png';
			@header('Content-Type: '.$mimeType);
			
			// file name
			@header('Content-disposition: '.(!in_array($mimeType, self::$inlineMimeTypes) ? 'attachment; ' : '').'filename="'.$this->attachment['attachmentName'].'"');
			
			// send file size
			@header('Content-Length: '.($this->thumbnail ? $this->attachment['thumbnailSize'] : $this->attachment['attachmentSize']));
			
			// no cache headers
			if (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) {
				// internet explorer doesn't cache files downloaded from a https website, if 'Pragma: no-cache' was sent 
				// @see http://support.microsoft.com/kb/316431/en
				@header('Pragma: public');
			}
			else {
				@header('Pragma: no-cache');
			}
			@header('Expires: 0');
			
			// show attachment
			readfile(WCF_DIR . 'attachments/'. ($this->thumbnail ? 'thumbnail' : 'attachment') .'-' . $this->attachment['attachmentID']);
			exit;
		}
		catch (Exception $e) {
			if ($this->embedded == 1) {
				@header('Content-Type: image/png');
				@header('Content-disposition: filename="imageNoPermissionL.png"');
				readfile(WCF_DIR . 'icon/imageNoPermissionL.png');
				exit;
			}
			else {
				throw $e;
			}
		}
	}
}
?>
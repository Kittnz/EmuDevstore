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
 * @subpackage	page
 * @category 	Community Framework
 */
class AttachmentPage extends AbstractPage {
	public $attachmentID = 0;
	public $thumbnail = 0;
	public $sha1Hash = '';
	public static $inlineMimeTypes = array('image/gif', 'image/jpeg', 'image/png', 'application/pdf', 'image/pjpeg', 'image/x-png');
	public $attachment = array();
	public $embedded = 0;
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['attachmentID'])) $this->attachmentID = intval($_REQUEST['attachmentID']);
		if (isset($_REQUEST['thumbnail'])) $this->thumbnail = intval($_REQUEST['thumbnail']);
		if (isset($_REQUEST['h'])) $this->sha1Hash = $_REQUEST['h'];
		if (isset($_REQUEST['embedded'])) $this->embedded = intval($_REQUEST['embedded']);
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		try {
			// get attachment from database
			$sql = "SELECT	*
				FROM 	wcf".WCF_N."_attachment
				WHERE 	attachmentID = ".$this->attachmentID." 
					AND packageID IN (
						SELECT	dependency
						FROM	wcf".WCF_N."_package_dependency
						WHERE	packageID = ".PACKAGE_ID."
					)";
			$this->attachment = WCF::getDB()->getFirstRow($sql);
			
			// check attachment id
			if (!isset($this->attachment['attachmentID'])) {
				throw new IllegalLinkException();
			}
			
			// check thumbnail status
			if ($this->thumbnail && !$this->attachment['thumbnailType']) {
				throw new IllegalLinkException();
			}
			
			parent::show();
			
			// reset URI in session
			if ($this->thumbnail && WCF::getSession()->lastRequestURI) {
				WCF::getSession()->setRequestURI(WCF::getSession()->lastRequestURI);
			}
			
			// update download count
			if (!$this->thumbnail) {
				$sql = "UPDATE	wcf".WCF_N."_attachment
					SET	downloads = downloads + 1,
						lastDownloadTime = ".TIME_NOW."
					WHERE	attachmentID = ".$this->attachmentID;
				WCF::getDB()->registerShutdownUpdate($sql);
			}
			
			// send headers
			// file type
			$mimeType = ($this->thumbnail ? $this->attachment['thumbnailType'] : $this->attachment['fileType']);
			if ($mimeType == 'image/x-png') $mimeType = 'image/png';
			@header('Content-Type: '.$mimeType);
			
			// file name
			@header('Content-disposition: '.(!in_array($mimeType, self::$inlineMimeTypes) ? 'attachment; ' : 'inline; ').'filename="'.$this->attachment['attachmentName'].'"');
			
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
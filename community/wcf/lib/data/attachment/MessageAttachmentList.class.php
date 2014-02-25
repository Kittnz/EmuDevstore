<?php
// wcf imports
require_once(WCF_DIR.'lib/data/attachment/AttachmentList.class.php');

/**
 * Represents a list of embedded attachments.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.message.attachment
 * @subpackage	data.attachment
 * @category 	Community Framework
 */
class MessageAttachmentList extends AttachmentList {
	/**
	 * list of container ids
	 *
	 * @var	array<integer>
	 */
	public $containerIDArray = array();

	/**
	 * container type
	 *
	 * @var	string
	 */
	public $containerType = '';
	
	/**
	 * package id
	 *
	 * @var	integer
	 */
	public $packageID = 0;
	
	/**
	 * sql limit
	 *
	 * @var integer
	 */
	public $sqlLimit = 0;
	
	/**
	 * sql order by statement
	 *
	 * @var	string
	 */
	public $sqlOrderBy = 'attachment.showOrder, attachment.uploadTime, attachment.attachmentID';

	/**
	 * Creates a new MessageAttachmentList object.
	 *
	 * @param	array<integer>	$containerIDArray 
	 * @param	string		$containerType
	 * @param	string		$idHash
	 * @param	integer		$packageID
	 */
	public function __construct($containerIDArray = array(), $containerType = 'post', $idHash = '', $packageID = PACKAGE_ID) {
		$this->containerIDArray = (!is_array($containerIDArray) ? array($containerIDArray) : $containerIDArray);
		$this->containerType = $containerType;
		$this->packageID = $packageID;
		
		// build sql
		if (count($this->containerIDArray)) {
			$this->sqlConditions .= "attachment.packageID = ".$this->packageID." AND attachment.containerID IN (".implode(',', $this->containerIDArray).") AND attachment.containerType = '".escapeString($this->containerType)."'";
		}
		else if (!empty($idHash)) {
			$this->sqlConditions .= "attachment.packageID = ".$this->packageID." AND attachment.idHash = '".escapeString($idHash)."' AND attachment.containerType = '".escapeString($this->containerType)."'";
		}
		else {
			throw new SystemException('missing argument containerIDArray.');
		}
	}
	
	/**
	 * @see DatabaseObjectList::readObjects()
	 */
	public function readObjects() {
		parent::readObjects();
		
		// group by container id
		$groupedAttachments = array();
		foreach ($this->attachments as $attachment) {
			if (!isset($groupedAttachments[$attachment->containerID])) {
				$groupedAttachments[$attachment->containerID] = array();
			}
			$groupedAttachments[$attachment->containerID][$attachment->attachmentID] = $attachment;
		}
		
		$this->attachments = $groupedAttachments;
	}
	
	/**
	 * Returns the attachments with the given container id.
	 *
	 * @param	integer		$containerID
	 * @return	array
	 */
	public function getAttachments($containerID = 0) {
		if (isset($this->attachments[$containerID])) return $this->attachments[$containerID];
		return array();
	}
	
	/**
	 * @see DatabaseObjectList::getObjects()
	 */
	public function getSortedAttachments($preview = true) {
		// group by container id and sort by file type
		$sortedAttachments = array();
		foreach ($this->getObjects() as $containerID => $attachments) {
			foreach ($attachments as $attachment) {
				if (!isset($sortedAttachments[$attachment->containerID])) {
					$sortedAttachments[$attachment->containerID] = array('files' => array(), 'images' => array());
				}
				
				// show image preview
				if ($preview && $attachment->isImage) {
					if ($attachment->thumbnailType) {
						// show thumbnail
						$sortedAttachments[$attachment->containerID]['images'][$attachment->attachmentID] = $attachment;
						continue;
					}
					else {
						// check image size
						if ($attachment->getWidth() > 0 && $attachment->getHeight() > 0 && $attachment->getWidth() <= ATTACHMENT_THUMBNAIL_WIDTH && $attachment->getHeight() <= ATTACHMENT_THUMBNAIL_HEIGHT) {
							$sortedAttachments[$attachment->containerID]['images'][$attachment->attachmentID] = $attachment;
							continue;
						}
					}
				}
				
				$sortedAttachments[$attachment->containerID]['files'][$attachment->attachmentID] = $attachment;
			}
		}
		
		return $sortedAttachments;
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
					if ($attachment->embedded) {
						unset($sortedAttachments[$messageID][$type][$attachmentID]);
					}
				}
			}
		}
	}
}
?>
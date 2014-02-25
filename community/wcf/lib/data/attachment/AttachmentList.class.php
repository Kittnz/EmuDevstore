<?php
// wcf imports
require_once(WCF_DIR.'lib/data/DatabaseObjectList.class.php');
require_once(WCF_DIR.'lib/data/attachment/Attachment.class.php');

/**
 * Represents a list of attachments.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.message.attachment
 * @subpackage	data.attachment
 * @category 	Community Framework
 */
class AttachmentList extends DatabaseObjectList {
	/**
	 * list of attachments
	 * 
	 * @var array<Attachment>
	 */
	public $attachments = array();

	/**
	 * class name for attachment objects
	 * 
	 * @var	string
	 */
	public $className = 'Attachment';
	
	/**
	 * @see DatabaseObjectList::countObjects()
	 */
	public function countObjects() {
		$sql = "SELECT	COUNT(*) AS count
			FROM	wcf".WCF_N."_attachment attachment
			".(!empty($this->sqlConditions) ? "WHERE ".$this->sqlConditions : '');
		$row = WCF::getDB()->getFirstRow($sql);
		return $row['count'];
	}
	
	/**
	 * @see DatabaseObjectList::readObjects()
	 */
	public function readObjects() {
		$sql = "SELECT		".(!empty($this->sqlSelects) ? $this->sqlSelects.',' : '')."
					attachment.*
			FROM		wcf".WCF_N."_attachment attachment
			".$this->sqlJoins."
			".(!empty($this->sqlConditions) ? "WHERE ".$this->sqlConditions : '')."
			".(!empty($this->sqlOrderBy) ? "ORDER BY ".$this->sqlOrderBy : '');
		$result = WCF::getDB()->sendQuery($sql, $this->sqlLimit, $this->sqlOffset);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$this->attachments[] = new $this->className(null, $row);
		}
	}
	
	/**
	 * @see DatabaseObjectList::getObjects()
	 */
	public function getObjects() {
		return $this->attachments;
	}
	
	/**
	 * Returns a list of available container types.
	 * 
	 * @return	array
	 */
	public function getAvailableContainerTypes() {
		$containerTypes = array();
		$sql = "SELECT		DISTINCT attachment.containerType
			FROM		wcf".WCF_N."_attachment attachment
			".(!empty($this->sqlConditions) ? "WHERE ".$this->sqlConditions : '');
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$containerTypes[$row['containerType']] = $row;
		}
		
		ksort($containerTypes);
		
		return $containerTypes;
	}
	
	/**
	 * Returns a list of available mime types.
	 * 
	 * @return	array
	 */
	public function getAvailableFileTypes() {
		$fileTypes = array();
		$sql = "SELECT		DISTINCT attachment.fileType
			FROM		wcf".WCF_N."_attachment attachment
			".(!empty($this->sqlConditions) ? "WHERE ".$this->sqlConditions : '');
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$fileTypes[$row['fileType']] = $row;
		}
		
		ksort($fileTypes);
		
		return $fileTypes;
	}
	
	/**
	 * Returns attachment statistics.
	 * 
	 * @return	array<integer>
	 */
	public function getStats() {
		$sql = "SELECT	COUNT(*) AS count,
				IFNULL(SUM(attachment.attachmentSize), 0) AS size,
				IFNULL(SUM(downloads), 0) AS downloads
			FROM	wcf".WCF_N."_attachment attachment
			".(!empty($this->sqlConditions) ? "WHERE ".$this->sqlConditions : '');
		$row = WCF::getDB()->getFirstRow($sql);
		return $row;
	}
}
?>
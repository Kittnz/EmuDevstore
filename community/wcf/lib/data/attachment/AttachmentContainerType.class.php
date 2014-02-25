<?php
/**
 * Represents an attachment container type.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.message.attachment
 * @subpackage	data.attachment
 * @category 	Community Framework
 */
class AttachmentContainerType extends DatabaseObject {
	/**
	 * Creates a new AttachmentContainerType object.
	 *
	 * @param	integer		$containerTypeID
	 * @param	array<mixed>	$row
	 */
	public function __construct($containerTypeID, $row = null) {
		if ($containerTypeID !== null) {
			$sql = "SELECT	*
				FROM	wcf".WCF_N."_attachment_container_type
				WHERE	containerTypeID = ".$containerTypeID;
			$row = WCF::getDB()->getFirstRow($sql);
		}
		
		parent::__construct($row);
	}
}
?>
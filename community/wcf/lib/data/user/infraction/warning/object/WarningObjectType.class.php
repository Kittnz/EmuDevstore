<?php
/**
 * A warning object type should implement this interface.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.user.infraction
 * @subpackage	data.user.infraction.warning.object
 * @category 	Community Framework (commercial)
 */
interface WarningObjectType {
	/**
	 * Gets warning objects by their ids.
	 * 
	 * @param	mixed		$objectID	id or list of ids
	 * @return	mixed
	 */
	public function getObjectByID($objectID);
}
?>
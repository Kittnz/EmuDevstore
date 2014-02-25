<?php
/**
 * A warning object should implement this interface.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.user.infraction
 * @subpackage	data.user.infraction.warning.object
 * @category 	Community Framework (commercial)
 */
interface WarningObject {
	/**
	 * Returns the title of this object.
	 * 
	 * @return	string
	 */
	public function getTitle();
	
	/**
	 * Returns the url of this object.
	 * 
	 * @return	string
	 */
	public function getURL();
}
?>
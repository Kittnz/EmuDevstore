<?php
/**
 * Any sidebar object should implement this interface.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.message.sidebar
 * @subpackage	data.message.sidebar
 * @category 	Community Framework
 */
interface MessageSidebarObject {
	/**
	 * Returns the user object of this message.
	 *
	 * @return 	UserProfile
	 */
	public function getUser();
	
	/**
	 * Returns the message id.
	 *
	 * @return 	integer
	 */
	public function getMessageID();
	
	/**
	 * Returns the message type.
	 *
	 * @return 	string
	 */
	public function getMessageType();
}
?>
<?php
// wcf imports
require_once(WCF_DIR.'lib/data/DatabaseObject.class.php');

/**
 * SimpleMessage is the basis class for all types of messages.
 * Messages are posts, private messages, events etc.
 *
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.message
 * @subpackage	data.message
 * @category 	Community Framework
 */
abstract class Message extends DatabaseObject {
	/**
	 * id of this message
	 *
	 * @var integer
	 */
	protected $messageID = 0;
	
	/**
	 * Returns the id of this message.
	 *
	 * @return integer
	 */
	public function getID() {
		return $this->messageID;
	}
}
?>
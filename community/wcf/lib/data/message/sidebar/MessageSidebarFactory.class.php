<?php
// wcf imports
require_once(WCF_DIR.'lib/data/message/sidebar/MessageSidebar.class.php');
require_once(WCF_DIR.'lib/data/message/sidebar/MessageSidebarObject.class.php');
require_once(WCF_DIR.'lib/system/event/EventHandler.class.php');

/**
 * Manages the message sidebars.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.message.sidebar
 * @subpackage	data.message.sidebar
 * @category 	Community Framework
 */
class MessageSidebarFactory {
	/**
	 * sidebar container.
	 * 
	 * @var	Page 
	 */
	public $container = null;
	
	/**
	 * list of sidebar objects.
	 *
	 * @var array<MessageSidebar>
	 */
	public $messageSidebars = array();
	
	/**
	 * Creates a new MessageSidebarFactory.
	 *
	 * @param	Page		$container
	 */
	public function __construct($container = null) {
		$this->container = $container;
	}
	
	/**
	 * Creates the sidebar object for given user.
	 *
	 * @param	MessageSidebarObject	$user
	 */
	public function create(MessageSidebarObject $object) {
		if (!isset($this->messageSidebars[$object->getMessageType().'-'.$object->getMessageID()])) {
			// create sidebar
			$sidebar = new MessageSidebar($object);
			$this->messageSidebars[$object->getMessageType().'-'.$object->getMessageID()] = $sidebar;
		}
	}
	
	/**
	 * Returns the sidebar object for given user.
	 *
	 * @param	string		$messageType
	 * @param	integer		$messageID
	 * @return	MessageSidebar
	 */
	public function get($messageType, $messageID) {
		return $this->messageSidebars[$messageType.'-'.$messageID];
	}
	
	/**
	 * Initializes the sidebars.
	 */
	public function init() {
		// call init event
		EventHandler::fireAction($this, 'init');
	}
}
?>
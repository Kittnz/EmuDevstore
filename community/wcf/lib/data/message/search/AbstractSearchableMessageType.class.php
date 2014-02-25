<?php
// wcf imports
require_once(WCF_DIR.'lib/data/message/search/SearchableMessageType.class.php');

/**
 * This class provides default implementations for the SearchableMessageType interface.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.message.search
 * @subpackage	data.message.search
 * @category 	Community Framework
 */
abstract class AbstractSearchableMessageType implements SearchableMessageType {
	/**
	 * @see SearchableMessageType::show()
	 */
	public function show($form = null) {}
	
	/**
	 * @see SearchableMessageType::getConditions()
	 */
	public function getConditions($form = null) {
		return '';
	}
	
	/**
	 * @see SearchableMessageType::getJoins()
	 */
	public function getJoins() {
		return '';
	}
	
	/**
	 * @see SearchableMessageType::getAdditionalData()
	 */
	public function getAdditionalData() {
		return false;
	}
	
	/**
	 * @see SearchableMessageType::isAccessible()
	 */
	public function isAccessible() {
		return true;
	}
	
	/**
	 * @see SearchableMessageType::getFormTemplateName()
	 */
	public function getFormTemplateName() {
		return '';
	}
	
	/**
	 * @see SearchableMessageType::getSubjectFieldNames()
	 */
	public function getSubjectFieldNames() {
		return array('subject');
	}
	
	/**
	 * @see SearchableMessageType::getMessageFieldNames()
	 */
	public function getMessageFieldNames() {
		return array('message');
	}
	
	/**
	 * @see SearchableMessageType::getUserIDFieldName()
	 */
	public function getUserIDFieldName() {
		return 'messageTable.userID';
	}
	
	/**
	 * @see SearchableMessageType::getUsernameFieldName()
	 */
	public function getUsernameFieldName() {
		return 'messageTable.username';
	}
	
	/**
	 * @see SearchableMessageType::getTimeFieldName()
	 */
	public function getTimeFieldName() {
		return 'messageTable.time';
	}
}
?>
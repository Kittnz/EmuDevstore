<?php
// wcf imports
require_once(WCF_DIR.'lib/action/AbstractSecureAction.class.php');
require_once(WCF_DIR.'lib/data/message/multiQuote/MultiQuoteManager.class.php');

/**
 * Removes the quotes of a message.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.message.multiQuote
 * @subpackage	action
 * @category 	Community Framework (commercial)
 */
class MessageQuotesRemoveAction extends AbstractSecureAction {
	/**
	 * object id
	 *
	 * @var	integer
	 */
	public $objectID = 0;
	
	/**
	 * object type
	 *
	 * @var string
	 */
	public $objectType = '';
	
	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		// get object id
		if (isset($_REQUEST['objectID'])) $this->objectID = intval($_REQUEST['objectID']);
		// get object type
		if (isset($_POST['objectType'])) $this->objectType = $_REQUEST['objectType'];
	}
	
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();
		
		// remove quotes
		MultiQuoteManager::removeQuotes($this->objectID, $this->objectType);
		MultiQuoteManager::saveStorage();
		$this->executed();
	}
}
?>
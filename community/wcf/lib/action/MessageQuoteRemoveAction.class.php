<?php
// wcf imports
require_once(WCF_DIR.'lib/action/AbstractSecureAction.class.php');
require_once(WCF_DIR.'lib/data/message/multiQuote/MultiQuoteManager.class.php');

/**
 * Removes a quote of a message.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.message.multiQuote
 * @subpackage	action
 * @category 	Community Framework (commercial)
 */
class MessageQuoteRemoveAction extends AbstractSecureAction {
	/**
	 * quote id
	 *
	 * @var	string
	 */
	public $quoteID = 0;
	
	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		// get quote id
		if (isset($_POST['quoteID'])) $this->quoteID = $_REQUEST['quoteID'];
	}
	
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();
		
		// remove quotes
		MultiQuoteManager::removeQuote($this->quoteID);
		MultiQuoteManager::saveStorage();
		$this->executed();
	}
}
?>
<?php
// wcf imports
require_once(WCF_DIR.'lib/action/AbstractMessageQuoteAction.class.php');
require_once(WCF_DIR.'lib/data/message/multiQuote/MultiQuoteManager.class.php');
require_once(WCF_DIR.'lib/data/message/pm/PM.class.php');

/**
 * Saves quotes of a pm.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.data.message.pm
 * @subpackage	action
 * @category 	Community Framework (commercial)
 */
class PmMessageQuoteAction extends AbstractMessageQuoteAction {
	/**
	 * pm object
	 *
	 * @var	PM
	 */
	public $pm = null;
	
	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		// get pm
		$this->pm = new PM($this->objectID);
		if (!$this->pm->pmID || !$this->pm->hasAccess()) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();
		
		if ((!is_array($this->text) && $this->text == '') || (is_array($this->text) && !count($this->text))) {
			$this->text = $this->pm->message;
		}
		if (!is_array($this->text)) {
			$this->text = array($this->text);
		}
		
		// store quotes
		foreach ($this->text as $key => $string) {
			MultiQuoteManager::storeQuote($this->objectID, 'pm', $string, $this->pm->username, 'index.php?page=PMView&pmID='.$this->objectID.'#pm'.$this->objectID, $this->pm->parentPmID, ((strlen($key) == 40 && preg_match('/^[a-f0-9]+$/', $key)) ? $key : ''));
		}
		MultiQuoteManager::saveStorage();
		$this->executed();
	}
}
?>
<?php
// wcf imports
require_once(WCF_DIR.'lib/system/event/EventListener.class.php');

/**
 * Manages the quotes in message forms.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.message.multiQuote
 * @subpackage	system.event.listener
 * @category 	Community Framework (commercial)
 */
class MessageFormMultiQuoteListener implements EventListener {
	/**
	 * list of used quotes
	 *
	 * @var	array<string>
	 */
	public $usedQuotes = array();

	/**
	 * @see EventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		if ($eventName == 'readFormParameters') {
			if (isset($_POST['usedQuotes']) && is_array($_POST['usedQuotes'])) {
				$this->usedQuotes = $_POST['usedQuotes'];
			}
		}
		else if ($eventName == 'saved') {
			if (count($this->usedQuotes) > 0) {
				require_once(WCF_DIR.'lib/data/message/multiQuote/MultiQuoteManager.class.php');
				foreach ($this->usedQuotes as $quoteID) {
					MultiQuoteManager::removeQuote($quoteID);
				}
				MultiQuoteManager::saveStorage();
			}
		}
		else if ($eventName == 'assignVariables') {
			require_once(WCF_DIR.'lib/data/message/multiQuote/MultiQuoteManager.class.php');
			$quotes = MultiQuoteManager::getStorage();
			$usedQuotes = array_flip($this->usedQuotes);
			foreach ($quotes as $quoteID => $quote) {
				$quote['used'] = (isset($usedQuotes[$quoteID]) ? 1 : 0);
				$quotes[$quoteID] = $quote;
			}
			
			WCF::getTPL()->assign('quotes', $quotes);
			WCF::getTPL()->append(array(
				'additionalTabs' => '<li id="multiQuoteTab"><a onclick="tabbedPane.openTab(\'multiQuote\');"><span>'.WCF::getLanguage()->get('wcf.multiQuote.title').'</span></a></li>',
				'additionalSubTabs' => WCF::getTPL()->fetch('messageFormMultiQuote')
			));
		}
	}
}
?>
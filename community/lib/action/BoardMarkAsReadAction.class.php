<?php
require_once(WBB_DIR.'lib/action/AbstractBoardAction.class.php');

/**
 * Marks a board as read.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	action
 * @category 	Burning Board
 */
class BoardMarkAsReadAction extends AbstractBoardAction {
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();
		
		$this->board->markAsRead();
		WCF::getSession()->unregister('lastSubscriptionsStatusUpdateTime');
		$this->executed();
	}
}
?>
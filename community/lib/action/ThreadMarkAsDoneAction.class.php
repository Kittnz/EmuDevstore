<?php
require_once(WBB_DIR.'lib/action/AbstractThreadAction.class.php');

/**
 * Marks a thread as done.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	action
 * @category 	Burning Board
 */
class ThreadMarkAsDoneAction extends AbstractThreadAction {
	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (!MODULE_THREAD_MARKING_AS_DONE || !$this->board->enableMarkingAsDone && !WCF::getUser()->userID || WCF::getUser()->userID != $this->thread->userID || !$this->board->getPermission('canMarkAsDoneOwnThread') && $this->thread->isDone) {
			throw new IllegalLinkException();
		}
	}

	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();
		
		$this->thread->markAsDone();
		$this->executed();
	}
}
?>
<?php
// wbb imports
require_once(WBB_DIR.'lib/acp/action/UpdateCounterAction.class.php');

// wcf imports
require_once(WCF_DIR.'lib/data/attachment/AttachmentEditor.class.php');

/**
 * Updates the thumbnails.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	acp.action
 * @category 	Burning Board
 */
class UpdateThumbnailsAction extends UpdateCounterAction {
	public $action = 'UpdateThumbnails';
	
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();
		
		// count threads
		$sql = "SELECT	COUNT(*) AS count
			FROM	wcf".WCF_N."_attachment
			WHERE	isImage = 1";
		$row = WCF::getDB()->getFirstRow($sql);
		$count = $row['count'];
		
		// get attachments
		$threadIDs = '';
		$sql = "SELECT		*
			FROM		wcf".WCF_N."_attachment
			WHERE		isImage = 1
			ORDER BY	attachmentID";
		$result = WCF::getDB()->sendQuery($sql, $this->limit, ($this->limit * $this->loop));
		if (!WCF::getDB()->countRows($result)) {
			$this->calcProgress();
			$this->finish();
		}
		while ($row = WCF::getDB()->fetchArray($result)) {
			$attachment = new AttachmentEditor(null, $row);
			$attachment->createThumbnail();
		}
		$this->executed();
		
		$this->calcProgress(($this->limit * $this->loop), $count);
		$this->nextLoop();
	}
}
?>
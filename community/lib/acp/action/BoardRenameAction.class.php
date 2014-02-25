<?php
// wbb imports
require_once(WBB_DIR.'lib/acp/action/AbstractBoardAction.class.php');

/**
 * Renames a board.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	acp.action
 * @category 	Burning Board
 */
class BoardRenameAction extends AbstractBoardAction {
	/**
	 * new board title
	 *
	 * @var string
	 */
	public $title = '';
	
	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_POST['title'])) {
			$this->title = $_POST['title'];
			if (CHARSET != 'UTF-8') $this->title = StringUtil::convertEncoding('UTF-8', CHARSET, $this->title);
		}
	}
	
	/**
	 * @see Action::execute();
	 */
	public function execute() {
		parent::execute();
		
		// check permission
		WCF::getUser()->checkPermission('admin.board.canEditBoard');
				
		// check board title
		if (StringUtil::encodeHTML($this->board->title) != WCF::getLanguage()->get(StringUtil::encodeHTML($this->board->title))) {
			// change language variable
			require_once(WCF_DIR.'lib/system/language/LanguageEditor.class.php');
			$language = new LanguageEditor(WCF::getLanguage()->getLanguageID());
			$language->updateItems(array($this->board->title => $this->title), 0, PACKAGE_ID, array($this->board->title => 1));
		}
		else {
			// change title
			$this->board->updateData(array('title' => $this->title));
		}
		
		// reset cache
		WCF::getCache()->clearResource('board');
		$this->executed();
	}
}
?>
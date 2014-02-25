<?php
// wcf imports
require_once(WCF_DIR.'lib/action/AbstractAction.class.php');

/**
 * Provides default implementations for style actions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.display.style
 * @subpackage	acp.action
 * @category 	Community Framework (commercial)
 */
abstract class StyleAction extends AbstractAction {
	/**
	 * list of needed permissions
	 * 
	 * @var	array<string>
	 */
	public $permissions = array();
	
	/**
	 * style id
	 * 
	 * @var	integer
	 */
	public $styleID = 0;
	
	/**
	 * style editor object
	 * 
	 * @var	StyleEditorObject
	 */
	public $style = null;
	
	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['styleID'])) $this->styleID = intval($_REQUEST['styleID']);
	}
	
	/**
	 * Executes the style action.
	 */
	protected abstract function __execute(); 
	
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();
		
		// check permission
		WCF::getUser()->checkPermission($this->permissions);
		
		// get style
		require_once(WCF_DIR.'lib/data/style/StyleEditor.class.php');
		$this->style = new StyleEditor($this->styleID);	
		if (!$this->style->styleID) {
			throw new IllegalLinkException();
		}
		$this->__execute();
		
		// reset cache
		WCF::getCache()->clear(WCF_DIR . 'cache', 'cache.style.php');
		$this->executed();
		
		// forward to list page
		HeaderUtil::redirect('index.php?page=StyleList&packageID='.PACKAGE_ID.SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
}
?>
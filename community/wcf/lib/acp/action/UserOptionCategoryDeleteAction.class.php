<?php
// wcf imports
require_once(WCF_DIR.'lib/action/AbstractAction.class.php');
require_once(WCF_DIR.'lib/data/user/option/category/UserOptionCategoryEditor.class.php');

/**
 * Deletes a user option category.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.user.option
 * @subpackage	acp.action
 * @category 	Community Framework (commercial)
 */
class UserOptionCategoryDeleteAction extends AbstractAction {
	/**
	 * category id
	 *
	 * @var	integer
	 */
	public $categoryID = 0;
	
	/**
	 * category editor object
	 *
	 * @var	UserOptionCategoryEditor
	 */
	public $category;
	
	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['categoryID'])) $this->categoryID = intval($_REQUEST['categoryID']);
		$this->category = new UserOptionCategoryEditor($this->categoryID);
		if (!$this->category->categoryID || $this->category->options != 0) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();
		
		// check permission
		WCF::getUser()->checkPermission('admin.user.option.canDeleteOptionCategory');
		
		// delete category
		$this->category->delete();
		
		// delete cache
		WCF::getCache()->clear(WCF_DIR.'cache', 'cache.user-option-*');
		$this->executed();
		
		// forward to list page
		HeaderUtil::redirect('index.php?page=UserOptionCategoryList&deletedCategoryID='.$this->categoryID.'&packageID='.PACKAGE_ID.SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
}
?>
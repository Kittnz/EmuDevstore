<?php
// wcf imports
require_once(WCF_DIR.'lib/action/AbstractAction.class.php');
require_once(WCF_DIR.'lib/data/user/avatar/category/AvatarCategoryEditor.class.php');

/**
 * Deletes an avatar category.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.user.avatar
 * @subpackage	acp.action
 * @category 	Community Framework (commercial)
 */
class AvatarCategoryDeleteAction extends AbstractAction {
	/**
	 * avatar category id
	 *
	 * @var	integer
	 */
	public $avatarCategoryID = 0;
	
	/**
	 * avatar category editor object
	 *
	 * @var	AvatarCategoryEditor
	 */
	public $avatarCategory;
	
	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['avatarCategoryID'])) $this->avatarCategoryID = intval($_REQUEST['avatarCategoryID']);
		$this->avatarCategory = new AvatarCategoryEditor($this->avatarCategoryID);
		if (!$this->avatarCategory->avatarCategoryID) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();
		
		// check permission
		WCF::getUser()->checkPermission('admin.avatar.canDeleteAvatarCategory');
		
		// delete category
		$this->avatarCategory->delete();
		$this->executed();
		
		// forward to list page
		HeaderUtil::redirect('index.php?page=AvatarCategoryList&deletedAvatarCategoryID='.$this->avatarCategoryID.'&packageID='.PACKAGE_ID.SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
}
?>
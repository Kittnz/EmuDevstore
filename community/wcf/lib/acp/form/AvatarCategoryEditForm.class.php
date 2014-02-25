<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/form/AvatarCategoryAddForm.class.php');

/**
 * Shows the form for editing avatar categories.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.user.avatar
 * @subpackage	acp.form
 * @category 	Community Framework (commercial)
 */
class AvatarCategoryEditForm extends AvatarCategoryAddForm {
	// system
	public $activeMenuItem = 'wcf.acp.menu.link.avatar';
	public $neededPermissions = 'admin.avatar.canEditAvatarCategory';
	
	// parameters
	public $avatarCategoryID = 0;
	
	/**
	 * avatar category editor object
	 * 
	 * @var	AvatarCategoryEditor
	 */
	public $avatarCategory = null;
	
	/**
	 * @see Page::readParameters()
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
	 * @see Form::save()
	 */
	public function save() {
		ACPForm::save();
		
		// save
		$this->avatarCategory->update($this->title, $this->showOrder, $this->groupID, $this->neededPoints);
		$this->saved();
		
		// show success message
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		if (!count($_POST)) {
			$this->title = $this->avatarCategory->title;
			$this->showOrder = $this->avatarCategory->showOrder;
			$this->groupID = $this->avatarCategory->groupID;
			$this->neededPoints = $this->avatarCategory->neededPoints;
		}
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'action' => 'edit',
			'avatarCategoryID' => $this->avatarCategoryID,
			'avatarCategory' => $this->avatarCategory
		));
	}
}
?>
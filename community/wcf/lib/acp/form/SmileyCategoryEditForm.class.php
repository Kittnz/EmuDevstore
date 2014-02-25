<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/form/SmileyCategoryAddForm.class.php');

/**
 * Shows the form for editing smiley categories.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.content.smiley
 * @subpackage	acp.form
 * @category 	Community Framework (commercial)
 */
class SmileyCategoryEditForm extends SmileyCategoryAddForm {
	// system
	public $activeMenuItem = 'wcf.acp.menu.link.smiley';
	public $neededPermissions = 'admin.smiley.canEditSmileyCategory';
	
	// parameters
	public $smileyCategoryID = 0;
	
	/**
	 * smiley category editor object
	 * 
	 * @var	SmileyCategoryEditor
	 */
	public $smileyCategory = null;
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['smileyCategoryID'])) $this->smileyCategoryID = intval($_REQUEST['smileyCategoryID']);
		$this->smileyCategory = new SmileyCategoryEditor($this->smileyCategoryID);
		if (!$this->smileyCategory->smileyCategoryID) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		ACPForm::save();
		
		// save
		$this->smileyCategory->update($this->title, $this->showOrder);
		
		// reset cache
		SmileyEditor::resetCache();
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
			$this->title = $this->smileyCategory->title;
			$this->showOrder = $this->smileyCategory->showOrder;
		}
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'action' => 'edit',
			'smileyCategoryID' => $this->smileyCategoryID,
			'smileyCategory' => $this->smileyCategory
		));
	}
}
?>
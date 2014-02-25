<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/form/SmileyAddForm.class.php');
require_once(WCF_DIR.'lib/data/user/group/Group.class.php');
require_once(WCF_DIR.'lib/data/message/smiley/SmileyEditor.class.php');

/**
 * Shows the smiley edit form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.content.smiley
 * @subpackage	acp.form
 * @category 	Community Framework (commercial)
 */
class SmileyEditForm extends SmileyAddForm {
	public $templateName = 'smileyEdit';
	public $activeMenuItem = 'wcf.acp.menu.link.smiley';
	public $neededPermissions = 'admin.smiley.canEditSmiley';
	
	public $smiley;
	public $smileyID = 0;
	public $path = '';
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		AbstractForm::readParameters();
		
		if (isset($_REQUEST['smileyID'])) $this->smileyID = intval($_REQUEST['smileyID']);
		$this->smiley = new SmileyEditor($this->smileyID);
		if (!$this->smiley->smileyID) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['path'])) $this->path = StringUtil::trim($_POST['path']);
	}
	
	/**
	 * Does nothing.
	 */
	protected function validateFile() {}
	
	/**
	 * @see SmileyAddForm::validateCode()
	 */
	public function validateCode() {
		if ($this->code != $this->smiley->smileyCode) {
			parent::validateCode();
		}
	}
	
	/**
	 * @see Form::validate()
	 */
	public function validate() {
		parent::validate();
		
		// validate path
		if (empty($this->path)) {
			throw new UserInputException('path');
		}
		
		if (!file_exists(WCF_DIR . $this->path)) {
			throw new UserInputException('path', 'notFound');
		}
	}
	
	/**
	 * @see Form::validate()
	 */
	public function save() {
		AbstractForm::save();

		// update
		$this->smiley->update($this->path, $this->title, $this->code, $this->showOrder, $this->smileyCategoryID);
		
		$this->smiley->removePositions();
		$this->smiley->addPosition($this->smileyCategoryID, ($this->showOrder ? $this->showOrder : null));
		
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
			// default value
			$this->path = $this->smiley->smileyPath;
			$this->title = $this->smiley->smileyTitle;
			$this->code = $this->smiley->smileyCode;
			$this->showOrder = $this->smiley->showOrder;
			$this->smileyCategoryID = $this->smiley->smileyCategoryID;
		}
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'path' => $this->path,
			'smileyID' => $this->smileyID,
			'smiley' => $this->smiley
		));
	}
}
?>
<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/form/SuspensionAddForm.class.php');

/**
 * Shows the suspension edit form.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.user.infraction
 * @subpackage	acp.form
 * @category 	Community Framework (commercial)
 */
class SuspensionEditForm extends SuspensionAddForm {
	// system
	public $activeMenuItem = 'wcf.acp.menu.link.user.infraction.suspension';
	public $neededPermissions = 'admin.user.infraction.canEditSuspension';
	
	/**
	 * suspension id
	 *
	 * @var	integer
	 */
	public $suspensionID = 0;
	
	/**
	 * suspension editor object
	 *
	 * @var	SuspensionEditor
	 */
	public $suspension = null;
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['suspensionID'])) $this->suspensionID = intval($_REQUEST['suspensionID']);
		$this->suspension = new SuspensionEditor($this->suspensionID);
		if (!$this->suspension->suspensionID) {
			throw new IllegalLinkException();
		}
		if ($this->suspension->suspensions != 0) {
			$this->suspensionType = $this->suspension->suspensionType;
		}
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		// default values
		if (!count($_POST)) {
			$this->title = $this->suspension->title;
			$this->points = $this->suspension->points;
			$this->suspensionType = $this->suspension->suspensionType;
			
			$expires = $this->suspension->expires;
			$this->expiresWeek = floor($expires / (86400 * 7));
			$expires = $expires % (86400 * 7);
			$this->expiresDay = floor($expires / 86400);
			$expires = $expires % 86400;
			$this->expiresHour = floor($expires / 3600);
			
			// type object
			$this->suspensionTypeObject = $this->availableSuspensionTypes[$this->suspensionType];
			$this->suspensionTypeObject->setData(unserialize($this->suspension->suspensionData));
		}
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		AbstractForm::save();
		
		// update
		$this->suspension->update($this->title, $this->points, ($this->suspension->suspensions == 0 ? $this->suspensionType : $this->suspension->suspensionType), ($this->suspension->suspensions == 0 ? $this->suspensionTypeObject->getData() : unserialize($this->suspension->suspensionData)), ($this->expiresHour * 3600 + $this->expiresDay * 86400 + $this->expiresWeek * 86400 * 7));
		$this->saved();
		
		// show success message
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'suspensionID' => $this->suspensionID,
			'suspension' => $this->suspension,
			'action' => 'edit'
		));
	}
}
?>
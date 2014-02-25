<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/form/WarningAddForm.class.php');

/**
 * Shows the warning edit form.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.user.infraction
 * @subpackage	acp.form
 * @category 	Community Framework (commercial)
 */
class WarningEditForm extends WarningAddForm {
	// system
	public $activeMenuItem = 'wcf.acp.menu.link.user.infraction.warning';
	public $neededPermissions = 'admin.user.infraction.canEditWarning';
	
	/**
	 * warning id
	 * 
	 * @var	integer
	 */
	public $warningID = 0;
	
	/**
	 * warning editor object
	 * 
	 * @var	WarningEditor
	 */
	public $warning = null;
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['warningID'])) $this->warningID = intval($_REQUEST['warningID']);
		$this->warning = new WarningEditor($this->warningID);
		if (!$this->warning->warningID) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		ACPForm::save();
		
		// save
		$this->warning->update($this->title, $this->points, ($this->expiresHour * 3600 + $this->expiresDay * 86400 + $this->expiresWeek * 86400 * 7));
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
			$this->title = $this->warning->title;
			$this->points = $this->warning->points;
			
			$expires = $this->warning->expires;
			$this->expiresWeek = floor($expires / (86400 * 7));
			$expires = $expires % (86400 * 7);
			$this->expiresDay = floor($expires / 86400);
			$expires = $expires % 86400;
			$this->expiresHour = floor($expires / 3600);
		}
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'action' => 'edit',
			'warningID' => $this->warningID,
			'warning' => $this->warning
		));
	}
}
?>
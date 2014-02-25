<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/form/ACPForm.class.php');
require_once(WCF_DIR.'lib/data/user/infraction/warning/WarningEditor.class.php');

/**
 * Shows the warning add form.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.user.infraction
 * @subpackage	acp.form
 * @category 	Community Framework (commercial)
 */
class WarningAddForm extends ACPForm {
	// system
	public $templateName = 'warningAdd';
	public $activeMenuItem = 'wcf.acp.menu.link.user.infraction.warning.add';
	public $neededPermissions = 'admin.user.infraction.canAddWarning';
	
	// parameters
	public $title = '';
	public $points = 0;
	public $expiresWeek = 0;
	public $expiresDay = 0;
	public $expiresHour = 0;
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['title'])) $this->title = StringUtil::trim($_POST['title']);
		if (isset($_POST['points'])) $this->points = abs(intval($_POST['points']));
		if (isset($_POST['expiresWeek'])) $this->expiresWeek = intval($_POST['expiresWeek']);
		if (isset($_POST['expiresDay'])) $this->expiresDay = intval($_POST['expiresDay']);
		if (isset($_POST['expiresHour'])) $this->expiresHour = intval($_POST['expiresHour']);
	}
	
	/**
	 * @see Form::validate()
	 */
	public function validate() {
		parent::validate();
		
		if (empty($this->title)) {
			throw new UserInputException('title');
		}
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		parent::save();
		
		// save
		WarningEditor::create($this->title, $this->points, ($this->expiresHour * 3600 + $this->expiresDay * 86400 + $this->expiresWeek * 86400 * 7));
		$this->saved();
		
		// reset values
		$this->title = '';
		$this->points = $this->expiresWeek = $this->expiresDay = $this->expiresHour = 0;

		// show success message
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'action' => 'add',
			'title' => $this->title,
			'points' => $this->points,
			'expiresWeek' => $this->expiresWeek,
			'expiresDay' => $this->expiresDay,
			'expiresHour' => $this->expiresHour
		));
	}
}
?>
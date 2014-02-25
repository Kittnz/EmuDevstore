<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/form/ACPForm.class.php');
require_once(WCF_DIR.'lib/data/user/infraction/suspension/UserSuspensionEditor.class.php');
require_once(WCF_DIR.'lib/data/user/infraction/suspension/Suspension.class.php');
require_once(WCF_DIR.'lib/page/util/InlineCalendar.class.php');

/**
 * Shows the user suspension edit form.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.user.infraction
 * @subpackage	acp.form
 * @category 	Community Framework (commercial)
 */
class UserSuspensionEditForm extends ACPForm {
	// system
	public $templateName = 'userSuspensionEdit';
	public $activeMenuItem = 'wcf.acp.menu.link.user.infraction.suspension';
	public $neededPermissions = 'admin.user.infraction.canEditSuspension';
	
	/**
	 * user suspension id
	 *
	 * @var	integer
	 */
	public $userSuspensionID = 0;
	
	/**
	 * user suspension editor object
	 *
	 * @var	UserSuspensionEditor
	 */
	public $userSuspension = null;
	
	// form parameters
	public $expiresDay = 0;
	public $expiresMonth = 0;
	public $expiresYear = 0;
	public $expiresHour = 0;
	public $expiresMinute = 0;
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['userSuspensionID'])) $this->userSuspensionID = intval($_REQUEST['userSuspensionID']);
		$this->userSuspension = new UserSuspensionEditor($this->userSuspensionID);
		if (!$this->userSuspension->userSuspensionID) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['expiresDay'])) $this->expiresDay = intval($_POST['expiresDay']);
		if (isset($_POST['expiresMonth'])) $this->expiresMonth = intval($_POST['expiresMonth']);
		if (isset($_POST['expiresYear'])) $this->expiresYear = intval($_POST['expiresYear']);
		if (isset($_POST['expiresHour'])) $this->expiresHour = intval($_POST['expiresHour']);
		if (isset($_POST['expiresMinute'])) $this->expiresMinute = intval($_POST['expiresMinute']);
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		parent::save();
		
		$this->userSuspension->update($this->userSuspension->userID, $this->userSuspension->suspensionID, ($this->expiresDay && $this->expiresMonth && $this->expiresYear ? DateUtil::getUTC(gmmktime($this->expiresHour, $this->expiresMinute, 0, $this->expiresMonth, $this->expiresDay, $this->expiresYear)) : 0));
		$this->saved();
		
		// show success message
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		// get default values
		if (!count($_POST)) {
			if ($this->userSuspension->expires != 0) {
				$expires = DateUtil::getLocalTimestamp($this->userSuspension->expires);
				$this->expiresDay = gmdate('j', $expires);
				$this->expiresMonth = gmdate('n', $expires);
				$this->expiresYear = gmdate('Y', $expires);
				$this->expiresHour = gmdate('G', $expires);
				$this->expiresMinute = gmdate('i', $expires);
				$this->expiresMinute -= $this->expiresMinute % 5;
			}
		}
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		InlineCalendar::assignVariables();
		WCF::getTPL()->assign(array(
			'userSuspensionID' => $this->userSuspensionID,
			'userSuspension' => $this->userSuspension,
			'expiresDay' => $this->expiresDay,
			'expiresMonth' => $this->expiresMonth,
			'expiresYear' => $this->expiresYear,
			'expiresHour' => $this->expiresHour,
			'expiresMinute' => $this->expiresMinute
		));
	}
}
?>
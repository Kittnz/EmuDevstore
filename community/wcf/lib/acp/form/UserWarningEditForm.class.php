<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/form/ACPForm.class.php');
require_once(WCF_DIR.'lib/data/user/infraction/warning/UserWarningEditor.class.php');
require_once(WCF_DIR.'lib/data/user/infraction/warning/Warning.class.php');
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
class UserWarningEditForm extends ACPForm {
	// system
	public $templateName = 'userWarningEdit';
	public $activeMenuItem = 'wcf.acp.menu.link.user.infraction.warning';
	public $neededPermissions = 'admin.user.infraction.canEditWarning';
	
	/**
	 * user warning id
	 *
	 * @var	integer
	 */
	public $userWarningID = 0;
	
	/**
	 * user warning editor object
	 *
	 * @var	UserWarningEditor
	 */
	public $userWarning = null;
	
	/**
	 * warning object
	 * 
	 * @var	WarningObject
	 */
	public $object = null;

	// form parameters
	public $warningID = 0;
	public $title = '';
	public $points = 0;
	public $reason = '';
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
		
		if (isset($_REQUEST['userWarningID'])) $this->userWarningID = intval($_REQUEST['userWarningID']);
		$this->userWarning = new UserWarningEditor($this->userWarningID);
		if (!$this->userWarning->userWarningID) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['warningID'])) $this->warningID = intval($_POST['warningID']);
		if (isset($_POST['title'])) $this->title = StringUtil::trim($_POST['title']);
		if (isset($_POST['points'])) $this->points = abs(intval($_POST['points']));
		if (isset($_POST['reason'])) $this->reason = StringUtil::trim($_POST['reason']);
		if (isset($_POST['expiresDay'])) $this->expiresDay = intval($_POST['expiresDay']);
		if (isset($_POST['expiresMonth'])) $this->expiresMonth = intval($_POST['expiresMonth']);
		if (isset($_POST['expiresYear'])) $this->expiresYear = intval($_POST['expiresYear']);
		if (isset($_POST['expiresHour'])) $this->expiresHour = intval($_POST['expiresHour']);
		if (isset($_POST['expiresMinute'])) $this->expiresMinute = intval($_POST['expiresMinute']);
	}
	
	/**
	 * @see Form::validate()
	 */
	public function validate() {
		parent::validate();
		
		if ($this->warningID) {
			$warning = new Warning($this->warningID);
			if (!$warning->warningID) {
				$this->warningID = 0;
			}
		}
		
		if (!$this->warningID) {
			if (empty($this->title)) {
				throw new UserInputException('title');
			}
		}
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		parent::save();
		
		$this->userWarning->update($this->warningID, $this->title, $this->points, ($this->expiresDay && $this->expiresMonth && $this->expiresYear ? DateUtil::getUTC(gmmktime($this->expiresHour, $this->expiresMinute, 0, $this->expiresMonth, $this->expiresDay, $this->expiresYear)) : 0), $this->reason);
		$this->saved();
		
		// show success message
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		// get predefined warnings
		$this->warnings = Warning::getWarnings();
		
		// get warning object
		if ($this->userWarning->objectID != 0 && $this->userWarning->objectType != '') {
			$this->object = Warning::getWarningObjectByID($this->userWarning->objectType, $this->userWarning->objectID);
		}
		
		// get default values
		if (!count($_POST)) {
			$this->warningID = $this->userWarning->warningID;
			$this->title = $this->userWarning->title;
			$this->points = $this->userWarning->points;
			$this->reason = $this->userWarning->reason;
			if ($this->userWarning->expires != 0) {
				$expires = DateUtil::getLocalTimestamp($this->userWarning->expires);
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
			'userWarningID' => $this->userWarningID,
			'userWarning' => $this->userWarning,
			'warningID' => $this->warningID,
			'title' => $this->title,
			'points' => $this->points,
			'reason' => $this->reason,
			'warnings' => $this->warnings,
			'object' => $this->object,
			'expiresDay' => $this->expiresDay,
			'expiresMonth' => $this->expiresMonth,
			'expiresYear' => $this->expiresYear,
			'expiresHour' => $this->expiresHour,
			'expiresMinute' => $this->expiresMinute
		));
	}
}
?>
<?php
require_once(WCF_DIR.'lib/system/event/EventListener.class.php');
require_once(WCF_DIR.'lib/system/database/ConditionBuilder.class.php');
require_once(WCF_DIR.'lib/page/util/InlineCalendar.class.php');

/**
 * Adds specials conditions to user search form.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	system.event.listener
 * @category 	Burning Board
 */
class UserSearchFormConditionsListener implements EventListener {
	public $registrationDateAfterDay = 0;
	public $registrationDateAfterMonth = 0;
	public $registrationDateAfterYear = '';
	public $registrationDateBeforeDay = 0;
	public $registrationDateBeforeMonth = 0;
	public $registrationDateBeforeYear = '';
	public $lastActivityAfterDay = 0;
	public $lastActivityAfterMonth = 0;
	public $lastActivityAfterYear = '';
	public $lastActivityBeforeDay = 0;
	public $lastActivityBeforeMonth = 0;
	public $lastActivityBeforeYear = '';
	public $postsGreaterThan = '';
	public $postsLessThan = '';
	public $registrationIpAddress1 = '';
	public $registrationIpAddress2 = '';
	public $registrationIpAddress3 = '';
	public $registrationIpAddress4 = '';
	public $enabled = 0;
	public $notEnabled = 0;
	public $banned = 0;
	public $notBanned = 0;
	public $hasSpecialPermissions = 0;
	
	protected function readFormParameters() {
		// registration date
		if (isset($_POST['registrationDateAfterDay'])) $this->registrationDateAfterDay = intval($_POST['registrationDateAfterDay']);
		if (isset($_POST['registrationDateAfterMonth'])) $this->registrationDateAfterMonth = intval($_POST['registrationDateAfterMonth']);
		if (!empty($_POST['registrationDateAfterYear'])) $this->registrationDateAfterYear = intval($_POST['registrationDateAfterYear']);
		if (isset($_POST['registrationDateBeforeDay'])) $this->registrationDateBeforeDay = intval($_POST['registrationDateBeforeDay']);
		if (isset($_POST['registrationDateBeforeMonth'])) $this->registrationDateBeforeMonth = intval($_POST['registrationDateBeforeMonth']);
		if (!empty($_POST['registrationDateBeforeYear'])) $this->registrationDateBeforeYear = intval($_POST['registrationDateBeforeYear']);
		
		// last activity
		if (isset($_POST['lastActivityAfterDay'])) $this->lastActivityAfterDay = intval($_POST['lastActivityAfterDay']);
		if (isset($_POST['lastActivityAfterMonth'])) $this->lastActivityAfterMonth = intval($_POST['lastActivityAfterMonth']);
		if (!empty($_POST['lastActivityAfterYear'])) $this->lastActivityAfterYear = intval($_POST['lastActivityAfterYear']);
		if (isset($_POST['lastActivityBeforeDay'])) $this->lastActivityBeforeDay = intval($_POST['lastActivityBeforeDay']);
		if (isset($_POST['lastActivityBeforeMonth'])) $this->lastActivityBeforeMonth = intval($_POST['lastActivityBeforeMonth']);
		if (!empty($_POST['lastActivityBeforeYear'])) $this->lastActivityBeforeYear = intval($_POST['lastActivityBeforeYear']);
		
		// posts
		if (isset($_POST['postsGreaterThan']) && $_POST['postsGreaterThan'] !== '') $this->postsGreaterThan = intval($_POST['postsGreaterThan']);
		if (isset($_POST['postsLessThan']) && $_POST['postsLessThan'] !== '') $this->postsLessThan = intval($_POST['postsLessThan']);
		
		// ip address
		if (isset($_POST['registrationIpAddress1']) && $_POST['registrationIpAddress1'] !== '') $this->registrationIpAddress1 = intval($_POST['registrationIpAddress1']);
		if (isset($_POST['registrationIpAddress2']) && $_POST['registrationIpAddress2'] !== '') $this->registrationIpAddress2 = intval($_POST['registrationIpAddress2']);
		if (isset($_POST['registrationIpAddress3']) && $_POST['registrationIpAddress3'] !== '') $this->registrationIpAddress3 = intval($_POST['registrationIpAddress3']);
		if (isset($_POST['registrationIpAddress4']) && $_POST['registrationIpAddress4'] !== '') $this->registrationIpAddress4 = intval($_POST['registrationIpAddress4']);
		
		// status
		if (isset($_POST['enabled'])) $this->enabled = intval($_POST['enabled']);
		if (isset($_POST['notEnabled'])) $this->notEnabled = intval($_POST['notEnabled']);
		if (isset($_POST['banned'])) $this->banned = intval($_POST['banned']);
		if (isset($_POST['notBanned'])) $this->notBanned = intval($_POST['notBanned']);
		if (isset($_POST['hasSpecialPermissions'])) $this->hasSpecialPermissions = intval($_POST['hasSpecialPermissions']);
	}
	
	protected function buildConditions(ConditionBuilder $conditions) {
		// registration date
		if ($this->registrationDateAfterDay && $this->registrationDateAfterMonth && $this->registrationDateAfterYear) {
			$time = @gmmktime(0, 0, 0, $this->registrationDateAfterMonth, $this->registrationDateAfterDay, $this->registrationDateAfterYear);
			if ($time !== false && $time !== -1) $conditions->add("user.registrationDate > ".$time);
		}
		if ($this->registrationDateBeforeDay && $this->registrationDateBeforeMonth && $this->registrationDateBeforeYear) {
			$time = @gmmktime(0, 0, 0, $this->registrationDateBeforeMonth, $this->registrationDateBeforeDay, $this->registrationDateBeforeYear);
			if ($time !== false && $time !== -1) $conditions->add("user.registrationDate < ".$time);
		}
		
		// last activity
		if ($this->lastActivityAfterDay && $this->lastActivityAfterMonth && $this->lastActivityAfterYear) {
			$time = @gmmktime(0, 0, 0, $this->lastActivityAfterMonth, $this->lastActivityAfterDay, $this->lastActivityAfterYear);
			if ($time !== false && $time !== -1) $conditions->add("user.lastActivityTime > ".$time);
		}
		if ($this->lastActivityBeforeDay && $this->lastActivityBeforeMonth && $this->lastActivityBeforeYear) {
			$time = @gmmktime(0, 0, 0, $this->lastActivityBeforeMonth, $this->lastActivityBeforeDay, $this->lastActivityBeforeYear);
			if ($time !== false && $time !== -1) $conditions->add("user.lastActivityTime < ".$time);
		}
		
		// posts
		if ($this->postsGreaterThan !== '' || $this->postsLessThan !== '') {
			$postsCondition = '';
			if ($this->postsGreaterThan !== '') {
				$postsCondition .= "posts > ".$this->postsGreaterThan;
			}
			if ($this->postsLessThan !== '') {
				if (!empty($postsCondition)) $postsCondition .= " AND ";
				$postsCondition .= "posts < ".$this->postsLessThan;
			}
			
			$conditions->add("user.userID IN (SELECT userID FROM wbb".WBB_N."_user WHERE ".$postsCondition.")");
		}
		
		// ip address
		if ($this->registrationIpAddress1 !== '' || $this->registrationIpAddress2 !== '' || $this->registrationIpAddress3 !== '' || $this->registrationIpAddress4 !== '') {
			$ipAddress = 	($this->registrationIpAddress1 !== '' ? $this->registrationIpAddress1 : '%') . '.' .
					($this->registrationIpAddress2 !== '' ? $this->registrationIpAddress2 : '%') . '.' .
					($this->registrationIpAddress3 !== '' ? $this->registrationIpAddress3 : '%') . '.' .
					($this->registrationIpAddress4 !== '' ? $this->registrationIpAddress4 : '%');
			$conditions->add("user.registrationIpAddress LIKE '".escapeString($ipAddress)."'");
		}
		
		// status
		if ($this->enabled) {
			$conditions->add("user.activationCode = 0");
		}
		if ($this->notEnabled) {
			$conditions->add("user.activationCode <> 0");
		}
		if ($this->banned) {
			$conditions->add("user.banned <> 0");
		}
		if ($this->notBanned) {
			$conditions->add("user.banned = 0");
		}
		if ($this->hasSpecialPermissions) {
			$conditions->add("user.userID IN (SELECT userID FROM wbb".WBB_N."_board_to_user)");
		}
	}
	
	protected function assignVariables() {
		InlineCalendar::assignVariables();
		
		WCF::getTPL()->assign(array(
			'registrationDateAfterDay' => $this->registrationDateAfterDay,
			'registrationDateAfterMonth' => $this->registrationDateAfterMonth,
			'registrationDateAfterYear' => $this->registrationDateAfterYear,
			'registrationDateBeforeDay' => $this->registrationDateBeforeDay,
			'registrationDateBeforeMonth' => $this->registrationDateBeforeMonth,
			'registrationDateBeforeYear' => $this->registrationDateBeforeYear,
			'lastActivityAfterDay' => $this->lastActivityAfterDay,
			'lastActivityAfterMonth' => $this->lastActivityAfterMonth,
			'lastActivityAfterYear' => $this->lastActivityAfterYear,
			'lastActivityBeforeDay' => $this->lastActivityBeforeDay,
			'lastActivityBeforeMonth' => $this->lastActivityBeforeMonth,
			'lastActivityBeforeYear' => $this->lastActivityBeforeYear,
			'postsGreaterThan' => $this->postsGreaterThan,
			'postsLessThan' => $this->postsLessThan,
			'registrationIpAddress1' => $this->registrationIpAddress1,
			'registrationIpAddress2' => $this->registrationIpAddress2,
			'registrationIpAddress3' => $this->registrationIpAddress3,
			'registrationIpAddress4' => $this->registrationIpAddress4,
			'enabled' => $this->enabled,
			'notEnabled' => $this->notEnabled,
			'banned' => $this->banned,
			'notBanned' => $this->notBanned,
			'hasSpecialPermissions' => $this->hasSpecialPermissions
		));
		
		WCF::getTPL()->append(array(
			'additionalTabs' => '<li id="conditions"><a onclick="tabMenu.showSubTabMenu(\'conditions\');"><span>'.WCF::getLanguage()->get('wbb.acp.user.search.conditions').'</span></a></li>',
			'additionalTabContents' => WCF::getTPL()->fetch('userSearchFormConditions')
		));
	}
	
	/**
	 * @see EventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		if ($eventName == 'readFormParameters') {
			$this->readFormParameters();
		}
		else if ($eventName == 'buildConditions') {
			$this->buildConditions($eventObj->conditions);
		}
		else if ($eventName == 'assignVariables') {
			$this->assignVariables();
		}
	}
}
?>
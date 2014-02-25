<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/form/UserOptionListForm.class.php');
require_once(WCF_DIR.'lib/system/database/ConditionBuilder.class.php');
require_once(WCF_DIR.'lib/system/event/EventHandler.class.php');
require_once(WCF_DIR.'lib/data/user/UserEditor.class.php');
require_once(WCF_DIR.'lib/data/user/group/Group.class.php');
		
/**
 * Shows the users mass processing form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category 	Community Framework
 */
class UsersMassProcessingForm extends UserOptionListForm {
	// system
	public $templateName = 'usersMassProcessing';
	
	// parameters
	public $username = '';
	public $email = '';
	public $groupIDArray = array();
	public $languageIDArray = array();
	public $invertGroupIDs = 0;
	
	// assign to group
	public $assignToGroupIDArray = array();
	
	// export mail address
	public $fileType = 'csv';
	public $separator = ',';
	public $textSeparator = '"'; 
	
	// send mail
	public $subject = '';
	public $text = '';
	public $from = '';
	public $enableHTML = 0;
	
	// data
	public $availableGroups = array();
	public $availableLanguages = array();
	public $options = array();
	public $availableActions = array('sendMail', 'exportMailAddress', 'assignToGroup', 'delete');
	public $affectedUsers = 0;
	
	/**
	 * conditions builder object.
	 * 
	 * @var	ConditionBuilder
	 */
	public $conditions = null;
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['username'])) $this->username = StringUtil::trim($_POST['username']);
		if (isset($_POST['email'])) $this->email = StringUtil::trim($_POST['email']);
		if (isset($_POST['groupIDArray']) && is_array($_POST['groupIDArray'])) $this->groupIDArray = ArrayUtil::toIntegerArray($_POST['groupIDArray']);
		if (isset($_POST['languageIDArray']) && is_array($_POST['languageIDArray'])) $this->languageIDArray = ArrayUtil::toIntegerArray($_POST['languageIDArray']);
		if (isset($_POST['invertGroupIDs'])) $this->invertGroupIDs = intval($_POST['invertGroupIDs']);
		// assign to group
		if (isset($_POST['assignToGroupIDArray']) && is_array($_POST['assignToGroupIDArray'])) $this->assignToGroupIDArray = ArrayUtil::toIntegerArray($_POST['assignToGroupIDArray']);
		// export mail address
		if (isset($_POST['fileType']) && $_POST['fileType'] == 'xml') $this->fileType = $_POST['fileType'];
		if (isset($_POST['separator'])) $this->separator = $_POST['separator'];
		if (isset($_POST['textSeparator'])) $this->textSeparator = $_POST['textSeparator'];
		// send mail
		if (isset($_POST['subject'])) $this->subject = StringUtil::trim($_POST['subject']);
		if (isset($_POST['text'])) $this->text = StringUtil::trim($_POST['text']);
		if (isset($_POST['from'])) $this->from = StringUtil::trim($_POST['from']);
		if (isset($_POST['enableHTML'])) $this->enableHTML = intval($_POST['enableHTML']);
	}
	
	/**
	 * @see Form::validate()
	 */
	public function validate() {
		AbstractForm::validate();

		// action
		if (!in_array($this->action, $this->availableActions)) {
			throw new UserInputException('action');
		}
		
		// assign to group
		if ($this->action == 'assignToGroup') {
			if (!count($this->assignToGroupIDArray)) {
				throw new UserInputException('assignToGroupIDArray');
			}
		}
		
		// send mail
		if ($this->action == 'sendMail') {
			if (empty($this->subject)) {
				throw new UserInputException('subject');
			}
			
			if (empty($this->text)) {
				throw new UserInputException('text');
			}
			
			if (empty($this->from)) {
				throw new UserInputException('from');
			}
		}
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		parent::save();
		
		// build conditions
		$this->conditions = new ConditionBuilder();
		// static fields
		if (!empty($this->username)) {
			$this->conditions->add("user.username LIKE '%".addcslashes(escapeString($this->username), '_%')."%'");
		}
		if (!empty($this->email)) {
			$this->conditions->add("user.email LIKE '%".addcslashes(escapeString($this->email), '_%')."%'");
		}
		if (count($this->groupIDArray) > 0) {
			$this->conditions->add("user.userID ".($this->invertGroupIDs == 1 ? 'NOT ' : '')."IN (SELECT userID FROM wcf".WCF_N."_user_to_groups WHERE groupID IN (".implode(',', $this->groupIDArray)."))");
		}
		if (count($this->languageIDArray) > 0) {
			$this->conditions->add("user.languageID IN (".implode(',', $this->languageIDArray).")");
		}
		
		// dynamic fields
		foreach ($this->activeOptions as $name => $option) {
			$value = isset($this->values[$option['optionName']]) ? $this->values[$option['optionName']] : null;
			$condition = $this->getTypeObject($option['optionType'])->getCondition($option, $value, isset($this->matchExactly[$name]));
			if ($condition !== false) $this->conditions->add($condition);
		}

		// call buildConditions event
		EventHandler::fireAction($this, 'buildConditions');
		
		// execute action
		switch ($this->action) {
			case 'sendMail':
				WCF::getUser()->checkPermission('admin.user.canMailUser');
				// get user ids
				$userIDArray = array();
				$sql = "SELECT		user.userID
					FROM		wcf".WCF_N."_user user
					LEFT JOIN	wcf".WCF_N."_user_option_value option_value USING (userID)
					".$this->conditions->get();
				$result = WCF::getDB()->sendQuery($sql);
				while ($row = WCF::getDB()->fetchArray($result)) {
					$userIDArray[] = $row['userID'];
					$this->affectedUsers++;
				}

				// save config in session
				$userMailData = WCF::getSession()->getVar('userMailData');
				if ($userMailData === null) $userMailData = array();
				$mailID = count($userMailData);
				$userMailData[$mailID] = array(
					'action' => '',
					'userIDs' => implode(',', $userIDArray),
					'groupIDs' => '',
					'subject' => $this->subject,
					'text' => $this->text,
					'from' => $this->from,
					'enableHTML' => $this->enableHTML
				);
				WCF::getSession()->register('userMailData', $userMailData);
				$this->saved();
				
				// show worker template
				WCF::getTPL()->assign(array(
					'pageTitle' => WCF::getLanguage()->get('wcf.acp.user.sendMail'),
					'url' => 'index.php?action=UserMail&mailID='.$mailID.'&packageID='.PACKAGE_ID.SID_ARG_2ND_NOT_ENCODED
				));
				WCF::getTPL()->display('worker');
				exit;
				break;
				
			case 'exportMailAddress':
				WCF::getUser()->checkPermission('admin.user.canMailUser');
				// send content type
				header('Content-Type: text/'.$this->fileType.'; charset='.CHARSET);
				header('Content-Disposition: attachment; filename="export.'.$this->fileType.'"');
				
				if ($this->fileType == 'xml') {
					echo "<?xml version=\"1.0\" encoding=\"".CHARSET."\"?>\n<addresses>\n";
				}
				
				// get users
				$sql = "SELECT		user.email
					FROM		wcf".WCF_N."_user user
					LEFT JOIN	wcf".WCF_N."_user_option_value option_value USING (userID)
					".$this->conditions->get()."
					ORDER BY	user.email";
				$result = WCF::getDB()->sendQuery($sql);
				$i = 0; $j = WCF::getDB()->countRows($result) - 1;
				while ($row = WCF::getDB()->fetchArray($result)) {
					if ($this->fileType == 'xml') echo "<address><![CDATA[".StringUtil::escapeCDATA($row['email'])."]]></address>\n";
					else echo $this->textSeparator . $row['email'] . $this->textSeparator . ($i < $j ? $this->separator : '');
					$i++;
					$this->affectedUsers++;
				}
				
				if ($this->fileType == 'xml') {
					echo "</addresses>";
				}
				$this->saved();
				exit;
				break;
				
			case 'assignToGroup':
				WCF::getUser()->checkPermission('admin.user.canEditUser');
				$userIDArray = array();
				$sql = "SELECT		user.*,
							GROUP_CONCAT(groupID SEPARATOR ',') AS groupIDs
					FROM		wcf".WCF_N."_user user
					LEFT JOIN	wcf".WCF_N."_user_option_value option_value USING (userID)
					LEFT JOIN	wcf".WCF_N."_user_to_groups groups
					ON		(groups.userID = user.userID)
					".$this->conditions->get()."		
					GROUP BY	user.userID";
				$result = WCF::getDB()->sendQuery($sql);
				while ($row = WCF::getDB()->fetchArray($result)) {
					if (!Group::isAccessibleGroup(explode(',', $row['groupIDs']))) {
						throw new PermissionDeniedException();
					}
					
					$user = new UserEditor(null, $row);
					$user->addToGroups($this->assignToGroupIDArray, false, false);
					$userIDArray[] = $row['userID'];
					$this->affectedUsers++;
				}
				
				Session::resetSessions($userIDArray);
				break;
				
			case 'delete':
				WCF::getUser()->checkPermission('admin.user.canDeleteUser');
				$userIDArray = array();
				$sql = "SELECT		user.*,
							GROUP_CONCAT(groupID SEPARATOR ',') AS groupIDs
					FROM		wcf".WCF_N."_user user
					LEFT JOIN	wcf".WCF_N."_user_option_value option_value USING (userID)
					LEFT JOIN	wcf".WCF_N."_user_to_groups groups
					ON		(groups.userID = user.userID)
					".$this->conditions->get()."		
					GROUP BY	user.userID";
				$result = WCF::getDB()->sendQuery($sql);
				while ($row = WCF::getDB()->fetchArray($result)) {
					if (!Group::isAccessibleGroup(explode(',', $row['groupIDs']))) {
						throw new PermissionDeniedException();
					}
					
					$userIDArray[] = $row['userID'];
					$this->affectedUsers++;
				}
				
				UserEditor::deleteUsers($userIDArray);
				break;
		}
		$this->saved();
		
		WCF::getTPL()->assign('affectedUsers', $this->affectedUsers);
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		if (!count($_POST)) {
			if (MAIL_USE_FORMATTED_ADDRESS)	$this->from = MAIL_FROM_NAME . ' <' . MAIL_FROM_ADDRESS . '>';
			else $this->from = MAIL_FROM_ADDRESS;
		}
		
		$this->availableGroups = $this->getAvailableGroups();
		$this->availableLanguages = $this->getAvailableLanguages();
		
		foreach ($this->activeOptions as $name => $option) {
			if (isset($this->values[$name])) {
				$this->activeOptions[$name]['optionValue'] = $this->values[$name];
			}
		}
		
		$this->options = $this->getCategoryOptions('profile');
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'username' => $this->username,
			'email' => $this->email,
			'groupIDArray' => $this->groupIDArray,
			'languageIDArray' => $this->languageIDArray,
			'invertGroupIDs' => $this->invertGroupIDs,
			'availableGroups' => $this->availableGroups,
			'availableLanguages' => $this->availableLanguages,
			'options' => $this->options,
			'availableActions' => $this->availableActions,
			// assign to group
			'assignToGroupIDArray' => $this->assignToGroupIDArray,
			// export mail address
			'separator' => $this->separator,
			'textSeparator' => $this->textSeparator,
			'fileType' => $this->fileType,
			// send mail
			'subject' => $this->subject,
			'text' => $this->text,
			'from' => $this->from,
			'enableHTML' => $this->enableHTML
		));
	}
	
	/**
	 * @see Form::show()
	 */
	public function show() {
		// set active menu item
		WCFACP::getMenu()->setActiveMenuItem('wcf.acp.menu.link.user.massProcessing');
		
		// check permission
		WCF::getUser()->checkPermission(array('admin.user.canEditUser', 'admin.user.canDeleteUser', 'admin.user.canMailUser'));
		
		// check master password
		WCFACP::checkMasterPassword();
		
		// get user options and categories from cache
		$this->readCache();
		
		// show form
		parent::show();
	}
	
	/**
	 * @see SearchableOptionType::getSearchFormElement()
	 */
	protected function getFormElement($type, &$optionData) {
		return $this->getTypeObject($type)->getSearchFormElement($optionData);
	}
	
	/**
	 * @see DynamicOptionListForm::checkOption()
	 */
	protected function checkOption($optionName) {
		$option = $this->cachedOptions[$optionName];
		return ($option['searchable'] == 1 && !$option['disabled'] && ($option['visible'] == 3 || $option['visible'] < 2));
	}
}
?>
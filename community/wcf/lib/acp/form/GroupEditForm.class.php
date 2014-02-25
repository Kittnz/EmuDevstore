<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/form/GroupAddForm.class.php');

/**
 * Shows the group edit form.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category 	Community Framework
 */
class GroupEditForm extends GroupAddForm {
	public $menuItemName = 'wcf.acp.menu.link.group';
	public $permission = 'admin.user.canEditGroup';
	
	public $groupID = 0;
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		// get group
		if (isset($_REQUEST['groupID'])) $this->groupID = intval($_REQUEST['groupID']);
		require_once(WCF_DIR.'lib/data/user/group/GroupEditor.class.php');
		$this->group = new GroupEditor($this->groupID);
		if (!$this->group->groupID) {
			throw new IllegalLinkException();
		}
		if (!$this->group->isAccessible()) {
			throw new PermissionDeniedException();
		}
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		if (!count($_POST)) {
			$this->groupName = $this->group->groupName;
			
			foreach ($this->activeOptions as $key => $option) {
				$value = $this->group->getGroupOption($option['optionName']);
				if ($value !== null) {
					$this->activeOptions[$key]['optionValue'] = $value;
				}
			}
		}
		
		parent::readData();
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'groupID' => $this->group->groupID,
			'action' => 'edit'
		));
		
		// add warning when the initiator is in the group
		if ($this->group->isMember($this->groupID)) {
			WCF::getTPL()->assign('warningSelfEdit', true);
		}
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		AbstractForm::save();
		
		// save group
		$this->group->update($this->groupName, $this->activeOptions, $this->additionalFields);
	
		// update sessions
		require_once(WCF_DIR.'lib/system/session/Session.class.php');
		Session::resetSessions();
		$this->saved();
		
		// show success message
		WCF::getTPL()->assign('success', true);
	}
}
?>
<?php
// wcf imports
require_once(WCF_DIR.'lib/page/AbstractPage.class.php');
require_once(WCF_DIR.'lib/data/user/UserProfileFrame.class.php');
require_once(WCF_DIR.'lib/data/user/infraction/warning/UserWarningList.class.php');
require_once(WCF_DIR.'lib/data/user/infraction/suspension/UserSuspensionList.class.php');

/**
 * Shows an overview of all warnings from a user.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.user.infraction
 * @subpackage	page
 * @category 	Community Framework (commercial)
 */
class UserWarningOverviewPage extends AbstractPage {
	// system
	public $templateName = 'userWarningOverview';
	
	/**
	 * user warning list object
	 * 
	 * @var	UserWarningList
	 */
	public $userWarningList = null;
	
	/**
	 * user suspension list object
	 * 
	 * @var	UserSuspensionList
	 */
	public $userSuspensionList = null;
	
	/**
	 * user profile frame
	 * 
	 * @var UserProfileFrame
	 */
	public $frame = null;
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		// get profile frame
		$this->frame = new UserProfileFrame($this);
		
		// init lists
		$this->userWarningList = new UserWarningList();
		$this->userSuspensionList = new UserSuspensionList();
		// set conditions
		$this->userWarningList->sqlConditions .= 'user_warning.userID = '.$this->frame->getUserID();
		$this->userWarningList->sqlConditions .= ' AND user_warning.packageID = '.PACKAGE_ID;
		
		$this->userSuspensionList->sqlConditions .= 'user_suspension.userID = '.$this->frame->getUserID();
		$this->userSuspensionList->sqlConditions .= ' AND user_suspension.packageID = '.PACKAGE_ID;
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		// read warnings
		$this->userWarningList->sqlLimit = 50;
		$this->userWarningList->sqlOrderBy = 'user_warning.expires DESC';
		$this->userWarningList->readObjects();
		// read suspensions
		$this->userSuspensionList->sqlLimit = 50;
		$this->userSuspensionList->sqlOrderBy = 'user_suspension.expires DESC';
		$this->userSuspensionList->readObjects();
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		$this->frame->assignVariables();
		WCF::getTPL()->assign(array(
			'userWarnings' => $this->userWarningList->getObjects(),
			'userSuspensions' => $this->userSuspensionList->getObjects()
		));
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		// set active menu item
		UserProfileMenu::getInstance()->setActiveMenuItem('wcf.user.profile.menu.link.infraction');
		
		// check permission
		if (!USER_CAN_SEE_HIS_WARNINGS || WCF::getUser()->userID != $this->frame->getUserID()) {
			WCF::getUser()->checkPermission('admin.user.infraction.canWarnUser');
		}
		
		parent::show();
	}
}
?>
<?php
// wcf imports
require_once(WCF_DIR.'lib/page/AbstractPage.class.php');
require_once(WCF_DIR.'lib/data/user/UserProfileFrame.class.php');

/**
 * UserPage show the user profile page.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.page.user.profile
 * @subpackage	page
 * @category 	Community Framework
 */
class UserPage extends AbstractPage {
	// system
	public $templateName = 'userProfile';

	/**
	 * user profile frame
	 * 
	 * @var UserProfileFrame
	 */
	public $frame = null;
	
	/**
	 * user option categories
	 *
	 * @var array
	 */
	public $categories = array();
	
	/**
	 * list of general information
	 * 
	 * @var	array
	 */
	public $generalInformation = array();
	
	/**
	 * list of contact information
	 * 
	 * @var	array
	 */
	public $contactInformation = array();
	
	/**
	 * list of friends
	 *
	 * @var array<UserProfile>
	 */
	public $friends = array();
	
	/**
	 * list of profile visitors
	 *
	 * @var array<UserProfile>
	 */
	public $profileVisitors = array();
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		// get profile frame
		$this->frame = new UserProfileFrame($this);
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		// get profile options
		require_once(WCF_DIR.'lib/data/user/option/UserOptions.class.php');
		$userOptions = new UserOptions();
		$this->categories = $userOptions->getOptionTree('profile', $this->frame->getUser());
		
		// move contact information and about me
		foreach ($this->categories as $category => $categoryData) {
			if ($category == 'profile.contact' || $category == 'profile.messenger') {
				foreach ($categoryData['options'] as $key => $option) {
					if (isset($option['outputData'])) {
						$this->contactInformation[] = $option['outputData'];
					}
					else if (!empty($option['optionValue']) && (empty($option['outputClass']) || !($userOptions->getOutputObject($option['outputClass']) instanceof UserOptionOutputContactInformation))) {
						$this->contactInformation[] = array(
							'icon' => '',
							'title' => WCF::getLanguage()->get('wcf.user.option.'.$option['optionName']),
							'value' => $option['optionValue'],
							'url' => ''
						);
					}
				}
				
				unset($this->categories[$category]);
			}
		}

		// add vcard link
		$this->contactInformation[] = array(
			'icon' => StyleManager::getStyle()->getIconPath('vCardM.png'),
			'title' => WCF::getLanguage()->get('wcf.user.profile.downloadVCard'),
			'value' => StringUtil::encodeHTML($this->frame->getUser()->username),
			'url' => 'index.php?page=VCard&amp;userID='.$this->frame->getUserID().SID_ARG_2ND
		);
		
		// add general informations
		// registration date
		$this->generalInformation[] = array(
			'icon' => StyleManager::getStyle()->getIconPath('registerM.png'),
			'title' => WCF::getLanguage()->get('wcf.user.registrationDate'),
			'value' => DateUtil::formatTime(null, $this->frame->getUser()->registrationDate)
		);
		// languages
		require_once(WCF_DIR.'lib/acp/form/UserOptionListForm.class.php');
		$languages = array();
		$availableLanguages = UserOptionListForm::getAvailableContentLanguages();
		if (!$this->frame->getUser()->languageIDs) {
			$this->languages = $availableLanguages;
		}
		else {
			$languageIDs = explode(',', $this->frame->getUser()->languageIDs);
			foreach ($languageIDs as $languageID) {
				if (isset($availableLanguages[$languageID])) {
					$languages[$languageID] = $availableLanguages[$languageID];
				}
			}
			
			// sort languages
			StringUtil::sort($languages);
		}
		if (count($languages)) {
			$this->generalInformation[] = array(
				'icon' => StyleManager::getStyle()->getIconPath('languageM.png'),
				'title' => WCF::getLanguage()->get('wcf.user.profile.languages'),
				'value' => implode(', ', $languages)
			);
		}
		// last activity
		if (!$this->frame->getUser()->isOnline() && (!$this->frame->getUser()->invisible || WCF::getUser()->getPermission('admin.general.canViewInvisible')) && $this->frame->getUser()->lastActivityTime != 0) {
			$this->generalInformation[] = array(
				'icon' => StyleManager::getStyle()->getIconPath('offlineM.png'),
				'title' => WCF::getLanguage()->get('wcf.user.lastActivity'),
				'value' => DateUtil::formatTime(null, $this->frame->getUser()->lastActivityTime, true)
			);
		}
		// profile visits
		WCF::getTPL()->assign('user', $this->frame->getUser());
		$this->generalInformation[] = array(
			'icon' => StyleManager::getStyle()->getIconPath('visitsM.png'),
			'title' => WCF::getLanguage()->get('wcf.user.profile.hits'),
			'value' => StringUtil::formatNumeric($this->frame->getUser()->profileHits).($this->frame->getUser()->getProfileAge() > 1 ? ' '.WCF::getLanguage()->getDynamicVariable('wcf.user.profile.hitsPerDay') : '')
		);
		
		// get profile visitors
		$sql = "SELECT		avatar.*, user_table.*, visitor.*
			FROM		wcf".WCF_N."_user_profile_visitor visitor
			LEFT JOIN 	wcf".WCF_N."_user user_table
			ON 		(user_table.userID = visitor.userID)
			LEFT JOIN 	wcf".WCF_N."_avatar avatar
			ON 		(avatar.avatarID = user_table.avatarID)
			WHERE		ownerID = ".$this->frame->getUserID()."
					AND user_table.userID IS NOT NULL
			ORDER BY	time DESC";
		$result = WCF::getDB()->sendQuery($sql, 5);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$this->profileVisitors[] = new UserProfile(null, $row);
		}
		
		// friends
		if (MODULE_MEMBERS_LIST == 1 && $this->frame->getUser()->shareWhitelist) {
			$sql = "SELECT		avatar.*, user_table.*
				FROM		wcf".WCF_N."_user_whitelist friends
				LEFT JOIN 	wcf".WCF_N."_user user_table
				ON 		(user_table.userID = friends.whiteUserID)
				LEFT JOIN 	wcf".WCF_N."_avatar avatar
				ON 		(avatar.avatarID = user_table.avatarID)
				WHERE		friends.userID = ".$this->frame->getUserID()."
						AND confirmed = 1
						AND user_table.userID IS NOT NULL
				ORDER BY	friends.time DESC";
			$result = WCF::getDB()->sendQuery($sql, 5);
			while ($row = WCF::getDB()->fetchArray($result)) {
				$this->friends[] = new UserProfile(null, $row);
			}
		}
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		$this->frame->assignVariables();
		WCF::getTPL()->assign(array(
			'categories' => $this->categories,
			'contactInformation' => $this->contactInformation,
			'generalInformation' => $this->generalInformation,
			'friends' => $this->friends,
			'profileVisitors' => $this->profileVisitors,
			'allowSpidersToIndexThisPage' => true
		));
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		// set active menu item
		UserProfileMenu::getInstance()->setActiveMenuItem('wcf.user.profile.menu.link.profile');
		
		// update profile hits
		if ($this->frame->getUserID() != WCF::getUser()->userID && !WCF::getSession()->spiderID) {
			$sql = "UPDATE	wcf".WCF_N."_user
				SET	profileHits = profileHits + 1
				WHERE	userID = ".$this->frame->getUserID();
			WCF::getDB()->registerShutdownUpdate($sql);
			
			// save visitor
			if (WCF::getUser()->userID && !WCF::getUser()->invisible) {
				$sql = "INSERT INTO			wcf".WCF_N."_user_profile_visitor
									(ownerID, userID, time)
					VALUES				(".$this->frame->getUserID().", ".WCF::getUser()->userID.", ".TIME_NOW.")
					ON DUPLICATE KEY UPDATE		time = VALUES(time)";
				WCF::getDB()->registerShutdownUpdate($sql);
			}
		}
		
		parent::show();
	}
}
?>
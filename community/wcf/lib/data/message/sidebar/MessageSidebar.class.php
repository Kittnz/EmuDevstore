<?php
// wcf imports
require_once(WCF_DIR.'lib/data/user/UserProfile.class.php');
require_once(WCF_DIR.'lib/data/user/option/UserOptions.class.php');
require_once(WCF_DIR.'lib/data/message/sidebar/MessageSidebarObject.class.php');

/**
 * Represents a message sidebar.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.message.sidebar
 * @subpackage	data.message.sidebar
 * @category 	Community Framework
 */
class MessageSidebar {
	/**
	 * user options
	 *
	 * @var UserOptions
	 */
	protected static $userOptions = null; 

	/**
	 * sidebar object.
	 *
	 * @var MessageSidebarObject
	 */
	public $object = null;
	
	/**
	 * list of user credits
	 *
	 * @var	array
	 */
	public $userCredits = array();
	
	/**
	 * list of user symbols
	 *
	 * @var	array
	 */
	public $userSymbols = array();
	
	/**
	 * list of user contacts
	 *
	 * @var	array
	 */
	public $userContacts = array();
	
	/**
	 * special username styling
	 *
	 * @var string
	 */
	public $usernameStyle = '%s';
	
	/**
	 * Creates a new MessageSidebar object.
	 *
	 * @param	UserProfile	$user
	 */
	public function __construct(MessageSidebarObject $object) {
		$this->object = $object;
		
		// init user options
		if ($this->getUser()->userID) {
			if ((!$this->getUser()->protectedProfile || $this->getUser()->userID == WCF::getUser()->userID)) {
				$userOptions = self::getUserOptions();
				$categories = $userOptions->getOptionTree('profile', $this->getUser());
		
				// add registration date
				if (MESSAGE_SIDEBAR_ENABLE_REGISTRATION_DATE == 1) {
					$this->addUserCredit(WCF::getLanguage()->get('wcf.user.registrationDate'), DateUtil::formatDate(null, $this->getUser()->registrationDate));
				}
				
				// user options
				foreach ($categories as $category) {
					if ($category['categoryName'] == 'profile.contact' || $category['categoryName'] == 'profile.messenger') {
						foreach ($category['options'] as $userOption) {
							$this->addUserContact($userOption['optionValue']);
						}
					}
					else {
						foreach ($category['options'] as $userOption) {
							if ($userOption['optionName'] == 'birthday' || $userOption['optionName'] == 'gender') {
								$this->addUserSymbol($userOption['optionValue']);
							}
							else {					
								$this->addUserCredit(WCF::getLanguage()->get('wcf.user.option.'.$userOption['optionName']), $userOption['optionValue']);
							}
						}
					}
				}
				
				// add friend icon
				if (MESSAGE_SIDEBAR_ENABLE_FRIEND_ICON) {
					if (WCF::getUser()->userID && UserProfile::isBuddy($this->getUser()->userID)) {
						$this->addUserSymbol('<img src="'.StyleManager::getStyle()->getIconPath('friendsS.png').'" alt="'.WCF::getLanguage()->getDynamicVariable('wcf.user.profile.friend', array('username' => $this->getUser()->username)).'" title="'.WCF::getLanguage()->getDynamicVariable('wcf.user.profile.friend', array('username' => $this->getUser()->username)).'" />');
					}
				}
			}
			
			// banned icon
			if ($object->getUser()->banned) {
				$this->addUserSymbol('<img src="'.StyleManager::getStyle()->getIconPath('bannedS.png').'" alt="'.WCF::getLanguage()->getDynamicVariable('wcf.user.profile.banned', array('username' => $this->getUser()->username)).'" title="'.WCF::getLanguage()->getDynamicVariable('wcf.user.profile.banned', array('username' => $this->getUser()->username)).'" />');
			}
		}
	}
	
	/**
	 * Returns the user object.
	 *
	 * @return	UserProfile
	 */
	public function getUser() {
		return $this->object->getUser();
	}
	
	/**
	 * Returns the sidebar object.
	 *
	 * @return	MessageSidebarObject
	 */
	public function getSidebarObject() {
		return $this->object;
	}
	
	/**
	 * Returns the message id.
	 *
	 * @return	integer
	 */
	public function getMessageID() {
		return $this->object->getMessageID();
	}
	
	/**
	 * Returns the user credits.
	 *
	 * @return array
	 */
	public function getUserCredits() {
		return $this->userCredits;
	}
	
	/**
	 * Returns the user symbols.
	 *
	 * @return array
	 */
	public function getUserSymbols() {
		return $this->userSymbols;
	}
	
	/**
	 * Returns the user contacts.
	 *
	 * @return array
	 */
	public function getUserContacts() {
		return $this->userContacts;
	}
	
	/**
	 * Returns a special username styles.
	 * 
	 * @return string
	 */
	public function getStyledUsername() {
		if ($this->usernameStyle != '%s') {
			return sprintf($this->usernameStyle, StringUtil::encodeHTML($this->getUser()->username));
		}
		return StringUtil::encodeHTML($this->getUser()->username);
	}
	
	/**
	 * Adds a user credit to the sidebar.
	 *
	 * @param	string		$name
	 * @param 	string		$value
	 * @param 	string		$url
	 */
	public function addUserCredit($name, $value, $url = '') {
		$this->userCredits[] = array('name' => $name, 'value' => $value, 'url' => $url);
	}
	
	/**
	 * Adds a user symbol to the sidebar.
	 *
	 * @param 	string		$value
	 */
	public function addUserSymbol($value) {
		$this->userSymbols[] = array('value' => $value);
	}
	
	/**
	 * Adds a user contact option to the sidebar.
	 *
	 * @param	string		$value
	 */
	public function addUserContact($value) {
		$this->userContacts[] = array('value' => $value);
	}
	
	/**
	 * Returns the cached user options.
	 *
	 * @return	array
	 */
	protected static function getUserOptions() {
		if (self::$userOptions === null) {
			self::$userOptions = new UserOptions('short', explode(',', MESSAGE_SIDEBAR_SHOW_USER_OPTIONS));
		}
		
		return self::$userOptions;
	}
}
?>
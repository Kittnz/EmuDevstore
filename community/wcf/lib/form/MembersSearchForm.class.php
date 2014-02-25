<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/form/UserSearchForm.class.php');
require_once(WCF_DIR.'lib/page/util/menu/PageMenu.class.php');
require_once(WCF_DIR.'lib/system/event/EventHandler.class.php');
require_once(WCF_DIR.'lib/page/MembersListPage.class.php');

/**
 * Shows the members search form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.page.user.profile
 * @subpackage	form
 * @category 	Community Framework
 */
class MembersSearchForm extends UserSearchForm {
	public $templateName = 'membersSearch';
	public $options = array(), $categories = array();
	public $maxResults = 1000;
	
	/**
	 * @see Form::save()
	 */	
	public function save() {
		AbstractForm::save();
		
		// store search result in database
		$sql = "INSERT INTO 	wcf".WCF_N."_search
					(userID, searchData, searchDate, searchType)
			VALUES		(".WCF::getUser()->userID.",
					'".escapeString(serialize($this->matches))."',
					".TIME_NOW.",
					'members')";
		WCF::getDB()->sendQuery($sql);
		unset($sql); // save memory
		
		// get new search id
		$this->searchID = WCF::getDB()->getInsertID();
		$this->saved();
		
		// forward to result page
		HeaderUtil::redirect('index.php?page=MembersList&searchID='.$this->searchID.SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
	
	/**
	 * Does nothing.
	 */
	protected function getSearchResult() {}
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		AbstractForm::readParameters();
	}
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		$this->staticParameters['groupIDs'] = array();
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		AbstractForm::readData();
		
		if (!count($_POST)) {
			if (isset($_GET['values']) && is_array($_GET['values'])) {
				$this->values = $_GET['values'];
				$this->submit();
			}
		}
		
		// get path to category icons
		foreach ($this->cachedCategories as $key => $category) {
			// add icon path
			if (!empty($category['categoryIconM'])) {
				// get relative path
				$path = '';
				if (empty($category['packageDir'])) {
					$path = RELATIVE_WCF_DIR;
				}
				else {						
					$path = FileUtil::getRealPath(RELATIVE_WCF_DIR.$category['packageDir']);
				}
				
				$this->cachedCategories[$key]['categoryIconM'] = $path . $category['categoryIconM'];
			}
		}
		
		// get categories
		$this->categories = $this->getOptionTree('profile');
		if (count($this->categories) == 0) $this->options = $this->getCategoryOptions('profile');
		
		// insert default values
		foreach ($this->activeOptions as $name => $option) {
			if (isset($this->values[$name])) {
				$this->activeOptions[$name]['optionValue'] = $this->values[$name];
			}
		}
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		AbstractForm::assignVariables();
		
		WCF::getTPL()->assign(array(
			'staticParameters' 	=> $this->staticParameters,
			'matchExactly' 		=> $this->matchExactly,
			'options' 		=> $this->options,
			'optionCategories' 	=> $this->categories,
			'hasFriends' 		=> MembersListPage::hasFriends()
		));
	}
	
	/**
	 * @see Form::show()
	 */
	public function show() {
		// set active header menu item
		PageMenu::setActiveMenuItem('wcf.header.menu.memberslist');
		
		// check permission
		WCF::getUser()->checkPermission('user.membersList.canView');
		
		if (MODULE_MEMBERS_LIST != 1) {
			throw new IllegalLinkException();
		}
		
		// get user options and categories from cache
		$this->readCache();
		
		AbstractForm::show();
	}
	
	/**
	 * @see DynamicOptionListForm::checkOption()
	 */
	protected function checkOption($optionName) {
		$option = $this->cachedOptions[$optionName];
		return ($option['searchable'] == 1 && !$option['disabled'] && $option['visible'] == 0);
	}
	
	/**
	 * @see UserSearchForm::buildStaticConditions()
	 */
	protected function buildStaticConditions() {
		parent::buildStaticConditions();
		
		if (isset($this->staticParameters['email']) && !empty($this->staticParameters['email'])) {
			$this->conditions->add("option_value.userOption".User::getUserOptionID('hideEmailAddress')." = 0");
		}
	}
}
?>
<?php
// wcf imports
require_once(WCF_DIR.'lib/form/AbstractForm.class.php');

/**
 * Shows the help search form.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.help
 * @subpackage	form
 * @category 	Community Framework
 */
class HelpSearchForm extends AbstractForm {
	// system
	public $templateName = 'helpSearch';
	
	/**
	 * given search query
	 * 
	 * @var	string
	 */
	public $query = '';
	
	/**
	 * list of results
	 * 
	 * @var	array
	 */
	public $result = array();
	
	/**
	 * existing search id
	 * 
	 * @var	integer
	 */
	public $searchID = 0;
	
	/**
	 * existing search data
	 * 
	 * @var	array
	 */
	public $searchData = null;
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['q'])) $this->query = StringUtil::trim($_REQUEST['q']);
	}
	
	/**
	 * @see Form::validate()
	 */
	public function validate() {
		parent::validate();
		
		if (empty($this->query)) {
			throw new UserInputException('query');
		}
		
		if (StringUtil::length($this->query) < 3 || strpos($this->query, '%') !== false || strpos($this->query, '_') !== false) {
			throw new UserInputException('query', 'invalid');
		}
		
		// search
		$itemNames = array();
		$sql = "SELECT	languageItem
			FROM	wcf".WCF_N."_language_item
			WHERE	languageID = ".WCF::getLanguage()->getLanguageID()."
				AND languageCategoryID = (
					SELECT	languageCategoryID
					FROM	wcf".WCF_N."_language_category
					WHERE	languageCategory = 'wcf.help.item'
				)
				AND packageID IN (
					SELECT	dependency
					FROM	wcf".WCF_N."_package_dependency
					WHERE	packageID = ".PACKAGE_ID."
				)
				AND (
					(languageUseCustomValue = 0 AND languageItemValue LIKE '%".escapeString($this->query)."%')
					OR (languageUseCustomValue = 1 AND languageCustomItemValue LIKE '%".escapeString($this->query)."%')
				)";
		$result = WCF::getDB()->sendQuery($sql, 1000);
		while ($row = WCF::getDB()->fetchArray($result)) {
			// search parsed variables
			try {
				$languageItemValue = WCF::getLanguage()->getDynamicVariable($row['languageItem']);
				if (preg_match('!'.preg_quote($this->query).'!i', $languageItemValue)) {
					$itemNames[] = str_replace('.description', '', str_replace('wcf.help.item.', '', $row['languageItem']));
				}
			}
			catch (SystemException $e) {} // ignore errors
		}
		
		if (!count($itemNames)) {
			throw new NamedUserException(WCF::getLanguage()->get('wcf.help.search.error.noMatches', array('$query' => StringUtil::encodeHTML($this->query))));
		}
		
		// get help items
		$sql = "SELECT		helpItem, permissions, options
			FROM		wcf".WCF_N."_help_item
			WHERE		helpItem IN ('".implode("','", $itemNames)."')
					AND isDisabled = 0
			ORDER BY	showOrder";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			// check options
			if (!empty($row['options'])) {
				$hasEnabledOption = false;
				$options = explode(',', strtoupper($row['options']));
				foreach ($options as $option) {
					if (defined($option) && constant($option)) {
						$hasEnabledOption = true;
						break;
					}
				}
				if (!$hasEnabledOption) continue;
			}
			
			// check permissions
			if (!empty($row['permissions'])) {
				$hasPermission = false;
				$permissions = explode(',', $row['permissions']);
				foreach ($permissions as $permission) {
					if (WCF::getUser()->getPermission($permission)) {
						$hasPermission = true;
						break;
					}
				}
				if (!$hasPermission) continue;
			}
			
			$this->result[] = $row['helpItem'];
		}
		
		if (!count($this->result)) {
			throw new NamedUserException(WCF::getLanguage()->get('wcf.help.search.error.noMatches', array('$query' => StringUtil::encodeHTML($this->query))));
		}
	}
	
	/**
	 * @see Form::submit()
	 */
	public function submit() {
		try {
			parent::submit();
		}
		catch (NamedUserException $e) {
			WCF::getTPL()->assign('errorMessage', $e->getMessage());
		}
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		parent::save();
		
		// save result in database
		$this->searchData = array('query' => $this->query, 'result' => $this->result);
		$sql = "INSERT INTO	wcf".WCF_N."_search
					(userID, searchData, searchDate, searchType)
			VALUES		(".WCF::getUser()->userID.",
					'".escapeString(serialize($this->searchData))."',
					".TIME_NOW.",
					'help')";
		WCF::getDB()->sendQuery($sql);
		$this->searchID = WCF::getDB()->getInsertID();
		$this->saved();
		
		// forward to result page
		HeaderUtil::redirect('index.php?page=HelpSearchResult&searchID='.$this->searchID.'&highlight='.urlencode($this->query).SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'query' => $this->query
		));
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		if (MODULE_HELP != 1) {
			throw new IllegalLinkException();
		}
		
		require_once(WCF_DIR.'lib/page/util/menu/PageMenu.class.php');
		PageMenu::setActiveMenuItem('wcf.header.menu.help');
		
		if (!count($_POST) && !empty($this->query)) {
			$this->submit();
		}
		
		parent::show();
	}
}
?>
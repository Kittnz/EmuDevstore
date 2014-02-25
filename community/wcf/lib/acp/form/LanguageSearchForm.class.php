<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/form/ACPForm.class.php');
require_once(WCF_DIR.'lib/system/language/LanguageEditor.class.php');

/**
 * Shows the language search form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.display.language
 * @subpackage	acp.form
 * @category 	Community Framework (commercial)
 */
class LanguageSearchForm extends ACPForm {
	public $templateName = 'languageSearch';
	public $activeMenuItem = 'wcf.acp.menu.link.language.search';
	public $neededPermissions = 'admin.language.canEditLanguage';
	
	public $languageID = 0;
	public $useRegex = 0;
	public $caseSensitive = 0;
	public $replace = 0;
	public $searchVariableName = 0;
	public $replaceBy = '';
	public $query = '';
	public $languages = array();
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		$this->languageID = $this->useRegex = $this->caseSensitive = $this->replace = $this->searchVariableName = 0;
		if (isset($_POST['languageID'])) $this->languageID = intval($_POST['languageID']);
		if (isset($_POST['useRegex'])) $this->useRegex = intval($_POST['useRegex']);
		if (isset($_POST['caseSensitive'])) $this->caseSensitive = intval($_POST['caseSensitive']);
		if (isset($_POST['replace'])) $this->replace = intval($_POST['replace']);
		if (isset($_POST['searchVariableName'])) $this->searchVariableName = intval($_POST['searchVariableName']);
		if (isset($_POST['replaceBy'])) $this->replaceBy = $_POST['replaceBy'];
		if (isset($_POST['query'])) $this->query = StringUtil::trim($_POST['query']);
	}
	
	/**
	 * @see Form::validate()
	 */
	public function validate() {
		parent::validate();

		// query
		if (empty($this->query)) {
			throw new UserInputException('query');
		}
		
		// test regex
		if ($this->useRegex) {
			try {
				preg_match('/'.$this->query.'/', '');
			}
			catch (SystemException $e) {
				throw new UserInputException('query', 'invalidRegex');
			}
		}
		
		if ($this->searchVariableName) {
			$this->replace = 0;
		}
		
		// get results
		$results = LanguageEditor::search($this->query, ($this->replace ? $this->replaceBy : null), ($this->languageID ? $this->languageID : null), 
				$this->useRegex, $this->caseSensitive, $this->searchVariableName);
		
		if (count($results)) {
			$languageItems = array();
			foreach ($results as $result) {
				if (!isset($languageItems[$result['languageID']])) $languageItems[$result['languageID']] = array();
				$languageItems[$result['languageID']][] = $result;
			}
			
			WCF::getTPL()->assign(array(
				'languageItems' => $languageItems,
				'languages' => WCF::getCache()->get('languages', 'languages')
			));
			WCF::getTPL()->display('languageSearchResult');
			exit;
		}
		else {
			WCF::getTPL()->assign('noMatches', true);
		}
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		$this->languages = Language::getLanguages();
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'languages' => $this->languages,
			'languageID' => $this->languageID,	
			'useRegex' => $this->useRegex,
			'query' => $this->query,
			'caseSensitive' => $this->caseSensitive,
			'replace' => $this->replace,
			'replaceBy' => $this->replaceBy,
			'searchVariableName' => $this->searchVariableName
		));
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		// check master password
		WCFACP::checkMasterPassword();
		
		parent::show();
	}
}
?>
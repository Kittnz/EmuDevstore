<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/form/ACPForm.class.php');
require_once(WCF_DIR.'lib/data/template/TemplateEditor.class.php');

/**
 * Shows the template search form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.display.template
 * @subpackage	acp.form
 * @category 	Community Framework (commercial)
 */
class TemplateSearchForm extends ACPForm {
	public $templateName = 'templateSearch';
	public $activeMenuItem = 'wcf.acp.menu.link.template.search';
	public $neededPermissions = 'admin.template.canSearchTemplate';
		
	public $allTemplates = 1;
	public $templateID = array();
	public $useRegex = 0;
	public $caseSensitive = 0;
	public $replace = 0;
	public $invertSearch = 0;
	public $invertTemplates = 0;
	public $replaceBy = '';
	public $query = '';
	public $templates = array();
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		$this->allTemplates = $this->useRegex = $this->caseSensitive = $this->replace = $this->invertSearch = $this->invertTemplates = 0;
		if (isset($_POST['allTemplates'])) $this->allTemplates = intval($_POST['allTemplates']);
		if (isset($_POST['templateID']) && is_array($_POST['templateID'])) $this->templateID = ArrayUtil::toIntegerArray($_POST['templateID']);
		if (isset($_POST['useRegex'])) $this->useRegex = intval($_POST['useRegex']);
		if (isset($_POST['caseSensitive'])) $this->caseSensitive = intval($_POST['caseSensitive']);
		if (isset($_POST['replace'])) $this->replace = intval($_POST['replace']);
		if (isset($_POST['invertSearch'])) $this->invertSearch = intval($_POST['invertSearch']);
		if (isset($_POST['invertTemplates'])) $this->invertTemplates = intval($_POST['invertTemplates']);
		if (isset($_POST['replaceBy'])) $this->replaceBy = $_POST['replaceBy'];
		if (isset($_POST['query'])) $this->query = $_POST['query'];
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		$this->readTemplates();
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'allTemplates' => $this->allTemplates,
			'templateID' => $this->templateID,	
			'useRegex' => $this->useRegex,
			'templates' => $this->templates,
			'query' => $this->query,
			'caseSensitive' => $this->caseSensitive,
			'replace' => $this->replace,
			'replaceBy' => $this->replaceBy,
			'invertSearch' => $this->invertSearch,
			'invertTemplates' => $this->invertTemplates
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
	
	/**
	 * @see Form::validate()
	 */
	public function validate() {
		parent::validate();

		// templates
		if (!$this->allTemplates) {
			if (empty($this->templateID)) {
				throw new UserInputException('templateID');
			}
		}
		
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
		
		if ($this->invertSearch) {
			$this->replace = 0;
		}
		
		// get results
		$results = TemplateEditor::search($this->query, ($this->replace ? $this->replaceBy : null), ($this->allTemplates ? null : $this->templateID), 
				$this->invertTemplates, $this->useRegex, $this->caseSensitive, $this->invertSearch);
		
		if (count($results)) {
			WCF::getTPL()->assign('templates', $results);
			WCF::getTPL()->display('templateSearchResult');
			exit;
		}
		else {
			WCF::getTPL()->assign('noMatches', true);
		}
	}
	
	/**
	 * Gets a list of templates.
	 */
	protected function readTemplates() {
		// get template ids
		$templateIDs = array();
		$sql = "SELECT		templateName, templateID 
			FROM		wcf".WCF_N."_template template,
					wcf".WCF_N."_package_dependency package_dependency
			WHERE 		template.packageID = package_dependency.dependency
					AND package_dependency.packageID = ".PACKAGE_ID."
			ORDER BY	package_dependency.priority";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$templateIDs[$row['templateName']] = $row['templateID'];
		}
		
		// get template
		if (count($templateIDs)) {
			$sql = "SELECT		templateID, templateName, template.templatePackID,
						pack.templatePackName
				FROM		wcf".WCF_N."_template template
				LEFT JOIN	wcf".WCF_N."_template_pack pack
				ON		(pack.templatePackID = template.templatePackID)
				WHERE		templateID IN (".implode(',', $templateIDs).")
				ORDER BY	template.templatePackID, templateName";
			$result = WCF::getDB()->sendQuery($sql);
			while ($row = WCF::getDB()->fetchArray($result)) {
				if (!$row['templatePackID']) $row['templatePackName'] = WCF::getLanguage()->get('wcf.acp.template.pack.default');
				if (!isset($this->templates[$row['templatePackName']])) $this->templates[$row['templatePackName']] = array();
				$this->templates[$row['templatePackName']][$row['templateID']] = $row['templateName'];
			}
		}
	}
}
?>
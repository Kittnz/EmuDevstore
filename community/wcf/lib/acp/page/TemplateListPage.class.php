<?php
// wcf imports
require_once(WCF_DIR.'lib/page/AbstractPage.class.php');

/**
 * Shows a list of installed templates.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.display.template
 * @subpackage	acp.page
 * @category 	Community Framework (commercial)
 */
class TemplateListPage extends AbstractPage {
	public $templateName = 'templateList';
	public $templatePackID = 0;
	public $deletedTemplates = 0;
	
	public $templatePacks = array();
	public $templates = array();
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['templatePackID'])) $this->templatePackID = intval($_REQUEST['templatePackID']);
		if (isset($_REQUEST['deletedTemplates'])) $this->deletedTemplates = intval($_REQUEST['deletedTemplates']);
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		$this->readTemplatePacks();
		$this->readTemplates();
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'templatePacks' => $this->templatePacks,
			'templatePackID' => $this->templatePackID,
			'templates' => $this->templates,
			'deletedTemplates' => $this->deletedTemplates
		));
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		// enable menu item
		WCFACP::getMenu()->setActiveMenuItem('wcf.acp.menu.link.template.view');
		
		// check permission
		WCF::getUser()->checkPermission(array('admin.template.canEditTemplate', 'admin.template.canDeleteTemplate'));
		
		parent::show();
	}
	
	/**
	 * Gets a list of templates.
	 */
	protected function readTemplates() {
		// get template ids
		$templateIDs = array();
		$sql = "SELECT		templateName, templatePackID, templateID, template.packageID
			FROM		wcf".WCF_N."_template template,
					wcf".WCF_N."_package_dependency package_dependency
			WHERE 		template.packageID = package_dependency.dependency
					AND package_dependency.packageID = ".PACKAGE_ID."
			ORDER BY	package_dependency.priority";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			if (!isset($templateIDs[$row['templateName'].'-'.$row['templatePackID']]) || PACKAGE_ID == $row['packageID']) {
				$templateIDs[$row['templateName'].'-'.$row['templatePackID']] = $row['templateID'];
			}
		}
		
		// get template
		if (count($templateIDs)) { 
			$sql = "SELECT		templateID, templateName
				FROM		wcf".WCF_N."_template
				WHERE		templatePackID = ".$this->templatePackID."
						AND templateID IN (".implode(',', $templateIDs).")
				ORDER BY	templateName";
			$result = WCF::getDB()->sendQuery($sql);
			while ($row = WCF::getDB()->fetchArray($result)) {
				$this->templates[$row['templateID']] = $row['templateName'];
			}
		}
	}
	
	/**
	 * Gets a list of template packs.
	 */
	protected function readTemplatePacks() {
		$sql = "SELECT		pack.templatePackID, pack.templatePackName, COUNT(template.templateID) AS templates
			FROM		wcf".WCF_N."_template_pack pack
			LEFT JOIN	wcf".WCF_N."_template template
			ON		(template.templatePackID = pack.templatePackID)
			GROUP BY	pack.templatePackID
			ORDER BY	pack.templatePackName";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$this->templatePacks[$row['templatePackID']] = $row['templatePackName'] . ' ('.$row['templates'].')';
		}
	}
}
?>
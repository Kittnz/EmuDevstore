<?php
// wcf imports
require_once(WCF_DIR.'lib/action/AbstractAction.class.php');

/**
 * Deletes templates.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.display.template
 * @subpackage	acp.action
 * @category 	Community Framework (commercial)
 */
class TemplateDeleteAction extends AbstractAction {
	public $templateID = array();
	public $templatePackID = 0;
	
	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['templateID']) && is_array($_REQUEST['templateID'])) $this->templateID = ArrayUtil::toIntegerArray($_REQUEST['templateID']);
		if (isset($_REQUEST['templatePackID'])) $this->templatePackID = intval($_REQUEST['templatePackID']);
	}
	
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();
		
		// check permission
		WCF::getUser()->checkPermission('admin.template.canDeleteTemplate');
		
		if (!count($this->templateID)) {
			throw new IllegalLinkException();
		}
		
		// delete templates (files)
		$templateIDs = '';
		require_once(WCF_DIR.'lib/data/template/TemplateEditor.class.php');
		$sql = "SELECT		template.*, pack.templatePackFolderName, package.packageDir
			FROM		wcf".WCF_N."_template template
			LEFT JOIN	wcf".WCF_N."_template_pack pack
			ON		(pack.templatePackID = template.templatePackID)
			LEFT JOIN	wcf".WCF_N."_package package
			ON		(package.packageID = template.packageID)
			WHERE		template.templateID IN (".implode(',', $this->templateID).")
					AND template.templatePackID > 0";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			if (!empty($templateIDs)) $templateIDs .= ',';
			$templateIDs .= $row['templateID'];
			
			$template = new TemplateEditor(null, $row);
			if ($template->templateID) {
				$template->deleteFile();
			}
		}
		
		// delete database entries
		if (!empty($templateIDs)) {
			TemplateEditor::deleteAll($templateIDs);
		}
		
		// reset cache
		WCF::getCache()->clear(WCF_DIR . 'cache', 'cache.templates-*.php');
		$this->executed();
		
		// forward to list page
		HeaderUtil::redirect('index.php?page=TemplateList&deletedTemplates='.count($this->templateID).'&templatePackID='.$this->templatePackID.'&packageID='.PACKAGE_ID.SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
}
?>
<?php
// wcf imports
require_once(WCF_DIR.'lib/action/AbstractAction.class.php');

/**
 * Deletes a template pack.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.display.template
 * @subpackage	acp.action
 * @category 	Community Framework (commercial)
 */
class TemplatePackDeleteAction extends AbstractAction {
	public $templatePackID = 0;
	
	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['templatePackID'])) $this->templatePackID = intval($_REQUEST['templatePackID']);
	}
	
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();
		
		// check permission
		WCF::getUser()->checkPermission('admin.template.canDeleteTemplatePack');
		
		// delete template pack
		require_once(WCF_DIR.'lib/data/template/TemplatePackEditor.class.php');
		$templatePack = new TemplatePackEditor($this->templatePackID);	
		if (!$templatePack->templatePackID) {
			throw new IllegalLinkException();
		}
		$templatePack->delete();
		WCF::getCache()->clear(WCF_DIR . 'cache', 'cache.templatePacks.php');
		$this->executed();
		
		// forward to list page
		HeaderUtil::redirect('index.php?page=TemplatePackList&deletedTemplatePackID='.$this->templatePackID.'&packageID='.PACKAGE_ID.SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
}
?>
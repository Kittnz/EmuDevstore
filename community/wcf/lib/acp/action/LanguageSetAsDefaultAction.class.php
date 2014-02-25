<?php
// wcf imports
require_once(WCF_DIR.'lib/action/AbstractAction.class.php');

/**
 * Sets a language as default.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.display.language
 * @subpackage	acp.action
 * @category 	Community Framework (commercial)
 */
class LanguageSetAsDefaultAction extends AbstractAction {
	public $languageID = 0;
	
	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['languageID'])) $this->languageID = intval($_REQUEST['languageID']);
	}
	
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();
		
		// check permission
		WCF::getUser()->checkPermission('admin.language.canEditLanguage');
		
		// delete language
		require_once(WCF_DIR.'lib/system/language/LanguageEditor.class.php');
		$language = new LanguageEditor($this->languageID);	
		if (!$language->getLanguageID() || $language->isDefault()) {
			throw new IllegalLinkException();
		}
		$language->makeDefault();
		$this->executed();
		
		// forward to list page
		HeaderUtil::redirect('index.php?page=LanguageList&packageID='.PACKAGE_ID.SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
}
?>
<?php
require_once(WCF_DIR.'lib/system/WCFACP.class.php');

/**
 * This class extends the main WCFACP class by forum specific functions.
 *
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	system
 * @category 	Burning Board
 */
class WBBACP extends WCFACP {
	/**
	 * @see WCF::getOptionsFilename()
	 */
	protected function getOptionsFilename() {
		return WBB_DIR.'options.inc.php';
	}
	
	/**
	 * Initialises the template engine.
	 */
	protected function initTPL() {
		global $packageDirs;
		
		self::$tplObj = new ACPTemplate(self::getLanguage()->getLanguageID(), ArrayUtil::appendSuffix($packageDirs, 'acp/templates/'));
		$this->assignDefaultTemplateVariables();
	}
	
	/**
	 * Does the user authentication.
	 */
	protected function initAuth() {
		parent::initAuth();
		
		// user ban
		if (self::getUser()->banned) {
			throw new PermissionDeniedException();
		}
	}
	
	/**
	 * @see WCF::assignDefaultTemplateVariables()
	 */
	protected function assignDefaultTemplateVariables() {
		parent::assignDefaultTemplateVariables();
		
		self::getTPL()->assign(array(
			// add jump to board link 			
			'additionalHeaderButtons' => '<li><a href="'.RELATIVE_WBB_DIR.'index.php?page=Index"><img src="'.RELATIVE_WBB_DIR.'icon/boardS.png" alt="" /> <span>'.WCF::getLanguage()->get('wbb.acp.jumpToBoard').'</span></a></li>',
			// individual page title
			'pageTitle' => WCF::getLanguage()->get(StringUtil::encodeHTML(PAGE_TITLE)) . ' - ' . StringUtil::encodeHTML(PACKAGE_NAME . ' ' . PACKAGE_VERSION)
		));
	}
	
	/**
	 * @see WCF::loadDefaultCacheResources()
	 */
	protected function loadDefaultCacheResources() {
		parent::loadDefaultCacheResources();
		$this->loadDefaultWBBCacheResources();
	}
	
	/**
	 * Loads default cache resources of burning board acp.
	 * Can be called statically from other applications or plugins.
	 */
	public static function loadDefaultWBBCacheResources() {
		WCF::getCache()->addResource('board', WBB_DIR.'cache/cache.board.php', WBB_DIR.'lib/system/cache/CacheBuilderBoard.class.php');
		WCF::getCache()->addResource('bbcodes', WCF_DIR.'cache/cache.bbcodes.php', WCF_DIR.'lib/system/cache/CacheBuilderBBCodes.class.php');
	}
}
?>
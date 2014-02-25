<?php
require_once(WCF_DIR.'lib/page/AbstractPage.class.php');

/**
 * Shows the aboutme page.
 * 
 * @author	Patrick Petersen
 * @copyright	2012 WBB-Center.de
 * @license	WBB-Center License <http://www.wbb-center.de>
 * @package	de.s4w-host.wcf.aboutme
 * @subpackage	page
 * @category 	Community Framework
 */

class AboutmePage extends AbstractPage {
public $templateName = 'aboutme';

	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (!MODULE_ABOUT_LISTING) {
			throw new IllegalLinkException();
		}
	}

	/**
	 * @see Page::show()
	 */
	public function show() {
		// Set active header menu item
		require_once(WCF_DIR.'lib/page/util/menu/PageMenu.class.php');
		require_once(WCF_DIR.'lib/page/util/menu/HeaderMenu.class.php');
		PageMenu::setActiveMenuItem('wcf.header.menu.aboutmepage');
		
		parent::show();
	}
}
?>
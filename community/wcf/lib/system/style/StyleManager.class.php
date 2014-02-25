<?php
// wcf imports
require_once(WCF_DIR.'lib/data/style/Style.class.php');

/**
 * Manages the active user styles.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.system.style
 * @subpackage	system.style
 * @category 	Community Framework
 */
class StyleManager {
	/**
	 * active style object
	 * 
	 * @var	Style
	 */
	protected static $style = null;
	
	/**
	 * style cache
	 * 
	 * @var	array
	 */
	protected static $cache = null;
	
	/**
	 * Changes the active style.
	 * 
	 * @param	integer		$styleID
	 */
	public static final function changeStyle($styleID, $ignorePermissions = false) {
		// load cache
		self::getCache();
		
		// check permission
		if (!$ignorePermissions) {
			if (isset(self::$cache['styles'][$styleID])) {
				if ((self::$cache['styles'][$styleID]->disabled || !empty(self::$cache['packages'][PACKAGE_ID]['disabled'][$styleID])) && !WCF::getUser()->getPermission('admin.style.canUseDisabledStyle')) {
					$styleID = 0;
				}
			}
		}
		
		// fallback to default style
		if (!isset(self::$cache['styles'][$styleID])) {
			// get package default style
			if (!empty(self::$cache['packages'][PACKAGE_ID]['default'])) {
				$styleID = self::$cache['packages'][PACKAGE_ID]['default'];
			}
			// get global default style
			else {
				$styleID = self::$cache['default'];
			}
			
			if (!isset(self::$cache['styles'][$styleID])) {
				throw new SystemException('no default style defined', 100000);
			}
		}

		// init style
		self::$style = self::$cache['styles'][$styleID]->getActiveStyle();
		
		// set template pack id
		if (WCF::getTPL()) {
			WCF::getTPL()->setTemplatePackID(self::$style->templatePackID);
		}
	}
	
	/**
	 * Returns the active style.
	 * 
	 * @return	ActiveStyle
	 */
	public static function getStyle() {
		return self::$style;
	}
	
	/**
	 * Returns a list of all for the current user available styles.
	 * 
	 * @return	array<Style>
	 */
	public static function getAvailableStyles() {
		self::getCache();
		$styles = array();
		
		foreach (self::$cache['styles'] as $styleID => $style) {
			if ((!$style->disabled && empty(self::$cache['packages'][PACKAGE_ID]['disabled'][$styleID])) || WCF::getUser()->getPermission('admin.style.canUseDisabledStyle')) {
				$styles[$styleID] = $style;
			}
		}
		
		return $styles;
	}
	
	/**
	 * Loads the cached styles.
	 */
	protected static function getCache() {
		if (self::$cache === null) {
			WCF::getCache()->addResource('style', WCF_DIR.'cache/cache.style.php', WCF_DIR.'lib/system/cache/CacheBuilderStyle.class.php');
			self::$cache = WCF::getCache()->get('style');
		}
	}
}
?>
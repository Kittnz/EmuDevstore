<?php
// wcf imports
require_once(WCF_DIR.'lib/data/style/Style.class.php');

/**
 * Represents the active user style.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.system.style
 * @subpackage	system.style
 * @category 	Community Framework
 */
class ActiveStyle extends Style {
	/**
	 * icon cache
	 * 
	 * @var	array
	 */
	protected $iconCache = array();
	
	/**
	 * @see DatabaseObject::handleData()
	 */
	protected function handleData($data) {
		parent::handleData($data);
		
		// calculate page logo path
		if (!empty($this->data['variables']['page.logo.image']) && !FileUtil::isURL($this->data['variables']['page.logo.image']) && StringUtil::substring($this->data['variables']['page.logo.image'], 0, 1) !== '/') {
			$this->data['variables']['page.logo.image'] = RELATIVE_WCF_DIR . $this->data['variables']['page.logo.image'];
		}
		
		// load icon cache
		WCF::getCache()->addResource('icon-'.PACKAGE_ID.'-'.$this->styleID, WCF_DIR.'cache/cache.icon-'.PACKAGE_ID.'-'.$this->styleID.'.php', WCF_DIR.'lib/system/cache/CacheBuilderIcon.class.php');
		$this->iconCache = WCF::getCache()->get('icon-'.PACKAGE_ID.'-'.$this->styleID);
	}
	
	/**
	 * Returns the value of a style variable.
	 * 
	 * @param	string		$name
	 * @return	string		value
	 */
	public function getVariable($name) {
		if (isset($this->data['variables'][$name])) return $this->data['variables'][$name];
		return '';
	}
	
	/**
	 * Returns the path of an icon.
	 * 
	 * @param	string		$iconName
	 * @return	string
	 */
	public function getIconPath($iconName) {
		if (isset($this->iconCache[$iconName])) return $this->iconCache[$iconName];
		return RELATIVE_WCF_DIR.'icon/'.$iconName;
	}
}
?>
<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/package/plugin/AbstractXMLPackageInstallationPlugin.class.php');

/**
 * This PIP installs, updates or deletes user profile menu items.
 * 
 * @author 	Benjamin Kunz
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.page.user.profile
 * @subpackage	acp.package.plugin
 * @category 	Community Framework
 */
class UserProfileMenuPackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin {
	public $tagName = 'userprofilemenu';
	public $tableName = 'user_profile_menu_item';
	
	/** 
	 * @see PackageInstallationPlugin::install()
	 */
	public function install() {
		parent::install();
		
		if (!$xml = $this->getXML()) {
			return;
		}
		
		// Create an array with the data blocks (import or delete) from the xml file.
		$acpMenuXML = $xml->getElementTree('data');
		
		// Loop through the array and install or uninstall acp-menu items.
		foreach ($acpMenuXML['children'] as $key => $block) {
			if (count($block['children'])) {
				// Handle the import instructions
				if ($block['name'] == 'import') {
					// Loop through acp-menu items and create or update them.
					foreach ($block['children'] as $acpMenuItem) {
						// Extract item properties.
						foreach ($acpMenuItem['children'] as $child) {
							if (!isset($child['cdata'])) continue;
							$acpMenuItem[$child['name']] = $child['cdata'];
						}
					
						// check required attributes
						if (!isset($acpMenuItem['attrs']['name'])) {
							throw new SystemException("Required 'name' attribute for user profile menu item is missing", 13023);
						}
						
						// default values
						$menuItemLink = $parentMenuItem = $menuItemIcon = $permissions = $options = '';
						$showOrder = null;
						
						// get values
						$menuItem = $acpMenuItem['attrs']['name'];
						if (isset($acpMenuItem['link'])) $menuItemLink = $acpMenuItem['link'];
						if (isset($acpMenuItem['parent'])) $parentMenuItem = $acpMenuItem['parent'];
						if (isset($acpMenuItem['icon'])) $menuItemIcon = $acpMenuItem['icon'];
						if (isset($acpMenuItem['showorder'])) $showOrder = intval($acpMenuItem['showorder']);
						$showOrder = $this->getShowOrder($showOrder, $parentMenuItem, 'parentMenuItem');
						if (isset($acpMenuItem['permissions'])) $permissions = $acpMenuItem['permissions'];
						if (isset($acpMenuItem['options'])) $options = $acpMenuItem['options'];
						
						// If a parent link was set and this parent is not in database 
						// or it is a link from a package from other package environment: don't install further.
						if (!empty($parentMenuItem)) {
							$sql = "SELECT	COUNT(*) AS count
								FROM 	wcf".WCF_N."_user_profile_menu_item
								WHERE	menuItem = '".escapeString($parentMenuItem)."'";
							$menuItemCount = WCF::getDB()->getFirstRow($sql);
							if ($menuItemCount['count'] == 0) {
								throw new SystemException("For the menu item '".$menuItem."' no parent item '".$parentMenuItem."' exists.", 13011);
							}
						}
						
						// Insert or update items. 
						// Update through the mysql "ON DUPLICATE KEY"-syntax. 
						$sql = "INSERT INTO			wcf".WCF_N."_user_profile_menu_item
											(packageID, menuItem, parentMenuItem, menuItemLink, menuItemIcon, showOrder, permissions, options)
							VALUES				(".$this->installation->getPackageID().",
											'".escapeString($menuItem)."',
											'".escapeString($parentMenuItem)."',
											'".escapeString($menuItemLink)."',
											'".escapeString($menuItemIcon)."',
											".$showOrder.",
											'".escapeString($permissions)."',
											'".escapeString($options)."')
							ON DUPLICATE KEY UPDATE 	parentMenuItem = VALUES(parentMenuItem),
											menuItemLink = VALUES(menuItemLink),
											menuItemIcon = VALUES(menuItemIcon),
											showOrder = VALUES(showOrder),
											permissions = VALUES(permissions),
											options = VALUES(options)";
						WCF::getDB()->sendQuery($sql);
					}
				}
				// Handle the delete instructions.
				else if ($block['name'] == 'delete') {
					if ($this->installation->getAction() == 'update') {
						// Loop through acp-menu items and delete them.
						$itemNames = '';
						foreach ($block['children'] as $acpMenuItem) {
							// check required attributes
							if (!isset($acpMenuItem['attrs']['name'])) {
								throw new SystemException("Required 'name' attribute for 'userprofilemenuitem'-tag is missing.", 13023);
							}
							// Create a string with all item names which should be deleted (comma separated).
							if (!empty($itemNames)) $itemNames .= ',';
							$itemNames .= "'".escapeString($acpMenuItem['attrs']['name'])."'";
						}
						// Delete items.
						if (!empty($itemNames)) {
							$sql = "DELETE FROM	wcf".WCF_N."_user_profile_menu_item
								WHERE		menuItem IN (".$itemNames.")
										AND packageID = ".$this->installation->getPackageID();
							WCF::getDB()->sendQuery($sql);
						}
					}
				}
			}
		}
	}
}
?>
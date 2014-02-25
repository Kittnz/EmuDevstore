<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/page/UserSuggestPage.class.php');

/**
 * Outputs an XML document with a list of permissions objects (user or user groups).
 *
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	acp.page
 * @category 	Burning Board
 */
class BoardPermissionsObjectsSuggestPage extends UserSuggestPage {
	/**
	 * @see Page::show()
	 */
	public function show() {
		AbstractPage::show();
				
		header('Content-type: text/xml');
		echo "<?xml version=\"1.0\" encoding=\"".CHARSET."\"?>\n<suggestions>\n";
		
		if (!empty($this->query)) {
			// get suggestions
			$sql = "(SELECT		username AS name, 'user' AS type
				FROM		wcf".WCF_N."_user
				WHERE		username LIKE '".escapeString($this->query)."%')
				UNION ALL
				(SELECT		groupName AS name, 'group' AS type
				FROM		wcf".WCF_N."_group
				WHERE		groupName LIKE '".escapeString($this->query)."%')
				ORDER BY	name";
			$result = WCF::getDB()->sendQuery($sql, 10);
			while ($row = WCF::getDB()->fetchArray($result)) {
				echo "<".$row['type']."><![CDATA[".StringUtil::escapeCDATA($row['name'])."]]></".$row['type'].">\n";
			}
		}
		echo '</suggestions>';
		exit;
	}
}
?>
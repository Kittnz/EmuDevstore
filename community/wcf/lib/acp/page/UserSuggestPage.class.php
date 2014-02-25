<?php
// wcf imports
require_once(WCF_DIR.'lib/page/AbstractPage.class.php');

/**
 * Outputs an XML document with a list of suggested users.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.page
 * @category 	Community Framework
 */
class UserSuggestPage extends AbstractPage {
	const DO_NOT_LOG = true;
	public $query = '';
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_POST['query'])) {
			$this->query = StringUtil::trim($_POST['query']);
			if (CHARSET != 'UTF-8') $this->query = StringUtil::convertEncoding('UTF-8', CHARSET, $this->query);
		}
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		parent::show();
				
		header('Content-type: text/xml');
		echo "<?xml version=\"1.0\" encoding=\"".CHARSET."\"?>\n<suggestions>\n";
		
		if (!empty($this->query)) {
			// get users
			$users = array();
			$sql = "SELECT		username
				FROM		wcf".WCF_N."_user
				WHERE		username LIKE '".escapeString($this->query)."%'
				ORDER BY	username";
			$result = WCF::getDB()->sendQuery($sql, 10);
			while ($row = WCF::getDB()->fetchArray($result)) {
				echo "<user><![CDATA[".StringUtil::escapeCDATA($row['username'])."]]></user>\n";
			}
		}
		echo '</suggestions>';
		exit;
	}
}
?>
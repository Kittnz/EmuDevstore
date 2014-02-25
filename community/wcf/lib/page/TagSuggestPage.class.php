<?php
require_once(WCF_DIR.'lib/page/AbstractPage.class.php');

/**
 * Outputs an XML document with a list of suggested tags.
 *
 * @author 	Arian Glander
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.tagging
 * @subpackage	page
 * @category 	Community Framework
 */
class TagSuggestPage extends AbstractPage {
	public $query = '';
	public $languageID = 0;
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_POST['query'])) {
			$this->query = StringUtil::trim($_POST['query']);
			if (CHARSET != 'UTF-8') $this->query = StringUtil::convertEncoding('UTF-8', CHARSET, $this->query);
		}
		if (count(Language::getAvailableContentLanguages(PACKAGE_ID)) > 0) {
			$this->languageID = WCF::getLanguage()->getLanguageID();
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
			$sql = "SELECT		DISTINCT name
				FROM		wcf".WCF_N."_tag
				WHERE		".($this->languageID ? "languageID = ".$this->languageID." AND" : '')."
						name LIKE '".escapeString($this->query)."%'
				ORDER BY	name";
			$result = WCF::getDB()->sendQuery($sql, 10);
			while ($row = WCF::getDB()->fetchArray($result)) {
				echo "<tag><![CDATA[".StringUtil::escapeCDATA($row['name'])."]]></tag>\n";
			}
		}
		echo '</suggestions>';
		exit;
	}
}
?>
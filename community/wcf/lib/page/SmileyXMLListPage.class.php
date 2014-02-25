<?php
// wcf imports
require_once(WCF_DIR.'lib/page/AbstractPage.class.php');
require_once(WCF_DIR.'lib/data/message/smiley/Smiley.class.php');

/**
 * Outputs an XML document with a list of smileys.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.message.bbcode
 * @subpackage	page
 * @category 	Community Framework
 */
class SmileyXMLListPage extends AbstractPage {
	/**
	 * smiley category id
	 *
	 * @var integer
	 */
	public $smileyCategoryID = 0;
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_POST['smileyCategoryID'])) $this->smileyCategoryID = intval($_POST['smileyCategoryID']);
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		parent::show();

		// get smileys
		$smileys = WCF::getCache()->get('smileys', 'smileys');
		if (isset($smileys[$this->smileyCategoryID])) {
			header('Content-type: text/xml');
			echo "<?xml version=\"1.0\" encoding=\"".CHARSET."\"?>\n<smileys>\n";
			
			foreach ($smileys[$this->smileyCategoryID] as $smiley) {
				echo "\t<smiley>\n";
				echo "\t\t<path><![CDATA[".StringUtil::escapeCDATA($smiley->smileyPath)."]]></path>\n";
				echo "\t\t<title><![CDATA[".StringUtil::escapeCDATA(WCF::getLanguage()->get($smiley->smileyTitle))."]]></title>\n";
				echo "\t\t<code><![CDATA[".StringUtil::escapeCDATA($smiley->smileyCode)."]]></code>\n";
				echo "\t</smiley>\n";
			}
			
			echo '</smileys>';
		}
		
		exit;
	}
}
?>
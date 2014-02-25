<?php
// wcf imports
require_once(WCF_DIR.'lib/data/cronjobs/Cronjob.class.php');

/**
 * Refreshes list of search robots.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.cronjobs
 * @subpackage	system.cronjob
 * @category 	Community Framework
 */
class RefreshSearchRobotsCronjob implements Cronjob {
	/**
	 * @see Cronjob::execute()
	 */
	public function execute($data) {
		$filename = FileUtil::downloadFileFromHttp('http://www.woltlab.com/spiderlist/spiderlist.xml', 'spiders');
		$xml = new XML($filename);
		$spiders = $xml->getElementTree('spiderlist');

		if (count($spiders['children'])) {
			// delete old entries
			$sql = "TRUNCATE TABLE wcf".WCF_N."_spider";
			WCF::getDB()->sendQuery($sql);
		
			$inserts = '';
			foreach ($spiders['children'] as $spider) {
				$identifier = $spider['attrs']['ident'];

				// get attributes
				foreach ($spider['children'] as $values) {
					$spider[$values['name']] = $values['cdata'];
				}

				$name = $spider['name'];
				$info = '';
				if (isset($spider['info'])) $info = $spider['info'];

				if (!empty($inserts)) $inserts .= ',';
				$inserts .= "('".escapeString(StringUtil::toLowerCase($identifier))."', '".escapeString($name)."', '".escapeString($info)."')";
			}
			
			if (!empty($inserts)) {
				$sql = "INSERT IGNORE INTO	wcf".WCF_N."_spider
								(spiderIdentifier, spiderName, spiderURL)
					VALUES			".$inserts;
				WCF::getDB()->sendQuery($sql);
			}
			
			// clear spider cache
			WCF::getCache()->clear(WCF_DIR.'cache', 'cache.spiders.php');
		}
		
		// delete tmp file
		@unlink($filename);
	}
}
?>
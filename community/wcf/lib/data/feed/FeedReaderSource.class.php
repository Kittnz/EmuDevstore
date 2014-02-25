<?php
// wcf imports
require_once(WCF_DIR.'lib/data/DatabaseObject.class.php');
require_once(WCF_DIR.'lib/system/language/Language.class.php');

/**
 * Reads rss news feeds.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.feed.reader
 * @subpackage	data.feed
 * @category 	Community Framework
 */
class FeedReaderSource extends DatabaseObject {
	/**
	 * Updates the entries of this news feed.
	 */
	public function update() {
		// get items
		$defaultLanguage = new Language(Language::getDefaultLanguageID());
		WCF::getLanguage()->setLocale();
		
		try {
			$sourceURL = sprintf($this->sourceURL, Language::fixLanguageCode($defaultLanguage->getLanguageCode()));
			$items = self::parseFeed($sourceURL);
		}
		// fallback to englisch feed
		catch(SystemException $e) {
			if ($defaultLanguage->getLanguageCode() != 'en') {
				$sourceURL = sprintf($this->sourceURL, 'en');
				$items = self::parseFeed($sourceURL);
			}
		}
		
		// save items
		foreach ($items as $newsItem) {
			// save item
			$sql = "REPLACE INTO	wcf".WCF_N."_feed_entry
						(sourceID, title, author, link, guid, pubDate, description)
				VALUES		(".$this->sourceID.", '".escapeString($newsItem['title'])."', '".escapeString($newsItem['author'])."', '".escapeString($newsItem['link'])."', '".escapeString($newsItem['guid'])."', ".$newsItem['pubDate'].", '".escapeString($newsItem['description'])."')";
			WCF::getDB()->sendQuery($sql);
		}
		
		// update timestamp
		$sql = "UPDATE	wcf".WCF_N."_feed_source
			SET	lastUpdate = ".TIME_NOW."
			WHERE	sourceID = ".$this->sourceID;
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * Returns a list of feed entries.
	 * 
	 * @return	array
	 */
	public static function getEntries($limit = 10, $orderBy = 'pubDate DESC', $source = '', $packageID = PACKAGE_ID) {
		$entries = array();
		$sql = "SELECT		*
			FROM		wcf".WCF_N."_feed_entry
			WHERE		sourceID IN (
						SELECT	sourceID
						FROM	wcf".WCF_N."_feed_source
						WHERE	packageID IN (
								SELECT	dependency
								FROM	wcf".WCF_N."_package_dependency
								WHERE	packageID = ".$packageID."
							)
							".(!empty($source) ? "AND sourceName = '".escapeString($source)."'" : '')."
					)
			ORDER BY	".$orderBy;
		$result = WCF::getDB()->sendQuery($sql, $limit);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$entries[] = $row;
		}
		
		return $entries;
	}
	
	/**
	 * Parse a rss feed.
	 * 
	 * @param	string		$sourceURL
	 * @return	array
	 */
	public static function parseFeed($sourceURL) {
		$newsItems = array();
		$filename = FileUtil::downloadFileFromHttp($sourceURL, 'feed');
		
		// open & parse file
		$xml = new XML($filename);
		$data = $xml->getElementTree('channel');
		$items = $data['children'][0]['children'];
		
		foreach ($items as $item) {
			if ($item['name'] != 'item') continue;
			
			$newsItem = array('title' => '', 'author' => '', 'link' => '', 'guid' => '', 'pubDate' => '', 'description' => '');
			foreach ($item['children'] as $child) {
				if (!isset($child['cdata'])) continue;
				$newsItem[$child['name']] = $child['cdata'];
			}
			
			// convert encodings
			if (CHARSET != 'UTF-8') {
				$newsItem['title'] = StringUtil::convertEncoding('UTF-8', CHARSET, $newsItem['title']);
				$newsItem['author'] = StringUtil::convertEncoding('UTF-8', CHARSET, $newsItem['author']);
				$newsItem['link'] = StringUtil::convertEncoding('UTF-8', CHARSET, $newsItem['link']);
				$newsItem['guid'] = StringUtil::convertEncoding('UTF-8', CHARSET, $newsItem['guid']);
				$newsItem['description'] = StringUtil::convertEncoding('UTF-8', CHARSET, $newsItem['description']);
			}
			
			@$newsItem['pubDate'] = intval(strtotime($newsItem['pubDate']));
			$newsItems[] = $newsItem;
		}
		
		// delete tmp file
		@unlink($filename);
		
		return $newsItems;
	}
}
?>
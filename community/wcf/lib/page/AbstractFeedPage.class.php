<?php
// wcf imports
require_once(WCF_DIR.'lib/page/AbstractPage.class.php');

/**
 * Prints a rss or an atom feed.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.message
 * @subpackage	page
 * @category 	Community Framework
 */
class AbstractFeedPage extends AbstractPage {
	public $format = 'rss2';
	public $limit = 30;
	public $hours = 0;
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['format'])) $this->format = StringUtil::toLowerCase($_REQUEST['format']);
		if (isset($_REQUEST['hours'])) $this->hours = intval($_REQUEST['hours']);
		if (isset($_REQUEST['limit'])) {
			$this->limit = intval($_REQUEST['limit']);
			if ($this->limit < 1) $this->limit = 1;
		}
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		parent::show();
				
		// send header
		@header('Content-Type: '.($this->type == 'format' ? 'application/atom+xml' : 'application/rss+xml').'; charset='.CHARSET);
	}
}
?>
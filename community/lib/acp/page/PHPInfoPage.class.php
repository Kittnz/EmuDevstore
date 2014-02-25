<?php
// wcf imports
require_once(WCF_DIR.'lib/page/AbstractPage.class.php');

/**
 * Shows phpinfo() output.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	acp.page
 * @category 	Burning Board
 */
class PHPInfoPage extends AbstractPage {
	// system
	public $templateName = 'phpInfo';
	
	/**
	 * counter
	 * 
	 * @var	integer
	 */
	public $counter = 0;
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		// get phpinfo() output
		ob_start();
		phpinfo();
		$info = ob_get_contents();
		ob_end_clean();

		// parse output
		$info = preg_replace('%^.*<body>(.*)</body>.*$%s', '$1', $info);
		
		// style fixes
		// remove first table
		$info = preg_replace('%<table.*?</table><br />%s', '', $info, 1);
		
		// fix tables
		$info = preg_replace('%<h2>(.*?)</h2>\s*<table border="0" cellpadding="3" width="600">%', '<div class="border titleBarPanel"><div class="containerHead"><h3>\\1</h3></div></div><div class="border borderMarginRemove"><table class="tableList">', $info); 
		$info = preg_replace('%<table border="0" cellpadding="3" width="600">%', '<div class="border titleBarPanel"><table class="tableList">', $info); 
		$info = str_replace('</table>', '</table></div>', $info); 
		$info = str_replace('<tr class="h">', '<thead><tr class="tableHead">', $info); 
		$info = str_replace('</th></tr>', '</th></tr></thead>', $info); 
		$info = preg_replace('%</td></tr>%', '</th></tr></thead>', $info, 1); 
		$info = str_replace('<tr class="tableHead"><td>', '<tr class="tableHead"><th>', $info); 
		$info = preg_replace('%<th(\s+.*?)?>%', '<th\\1><div><span class="emptyHead">', $info); 
		$info = str_replace('</th>', '</span></div></th>', $info); 
		
		// fix row colors
		$info = preg_replace_callback('%<tr>%', array($this, 'insertRowColorsCallback'), $info); 
		
		// h1, h2 fixes
		$info = str_replace('</h2>', '</h3>', $info); 
		$info = str_replace('<h2>', '<h3>', $info); 
		$info = str_replace('</h1>', '</h2>', $info); 
		$info = str_replace('<h1', '<h2', $info); 
		
		WCF::getTPL()->assign(array(
			'phpInfo' => $info
		));
	}
	
	private function insertRowColorsCallback($matches) {
		return '<tr class="container-'.($this->counter++ % 2 == 0 ? 1 : 2).'">';
	}
}
?>
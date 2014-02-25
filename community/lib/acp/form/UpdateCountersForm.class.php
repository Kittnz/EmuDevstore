<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/form/ACPForm.class.php');

/**
 * Shows the update counters form.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	acp.form
 * @category 	Burning Board
 */
class UpdateCountersForm extends ACPForm {
	// system
	public $templateName = 'updateCounters';
	public $activeMenuItem = 'wbb.acp.menu.link.maintenance.updateCounters';
	public $neededPermissions = 'admin.maintenance.canUpdateCounters';
	
	/**
	 * list of available counters
	 *
	 * @var array
	 */
	public $counters = array('posts' => 1000, 'threads' => 500, 'boards' => 50, 'users' => 100, /*'similarThreads' => 50,*/ 'privateMessages' => 500, 'messagePreviews' => 100, 'thumbnails' => 50, 'installationDate' => 1);
	
	/**
	 * selected counter
	 *
	 * @var string
	 */
	public $counter = '';
	
	/**
	 * selected limit
	 *
	 * @var integer
	 */
	public $limit = 500;
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['counter'])) $this->counter = $_POST['counter'];
		if (isset($_POST['limit'])) $this->limit = intval($_POST['limit']);
	}
	
	/**
	 * @see Form::validate()
	 */
	public function validate() {
		parent::validate();

		// counter
		if (!isset($this->counters[$this->counter])) {
			throw new UserInputException('counter');
		}
		
		// limit
		if ($this->limit < 1) {
			throw new UserInputException('limit');
		}
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		parent::save();
		
		WCF::getTPL()->assign(array(
			'pageTitle' => WCF::getLanguage()->get('wbb.acp.updateCounters.counter.'.$this->counter),
			'url' => 'index.php?action=Update'.ucfirst($this->counter).'&limit='.$this->limit.'&packageID='.PACKAGE_ID.SID_ARG_2ND_NOT_ENCODED
		));
		WCF::getTPL()->display('worker');
		exit;
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'counter' => $this->counter,
			'counters' => $this->counters,
			'limit' => $this->limit
		));
	}
}
?>
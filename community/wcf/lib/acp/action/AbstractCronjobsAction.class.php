<?php
// wcf imports
require_once(WCF_DIR.'lib/action/AbstractAction.class.php');
require_once(WCF_DIR.'lib/data/cronjobs/CronjobEditor.class.php');

/**
 * Abstract cronjob action.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.cronjobs
 * @subpackage	acp.action
 * @category 	Community Framework
 */
abstract class AbstractCronjobsAction extends AbstractAction {
	/**
	 * cronjob id
	 *
	 * @var integer
	 */
	public $cronjobID = 0;
	
	/**
	 * cronjob editor object
	 *
	 * @var CronjobEditor
	 */
	public $cronjob = null;
	
	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['cronjobID'])) $this->cronjobID = intval($_REQUEST['cronjobID']);
		$this->cronjob = new CronjobEditor($this->cronjobID);
		if (!$this->cronjob->cronjobID) {
			throw new IllegalLinkException();
		}
	}
}
?>
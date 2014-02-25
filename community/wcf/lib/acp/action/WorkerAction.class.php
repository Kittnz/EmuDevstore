<?php
// wcf imports
require_once(WCF_DIR.'lib/action/AbstractAction.class.php');

/**
 * Provides default implementations for worker actions.
 * 
 * @author	Benjamin Kunz
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.action
 * @category 	Community Framework
 */
abstract class WorkerAction extends AbstractAction {
	const DO_NOT_LOG = true;
	public $loop = 0;
	public $limit = 0;
	public $action = '';
	
	/**
	 * Creates a new WorkerAction object.
	 */
	public function __construct() {
		try {
			parent::__construct();
		}
		catch (SystemException $e) {
			WCF::getTPL()->assign('e', $e);
			WCF::getTPL()->display('workerException');
		}
	}
	
	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		// parameters
		if (isset($_REQUEST['limit'])) $this->limit = intval($_REQUEST['limit']);
		if (isset($_REQUEST['loop'])) $this->loop = intval($_REQUEST['loop']);
	}
	
	/**
	 * Calculates the progress bar.
	 * 
	 * @param	integer		$offset
	 * @param	integer		$count
	 */
	protected function calcProgress($offset = 1, $count = 1) {
		// calculate progress
		$progress = $offset / $count * 100;
		
		// format
		$progress = round($progress, 0);
		WCF::getTPL()->assign('progress', $progress);
	}
	
	/**
	 * Forwards to next loop.
	 * 
	 * @param	string		$title
	 * @param	string		$url
	 */
	protected function nextLoop($title = 'wcf.acp.worker.progress.working', $url = null) {
		if ($url === null) $url = 'index.php?action='.$this->action.'&limit='.$this->limit.'&loop='.($this->loop + 1).'&packageID='.PACKAGE_ID.SID_ARG_2ND_NOT_ENCODED;
		WCF::getTPL()->assign(array(
			'stepTitle' => WCF::getLanguage()->get($title),
			'url' => $url
		));
		WCF::getTPL()->display('workerNext');
		exit;
	}
	
	/**
	 * Shows the worker finish page.
	 * 
	 * @param	string		$title
	 * @param	string		$url
	 */
	protected function finish($title = 'wcf.acp.worker.progress.finish', $url = null) {
		if ($url === null) $url = 'index.php?packageID='.PACKAGE_ID.SID_ARG_2ND_NOT_ENCODED;
		// show finish
		WCF::getTPL()->assign(array(
			'stepTitle' => WCF::getLanguage()->get($title),
			'url' => $url
		));
		WCF::getTPL()->display('workerFinish');
		exit;
	}
}
?>
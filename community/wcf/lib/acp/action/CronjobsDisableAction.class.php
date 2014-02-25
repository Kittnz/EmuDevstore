<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/action/CronjobsEnableAction.class.php');

/**
 * Disables a cronjob.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.cronjobs
 * @subpackage	acp.action
 * @category 	Community Framework
 */
class CronjobsDisableAction extends CronjobsEnableAction {
	public $enable = false;
}
?>
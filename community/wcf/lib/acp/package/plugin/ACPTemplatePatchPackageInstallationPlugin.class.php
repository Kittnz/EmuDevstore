<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/package/plugin/TemplatePatchPackageInstallationPlugin.class.php');

/**
 * This PIP looks for acp template patches, reads them and calls the class(es) that apply them.
 * 
 * @author	Siegfried Schweizer
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.package.plugin
 * @category 	Community Framework
 */
class ACPTemplatePatchPackageInstallationPlugin extends TemplatePatchPackageInstallationPlugin {
	public $tagName = 'acptemplatepatch';
	public $tableName = 'acp_template_patch';
	protected $type = 'acp_';
}
?>
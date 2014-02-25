<?php
// wcf imports
require_once(WCF_DIR.'lib/action/AbstractAction.class.php');
require_once(WCF_DIR.'lib/acp/option/Options.class.php');

/**
 * Exports the options to an XML.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.action
 * @category 	Community Framework
 */
class OptionExportAction extends AbstractAction {
	/**
	 * @see Action::execute();
	 */
	public function execute() {
		parent::execute();
		
		// check permissions
		WCF::getUser()->checkPermission('admin.system.canEditOption');

		// header
		@header('Content-type: text/xml');
		
		// file name
		@header('Content-disposition: attachment; filename="options.xml"');
			
		// no cache headers
		@header('Pragma: no-cache');
		@header('Expires: 0');
		
		// content
		echo "<?xml version=\"1.0\" encoding=\"".CHARSET."\"?>\n<options>\n";
		
		$options = Options::getOptions();
		foreach ($options as $option) {
			echo "\t<option>\n";
			echo "\t\t<name><![CDATA[".StringUtil::escapeCDATA($option['optionName'])."]]></name>\n";
			echo "\t\t<value><![CDATA[".StringUtil::escapeCDATA($option['optionValue'])."]]></value>\n";
			echo "\t</option>\n";
		}
		
		echo '</options>';
		$this->executed();
		exit;
	}
}
?>
<?php
// wcf imports
require_once(WCF_DIR.'lib/action/AbstractAction.class.php');

/**
 * Exports system information to xml file.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	acp.action
 * @category 	Burning Board
 */
class SystemInformationXMLExportAction extends AbstractAction {
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();
		
		// header
		$buffer = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
		$buffer .= "<systeminfo xmlns=\"http://www.woltlab.com\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"http://www.woltlab.com/XSD/systeminfo.xsd\">\n";
		
		// system block
		$buffer .= "\t<system>\n";
		
		// os block
		$buffer .= "\t\t<os>\n";
		
		// os type
		$buffer .= "\t\t\t<type><![CDATA[".StringUtil::escapeCDATA(StringUtil::toLowerCase(PHP_OS))."]]></type>\n";
		// try to get os version
		$osVersion = @exec('cat /proc/version'); // Linux
		if (empty($osVersion)) $osVersion = @exec('uname -a'); // FreeBSD / Darwin
		if (empty($osVersion)) $osVersion = @exec('ver'); // Windows
		$buffer .= "\t\t\t<version><![CDATA[".StringUtil::escapeCDATA($osVersion)."]]></version>\n";

		$buffer .= "\t\t</os>\n";
		
		// webserver block
		$buffer .= "\t\t<webserver>\n";
		
		// webserver type
		$webserver = (isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : '');
		$webserverType = 'other';
		if (stripos($webserver, 'apache') !== false) $webserverType = 'apache';
		else if (stripos($webserver, 'iis') !== false) $webserverType = 'iis';
		else if (stripos($webserver, 'lighttpd') !== false) $webserverType = 'lighttpd';
		else if (stripos($webserver, 'zeus') !== false) $webserverType = 'zeus';
		
		$buffer .= "\t\t\t<type><![CDATA[".$webserverType."]]></type>\n";
		// webserver version
		$buffer .= "\t\t\t<version><![CDATA[".StringUtil::escapeCDATA($webserver)."]]></version>\n";
		// webserver modules
		$modules = '';
		if (function_exists('apache_get_modules')) {
			$modules = @implode(', ', apache_get_modules());
		}
		$buffer .= "\t\t\t<modules><![CDATA[".StringUtil::escapeCDATA($modules)."]]></modules>\n";
		
		$buffer .= "\t\t</webserver>\n";

		// php block
		$buffer .= "\t\t<php>\n";
		
		// version
		$buffer .= "\t\t\t<version><![CDATA[".StringUtil::escapeCDATA(PHP_VERSION)."]]></version>\n";
		// integration
		$buffer .= "\t\t\t<integration><![CDATA[".StringUtil::escapeCDATA(php_sapi_name())."]]></integration>\n";
		// safe-mode?
		$buffer .= "\t\t\t<safemode><![CDATA[".FileUtil::getSafeMode()."]]></safemode>\n";
		// suhosin?
		$buffer .= "\t\t\t<suhosin><![CDATA[".intval(extension_loaded('suhosin'))."]]></suhosin>\n";
		// php modules
		$buffer .= "\t\t\t<modules><![CDATA[".StringUtil::escapeCDATA(implode(', ', get_loaded_extensions()))."]]></modules>\n";
		
		$buffer .= "\t\t</php>\n";
		
		// sql block
		$buffer .= "\t\t<sql>\n";
		
		// type
		$buffer .= "\t\t\t<type><![CDATA[".StringUtil::escapeCDATA(str_replace('Database', '', WCF::getDB()->getDBType()))."]]></type>\n";
		// version
		$buffer .= "\t\t\t<version><![CDATA[".StringUtil::escapeCDATA(WCF::getDB()->getVersion())."]]></version>\n";
		
		$buffer .= "\t\t</sql>\n";
		$buffer .= "\t</system>\n\n";
		
		// wcf block
		$buffer .= "\t<wcf>\n";
		$buffer .= "\t\t<packages>\n";
		
		$sql = "SELECT		*
			FROM		wcf".WCF_N."_package
			ORDER BY	package";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$buffer .= "\t\t\t<package>\n";
			$buffer .= "\t\t\t\t<name><![CDATA[".StringUtil::escapeCDATA($row['package'])."]]></name>\n";
			$buffer .= "\t\t\t\t<version><![CDATA[".StringUtil::escapeCDATA($row['packageVersion'])."]]></version>\n";
			$buffer .= "\t\t\t</package>\n";
		}
		
		$buffer .= "\t\t</packages>\n";
		$buffer .= "\t</wcf>\n";
		
		$buffer .= "</systeminfo>";
		
		// output
		header('Content-Type: application/xml; charset='.CHARSET);
		header('Content-Disposition: attachment; filename="systeminfo.xml"');
		header('Content-Length: '.strlen($buffer));
		print $buffer;
		
		$this->executed();
		exit;
	}
}
?>
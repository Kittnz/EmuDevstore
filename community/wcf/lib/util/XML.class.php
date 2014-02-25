<?php
/**
 * Opens and reads data from an XML file or string.
 *
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	util
 * @category 	Community Framework
 */
class XML {
	protected $encoding = 'UTF-8';
	protected $xmlObj = null;
	
	/**
	 * Contructs a new XML object.
	 * Optional parameter is a filename of an XML file.
	 *
	 * @param 	string 		$filename
	 */
	public function __construct($filename = '') {
		if ($filename != '') {
			$this->loadFile($filename);	
		}
	}
	
	/**
	 * Loads and parses an XML file.
	 *
	 * @param 	string 		$filename
	 */
	public function loadFile($filename) {
		$this->encoding = $this->detectEncoding(file_get_contents($filename));
		$this->xmlObj = simplexml_load_file($filename);
		if ($this->xmlObj === false) {
			throw new SystemException("file '".$filename."' is not a valid xml document");
		}
	}
	
	/**
	 * Parses a string of xml data.
	 *
	 * @param 	string 		$sourceContent
	 */
	public function loadString($sourceContent) {
		$this->encoding = $this->detectEncoding($sourceContent);
		$this->xmlObj = simplexml_load_string($sourceContent);
		if ($this->xmlObj === false) {
			throw new SystemException("given string is not a valid xml document");
		}
	}
	
	/**
	 * Sends a xpath query and 
	 * returns an array of SimpleXMLElements. 
	 * This is actually a wrapper for SimpleXMLElement::xpath().
	 *
	 * @param 	string 		$path
	 * @return 	array 		$result
	 */
	public function xpath($path) {
		return $this->xmlObj->xpath($path);	
	}
	
	/**
	 * Returns an array with all elements of an SimpleXML Object.
	 * The array has the following structure:
	 * [name] => tagName
	 * [attrs] => Array
	 * (
	 * )
	 * [children] => Array
	 * (
	 * )
	 *
	 *
	 * attrs contains all attributes in an associative array.
	 * children contains sub elements (with the same structure as above).
	 *
	 * @param 	string 				$name
	 * @param 	SimpleXMLElement	 	$xmlObj
	 * @return 	array				$element 			
	 */
	public function getElementTree($name, $xmlObj = null) {
		if (!($xmlObj instanceof SimpleXMLElement)) {
			$xmlObj = $this->xmlObj;	
		}
		$element = array('name' => $name);

		$element['attrs']	= $this->getAttributes($xmlObj);
		$element['cdata']	= $this->getCDATA($xmlObj);
		$element['children']	= $this->getChildren($xmlObj, true);
		
		return $element;
	}
	
	/**
	 * Returns the CDATA of an XML element.
	 * 
	 * @param 	SimpleXMLElement 	$xmlObj
	 * @return 	string 			$CDATA
	 */
	public function getCDATA($xmlObj = null) {
		if (!($xmlObj instanceof SimpleXMLElement)) {
			$xmlObj = $this->xmlObj;	
		}
		if (StringUtil::trim((string)$xmlObj) != '') {
			return (string)$xmlObj;	
		}
		else {
			return '';
		}
	}
	
	/**
	 * Returns an array of sub elements.
	 * If this method is called from XML::getElementTree(), it
	 * works recursively.
	 *
	 * @param 	SimpleXMLElement 	$xmlObj
	 * @param 	boolean 		$tree
	 * @return 	array 			$childrenArray
	 */
	public function getChildren($xmlObj = null, $tree = false) {
		if (!($xmlObj instanceof SimpleXMLElement)) {
			$xmlObj = $this->xmlObj;	
		}
		$childrenArray = array();
		
		$children = $xmlObj->children();
		
		foreach ($children as $key => $childObj) {
			if ($tree) {
				$childrenArray[] = $this->getElementTree($key, $childObj);
			}
			else {
				$childrenArray[] = $childObj;
			}
		}
		return $childrenArray;
	}
	
	/**
	 * Returns an associative array with attributes of an XML element.
	 * 
	 * @param 	SimpleXMLElement 	$xmlObj
	 * @param 	array 			$attributesArray
	 */
	public function getAttributes($xmlObj = null) {
		if (!($xmlObj instanceof SimpleXMLElement)) {
			$xmlObj = $this->xmlObj;	
		}
		$attributesArray = array();
		$attributes = $xmlObj->attributes();	
		foreach ($attributes as $key => $val) {
			$attributesArray[$key] = (string)$val;	
		}
		
		return $attributesArray;
	}
	
	/**
	 * Returns the encoding of this xml document.
	 * 
	 * @return	string
	 */
	public function getEncoding() {
		return $this->encoding;	
	}
	
	/**
	 * Detects encoding of an XML file.
	 *
	 * @param 	string 		$xmlSource
	 * @return 	string 		$encoding
	 */
	protected static function detectEncoding($xmlSource) {
		$matches = array();
		if (preg_match('/<\?xml.*encoding="([a-z0-9-]+)".*\?>/i', $xmlSource, $matches)) {
			$encoding = strtoupper($matches[1]);
		}
		else {
			$encoding = 'UTF-8';	
		}
		
		return $encoding;
	}
}
?>
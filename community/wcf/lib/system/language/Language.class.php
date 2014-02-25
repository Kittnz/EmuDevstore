<?php
/**
 * Language loads the needed language files and gives access to language variables.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.language
 * @category 	Community Framework
 */
class Language {
	/**
	 * scripting compiler object
	 * 
	 * @var	TemplateScriptingCompiler
	 */
	protected static $scriptingCompiler = null;
	
	protected $languageID = 0;
	protected $items = array(), $dynamicItems = array();
	protected $data = array();
	public static $cache = null;
	protected static $languageObjectCache = array();
	protected $editor = null;
	public $packageID = PACKAGE_ID;
	public static $dateFormatLocalized = false;
	public static $supportedCharsets = array(
		// latin
		'ISO-8859-1' => array('multibyte' => false, 'languages' => array('en', 'de', 'de-informal', 'es', 'it', 'nl', 'pt', 'pt-BR', 'sv', 'da', 'no', 'fi')), // latin1
		'ISO-8859-2' => array('multibyte' => false, 'languages' => array('en', 'de', 'de-informal', 'bs', 'hr', 'sr', 'sk', 'pl', 'cs', 'hu', 'ro')), // latin2

		// greek
		'ISO-8859-7' => array('multibyte' => false, 'languages' => array('en', 'el')),
	
		// hebrew
		'ISO-8859-8' => array('multibyte' => false, 'languages' => array('en', 'he')),
	
		// latin (turkish)
		'ISO-8859-9' => array('multibyte' => false, 'languages' => array('en', 'de', 'de-informal', 'tr')), // latin5
	
		// japanese
		'EUC-JP' => array('multibyte' => true, 'languages' => array('en', 'ja')),
		'SJIS' => array('multibyte' => true, 'languages' => array('ja')), 
		
		// chinese
		//'BIG-5' => array('multibyte' => true, 'languages' => array('en', 'zh-TW')), // traditional
		'CP936' => array('multibyte' => true, 'languages' => array('en', 'zh-CN')), // simplified
		'EUC-CN' => array('multibyte' => true, 'languages' => array('en', 'zh-CN')), // simplified
		
		// russian
		'KOI8-R' => array('multibyte' => false, 'languages' => array('en', 'ru')),
		'Windows-1251' => array('multibyte' => false, 'languages' => array('en', 'ru')),
	
		// korean
		'EUC-KR' => array('multibyte' => true, 'languages' => array('en', 'ko'))
	);
	
	/**
	 * Creates a new Language object.
	 * 
	 * @param 	integer		$languageID
	 */
	public function __construct($languageID) {
		$this->languageID = $languageID;
		
		// get cache
		$this->loadCache();
		
		// unknown language id given
		// search right language
		if (!isset(self::$cache['languages'][$this->languageID])) {
			$this->findPreferredLanguage();
		}
		
		// get language data
		$this->data = self::$cache['languages'][$this->languageID];
		
		// init language
		$this->init();
	}
	
	/**
	 * Searches the preferred language of the current user.
	 */
	protected function findPreferredLanguage() {
		// get available language codes
		$availableLanguageCodes = array();
		foreach (self::getAvailableLanguages(PACKAGE_ID) as $language) {
			$availableLanguageCodes[] = $language['languageCode'];
		}
		
		// get default language
		$defaultLanguageCode = self::$cache['languages'][self::$cache['default']]['languageCode'];
		
		// get preferred language
		$languageCode = $this->getPreferredLanguage($availableLanguageCodes, $defaultLanguageCode);
		
		// get language id of preferred language
		foreach (self::$cache['languages'] as $key => $language) {
			if ($language['languageCode'] == $languageCode) {
				$this->languageID = $key;
				break;
			}
		}
	}
	
	/**
	 * Initialise the language engine. 
	 * Sets the default constants and calls the setlocale() function.
	 */
	protected function init() {
		if (!defined('CHARSET')) {
			define('CHARSET', $this->data['languageEncoding']);
			define('LANGUAGE_CODE', self::fixLanguageCode($this->data['languageCode']));
			if ((CHARSET == 'UTF-8' || self::$supportedCharsets[CHARSET]['multibyte']) && extension_loaded('mbstring')) {
				define('USE_MBSTRING', true);
				mb_internal_encoding(CHARSET);
				if (function_exists('mb_regex_encoding')) mb_regex_encoding(CHARSET);
				if (CHARSET == 'UTF-8') mb_language('uni');
			}
			else {
				define('USE_MBSTRING', false);
			}
		}
		
		$this->setLocale();
	}
	
	/**
	 * Sets the local language.
	 * Recall this function after language changed.
	 */
	public function setLocale() {
		// set locale for
		// string comparison
		// character classification and conversion
		// date and time formatting
		if (!defined('PAGE_DIRECTION')) define('PAGE_DIRECTION', $this->get('wcf.global.pageDirection'));
		setlocale(LC_COLLATE, $this->get('wcf.global.locale.unix').'.'.CHARSET, $this->get('wcf.global.locale.unix').'.'.str_replace('ISO-', 'ISO', CHARSET), $this->get('wcf.global.locale.unix'), $this->get('wcf.global.locale.win'));
		setlocale(LC_CTYPE, $this->get('wcf.global.locale.unix').'.'.CHARSET, $this->get('wcf.global.locale.unix').'.'.str_replace('ISO-', 'ISO', CHARSET), $this->get('wcf.global.locale.unix'), $this->get('wcf.global.locale.win'));
		if (setlocale(LC_TIME, $this->get('wcf.global.locale.unix').'.'.CHARSET, $this->get('wcf.global.locale.unix').'.'.str_replace('ISO-', 'ISO', CHARSET), $this->get('wcf.global.locale.unix'), $this->get('wcf.global.locale.win')) !== false) {
			self::$dateFormatLocalized = true;
		}
	}
	
	/**
	 * Returns a single language variable.
	 * Replace variables defined in associative $variables array.
	 * 
	 * Example: 
	 * 
	 * WCF::getLanguage->get('name.of.lang.var', array('$value' => $value));
	 * 
	 * <item name="name.of.lang.var">Some content with the value {$value}</item>
	 *
	 * @param 	string 		$item
	 * @param 	array 		$variables
	 * @return 	string 				variable
	 */
	public function get($item, $variables = array()) {
		if (!isset($this->items[$this->languageID][$item])) {
			// check category name
			if (strpos($item, ' ') !== false) {
				return $item;
			}
			
			// load category file
			$explodedItem = explode('.', $item);
			if (count($explodedItem) < 2) {
				return $item;
			}
			
			if (count($explodedItem) < 4 || !$this->loadCategory($explodedItem[0].'.'.$explodedItem[1].'.'.$explodedItem[2].'.'.$explodedItem[3])) {
				if (count($explodedItem) < 3 || !$this->loadCategory($explodedItem[0].'.'.$explodedItem[1].'.'.$explodedItem[2])) {
					$this->loadCategory($explodedItem[0].'.'.$explodedItem[1]);
				}
			}
		}
		
		if (isset($this->items[$this->languageID][$item])) {
			if (count($variables) == 0) {
				return $this->items[$this->languageID][$item];
			}
			else {
				$newVariables = array();
				foreach ($variables as $key => $value) {
					$newVariables['{'.$key.'}'] = $value;
				}
				return strtr($this->items[$this->languageID][$item], $newVariables);
			}
		}
		else {
			return $item;
		}
	}
	
	/**
	 * Executes template scripting in a language variable.
	 *
	 * @param 	string 		$item
	 * @param 	array 		$variables 
	 * @return 	string 		result
	 */
	public function getDynamicVariable($item, $variables = array()) {
		$staticItem = $this->get($item, $variables);
		
		if (isset($this->dynamicItems[$this->languageID][$item])) {
			if (count($variables)) WCF::getTPL()->assign($variables);
			return WCF::getTPL()->fetchString($this->dynamicItems[$this->languageID][$item]);
		}
		
		return $staticItem;
	}
	
	/**
	 * Loads category files.
	 *
	 * @param 	string 		$category
	 * @return 	boolean		result
	 */
	protected function loadCategory($category) {
		// check language category
		if (!isset(self::$cache['categories'][$category])) {
			return false;
		}
		
		// search language file
		$filename = WCF_DIR.'language/'.$this->packageID.'_'.$this->languageID.'_'.$category.'.php';
		if (!@file_exists($filename)) { 
			// rebuild language file
			if ($this->editor == null) {
				require_once(WCF_DIR.'lib/system/language/LanguageEditor.class.php');
				$this->editor = new LanguageEditor($this->languageID);
			}
			
			$this->editor->updateCategory(self::$cache['categories'][$category]['languageCategoryID'], $this->packageID);
		}
		
		// include language file
		@include_once($filename);
		return true;
	}
	
	/**
	 * Returns the id of this language. 
	 * 
	 * @return	integer
	 */
	public function getLanguageID() {
		return $this->languageID;
	}
	
	/**
	 * Returns the code of this language. 
	 * 
	 * @return	string
	 */
	public function getLanguageCode() {
		return $this->data['languageCode'];
	}
	
	/**
	 * Returns true, if this language is the default language.
	 * 
	 * @return	boolean
	 */
	public function isDefault() {
		return ($this->data['isDefault'] == 1);
	}
	
	/**
	 * Returns true, if this language uses strftime to format date.
	 * 
	 * @return	boolean
	 */
	public function useStrftime() {
		return ($this->get('wcf.global.dateMethod') == 'strftime');
	}
	
	/**
	 * Determines the preferred language of the current user.
	 * 
	 * @param	array		$availableLanguages
	 * @param	string		$defaultLanguageCode
	 * @return	string
	 */
	public static function getPreferredLanguage($availableLanguageCodes, $defaultLanguageCode) {
		if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) && $_SERVER['HTTP_ACCEPT_LANGUAGE']) {
			$acceptedLanguages = explode(',', str_replace('_', '-', strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE'])));
			foreach ($acceptedLanguages as $acceptedLanguage) {
				foreach ($availableLanguageCodes as $availableLanguageCode) {
					$fixedCode = strtolower(Language::fixLanguageCode($availableLanguageCode));
					
					if ($fixedCode == $acceptedLanguage || $fixedCode == preg_replace('%^([a-z]{2}).*$%i', '$1', $acceptedLanguage)) {
						return $availableLanguageCode;
					}
				}
			}
		}
		
		return $defaultLanguageCode;
	}
	
	/**
	 * Returns all available languages for given package  
	 * 
	 * @param 	integer		$packageID
	 * @return	array		$availableLanguages 	infos about each language (code, id, encoding, etc)
	 */
	public static function getAvailableLanguages($packageID = PACKAGE_ID) {
		// get list of all available languages
		$availableLanguages = array();
		if (isset(self::$cache['packages'][$packageID])) {
			foreach (self::$cache['packages'][$packageID] as $availableLanguageID) {
				$availableLanguages[] = self::$cache['languages'][$availableLanguageID];
			}
		}
		return $availableLanguages;
	}
	
	/**
	 * Returns all available content languages for given package.  
	 * 
	 * @param 	integer		$packageID
	 * @return	array		$availableLanguages 	infos about each language (code, id, encoding, etc)
	 */
	public static function getAvailableContentLanguages($packageID = PACKAGE_ID) {
		$availableLanguages = array();
		if (isset(self::$cache['packages'][$packageID])) {
			foreach (self::$cache['packages'][$packageID] as $availableLanguageID) {
				if (self::$cache['languages'][$availableLanguageID]['hasContent']) {
					$availableLanguages[$availableLanguageID] = self::$cache['languages'][$availableLanguageID];
				}
			}
		}
		return $availableLanguages;
	}
	
	/**
	 * Returns all content languages for given package.
	 * 
	 * @param 	integer		$packageID
	 * @return	array
	 */
	public static function getContentLanguages($packageID = PACKAGE_ID) {
		$languages = array();
		if (isset(self::$cache['packages'][$packageID])) {
			foreach (self::$cache['packages'][$packageID] as $languageID) {
				if (self::$cache['languages'][$languageID]['hasContent']) {
					$languages[$languageID] = WCF::getLanguage()->getDynamicVariable('wcf.global.language.'.self::$cache['languages'][$languageID]['languageCode']);
				}
			}
		}
		
		StringUtil::sort($languages);
		return $languages;
	}
	
	/**
	 * Returns all available language codes for given package  
	 * 
	 * @param 	integer		$packageID
	 * @return	array
	 */
	public static function getAvailableLanguageCodes($packageID = PACKAGE_ID) {
		// get list of all available languages
		$availableLanguages = array();
		if (isset(self::$cache['packages'][$packageID])) {
			foreach (self::$cache['packages'][$packageID] as $availableLanguageID) {
				$availableLanguages[$availableLanguageID] = self::$cache['languages'][$availableLanguageID]['languageCode'];
			}
		}
		
		asort($availableLanguages);
		return $availableLanguages;
	}
	
	/**
	 * Counts available languages for given package.  
	 * 
	 * @param 	integer		$packageID
	 * @return	integer
	 */
	public static function countAvailableLanguages($packageID = PACKAGE_ID) {
		if (isset(self::$cache['packages'][$packageID])) return count(self::$cache['packages'][$packageID]);
		return 0;
	}
	
	/**
	 * Returns the default language id
	 * 
	 * @return	integer		
	 */
	public static function getDefaultLanguageID() {
		return self::$cache['default'];
	}
	
	/**
	 * Returns the data of a language
	 * 
	 * @return	array
	 */
	public static function getLanguage($languageID) {
		if (isset(self::$cache['languages'][$languageID])) {
			return self::$cache['languages'][$languageID];
		}
		
		return null;
	}
	
	/**
	 * Returns true, if the given language is supported by this wcf installation.
	 * 
	 * @param	string		$languageCode 		ISO 639-1
	 * @return	boolean
	 */
	public static function isSupported($languageCode) {
		return (CHARSET == 'UTF-8' || in_array($languageCode, self::$supportedCharsets[CHARSET]['languages']));
	}
	
	/**
	 * Returns a sorted list of all installed languages.
	 * 
	 * @return	array
	 */
	public static function getLanguages() {
		$languages = array();
		foreach (self::$cache['languages'] as $languageID => $language) {
			$languages[$languageID] = WCF::getLanguage()->getDynamicVariable('wcf.global.language.'.$language['languageCode']);
		}
		
		StringUtil::sort($languages);
		return $languages;
	}
	
	/**
	 * Returns a sorted list of all installed language codes.
	 * 
	 * @return	array
	 */
	public static function getLanguageCodes() {
		$languages = array();
		foreach (self::$cache['languages'] as $languageID => $language) {
			$languages[$languageID] = $language['languageCode'];
		}
		
		asort($languages);
		return $languages;
	}
	
	/**
	 * Counts installed languages.
	 * 
	 * @return	integer
	 */
	public static function countLanguages() {
		return count(self::$cache['languages']);
	}
	
	/**
	 * Returns a list of all language categories.
	 * 
	 * @return	array
	 */
	public static function getLanguageCategories() {
		$categories = array();
		$sql = "SELECT		*
			FROM		wcf".WCF_N."_language_category
			ORDER BY	languageCategory";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$categories[$row['languageCategoryID']] = $row['languageCategory'];
		}
		
		return $categories;
	}
	
	/**
	 * Resets the language cache resource.
	 */
	public static function clearCache() {
		// reset cache
		WCF::getCache()->clearResource('languages');
		self::$cache = null;
		self::$languageObjectCache = array();
		
		// reload cache
		self::loadCache();
	}
	
	/**
	 * Loads the language cache.
	 */
	protected static function loadCache() {
		if (self::$cache == null) {
			self::$cache = WCF::getCache()->get('languages');
		}
	}
	
	/**
	 * Removes additional language identifier from given language code.
	 * Converts e.g. 'de-informal' to 'de'.
	 * 
	 * @param	string		$languageCode
	 * @return	string		$languageCode
	 */
	public static function fixLanguageCode($languageCode) {
		return preg_replace('/-[a-z0-9]+/', '', $languageCode);
	}
	
	/**
	 * Returns true, if this language is flaged as content language.
	 * 
	 * @return 	boolean
	 */
	public function isContentLanguage() {
		return ($this->data['hasContent'] == 1);
	}
	
	/**
	 * Returns the active scripting compiler object.
	 * 
	 * @return	TemplateScriptingCompiler
	 */
	public static function getScriptingCompiler() {
		if (self::$scriptingCompiler === null) {
			if (!defined('NO_IMPORTS')) require_once(WCF_DIR.'lib/system/template/TemplateScriptingCompiler.class.php');
			self::$scriptingCompiler = new TemplateScriptingCompiler(WCF::getTPL());
		}
		
		return self::$scriptingCompiler;
	}
	
	/**
	 * Returns a language object from cache.
	 * 
	 * @param	integer		$languageID
	 * @return	Language
	 */
	public static function getLanguageObjectByID($languageID) {
		if (!isset(self::$languageObjectCache[$languageID])) {
			self::$languageObjectCache[$languageID] = new Language($languageID);
		}
		
		return self::$languageObjectCache[$languageID];
	}
}
?>
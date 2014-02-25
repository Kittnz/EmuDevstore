<?php
// wcf imports
require_once(WCF_DIR.'lib/data/DatabaseObject.class.php');

/**
 * Generates and manages captchas.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.image.captcha
 * @subpackage	data.image.captcha
 * @category 	Community Framework
 */
class Captcha extends DatabaseObject {
	const CONSONANTS 	= 'BCDFGHJKLMNPRSTVWXYZ';
	const VOCALS		= 'AEIOU';
	
	/**
	 * Creates a new Captcha object.
	 * 
	 * @param	integer		$captchaID
	 */
	public function __construct($captchaID) {
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_captcha
			WHERE	captchaID = ".$captchaID;
		$row = WCF::getDB()->getFirstRow($sql);
		parent::__construct($row);
	}
	
	/**
	 * Validates the given captcha string.
	 * 
	 * @param	string		$captchaString
	 */
	public function validate($captchaString) {
		if (Captcha::isSupported()) {
			try {
				if (!$this->captchaID) {
					throw new UserInputException('captchaString');
				}
				
				if (empty($captchaString)) {
					throw new UserInputException('captchaString');
				}
				
				if (StringUtil::toUpperCase($captchaString) != $this->captchaString) {
					throw new UserInputException('captchaString', 'false');
				}
				
				// captcha ok
				WCF::getSession()->register('captchaDone', true);
				$this->delete();
			}
			catch (Exception $e) {
				$this->delete();
				throw $e;
			}
		}
	}
	
	/**
	 * Deletes this captcha.
	 */
	public function delete() {
		if ($this->captchaID) {
			$sql = "DELETE FROM	wcf".WCF_N."_captcha
				WHERE		captchaID = ".$this->captchaID;
			WCF::getDB()->registerShutdownUpdate($sql);
		}
	}
	
	/**
	 * Creates a new captcha.
	 * Returns the id of the created captcha.
	 * 
	 * @param	integer		$length		length of captcha string
	 * @return	integer		id
	 */
	public static function create($length = null) {
		if (!self::isSupported()) {
			return 0;
		}
		
		if ($length === null) {
			$length = mt_rand(5, 8);
		}
		
		// get random string
		$captchaString = self::getRandomMnemonicString($length);
		
		// save new captcha
		$sql = "INSERT INTO	wcf".WCF_N."_captcha
					(captchaString, captchaDate)
			VALUES		('".escapeString($captchaString)."',
					".TIME_NOW.")";
		WCF::getDB()->sendQuery($sql);
		return WCF::getDB()->getInsertID();
	}
	
	/**
	 * Generates a random mnemonic password
	 *
	 * @param	integer		$length
	 * @return	string
	 */
	protected static function getRandomMnemonicString($length) {
		$string = '';
		for ($i = 1; $i <= $length; $i++) {
			if ($i % 2) $string .= substr(self::CONSONANTS, mt_rand(0, 19), 1);
			else $string .= substr(self::VOCALS, mt_rand(0, 4), 1);
		}
	
		return $string;
	}
	
	/**
	 * Checks the requirements of captcha generation.
	 * 
	 * @return 	boolean
	 */
	public static function isSupported() {
		if (!function_exists('gd_info')) {
			return false;
		}
		
		// get information about gd lib
		$gdInfo = gd_info();
			
		// get gd lib version
		$gdVersion = '0.0.0';
		if (preg_match('!([0-9]+\.[0-9]+(?:\.[0-9]+)?)!', $gdInfo['GD Version'], $match)) {
			$gdVersion = $match[1];
			if ($gdVersion == '2.0') $gdVersion = '2.0.0';
		}
		
		// check support for ttf and png and the right version	
		if (!$gdInfo['FreeType Support'] || !$gdInfo['PNG Support'] || version_compare($gdVersion, '2.0.0') < 0) {
			return false;
		}
		
		return true;
	}
}
?>
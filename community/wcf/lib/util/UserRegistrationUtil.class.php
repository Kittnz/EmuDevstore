<?php
/**
 * Contains user registration related functions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.form.user
 * @subpackage	util
 * @category 	Community Framework
 */
class UserRegistrationUtil {	
	/**
	 * Returns true, if the given name is a valid username.
	 * 
	 * @param	string		$name		username
	 * @return 	boolean
	 */
	public static function isValidUsername($name) {
		$length = StringUtil::length($name);
		return (UserUtil::isValidUsername($name) && $length >= REGISTER_USERNAME_MIN_LENGTH && $length <= REGISTER_USERNAME_MAX_LENGTH && self::checkForbiddenUsernames($name));
	}
	
	/**
	 * Returns true, if the given e-mail is a valid address.
	 * 
	 * @param	string		$email
	 * @return 	boolean
	 */
	public static function isValidEmail($email) {
		return (UserUtil::isValidEmail($email) && self::checkForbiddenEmails($email));
	}
	
	/**
	 * Returns false, if the given name is a forbidden username.
	 * 
	 * @return	boolean
	 */
	public static function checkForbiddenUsernames($name) {
		return StringUtil::executeWordFilter($name, REGISTER_FORBIDDEN_USERNAMES);
	}
	
	/**
	 * Returns false, if the given email is a forbidden email.
	 * 
	 * @return	boolean
	 */
	public static function checkForbiddenEmails($email) {
		return (StringUtil::executeWordFilter($email, REGISTER_FORBIDDEN_EMAILS) && (!REGISTER_ALLOWED_EMAILS || !StringUtil::executeWordFilter($email, REGISTER_ALLOWED_EMAILS)));
	}
	
	/**
	 * Returns false, if the given word is forbidden by given word filter.
	 * 
	 * @return	boolean
	 * @deprecated
	 */
	protected static function executeWordFilter($word, $filter) {
		return StringUtil::executeWordFilter($word, $filter);
	}
	
	/**
	 * Returns true, if the given password is secure.
	 * 
	 * @param	string		$password
	 * @return 	boolean
	 */
	public static function isSecurePassword($password) {
		if (REGISTER_ENABLE_PASSWORD_SECURITY_CHECK) {
			if (StringUtil::length($password) < REGISTER_PASSWORD_MIN_LENGTH) return false;
			
			if (REGISTER_PASSWORD_MUST_CONTAIN_DIGIT && !preg_match('![0-9]+!', $password)) return false;
			if (REGISTER_PASSWORD_MUST_CONTAIN_LOWER_CASE && !preg_match('![a-z]+!', $password)) return false;
			if (REGISTER_PASSWORD_MUST_CONTAIN_UPPER_CASE && !preg_match('![A-Z]+!', $password)) return false;
			if (REGISTER_PASSWORD_MUST_CONTAIN_SPECIAL_CHAR && !preg_match('![^A-Za-z0-9]+!', $password)) return false;
		}
		
		return true;
	}
	
	/**
	 * Generates a random activation code with the given length.
	 * Warning: A length greater than 9 is out of integer range.
	 * 
	 * @param	integer		$length
	 * @return	integer
	 */
	public static function getActivationCode($length = 9) {
		return MathUtil::getRandomValue(pow(10, $length - 1), pow(10, $length) - 1);
	}
	
	/**
	 * Generates a random user password with the given character length.
	 *
	 * @param	integer		$length
	 * @return	string		new password
	 */
	public static function getNewPassword($length = 9) {
		static $availableCharacters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789+#-.,;:?!';
		
		$password = '';
		for ($i = 0; $i < $length; $i++) {
			$password .= substr($availableCharacters, MathUtil::getRandomValue(0, strlen($availableCharacters) - 1), 1);
		}
		
		return $password;
	}
}
?>
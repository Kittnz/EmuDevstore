<?php
// wcf imports
require_once(WCF_DIR.'lib/system/event/EventHandler.class.php');

/**
 * Generates a vcard.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.page.user.profile
 * @subpackage	data.user
 * @category 	Community Framework
 */
class VCard {
	/**
	 * source of this vcard
	 *
	 * @var string
	 */
	public $content = '';
	
	/**
	 * user object
	 *
	 * @var User
	 */
	public $user;
	
	/**
	 * Creates a new VCard object.
	 * 
	 * @param	User		$user
	 */
	public function __construct(User $user) {
		$this->user = $user;
		$this->generate();
	}
	
	/**
	 * Returns the content of the vcard.
	 * 
	 * @return	string
	 */
	public function getContent() {
		return $this->content;
	}
	
	/**
	 * Generates the vcard.
	 */
	protected function generate() {
		$this->content .= "BEGIN:VCARD\r\n";
		$this->content .= "VERSION:2.1\r\n";
		
		// name
		if ($this->user->name || $this->user->surname) {
			$this->content .= "N:".$this->user->surname.";".$this->user->name."\r\n";
			$this->content .= "FN:".$this->user->name." ".$this->user->surname."\r\n";
		}
	
		// telephone
		if ($this->user->telephone) {
			$this->content .= "TEL;VOICE:".$this->user->telephone."\r\n";
		}
		
		// gender
		if ($this->user->gender) { 
			$this->content .= "X-WAB-GENDER:".(1 - $this->user->gender)."\r\n";
		}
		
		// username
		$this->content .= "NICKNAME:".$this->user->username."\r\n";
		
		// occupation
		if ($this->user->occupation) {
			$this->content .= "TITLE;".$this->user->occupation."\r\n";
		}
		
		// email
		if (!$this->user->hideEmailAddress) {
			$this->content .= "EMAIL;INTERNET:".$this->user->email."\r\n";
		}
		
		// homepage
		if ($this->user->homepage) {
			$this->content .= "URL:".$this->user->homepage."\r\n";
		}
		
		// birthday
		if ($this->user->birthday) {
			$this->content .= "BDAY;value=date:".$this->user->birthday."\r\n";
		}
		
		// location
		if ($this->user->location) {
			$this->content .= "item2.ADR;type=HOME:;;".$this->user->street.";".$this->user->location.";".$this->user->state.";".$this->user->zipcode.";".$this->user->country."\r\n";	
		}
		
		// fire event
		EventHandler::fireAction($this, 'generate');
		
		$this->content .= "END:VCARD";
		
		// vCard format does not support utf-8?
		if (CHARSET != 'ISO-8859-1') {
			$this->content = StringUtil::convertEncoding(CHARSET, 'ISO-8859-1', $this->content);
		}
	}
}
?>
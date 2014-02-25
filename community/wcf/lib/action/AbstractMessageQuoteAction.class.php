<?php
// wcf imports
require_once(WCF_DIR.'lib/action/AbstractSecureAction.class.php');

/**
 * Provides default implementations for message quote actions.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.message.multiQuote
 * @subpackage	action
 * @category 	Community Framework (commercial)
 */
abstract class AbstractMessageQuoteAction extends AbstractSecureAction {
	/**
	 * object id
	 *
	 * @var	integer
	 */
	public $objectID = 0;
	
	/**
	 * quote text
	 *
	 * @var mixed
	 */
	public $text = '';
	
	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		// get object id
		if (isset($_REQUEST['objectID'])) $this->objectID = intval($_REQUEST['objectID']);
		
		// get quote(s)
		if (isset($_REQUEST['text'])) $this->text = $_REQUEST['text'];
		if (is_array($this->text)) {
			$this->text = ArrayUtil::unifyNewlines(ArrayUtil::trim($this->text));
			if (CHARSET != 'UTF-8') {
				$this->text = ArrayUtil::convertEncoding('UTF-8', CHARSET, $this->text);
			}
		}
		else {
			$this->text = StringUtil::unifyNewlines(StringUtil::trim($this->text));
			if (CHARSET != 'UTF-8') {
				$this->text = StringUtil::convertEncoding('UTF-8', CHARSET, $this->text);
			}
		}
	}
}
?>
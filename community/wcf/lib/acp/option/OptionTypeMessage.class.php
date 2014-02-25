<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/option/OptionTypeTextarea.class.php');

/**
 * OptionTypeMessage is an implementation of OptionType for messages.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.page.user.profile
 * @subpackage	acp.option
 * @category 	Community Framework
 */
class OptionTypeMessage extends OptionTypeTextarea {
	/**
	 * height of the wysiwyg
	 * 
	 * @var	integer
	 */
	public $wysiwygEditorHeight = 0;
	
	/**
	 * wysiwyg mode
	 * 
	 * @var	integer
	 */
	public $wysiwygEditorMode = 0;

	/**
	 * Creates a new OptionTypeMessage object.
	 */
	public function __construct() {
		if (WCF::getUser()->userID) {
			$this->wysiwygEditorMode = WCF::getUser()->wysiwygEditorMode;
			$this->wysiwygEditorHeight = WCF::getUser()->wysiwygEditorHeight;
		}
		else {
			$this->wysiwygEditorMode = WYSIWYG_EDITOR_MODE;
			$this->wysiwygEditorHeight = WYSIWYG_EDITOR_HEIGHT;
		}
	}
	
	/**
	 * @see OptionType::validate()
	 */
	public function validate($optionData, $newValue) {
		if (isset($_POST['wysiwygEditorMode'])) $this->wysiwygEditorMode = intval($_POST['wysiwygEditorMode']);
		if (isset($_POST['wysiwygEditorHeight'])) $this->wysiwygEditorHeight = intval($_POST['wysiwygEditorHeight']);
		
		// save wysiwyg config
		if (WCF::getUser()->userID) {
			$options = array(
				'wysiwygEditorMode' => $this->wysiwygEditorMode,
				'wysiwygEditorHeight' => $this->wysiwygEditorHeight
			);
			
			$editor = WCF::getUser()->getEditor();
			$editor->updateOptions($options);
		}
		
		parent::validate($optionData, $newValue);
	}
	
	/**
	 * @see OptionType::getFormElement()
	 */
	public function getFormElement(&$optionData) {
		if (!isset($optionData['optionValue'])) {
			if (isset($optionData['defaultValue'])) $optionData['optionValue'] = $optionData['defaultValue'];
			else $optionData['optionValue'] = '';
		}
		
		if (class_exists('WCFACP')) {
			WCF::getTPL()->assign(array(
				'optionData' => $optionData
			));
			return WCF::getTPL()->fetch('optionTypeTextarea');
		}
		else {
			$optionData['divClass'] = 'editorFrame';

			// get smileys
			require_once(WCF_DIR.'lib/data/message/smiley/Smiley.class.php');
			$smileys = WCF::getCache()->get('smileys', 'smileys');
			$defaultSmileys = (isset($smileys[0]) ? $smileys[0] : array());

			// show wysiwyg
			WCF::getTPL()->assign(array(
				'defaultSmileys' => $defaultSmileys,
				'optionData' => $optionData,
				'wysiwygBBCodes' => WCF::getCache()->get('bbcodes', 'all'),
				'wysiwygEditorMode' => $this->wysiwygEditorMode,
				'wysiwygEditorHeight' => $this->wysiwygEditorHeight
			));
			return WCF::getTPL()->fetch('optionTypeMessage');
		}
	}
}
?>
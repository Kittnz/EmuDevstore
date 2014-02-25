<?php
// wcf imports
require_once(WCF_DIR.'lib/data/message/bbcode/BBCodeParser.class.php');
require_once(WCF_DIR.'lib/data/message/bbcode/BBCode.class.php');

/**
 * Parses the [attach] bbcode tag. Shows in a message embedded attachments.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.message.bbcode
 * @subpackage	data.message.bbcode
 * @category 	Community Framework
 */
class AttachmentBBCode implements BBCode {
	protected static $attachments = null;
	protected static $messageID = 0;
	
	/**
	 * Sets the attachments.
	 */
	public static function setAttachments($attachments) {
		self::$attachments = $attachments;
	}
	
	/**
	 * Sets the active message id.
	 */
	public static function setMessageID($messageID) {
		self::$messageID = $messageID;
	}
	
	/**
	 * @see BBCode::getParsedTag()
	 */
	public function getParsedTag($openingTag, $content, $closingTag, BBCodeParser $parser) {
		if (self::$messageID == 0 && !isset(self::$attachments[self::$messageID]) && count(self::$attachments) == 1) {
			// get first message id
			$keys = array_keys(self::$attachments);
			self::$messageID = reset($keys);
		}
		
		if (isset($openingTag['attributes'][0])) {
			$attachmentID = $openingTag['attributes'][0];
			
			if (isset(self::$attachments[self::$messageID]['images'][$attachmentID])) {
				// image
				$attachment = self::$attachments[self::$messageID]['images'][$attachmentID];
				if ($parser->getOutputType() == 'text/html') {
					$align = (isset($openingTag['attributes'][1]) ? $openingTag['attributes'][1] : '');
					$result = '<img src="index.php?page=Attachment&amp;attachmentID=' . $attachmentID . ($attachment->thumbnailType ? '&amp;thumbnail=1' : '') . '&amp;embedded=1" alt="" class="embeddedAttachment" style="width: '.($attachment->thumbnailType ? $attachment->getThumbnailWidth() : $attachment->getWidth()).'px; height: '.($attachment->thumbnailType ? $attachment->getThumbnailHeight() : $attachment->getHeight()).'px;'.(!empty($align) ? ' float:' . StringUtil::encodeHTML($align) . '; margin: ' . ($align == 'left' ? '0 15px 7px 0' : '0 0 7px 15px' ) : '').'" />';
					if ($attachment->thumbnailType) {
						$result = '<a href="index.php?page=Attachment&amp;attachmentID='.$attachmentID.'" class="enlargable">'.$result.'</a>';
					}
					return $result;
				}
				else if ($parser->getOutputType() == 'text/plain') {
					return ($content != $attachmentID ? $content : $attachment->attachmentName).': '.PAGE_URL.'/index.php?page=Attachment&attachmentID='.$attachmentID.($attachment->thumbnailType ? '&thumbnail=1' : '');
				}
			}
			else if (isset(self::$attachments[self::$messageID]['files'][$attachmentID])) {
				// file
				$attachment = self::$attachments[self::$messageID]['files'][$attachmentID];
				if ($parser->getOutputType() == 'text/html') {
					return '<a href="index.php?page=Attachment&amp;attachmentID='.$attachmentID.'">'.((!empty($content) && $content != $attachmentID) ? $content : StringUtil::encodeHTML($attachment->attachmentName)).'</a>';
				}
				else if ($parser->getOutputType() == 'text/plain') {
					return ($content != $attachmentID ? $content : $attachment->attachmentName).': '.PAGE_URL.'/index.php?page=Attachment&attachmentID='.$attachmentID;
				}
			}
		}
		
		if ($parser->getOutputType() == 'text/html') {
			return '<a href="index.php?page=Attachment&amp;attachmentID='.$attachmentID.'">index.php?page=Attachment&amp;attachmentID='.$attachmentID.'</a>';
		}
		else if ($parser->getOutputType() == 'text/plain') {
			return PAGE_URL.'/index.php?page=Attachment&attachmentID='.$attachmentID;
		}
	}
}
?>
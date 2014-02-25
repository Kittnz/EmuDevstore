<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/form/ACPForm.class.php');
require_once(WCF_DIR.'lib/data/message/smiley/SmileyEditor.class.php');
require_once(WCF_DIR.'lib/data/message/smiley/category/SmileyCategory.class.php');
require_once(WCF_DIR.'lib/system/io/Tar.class.php');

/**
 * Shows the smiley add form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.content.smiley
 * @subpackage	acp.form
 * @category 	Community Framework (commercial)
 */
class SmileyAddForm extends ACPForm {
	public $templateName = 'smileyAdd';
	public $activeMenuItem = 'wcf.acp.menu.link.smiley.add';
	public $neededPermissions = 'admin.smiley.canAddSmiley';
	
	public $upload;
	public $filename = '';
	public $title = '';
	public $code = '';
	public $showOrder = 0;
	public $smileyIDs = array();
	public $smileyCategoryID = 0;
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_FILES['upload'])) $this->upload = $_FILES['upload'];
		if (isset($_POST['filename'])) $this->filename = StringUtil::trim($_POST['filename']);
		if (isset($_POST['title'])) $this->title = StringUtil::trim($_POST['title']);
		if (isset($_POST['code'])) $this->code = StringUtil::trim($_POST['code']);
		if (isset($_POST['showOrder'])) $this->showOrder = intval($_POST['showOrder']);
		if (isset($_POST['smileyCategoryID'])) $this->smileyCategoryID = intval($_POST['smileyCategoryID']);
	}
	
	/**
	 * Validates upload file.
	 */
	protected function validateFile() {
		$savedSmilies = 0;
		WCF::getTPL()->assignByRef('savedSmilies', $savedSmilies);
		
		// upload smiley(s)
		if ($this->upload && $this->upload['error'] != 4) {
			if ($this->upload['error'] != 0) {
				throw new UserInputException('upload', 'uploadFailed');
			}
		
			// try to open file as an archive
			if (preg_match('/(?:tar\.gz|tgz|tar)$/i', $this->upload['name'])) {
				$errors = array();
				$tar = new Tar($this->upload['tmp_name']);
				foreach ($tar->getContentList() as $file) {
					if ($file['type'] != 'folder') {
						// extract to tmp dir
						$tmpname = FileUtil::getTemporaryFilename('smiley_');
						$tar->extract($file['index'], $tmpname);
						
						try {
							// find filename
							$i = 0;
							$destination = WCF_DIR.'images/smilies/'.basename($file['filename']);
							while (file_exists($destination)) {
								$destination = preg_replace('/((?=\.[^\.]*$)|(?=$))/', '_'.(++$i), $destination, 1);
							}
							
							// save
							$this->smileyIDs[] = SmileyEditor::create($tmpname, $destination, 'upload', null, null, ($this->showOrder ? $this->showOrder : null), $this->smileyCategoryID)->smileyID;
							$savedSmilies++;
						}
						catch (UserInputException $e) {
							$errors[] = array('filename' => $file['filename'], 'errorType' => $e->getType());
						}
					}
				}
				$tar->close();
				@unlink($this->upload['tmp_name']);
				
				if (count($errors)) {
					throw new UserInputException('upload', $errors);
				}
				else if ($savedSmilies == 0) {
					throw new UserInputException('upload', 'emptyArchive');
				}
			}
			else {
				$this->validateTitle();
				$this->validateCode();

				// import as image file
				$this->smileyIDs[] = SmileyEditor::create($this->upload['tmp_name'], WCF_DIR.'images/smilies/'.basename($this->upload['name']), 'upload', $this->title, $this->code, ($this->showOrder ? $this->showOrder : null), $this->smileyCategoryID)->smileyID;
				$savedSmilies++;
			}
		}
		// copy smiley(s)
		else if (!empty($this->filename)) {
			if (!file_exists($this->filename)) {
				throw new UserInputException('filename', 'notFound');
			}
			
			// copy smileys from a dir
			if (is_dir($this->filename)) {
				$errors = array();
				$this->filename = FileUtil::addTrailingSlash($this->filename);
				$handle = opendir($this->filename);
				while (($file = readdir($handle)) !== false) {
					if ($file != '.' && $file != '..' && is_file($this->filename . $file)) {
						try {
							// find filename
							$i = 0;
							$destination = WCF_DIR.'images/smilies/'.$file;
							while (file_exists($destination)) {
								$destination = preg_replace('/((?=\.[^\.]*$)|(?=$))/', '_'.(++$i), $destination, 1);
							}
							
							// save
							$this->smileyIDs[] = SmileyEditor::create($this->filename . $file, $destination, 'filename', null, null, ($this->showOrder ? $this->showOrder : null), $this->smileyCategoryID)->smileyID;
							$savedSmilies++;
						}
						catch (UserInputException $e) {
							$errors[] = array('filename' => $this->filename . $file, 'errorType' => $e->getType());
						}
					}
				}
				
				if (count($errors)) {
					throw new UserInputException('filename', $errors);
				}
				else if ($savedSmilies == 0) {
					throw new UserInputException('filename', 'emptyFolder');
				}
			}
			// simple file name
			else {
				$this->validateTitle();
				$this->validateCode();
				
				$this->smileyIDs[] = SmileyEditor::create($this->filename, WCF_DIR.'images/smilies/'.basename($this->filename), 'filename', $this->title, $this->code, ($this->showOrder ? $this->showOrder : null), $this->smileyCategoryID)->smileyID;
				$savedSmilies++;
			}
		}
		else {
			throw new UserInputException('upload');
		}
	}
	
	/**
	 * Validates the smiley code.
	 */
	public function validateCode() {
		if (empty($this->code)) {
			throw new UserInputException('code');
		}
		
		$sql = "SELECT	COUNT(*) AS count
			FROM	wcf".WCF_N."_smiley
			WHERE	smileyCode = '".escapeString($this->code)."'";
		$row = WCF::getDB()->getFirstRow($sql);
		if ($row['count']) {
			throw new UserInputException('code', 'notUnique');
		}
	}
	
	/**
	 * Validates the smiley title.
	 */
	public function validateTitle() {
		if (empty($this->title)) {
			throw new UserInputException('title');
		}
	}
	
	/**
	 * @see Form::validate()
	 */
	public function validate() {
		parent::validate();
		
		// category
		if ($this->smileyCategoryID != 0) {
			$smileyCategory = new SmileyCategory($this->smileyCategoryID);
			if (!$smileyCategory->smileyCategoryID) {
				throw new UserInputException('smileyCategoryID');
			}
		}
		
		// validate file upload / file name
		$this->validateFile();
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		parent::save();

		// reset values
		$this->filename = $this->title = $this->code = '';
		$this->showOrder = $this->smileyCategoryID = 0;
		
		// reset cache
		SmileyEditor::resetCache();
		$this->saved();
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'title' => $this->title,	
			'code' => $this->code,
			'showOrder' => $this->showOrder,
			'filename' => $this->filename,
			'smileyCategoryID' => $this->smileyCategoryID,
			'availableSmileyCategories' => SmileyCategory::getSmileyCategories()
		));
	}
}
?>
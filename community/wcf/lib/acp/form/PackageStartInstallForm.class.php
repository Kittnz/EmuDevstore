<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/package/PackageArchive.class.php');
require_once(WCF_DIR.'lib/acp/form/ACPForm.class.php');

/**
 * Shows the package install and update form.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category 	Community Framework
 */
class PackageStartInstallForm extends ACPForm {
	public $templateName = 'packageStartInstall';
	public $activeMenuItem = 'wcf.acp.menu.link.package.install';
		
	public $packageID = 0;
	public $package = null;
	public $downloadPackage = '';
	public $uploadPackage = '';
	public $archive = null;
	public $queueID;
	
	/**
	 * @see Form::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['activePackageID'])) {
			$this->packageID = intval($_REQUEST['activePackageID']);
			if ($this->packageID != 0) {
				try {
					require_once(WCF_DIR.'lib/acp/package/Package.class.php');
					$this->package = new Package($this->packageID);
				}
				catch (SystemException $e) {
					throw new IllegalLinkException();
				}
			}
		}
	}
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['downloadPackage'])) $this->downloadPackage = StringUtil::trim($_POST['downloadPackage']);
		if (isset($_FILES['uploadPackage'])) $this->uploadPackage = $_FILES['uploadPackage'];
	}
	
	/**
	 * @see Form::validate()
	 */
	public function validate() {
		parent::validate();
		
		if (!empty($this->uploadPackage['name'])) {
			$this->validateUploadPackage();
		}
		else if (!empty($this->downloadPackage)) {
			$this->validateDownloadPackage();
		}
		else {
			throw new UserInputException('uploadPackage');
		}
	}
	
	/**
	 * Validates the upload package input.
	 */
	protected function validateUploadPackage() {
		if (empty($this->uploadPackage['tmp_name'])) {
			throw new UserInputException('uploadPackage', 'uploadFailed');
		}
		
		// try to unzip zipped package files
		if (preg_match('!\.(tar\.gz|tgz)$!', basename($this->uploadPackage['name']))) {
			$tmpFilename = FileUtil::getTemporaryFilename('package_', '.tar');
			if (FileUtil::uncompressFile($this->uploadPackage['tmp_name'], $tmpFilename)) {
				@unlink($this->uploadPackage['tmp_name']);
				$this->uploadPackage['tmp_name'] = $tmpFilename;
			}
		}
		else {
			$tmpFilename = FileUtil::getTemporaryFilename('package_', '.tar');
			if (@copy($this->uploadPackage['tmp_name'], $tmpFilename)) {
				@unlink($this->uploadPackage['tmp_name']);
				$this->uploadPackage['tmp_name'] = $tmpFilename;
			}
		}
		
		$this->archive = new PackageArchive($this->uploadPackage['tmp_name'], $this->package);
		$this->validateArchive('uploadPackage');
	}
	
	/**
	 * Validates the download package input.
	 */
	protected function validateDownloadPackage() {
		if (FileUtil::isURL($this->downloadPackage)) {
			// download package
			$this->archive = new PackageArchive($this->downloadPackage, $this->package);
			
			try {
				$this->downloadPackage = $this->archive->downloadArchive();
				//$this->archive->downloadArchive();
			}	
			catch (SystemException $e) {
				throw new UserInputException('downloadPackage', 'notFound');
			}
		}
		else {
			// probably local path
			if (!file_exists($this->downloadPackage)) {
				throw new UserInputException('downloadPackage', 'notFound');
			}
			
			$this->archive = new PackageArchive($this->downloadPackage, $this->package);
		}
		
		$this->validateArchive('downloadPackage');
	}
	
	/**
	 * Validates the package archive.
	 * 
	 * @param	string		$type		upload or download package
	 */
	protected function validateArchive($type) {
		// try to open the archive
		try {
			$this->archive->openArchive();
		}
		catch (SystemException $e) {
			throw new UserInputException($type, 'noValidPackage');
		}
		
		// check update or install support
		if ($this->package !== null) {
			if (!$this->archive->isValidUpdate()) {
				throw new UserInputException($type, 'noValidUpdate');
			}	
		}
		else {
			if (!$this->archive->isValidInstall()) {
				throw new UserInputException($type, 'noValidInstall');
			}
			elseif ($this->archive->isAlreadyInstalled()) {
				throw new UserInputException($type, 'uniqueAlreadyInstalled');
			}
		}
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		parent::save();
		
		// get new process no
		require_once(WCF_DIR.'lib/acp/package/PackageInstallationQueue.class.php');
		$processNo = PackageInstallationQueue::getNewProcessNo();
		
		// set installationType for redirecting after finish installation
		$type = 'other';
		if ($this->package === null) {
			$type = 'install';
		}
		
		// insert queue entry
		$sql = "INSERT INTO	wcf".WCF_N."_package_installation_queue
					(processNo, userID, package, packageID, archive, action, confirmInstallation, cancelable, installationType)
			VALUES		(".$processNo.",
					".WCF::getUser()->userID.",
					'".escapeString($this->archive->getPackageInfo('name'))."',
					".$this->packageID.",
					'".escapeString((!empty($this->uploadPackage['tmp_name']) ? $this->uploadPackage['tmp_name'] : $this->downloadPackage))."',
					'".($this->package != null ? 'update' : 'install')."',
					1,
					".($this->package != null ? 0 : 1).",
					'".$type."')";
		WCF::getDB()->sendQuery($sql);
		$this->queueID = WCF::getDB()->getInsertID();
		$this->saved();
		
		// open queue
		HeaderUtil::redirect('index.php?page=Package&action=openQueue&processNo='.$processNo.'&packageID='.PACKAGE_ID.SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'packageID' => $this->packageID,
			'package' => $this->package
		));
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		if ($this->action == 'install') WCF::getUser()->checkPermission('admin.system.package.canInstallPackage');
		else WCF::getUser()->checkPermission('admin.system.package.canUpdatePackage');
		
		// check master password
		WCFACP::checkMasterPassword();
		
		parent::show();
	}
}
?>
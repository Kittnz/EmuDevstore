<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/package/PackageUninstallation.class.php');
require_once(WCF_DIR.'lib/acp/package/PackageArchive.class.php');

/**
 * PackageInstallationRollback extends PackageUninstallation for
 * a rollback during the installation of a package.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.package
 * @category 	Community Framework
 */
class PackageInstallationRollback extends PackageUninstallation {
	protected $packageArchive = null;
	
	/**
	 * @see PackageUninstallation::uninstall()
	 */
	protected function uninstall() {
		try {
			switch ($this->step) {
				case 'optionals':
					$this->nextStep = $this->uninstallOptionals();
					break;
				
				default:
					return parent::uninstall();
			}
		
			WCF::getTPL()->assign('nextStep', $this->nextStep);
			WCF::getTPL()->display('packageInstallationNext');
		}
		catch (SystemException $e) {
			$this->showPackageInstallationException($e);
		}
	}
	
	/**
	 * @see PackageUninstallation::finishInstallation()
	 */
	protected function finishUninstallation() {
		if ($this->packageArchive !== null) {
			$this->packageArchive->deleteArchive();
		}

		// unregister package installation plugins
		WCF::getSession()->unregister('queueID'.$this->queueID.'PIPs');
		
		// mark this package uninstallation as done
		$sql = "UPDATE	wcf".WCF_N."_package_installation_queue
			SET	done = 1
			WHERE	queueID = ".$this->queueID;
		WCF::getDB()->sendQuery($sql);
		// search for open queue children
		$sql = "SELECT		queueID, action
			FROM		wcf".WCF_N."_package_installation_queue
			WHERE		parentQueueID = ".$this->queueID."
					AND processNo = ".$this->processNo."
					AND done = 0
			ORDER BY	queueID";
		$row = WCF::getDB()->getFirstRow($sql);
		if (isset($row['queueID'])) {
			// entry found
			WCF::getTPL()->assign(array(
				'action' => $row['action'],
				'queueID' => $row['queueID']
			));
			
			return '';
		}
		else {
			// search for other open queue entries in current level
			$sql = "SELECT		queueID, action
				FROM		wcf".WCF_N."_package_installation_queue
				WHERE		parentQueueID = ".$this->parentQueueID."
						AND processNo = ".$this->processNo."
						AND done = 0
				ORDER BY	queueID";
			$row = WCF::getDB()->getFirstRow($sql);
			if (isset($row['queueID'])) {
				// other entries found
				WCF::getTPL()->assign(array(
					'action' => $row['action'],
					'queueID' => $row['queueID'],
					'processNo' => $this->processNo
				));
				
				if ($this->parentQueueID == 0) {
					// reload installation frame
					// and uninstall next package
					WCF::getTPL()->display('packageInstallationReloadFrame');
					exit;
				}
				else {
					// uninstall next package in current window
					return '';
				}
			}
			else {
				if ($this->parentQueueID == 0) {
					// nothing to do
					// finish uninstallation
					
					// delete all package installation queue entries with the active process number
					$sql = "DELETE FROM	wcf".WCF_N."_package_installation_queue
						WHERE		processNo = ".$this->processNo;
					WCF::getDB()->sendQuery($sql);
					
					// var to redirect to package list
					WCF::getTPL()->assign('installationType', 'other');
					
					// show finish page
					WCF::getTPL()->display('packageInstallationFinish');
					exit;
				}
				else {
					// jump to parent package uninstallation
					// get information about parent queue id
					WCF::getTPL()->assign(array(
						'action' => $this->action,
						'queueID' => $this->parentQueueID
					));
					
					if ($this->packageType == 'requirement') {
						return 'finish';
					}
					if ($this->packageType == 'optional') {
						return 'optionals';
					}
				}	
			}
		}
	}
	
	/**
	 * @see PackageInstallationQueue::loadPackageInstallationPlugins()
	 */
	protected function loadPackageInstallationPlugins() {
		$this->action = 'uninstall';
		parent::loadPackageInstallationPlugins();
		$this->action = 'rollback';
	}
	
	/**
	 * @see PackageInstallationQueue::executePackageInstallationPlugin()
	 */
	protected function executePackageInstallationPlugin($pluginName) {
		$this->action = 'uninstall';
		try { // ignore pip exceptions during the rollback
			parent::executePackageInstallationPlugin($pluginName);
		}
		catch (SystemException $e) {}
		$this->action = 'rollback';
	}
	
	/**
	 * Starts the uninstallation of delivered optional packages.
	 * 
	 * @return	string		next step
	 */
	protected function uninstallOptionals() {
		// get first optional
		$sql = "SELECT		queueID, action
			FROM		wcf".WCF_N."_package_installation_queue
			WHERE		parentQueueID = ".$this->queueID."
					AND packageType = 'optional'
					AND done = 0
					AND action = 'rollback'
			ORDER BY	queueID";
		$row = WCF::getDB()->getFirstRow($sql);
		if (isset($row['queueID'])) {
			WCF::getTPL()->assign(array(
				'action' => $row['action'],
				'queueID' => $row['queueID']
			));
			return '';
		}
		
		return 'requirements';
	}
	
	/**
	 * @see PackageInstallationQueue::assignPackageInfo()
	 */
	protected function assignPackageInfo() {
		if ($this->packageID == 0) {
			if ($this->packageArchive !== null) {
				try {
					$this->packageArchive->openArchive();
				}
				catch (SystemException $e) {} // ignore package errors in rollback
					
				WCF::getTPL()->assign(array(
					'packageName' => $this->packageArchive->getPackageInfo('packageName'),
					'packageDescription' => $this->packageArchive->getPackageInfo('packageDescription'),
					'packageVersion' => $this->packageArchive->getPackageInfo('version'),
					'packageDate' => $this->packageArchive->getPackageInfo('date'),
					'packageAuthor' => $this->packageArchive->getAuthorInfo('author'),
					'packageAuthorURL' => $this->packageArchive->getAuthorInfo('authorURL')
				));
			}
			else {
				WCF::getTPL()->assign(array(
					'packageName' => '',
					'packageDescription' => '',
					'packageVersion' => '',
					'packageDate' => '',
					'packageAuthor' => '',
					'packageAuthorURL' => ''
				));
			}
		}
		else {
			parent::assignPackageInfo();
		}
	}
	
	/**
	 * @see PackageUninstallation::checkPackageRequirements()
	 */
	protected function checkPackageRequirements() {
		if ($this->package->isRequired()) {
			// jump to rollback of next package in line
			return $this->finishUninstallation();
		}
		return 'execPackageInstallationPlugins';
	}
	
	
	/**
	 * @see PackageUninstallation::openPackage()
	 */
	protected function openPackage($info) {
		if (!empty($info['archive']) && file_exists($info['archive'])) {
			$this->packageArchive = new PackageArchive($info['archive'], null);
		}
		
		if ($this->packageID != 0) {
			parent::openPackage($info);
		}
	}
	
	/**
	 * @see PackageUninstallation::startUninstallation()
	 */
	protected function startUninstallation() {
		if ($this->parentQueueID != 0 && $this->packageID == 0) {
			return 'finish';
		}
		return parent::startUninstallation();
	}
	
	/**
	 * @see PackageUninstallation::startUninstallation()
	 */
	protected function showInstallationFrame() {
		if ($this->packageID == 0) {
			$this->nextStep = 'finish';
		}
		else {
			$this->nextStep = 'optionals';
		}
		WCF::getTPL()->assign('nextStep', $this->nextStep);
		WCF::getTPL()->display('packageInstallationFrame');
		exit;
	}
}
?>
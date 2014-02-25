<?php
// wcf imports
require_once(WCF_DIR.'lib/system/template/patch/TemplatePatchUnified.class.php');

/**
 * Gets diff hunks from a patch file and passes them to an object of a patch-type specific class
 * that applies them to the original file.
 * 
 * This class is only to be used with unified diffs!
 * 
 * @author	Siegfried Schweizer
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.template.patch
 * @category 	Community Framework
 */
class TemplatePatch {
	protected $packageID		= 0;		// the ID of the package that initiates this process.
	protected $templateID 		= 0;		// the ID of the template that's to be patched.
	protected $lines		= array();	// the contents of that file.
	protected $garbage		= array();	// lines that are neither context nor diff lines.
	protected $patch		= array();	// where the completed patches will be stored.
	protected $patchNo		= 0;		// the number of the patch (there might be multiple patches within a diff).
	protected $targetPosition	= 0;		// byte position in the original file.
	protected $regexCheckFile	= '';		// a regex that checks the first two lines of each diff.
	protected $tempOutputFile	= '';		// the name of the working temp file.
	protected $reverse		= false;	// if it is a patch or a reverse patch.
	protected $fuzzFactor		= 0;		// the fuzziness that may be applied to the patching algorithm.
	protected $inputFileHandle	= null;		// the filehandle for the original file.
	protected $outputFileHandle	= null;		// the filehandle for the output file.
	protected $templatePatchUnified	= null;		// an object instance of the class that actually applies the unidiff.
	protected $originalFile = '';
	
	/**
	 * Constructs a new Patch object.
	 * 
	 * @param	integer		$packageID		The ID of the package that initiates this process.
	 * @param	string		$patchFileString	The string containing the diff.
	 * @param	boolean		$reverse		If it is a patch or a reverse patch.
	 * @param	boolean		$rePatch		If this is a re-patching.
	 * @param	integer		$templateID		While reverse patching, we get the $templateID from the caller.
	 * @param	string		$type			If it is a template or an acp template.
	 * @param 	integer		$fuzzFactor
	 */
	public function __construct($packageID = 0, $patchFileString = '', $reverse = false, $rePatch = false, $templateID = 0, $type = '', $fuzzFactor = 0) {
		if ($patchFileString) {
			$this->packageID = $packageID;
			$this->reverse = $reverse;
			$this->rePatch = $rePatch;
			$this->templateID = $templateID;
			$this->type = $type;
			$this->fuzzFactor = $fuzzFactor;
			
			// read the diff string into an array.
			$lines = preg_split('/\r?\n|\r/', $patchFileString);
			
			// make sure to remove an eventual BOM at the very beginning of UTF-8 encoded files.
			// further care about the patchfile's encoding is not needed because we are only 
			// patching templates here which consist of ascii characters only.
			$lines['0'] = FileUtil::stripBoms($lines['0']);
			
			if ($this->rePatch === false && $this->reverse === false) {
				// needed for checking if there are successive files to be patched within this diff.
				// there is no sufficient "official" specification about how the format of the header lines
				// in a unified diff has to be, so we take into account practice and this web resource:
				// http://www.artima.com/weblogs/viewpost.jsp?thread=164293
				// unfortunately some non-GNU versions of patch do not respect the format mentioned above,
				// therefore a relatively simple regex is being used (we cannot check something we don't know
				// how it actually looks).
				$this->regexCheckFile = "%^([ ]*[\-|\+]{3}[ ])([\w\./\\\\]+)%";
				
				// lines at the beginning that are neither context nor diff lines are being omitted.
				foreach ($lines as $key => $line) {
					if ($line == '' || !((preg_match($this->regexCheckFile, $line, $result) == 1) && (preg_match($this->regexCheckFile, $lines[$key + 1], $result) == 1))) {
						unset($lines[$key]);
					} else {
						break;
					}
				}
				
				// remember positions where consecutive patches start.
				$lines = array_values($lines);
				$diffStarts = array();
				$i = 0;
				foreach ($lines as $key => $line) {
					if ((preg_match($this->regexCheckFile, $line, $result) == 1) && (preg_match($this->regexCheckFile, $lines[$key + 1], $result) == 1)) {
						$diffStarts[] = $key;
						$this->patch[$i]['patch'] = array();
						array_push($this->patch[$i]['patch'], $line);
						array_push($this->patch[$i]['patch'], $lines[$key + 1]);
						array_push($this->garbage, $line);
						array_push($this->garbage, $lines[$key + 1]);
						$i = $i + 1;
					}
				}
			}
			
			// if our diff contains more than one patch which means that there is more than one
			// file to be patched), we will split $lines into separate arrays where every array
			// contains the respective patch which is to be applied to the respective target file.
			if (($this->rePatch === true) || ($this->reverse === true) || (count($diffStarts) == 1)) {
				// if it's only one patch, we go ahead and attempt to apply it.
				$this->lines = $lines;
				$this->checkDiffType(0);
			} else {
				// add the end of the last patch within this diff to the $diffStarts array.
				$diffStarts[] = count($lines) + 1;
				// split the original array into the respective patches.
				$i = 0;
				foreach ($diffStarts as $key => $diffStart) {
					if ($diffStart < count($lines)) {
						$this->lines = array_slice($lines, $diffStart, $diffStarts[$key + 1] - $diffStart);
					}
					// start processing each patch.
					$this->garbage = array();
					$this->originalFile = '';
					$this->targetPosition = 0;
					if (count($this->lines)) {
						$this->checkDiffType($i);
						$i = $i + 1;
					}
				}
			}
			
			// delete reversed patches from database.
			if (($this->rePatch === false) && ($this->reverse === true)) {
				$this->deletePatch();
			}
			
		} else {
			throw new TemplatePatchException("No diff string has been given.", 20000);
		}
	}
	
	/**
	 * Every call to this function handles processing of a single patch out of the respective diff.
	 * For every hunk contained in this patch, a new instance of TemplatePatchUnified which handles 
	 * the actual application of of a single hunk is called.
	 * 
	 * @param	integer		$patchNo
	 */
	protected function checkDiffType($patchNo = 0) {
		// needed for checking range information lines of each hunk in the unified diff syntax.
		$regexCheckHunk = "%^(\s*)(\@\@ -(\d+)(?:,(\d+))? \+(\d+)(?:,(\d+))? \@\@)$%";
		
		$isUnifiedDiff = false;
		$hunk = array();
		$hunkNo = 0;
		$this->patchNo = $patchNo;
		
		// create a filehandle to the output file.
		if (!$this->tempOutputFile = FileUtil::getTemporaryFilename('patchOutput_')) {
			throw new TemplatePatchException("Can't create tempfile for output.", 20001);
		}
		
		if (!$this->outputFileHandle = fopen($this->tempOutputFile, 'wb+')) {
			throw new TemplatePatchException("Can't open tempfile for output.", 20002);
		}
		
		// go through the lines of the patchfile.
		foreach ($this->lines as $key => $line) {
			
			// if this really is a unified diff, we start processing the contents of the patchfile.
			// other diff types are not being supported at present.
			if (preg_match($regexCheckHunk, $line, $result) == 1) {
				
				if ($this->rePatch === false && $this->reverse === false) array_push($this->patch[$this->patchNo]['patch'], $line);
				
				// don't need this line anymore.
				array_push($this->garbage, $line);
				unset($this->lines[$key]);
				
				$isUnifiedDiff = true;
				
				// store diff info.
				if (empty($result[4])) $result[4] = 1;
				if (empty($result[6])) $result[6] = 1;
				$diffInfo = array(
					'space'		=>		trim($result[1]),
					'range'		=>		trim($result[2]),
					'i_start'	=>		trim($result[3]),
					'i_lines'	=>		trim($result[4]),
					'o_start'	=>		trim($result[5]),
					'o_lines'	=>		trim($result[6])
				);
				
				// build the array needed for checking the end of each addition/deletion lines block
				// within the respective diff.
				$saw = array(
					' '		=>		0,
					'+'		=>		0,
					'-'		=>		0
				);
				
				// checks if a diff line is an addition line or a deletion line.
				$regex = "/^".$diffInfo['space']."([ +-])/";
				
				foreach ($this->lines as $subkey => $diffline) {
					
					// if this is an addition line or a deletion line, we store it into a special array.
					if (preg_match($regex, $diffline, $result) && $diffline == (preg_replace($regex, $result[1], $diffline))) {
						
						if ($this->rePatch === false && $this->reverse === false) {
							array_push($this->patch[$this->patchNo]['patch'], $diffline);
						} elseif ($this->reverse === true) {
							// inverse the indicator characters of the presumable hunk lines.
							$diffline = preg_replace_callback("/^(\+|\-)/", create_function('$match', 'return $match[1] == "+" ? "-" : "+";'), $diffline);
						}
						
						// add this line to the hunks array.
						array_push($hunk, $diffline);
						unset($this->lines[$subkey]);
						
						// stop this if we're at the end of this patch.
						$saw[$result[1]]++;
						if (($saw['-'] + $saw[' '] == $diffInfo['i_lines']) && ($saw['+'] + $saw[' '] == $diffInfo['o_lines'])) {
							break;
						}
						
					} else {
						continue;
					}
				}
				
				// if the name of the file that's about to be patched has been omitted in the http request 
				// calling this class, we will try to read this filename from the diff file.
				if ($this->rePatch === false && $this->reverse === false) {
					$this->originalFile = $this->rummage();
				} else {
					$this->originalFile = $this->getTemplatePath();
				}
				
				// @todo: make sure to fclose all filehandles after processing!
				// get a filehandle to the original file.
				if (!$this->inputFileHandle = fopen($this->originalFile, 'rb')) {
					throw new TemplatePatchException("Can't open original file ".$this->originalFile.".", 20003, $this->originalFile);
				}
				
				// attempt to apply this hunk.
				$hunkNo = $hunkNo + 1;
				$this->templatePatchUnified = new TemplatePatchUnified($this->originalFile, $this->inputFileHandle, $this->outputFileHandle, $diffInfo, $hunk, $hunkNo, $this->targetPosition, $this->fuzzFactor);
				
				// remember the byte position in the target.
				$this->targetPosition = $this->templatePatchUnified->i_pos; // @todo: replace variable names in the PatchUnified like this one
											    // with better ones according to our coding style.
				$hunk = array();
				
			} else {
				// lines that are neither context nor diff lines are being omitted.
				array_push($this->garbage, $line);
				unset($this->lines[$key]);
			}
		}
		
		// there is no correct range information line, so we can't be sure that this is a unified patch.
		if ($isUnifiedDiff === false) {
			throw new TemplatePatchException("This is not a unified diff -- aborting.", 20004);
		} else {
			if (($this->rePatch === false) && ($this->reverse === false) && isset($this->patch[$patchNo])) {
				// save the applied patches to the database (with comments and blank lines omitted).
				$patchString = implode("\n", $this->patch[$patchNo]['patch']);
				$this->savePatch($patchString, $this->patch[$patchNo]['templateID']);
			}
			// copy the patched tempfile to the final destination.
			$this->copyPatchedFile();
			// clean up.
			$this->end();
		}
	}
	
	/**
	 * Go through leading garbage looking for name of file to patch.
	 */
	protected function rummage() {
		if (count($this->garbage) > 0) {
			$result = array();
			foreach ($this->garbage as $line) {
				if (preg_match("/^([ ]*[\-]{3}[ ])(.+\.tpl)/", $line, $result)) {
					// omit eventual paths to the file to patch.
					$pieces = preg_split("/[\/|\\\\]+/", $result['2']); 
					$path = $this->getTemplatePath($pieces[count($pieces) - 1]);
					if (file_exists($path))  {
						return $path;
					}
				} else {
					continue;
				}
			}
			return;
		}
		return;
	}
	
	/**
	 * Get path to the name of the template from database.
	 * 
	 * @param	string		$templateName
	 * @return	string		$templatePath
	 */
	protected function getTemplatePath($templateName = '') {
		if ($templateName) {
			$parts = explode('.', $templateName);
			$templateNameShort = $parts['0'];
		}
		
		if ($this->reverse === true) {
			$sql = "SELECT		package.packageDir, template.templateName
				FROM		wcf".WCF_N."_".$this->type."template template
				LEFT JOIN	wcf".WCF_N."_package package
						ON (template.packageID = package.packageID)
				WHERE		template.templateID = ".$this->templateID;
		} elseif ($this->rePatch === true) {
			$sql = "SELECT		package.packageDir, template.templateName
				FROM		wcf".WCF_N."_".$this->type."template template
				LEFT JOIN	wcf".WCF_N."_package package
						ON (template.packageID = package.packageID)
				WHERE		template.templateID = ".$this->templateID."
						AND template.packageID = ".$this->packageID;
		} else {
			$sql = "SELECT		template.packageID
				FROM		wcf".WCF_N."_".$this->type."template template
				LEFT JOIN	wcf".WCF_N."_package_dependency dependency
				ON		dependency.dependency = template.packageID
				WHERE		dependency.packageID = ".$this->packageID."
						AND template.templateName = '".escapeString($templateNameShort)."'
				ORDER BY	template.packageID DESC";
			$result = WCF::getDB()->getFirstRow($sql);
			
			if ($result) {
				$sql = "SELECT		package.packageDir, template.templateID
					FROM		wcf".WCF_N."_".$this->type."template template
					LEFT JOIN	wcf".WCF_N."_package package
							ON (template.packageID = package.packageID)
					WHERE		template.templateName = '".escapeString($templateNameShort)."'
							AND template.packageID = ".$result['packageID']."
					ORDER BY	template.templateID DESC";
			} else return null;
		}
		$result = WCF::getDB()->getFirstRow($sql);
		if ($result) {
			if ($this->rePatch === false && $this->reverse === false) {
				$this->patch[$this->patchNo]['templateID'] = $result['templateID'];
			} else {
				$templateName = $result['templateName'].'.tpl';
			}
			if ($this->type ? $sub = 'acp/' : $sub = '');
			return WCF_DIR.$result['packageDir'].$sub.'templates/'.$templateName;
		}
	}
	
	/**
	 * Write trailing lines which may be left in the input filehandle to the output filehandle.
	 */
	protected function tail() {
		while (!feof($this->inputFileHandle)) {
			$tailLine = fgets($this->inputFileHandle);
			fwrite($this->outputFileHandle, $tailLine);
		}
	}
	
	/**
	 * Copy the resulting patched file to its appropriate destination.
	 */
	protected function copyPatchedFile() {
		$this->tail();
		if (!copy($this->tempOutputFile, $this->originalFile)) { // @todo!!!
			throw new TemplatePatchException("Patched file cannot be copied to target ".$this->originalFile, 20006, $this->originalFile);
		}
	}
	
	/**
	 * Save patch to database in order to be able to remove it 
	 * in case the package that installs this patch is being deinstalled.
	 * 
	 * @param	string		$patch
	 * @param	integer		$templateID
	 */
	protected function savePatch($patch = '', $templateID = 0) {
		$sql = "INSERT INTO		wcf".WCF_N."_".$this->type."template_patch 
						(packageID, templateID, patch, success, fuzzFactor) 
			VALUES			(".$this->packageID.", 
						".$templateID.", 
						'".escapeString($patch)."', 
						1, 
						".$this->fuzzFactor.")";
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * Remove a patch from database after reverse applying it 
	 * in case the package that installs this patch is being deinstalled.
	 */
	protected function deletePatch() {
		$sql = "DELETE FROM		wcf".WCF_N."_".$this->type."template_patch 
			WHERE			packageID = ".$this->packageID;
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * Clean up after applying all hunks.
	 */
	protected function end() {
		fclose($this->inputFileHandle);
		fclose($this->outputFileHandle);
		unlink($this->tempOutputFile);
	}
}
?>
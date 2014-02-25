<?php
/**
 * Applies a patch hunk to an original file resulting in a patched file.
 * Subsequent hunks in one patch require subsequent instances of this class.
 * 
 * This class is only to be used with unified diffs!
 * This is still a work in progress!
 * 
 * @author	Siegfried Schweizer
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.template.patch
 * @category 	Community Framework
 */
class TemplatePatchUnified {
	public $i_pos			= 0;		// current byte position in the 'in' file -- important for the calling class!
	protected $fuzzFactor		= 2;		// the fuzziness that may be applied to the patching algorithm.
	protected $i_start		= 0;		// what is this?
	protected $i_lines		= 0;		// lines read in 'in' file
	protected $o_start		= 0;		// what is this?
	protected $o_lines		= 0;		// lines written to 'out' file
	protected $o_fh			= null;		// another filehandle for output.
	protected $i_fh			= null;		// input filehandle (the original file that's about to be patched).
	protected $newLine		= array();	// the type of linebreaks being used in the original file.
	protected $originalfile		= '';
	
	/**
	 * Constructs a new Patch object.
	 * 
	 * @param	string		$originalfile		the path to the original file. @todo: care for this!
	 * @param 	integer		$inputFileHandle
	 * @param 	integer		$outputFileHandle
	 * @param	array		$diffInfo		information extracted from the range information line that denotes the respective hunk.
	 * @param	array		$hunk			an array containing the lines of the respective hunk.
	 * @param	integer		$hunkNo			the number of the hunk within the patchfile.
	 * @param	integer		$targetPosition		the byte position within the original file where to start searching for the context of the respective hunk.
	 * @param 	integer		$fuzzFactor
	 */
	public function __construct($originalfile = '', $inputFileHandle, $outputFileHandle, $diffInfo = array(), $hunk = array(), $hunkNo = 0, $targetPosition = 0, $fuzzFactor = 0) {
		if ($originalfile != '') {
			$this->originalfile = $originalfile;

			// byte position in the original file (the one that's about to be patched)
			$this->i_pos = $targetPosition;
			
			// more data
			$this->space		= $diffInfo['space'];
			$this->range		= $diffInfo['range'];
			$this->i_start		= $diffInfo['i_start'];
			$this->i_lines		= $diffInfo['i_lines'];
			$this->o_start		= $diffInfo['o_start'];
			$this->o_lines		= $diffInfo['o_lines'];
			$this->hunk		= $hunk;
			$this->hunkNo		= $hunkNo;
			$this->fuzzFactor	= $fuzzFactor;
			
			// the filehandles for the original file and for the patched output.
			$this->i_fh = $inputFileHandle;
			$this->o_fh = $outputFileHandle;
			
			// detect which line breaks are being used in the original file.
			$this->detectLineBreaks($this->i_fh);
			
			// go on with the patch process.
			$this->apply();
			
		} else {
			throw new TemplatePatchException("No file containing a diff has been given.", 20000, $originalfile);
		}
	}
	
	/**
	 * Apply one patch hunk (a block of lines which makes up one specific patch)
	 * to the target file.
	 */
	public function apply() {
		// get the context in the destination file which is going to be patched.
		$context = array();
		foreach ($this->hunk as $hunkLine) {
			if (preg_match('/^[ -](.*)/s', $hunkLine, $result) === 1) {
				$context[] = $result['1'];
			}
		}
		
		$position = array();
		$posLines = array();
		$pos = $this->i_pos;
		$fuzz = 0;
		$lines = 0;
		
		if (count($context)) {
			// find a place in the destination file to apply the hunk where the context matches.
			for($fuzz = 0; $fuzz <= $this->fuzzFactor; $fuzz++) {
				while (1) {
					$posLines = $this->index($context, $pos, $lines);
					
					if (count($posLines)) {
						$pos = $posLines['pos'];
						$lines = $posLines['lines'];
					} else break;
					
					$lineIn = $this->i_lines + $lines + 1;
					if ($lineIn >= $this->i_start) {
						$off1 = $lineIn - $this->i_start;
						if (!(count($position) && $position[count($position) - 1] < $off1)) {
							$position = array($lines, $off1);
						}
						break;
					}
					
					$position = array($lines, $this->i_start - $lineIn);
					$pos++;
					$lines = 1;
				}
				
				if (count($position)) break;
				if (!(preg_match('/^ /', $this->hunk[0]) && array_shift($this->hunk)) || 
					!(preg_match('/^ /', $this->hunk[count($this->hunk) - 1]) && array_pop($this->hunk))) {
					break;
				}
				
				$hunkLines = array();
				foreach ($this->hunk as $hunkLine) {
					if (preg_match('/^[ -](.*)/s', $hunkLine)) $hunkLines[] = StringUtil::substring($hunkLine, 1);
				}
				if (count($hunkLines)) $context = $hunkLines;
				else break;
			}
			
			if (!count($position) || $position == 0) {
				throw new TemplatePatchException("Can't find an appropriate location for applying this hunk.", 20015, $this->originalfile);
			}
			
		} else {
			// No context. Use given position.
			$position[] = $this->i_start - $this->i_lines - 1;
		}
		
		$in = $this->i_fh;
		$out = $this->o_fh;
		
		// Make sure we're at the point where we left off.
		if (fseek($in, $this->i_pos, 0) == -1) {
			throw new TemplatePatchException("Could not set byte position in the original file.", 20010, $this->originalfile);
		}
		
		// @todo: $line is now a new variable, maybe better rename it for better distinction.
		$line = $this->o_lines + $position['0'] + 1;
		
		// Set to new position.
		$this->i_lines += $position['0'];
		$this->o_lines += $position['0'];
		
		// write first lines from the destination file to the output filehandle.
		while ($position['0'] > 0) {
			fwrite($out, fgets($in));
			$position['0']--;
		}
		
		// Apply hunk.
		foreach ($this->hunk as $hunkLine) {
			$regex = '/^([ +-])(.*)/s';
			if (preg_match($regex, $hunkLine) === 0) {
				continue;
			}
			
			$cmd = substr($hunkLine, 0, 1);
			$regex = "/^([ +-])/s";
			$hunkLine = preg_replace($regex, '', $hunkLine);
			
			if ($cmd == '-') {
				// shift one line ahead in the original file. this line is being omitted.
				fgets($in);
				$this->i_lines++;
			} elseif ($cmd == '+') {
				// this line is being added to the output file.
				$my = $this->putLineBreaks($hunkLine);
				fwrite($out, $my);
				$this->o_lines++;
			} else {
				// shift one line ahead in the original file. this line just stays unchanged.
				fgets($in);
				$my = $this->putLineBreaks($hunkLine);
				fwrite($out, $my);
				$this->i_lines++;
				$this->o_lines++;
			}
		}
		// Keep track of where we leave off.
		$this->i_pos = ftell($in);
	}
	
	/**
	 * Find where an array of lines matches in a file after a given position.
	 * $match  => [array containing the context lines]
	 * $pos    => search after this position and...
	 * $lines  => ...after this many lines after $pos
	 * Returns the position of the match and the number of lines between the
	 * starting and matching positions.
	 * 
	 * @param	array		$match
	 * @param	integer		$pos
	 * @param	integer		$lines
	 * @return	array		$found
	 */
	public function index($match = array(), $pos = 0, $lines = 0) {
		$in = $this->i_fh;
		
		if (fseek($in, $pos, 0) == -1) {
			throw new TemplatePatchException("Could not set byte position in the original file.", 20010, $this->originalfile);
		}
		
		while ($lines > 0) {
			// get the next line of the filehandle.
			$line = fgets($in);
			$lines--;
		}
		
		$tell = ftell($in);
		$lineNo = 0;
		
		while (!feof($in)) {
			$line = fgets($in);
			
			// remove all trailing newlines.
			$line = $this->removeLineBreaks($line);
			$match['0'] = $this->removeLineBreaks($match['0']);
			
			if ($line === $match['0']) {
				
				$fail = 0;
				for ($i = 1; $i < count($match); $i++) {
					// get the next line of the filehandle.
					$nextLine = fgets($in);
					
					$nextLine = $this->removeLineBreaks($nextLine);
					$match[$i] = $this->removeLineBreaks($match[$i]);
					
					if ($nextLine !== $match[$i]) {
						$fail++;
						break; // get out of the for loop.
					}
				}
				
				if ($fail > 0) {
					if (fseek($in, $tell, 0) == -1) {
						throw new TemplatePatchException("Could not set byte position in the original file.", 20010, $this->originalfile);
					} else {
						// get the next line of the filehandle.
						$nextLine = fgets($in);
					}
				} else {
					return array(
						'pos' => $tell,
						'lines' => $lineNo
						);
				}
			}
			$lineNo++;
			$tell = ftell($in);
		}
		return array();
	}
	
	/**
	 * Detect which type of line break is being used in the original file
	 * in order to preserve a consistent use of line breaks in the patched file.
	 * 
	 * @param	resource	$fileHandle		A filehandle to the original input file.
	 */
	public function detectLineBreaks($fileHandle) {
		
		// read newline characters from the original input file.
		$lineBreaks = '';
		while(!feof($fileHandle)) {
			$char = fgetc($fileHandle);
			if (preg_match('/\r?\n|\r/', $char) == 1) {
				$lineBreaks .= $char;
			}
			if (strlen($lineBreaks) == 2) break; 
		}
		
		// set back file pointer to position zero where it was before the fgetc stuff.
		rewind($fileHandle);
		
		// put the $line's bytes to an array.
		$workArray = unpack('C*', $lineBreaks);
		if (!is_array($workArray)) {
			throw new TemplatePatchException("Unable to disassemble line of original file into bytes.", 20011, $this->originalfile);
		}
		
		if (count($workArray) == 1) {
			$this->newLine = array($workArray['1']); // non-windows
		} elseif (intval($workArray[count($workArray)]) == 10 && intval($workArray[count($workArray) - 1]) == 13) {
			$this->newLine = array(13, 10); // windows
		} elseif (intval($workArray[count($workArray)]) == 10) {
			$this->newLine = array(10); // unix
		} elseif (intval($workArray[count($workArray)]) == 13) {
			$this->newLine = array(13); // pre-os x mac
		} elseif (intval($workArray[count($workArray)]) == 21) {
			$this->newLine = array(21); // aix
		}
	}
	
	/**
	 * Remove all line breaks.
	 * 
	 * @param	string		$line
	 * @return	string		$line
	 */
	public function removeLineBreaks($line) {
		$regexNewlines = '/\r?\n|\r/';
		$line = preg_replace($regexNewlines, '', $line);
		return $line;
	}
	
	/**
	 * Replace line breaks of added lines with the same ones like in the original file.
	 * 
	 * @param	string		$line
	 * @return	string		$line
	 */
	public function putLineBreaks($line) {
		// disassemble one line of the original file into its bytes.
		$input = unpack('C*', $line);
		if (!is_array($input)) {
			throw new TemplatePatchException("Unable to disassemble line of original file into bytes.", 20011, $this->originalfile);
		}
		$result = array_merge($input, $this->newLine);
		array_values($result);
		
		// put the bytes back together.
		$line = '';
		foreach ($result as $value) {
			$line .= pack('C*', $value);
		}
		return $line;
	}
}
?>
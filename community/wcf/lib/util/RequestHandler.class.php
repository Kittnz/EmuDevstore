<?php
/**
 * Handles http request.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	util
 * @category 	Community Framework
 */
class RequestHandler {
	private static $activeRequest = null;
	public $type = '', $page = '', $action = '', $form = '';
	protected $controllerObj = null;
	
	/**
	 * Creates a new RequestHandler object.
	 *
	 * @param 	string 		$className
	 * @param 	array 		$applicationDir
	 * @param 	string 		$type
	 */
	protected function __construct($className, $applicationDir, $type) {
		self::$activeRequest = $this;
		
		try {
			// validate class name
			if (!preg_match('/^[a-z0-9_]+$/i', $className)) {
				throw new SystemException("Illegal class name '".$className."'", 11009);
			}
			
			// find class
			$className = ucfirst($className).ucfirst($type);
			$classPath = $type.'/'.$className.'.class.php';
			$found = false;
			foreach ($applicationDir as $dir) {
				if (file_exists($dir . $classPath)) {
					$classPath = $dir . $classPath;
					$found = true;
					break;
				}
			}
			
			if (!$found) {
				throw new SystemException("unable to find class file '".$classPath."'", 11000);
			}
		}
		catch (SystemException $e) {
			throw new IllegalLinkException();
		}
		
		// define vars
		$this->type = $type;
		$this->$type = $className;
		
		// include class
		require_once($classPath);
		
		// execute class
		if (!class_exists($className)) {
			throw new SystemException("unable to find class '".$className."'", 11001);
		}
		$this->controllerObj = new $className();
	}
	
	/**
	 * Returns the instance of the active controller class.
	 * 
	 * @return	Page 
	 */
	public function getController() {
		return $this->controllerObj;
	}
	
	/**
	 * Returns the active request object.
	 *
	 * @return	RequestHandler
	 */
	public static function getActiveRequest() {
		return self::$activeRequest;
	}
	
	/**
	 * Handles a http request
	 *
	 * @param	array		$applicationDir		library directories
	 */
	public static function handle($applicationDir) {
		if (!empty($_GET['page']) || !empty($_POST['page'])) {
			new RequestHandler((!empty($_GET['page']) ? $_GET['page'] : $_POST['page']), $applicationDir, 'page');
		}
		else if (!empty($_GET['form']) || !empty($_POST['form'])) {
			new RequestHandler((!empty($_GET['form']) ? $_GET['form'] : $_POST['form']), $applicationDir, 'form');
		}
		else if (!empty($_GET['action']) || !empty($_POST['action'])) {
			new RequestHandler((!empty($_GET['action']) ? $_GET['action'] : $_POST['action']), $applicationDir, 'action');
		}
		else {
			new RequestHandler('Index', $applicationDir, 'page');
		}
	}
}
?>
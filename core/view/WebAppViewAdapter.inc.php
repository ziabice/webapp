<?php

/**
 * (c) 2012 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */
 
/**
 * Base implementation of WebAppViewInterface.
 * 
 * @author Luca Gambetta <l.gambetta@bluenine.it>
 *
 */
class WebAppViewAdapter implements WebAppViewInterface {
	
	protected
		/**
		 * @var BasePageLayout
		 */
		$page_layout = NULL;
	
	public function __construct() {
		$this->reset();
	}
	
	/**
	 * Includes the standard page layout helper.
	 */
	public function includeHelper() {
		require_once WEBAPP_CORE_PATH.DIRECTORY_SEPARATOR.'page'.DIRECTORY_SEPARATOR.'PageLayoutHelper.inc.php';
	}
	
	public function reset() {
		$this->page_layout = NULL;
	}
	
	/**
	* Initializes the page layout manager.
	*
	*/
	public function initialize() {
		return TRUE;
	}
	
	/**
	 * Tells if a page layout manager is present.
	 *
	 * @return boolean TRUE if there's a page layout manager, FALSE otherwise
	 */
	public function hasPageLayout() {
		return is_object( $this->page_layout );
	}
	
	/**
	 * Returns the page layout manager
	 * 
	 * @return BasePageLayout the layout manager
	 */
	public function getPageLayout() {
		return $this->page_layout;
	}
	
	/**
	 * Sets the page layout manager.
	 *
	 * @param BasePageLayout $page_layout the page layout manager
	 * @return BasePageLayout l'oggetto aggiunto
	 */
	public function setPageLayout(BasePageLayout $page_layout) {
		$this->page_layout = $page_layout;
		return $this->page_layout;
	}

	/**
	 * Ritorna il percorso di un file di View.
	 * Se il nome è esplicitato viene cercato nei seguenti percorsi:
	 *
	 * %modulocorrente%/view/%nomefile%
	 * %pathapplicazione%/view/%nomefile%
	 *
	 * Altrimenti (nome del file vuoto) utilizza i seguenti percorsi, che si basano sull'azione
	 * corrente:
	 *
	 * %modulocorrente%/view/%azionecorrente%.html|.php|.phtml
	 * %modulocorrente%/view/%modulocorrente%.html|.php|.phtml
	 * %modulocorrente%/view/layout.html|.php|.phtml
	 * %pathapplicazione%/view/layout.html|.php|.phtml
	 *
	 * Se il file non è presente ritorna FALSE.
	 *
	 * @param string $filename stringa vuota o col nome del file da cercare (un file html)
	 * @return string|boolean con il percorso o FALSE se il file non esiste
	 * */
	public function getViewPath($filename = '') {
		$paths = array();
		
		if (empty($filename)) {
			if (WebApp::getInstance()->hasCurrentModule()) {
				$modulepath = WebApp::getInstance()->getModulePath( WebApp::getInstance()->getCurrentModule() );
				if (WebApp::getInstance()->hasCurrentAction()) {
					$paths[] = $modulepath.'view'.DIRECTORY_SEPARATOR.strtolower(WebApp::getInstance()->getCurrentAction()).'.html';
					$paths[] = $modulepath.'view'.DIRECTORY_SEPARATOR.strtolower(WebApp::getInstance()->getCurrentAction()).'.php';
					$paths[] = $modulepath.'view'.DIRECTORY_SEPARATOR.strtolower(WebApp::getInstance()->getCurrentAction()).'.phtml';
				}
				$paths[] = $modulepath.'view'.DIRECTORY_SEPARATOR.strtolower( WebApp::getInstance()->getCurrentModule() ).'.html';
				$paths[] = $modulepath.'view'.DIRECTORY_SEPARATOR.strtolower( WebApp::getInstance()->getCurrentModule() ).'.php';
				$paths[] = $modulepath.'view'.DIRECTORY_SEPARATOR.strtolower( WebApp::getInstance()->getCurrentModule() ).'.phtml';
				$paths[] = $modulepath.'view'.DIRECTORY_SEPARATOR.'layout.html';
				$paths[] = $modulepath.'view'.DIRECTORY_SEPARATOR.'layout.php';
				$paths[] = $modulepath.'view'.DIRECTORY_SEPARATOR.'layout.phtml';
			}
			// Mette il layout standard
			$paths[] = WebApp::getInstance()->getPath().'view'.DIRECTORY_SEPARATOR.'layout.html';
			$paths[] = WebApp::getInstance()->getPath().'view'.DIRECTORY_SEPARATOR.'layout.php';
			$paths[] = WebApp::getInstance()->getPath().'view'.DIRECTORY_SEPARATOR.'layout.phtml';
			$paths[] = WEBAPP_BASE_PATH.'view'.DIRECTORY_SEPARATOR.'layout.html';
			$paths[] = WEBAPP_BASE_PATH.'view'.DIRECTORY_SEPARATOR.'layout.php';
			$paths[] = WEBAPP_BASE_PATH.'view'.DIRECTORY_SEPARATOR.'layout.phtml';
		} else {
			if (WebApp::getInstance()->hasCurrentModule()) {
				$paths[] = WebApp::getInstance()->getModulePath( WebApp::getInstance()->getCurrentModule() ).'view'.DIRECTORY_SEPARATOR.$filename;
			}
			$paths[] = WebApp::getInstance()->getPath().'view'.DIRECTORY_SEPARATOR.$filename;
			$paths[] = WEBAPP_BASE_PATH.'view'.DIRECTORY_SEPARATOR.$filename;
		}
	
		foreach($paths as $p) if (@file_exists($p)) return $p;
		Logger::getInstance()->debug('WebAppViewAdapter::getViewPath: file not found: \''.$filename.'\'');
		return FALSE;
	}
}

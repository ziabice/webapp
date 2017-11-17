<?php

/**
 * (c) 2012-2013 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */
 
/**
 * Permette alla WebApp di selezionare il page layout da un file di template XHTML.
 * 
 * É il view di default basato su file.
 * 
 * @author Luca Gambetta <l.gambetta@bluenine.it>
 *
 */
class WebAppTemplateView extends WebAppViewAdapter {
	
	public function __construct() {
		parent::__construct();
		
		$this->includeHelper();
	}
	
	/**
	* Inizializza il View.
	*
	* Procede prima caricando un PageLayout personalizzato mediante  {@link loadPageLayout} e
	* poi il fallback con {@link setDefaultPageLayout()}
	*/
	public function initialize() {
		return $this->loadPageLayout( WebApp::getInstance()->getCurrentModule(), WebApp::getInstance()->getCurrentAction() );
	}
	
	/**
	 *
	 * Imposta il gestore di pagina attuale (imposta il View).
	 *
	 * Il layout è una sottoclasse di PageLayout che viene cercato nelle seguenti directory:
	 * - %modulocorrente%/view/%azionecorrente%.inc.php
	 * - %modulocorrente%/view/%modulocorrente%.inc.php
	 * - %modulocorrente%/view/layout.inc.php
	 * - %pathapplicazione%/view/layout.inc.php
	 *
	 * Il file viene incluso e viene costruita la classe col nome:
	 * - NomeAzioneLayout
	 * - NomeModuloLayout
	 * - SiteLayout
	 * - PageLayout
	 * - BasePageLayout
	 *
	 * Dove "NomeModulo" e "NomeAzione" sono la versione camel case fornita da {@link camelCase} del
	 * nome del modulo attuale e dell'azione attuale.
	 *
	 *
	 * Al termine del processo {@link getPageLayout} ritornerà il gestore di pagina
	 *
	 * @param string $module modulo per cui caricare il layout
	 * @param string $action azione del modulo per cui caricare il layout
	 * @return boolean TRUE se ha istanziato il layout, FALSE altrimenti
	 */
	public function loadPageLayout($module, $action = '') {
		$paths = array();
		$layout_class = array();
		// Se siamo qui l'azione è stata eseguita, quindi abbiamo un modulo
		if (!empty($module)) {
			if (!empty($action)) {
				$paths[] = WebApp::getInstance()->getModulePath( $module ).'view'.DIRECTORY_SEPARATOR.strtolower($action).'.inc.php';
				$layout_class[] = WebApp::getInstance()->camelCase($action).'Layout';
			}
			$paths[] = WebApp::getInstance()->getModulePath( $module ).'view'.DIRECTORY_SEPARATOR.strtolower($module).'.inc.php';
			$paths[] = WebApp::getInstance()->getModulePath( $module ).'view'.DIRECTORY_SEPARATOR.'layout.inc.php';
			$layout_class[] = WebApp::getInstance()->camelCase($module).'Layout';
		}
		
		// Mette il layout personalizzato dall'utente
		$paths[] = WebApp::getInstance()->getPath().'view'.DIRECTORY_SEPARATOR.'layout.inc.php';
		$layout_class[] = 'SiteLayout';
		$layout_class[] = 'PageLayout';
		$layout_class[] = 'BasePageLayout';
		foreach($paths as $p) {
			if (@file_exists($p)) {
				require_once $p;
				foreach($layout_class as $page_layout_class) {
					if (class_exists($page_layout_class) && is_subclass_of($page_layout_class, 'BasePageLayout')) {
						$l = new $page_layout_class ( WebApp::getInstance()->getEncoding() );
						$this->setPageLayout($l);
						$l->setTemplateFilename( $this->getViewPath() );
						$l->initialize();
						return TRUE;
					}
				}
			}
		}
		
		// Non ha trovato nessun gestore di layout personalizzato, usa quello standard
		if (class_exists('PageLayout')) {
			$l = new PageLayout ( WebApp::getInstance()->getEncoding() );
			$this->setPageLayout($l);
			$l->setTemplateFilename( $this->getViewPath() );
			$l->initialize();
			return TRUE;
		}
		
		return FALSE;
	}

}

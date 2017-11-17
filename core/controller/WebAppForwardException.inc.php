<?php 
/**
 * (c) 2008, 2009 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Eccezione sollevata quando una azione fa forward verso un'altra azione
 */
class WebAppForwardException extends Exception {
	protected
	  $module, $action;

	  /**
	   * Inizializza col nome del modulo e l'azione a cui saltare
	   * se l'azione è una stringa vuota, viene ignorata
	   *
	   * @param string $module nome del modulo a cui saltare
	   * @param string $action nome dell'azione a cui saltare
	   */
	public function __construct($module, $action = '') {
		$this->module = $module;
		$this->action = $action;
		parent::__construct('', 0);
	}

	/**
	 * Ritorna il nome del modulo a cui saltare
	 * @return string il nome del modulo
	 */
	public function getModule() {
		return $this->module;
	}

	/**
	 * Ritorna il nome dell'azione a cui saltare
	 * @return string il nome dell'azione
	 */
	public function getAction() {
		return $this->action;
	}
}


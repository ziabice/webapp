<?php 
/**
 * (c) 2012 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Eccezione sollevata quando non è possibile lanciare una azione.
 */
class WebAppActionLaunchException extends Exception {
	protected
	  $module, $action;

	  /**
	   * Inizializza col nome del modulo e l'azione che non è
	   * stato possibile eseguire
	   *
	   * @param string $module nome del modulo 
	   * @param string $action nome dell'azione 
	   */
	public function __construct($module, $action = '') {
		$this->module = $module;
		$this->action = $action;
		parent::__construct('', 0);
	}

	/**
	 * Ritorna il nome del modulo.
	 * 
	 * @return string il nome del modulo
	 */
	public function getModule() {
		return $this->module;
	}

	/**
	 * Ritorna il nome dell'azione.
	 * 
	 * @return string il nome dell'azione
	 */
	public function getAction() {
		return $this->action;
	}
}


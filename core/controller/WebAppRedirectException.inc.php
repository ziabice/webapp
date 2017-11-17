<?php
/**
 * (c) 2008, 2009 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Eccezione sollevata da {@link WebApp} quando si fa un HTTP redirect
 */
class WebAppRedirectException extends Exception {
	protected
	$location = '';

	/**
	 * costruire con una URL completa o una destinazione interna.
	 * Le stringhe che cominciano per http:// o ftp:// vengono considerate
	 * URL assolute, mentre tutte le altre stringhe destinazioni interne (ossia
	 * una stringa del tipo "modulo/azione").
	 * @param string $location destinazione a cui saltare
	 */
	public function __construct($location) {
		$this->location = $location;
		parent::__construct();
	}

	/**
	 * Ritorna la destinazione a cui saltare
	 * @return string la destinazione
	 */
	public function getLocation() {
		return $this->location;
	}

	/**
	 * Informa se la destinazione sia una URL o una destinazione interna
	 * @return boolean TRUE se la destinazione è una URL, FALSE altrimenti
	 */
	public function isHTTPLocation() {
		return preg_match('/^(ht|f)tp:\/\//', $this->location);
	}
}


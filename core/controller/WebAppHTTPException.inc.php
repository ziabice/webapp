<?php
/**
 * (c) 2008, 2009 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Un'eccezione sollevata quando si deve mostrare una pagina
 * di errore HTTP.
 *
*/
class WebAppHTTPException extends Exception {
	
	/**
	 * Costruire col codice di errore HTTP.
	 * Ad esempio 404 per pagina non trovata.
	 * 
	 * @param integer $http_error_code intero col codice di errore
	 */
	public function __construct($http_error_code) {
		parent::__construct('', $http_error_code);
	}
}


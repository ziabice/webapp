<?php
/**
 * (c) 2008, 2009 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Eccezione generata dal lancio di una query SQL.
 * Il codice numerico di errore si estrae con getCode().
*/
class DBQueryException extends DBException {
	protected
		$sqlquery = ''; // Query che ha causato l'errore
	
	/**
	 * 
	 * @param string $sql stringa SQL che ha causato l'errore
	 * @param string $message stringa col messaggio di errore
	 * @param integer $code codice numerico di errore
	 *
	*/
	public function __construct($sql, $message = '', $code = 0) {
		$this->sqlquery = $sql;
		parent::__construct($message, $code);
	}
	
	/**
	 * Ritorna la stringa col codice SQL che ha generato l'eccezione
	 * 
	 * @return string una stringa col codice SQL che ha generato l'errore
	*/
	public final function getSQL() {
		return $this->sqlquery;
	}
}

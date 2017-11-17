<?php
/**
 * (c) 2008, 2009 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * 	Un InputValidator che interagisce col database
 * @author Luca Gambetta <l.gambetta@bluenine.it>
*/
class DBInputValidator extends InputValidator {
	protected 
		$database;
	
	/**
	 * Costruisce con la connessione al database
	 *
	 * @param DatabaseDriver $database istanza della connessione
	*/	
	public function __construct(DatabaseDriver $database) {
		$this->database = $database;
		parent::__construct();
	}
	
	/**
	 * Ritorna la connessione al database
	 * @return DatabaseDriver istanza della connessione
	*/
	public function getDB() {
		return $this->database;
	}
	
	/**
	 * Imposta la connessione al database
	 *
	 * @param DatabaseDriver $database istanza della connessione
	 * @return DatabaseDriver istanza della connessione
	*/
	public function setDB(DatabaseDriver $database) {
		$this->database = $database;
		return $this->database;
	}
}

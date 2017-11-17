<?php
/**
 * (c) 2008, 2009 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */


/**
 * Fornisce la funzionalità di accesso al database per gli oggetti.
 *
 * Permette di estendere un oggetto "agganciando" una connessione al database.
 *
 * @author Luca Gambetta <l.gambetta@bluenine.it>
 */
class DatabaseAccess {
    protected
		$db_error, // eccezione generata dal database
		$database; // connessione al database

	/**
	 * Inizializza con una connessione
	 *
	 * @param DatabaseDriver $database connessione al database
	 */
	public function __construct(DatabaseDriver $database) {
		$this->database = $database;
		$this->db_error = NULL;
	}

	/**
	 * Ritorna la connessione al database.
	 *
	 * @return DatabaseDriver istanza della connessione
	*/
	public function getDB() {
		return $this->database;
	}

	/**
	 * Imposta la connessione al database.
	 *
	 * @param DatabaseDriver $database istanza della connessione
	 * @return DatabaseDriver istanza della connessione
	*/
	public function setDB(DatabaseDriver $database) {
		$this->database = $database;
		return $this->database;
	}

	/**
	 * Imposta la condizione di errore per questo oggetto.
	 *
	 * Una operazione col database ha generato un errore e si vuole
	 * salvare tale condizione.
	 *
	 * @param DBException $error
	 */
	public function setDBError(DBException $error) {
		$this->db_error = $error;
	}

	/**
	 * Ritorna la condizione di errore di accesso al database.
	 *
	 * @return DBException
	 * @see setDBError
	 */
	public function getDBError() {
		return $this->db_error;
	}

	/**
	 * Informa se ci sia un errore col database.
	 *
	 * @return boolean TRUE se c'è un errore, FALSE altrimenti
	 */
	public function hasDBError() {
		return is_object($this->db_error);
	}
		
}


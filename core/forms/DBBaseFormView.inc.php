<?php
/**
 * (c) 2008, 2009 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * BaseFormView con il supporto per i database
 * */
class DBBaseFormView extends BaseFormView {
	protected
		$database;

	/**
	 * Inizializza con una connessione al database
	 *
	 * @param DatabaseDriver $database la connessione da utilizzare
	 * @param integer $control_name_style stile da usare per la generazione dei nomi
	 */
	public function __construct(DatabaseDriver $database, $control_name_style = self::CTRL_NAME_BOTH) {
		$this->database = new DatabaseAccess($database);
		parent::__construct($control_name_style);
	}
	
	/**
	 * Ritorna la connessione al database
	 * @return DatabaseDriver istanza della connessione
	*/
	public function getDB() {
		return $this->database->getDB();
	}

	/**
	 * Imposta la connessione al database
	 *
	 * @param DatabaseDriver $database istanza della connessione
	 * @return DatabaseDriver istanza della connessione
	*/
	public function setDB(DatabaseDriver $database) {
		return $this->database->setDB($database);
	}

	/**
	 * Ritorna il gestore di accesso al database.
	 *
	 * @return DatabaseAccess
	 */
	public function getDatabaseAccess() {
		return $this->database;
	}
}


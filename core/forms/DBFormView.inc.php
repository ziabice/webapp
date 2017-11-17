<?php
/**
 * (c) 2008, 2009 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * FormView che fornisce una connessione al database
 * @author 	Luca Gambetta <l.gambetta@bluenine.it>
*/
class DBFormView extends FormView {
	protected
		$database;

	/**
	 * Inizializza con una connessione
	 *
	 * @param DatabaseDriver $database connessione al database
	 * @param boolean $showerrors TRUE mostra gli errori, FALSE altrimenti
	 * @param boolean $show_buttonbox TRUE mostra la pulsantiera, FALSE altrmenti
	 * @param integer $control_name_style stile da usare per i nomi dei controlli
	 */
	public function __construct(DatabaseDriver $database, $showerrors = TRUE, $show_buttonbox = TRUE, $control_name_style = self::CTRL_NAME_BOTH) {
		$this->database = new DatabaseAccess($database);
		parent::__construct($showerrors, $show_buttonbox, $control_name_style);
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


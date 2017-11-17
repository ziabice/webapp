<?php
/**
 * (c) 2008, 2009 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * L'insieme dei record risultato di una query al database server
 * 
 * Permettendo di iterare sui dati.
 *
 * ATTENZIONE: non è implementato rewind
 *
 * Nell'implementare una sottoclasse ricordarsi di rimuovere gli slash nelle stringhe estratte
 * dal database e di interagire correttamente coi valori NULL.
*/
abstract class ResultSet implements Iterator {

	protected
		$database_driver = NULL, // istanza di DatabaseDriver che ha generato questo resultset 
		$query_id = NULL, // Risorsa relativa alla query corrente
		$current_record = -1, // Indice del record corrente
		$record_count = -1, // Numero di record restituiti dalla query (cached)
		$affected_rows = -1, // Numero di record restituiti dalla query (cached)
		$fetchmode = NULL, // Modalità di estrazione dei record corrente
		$current_row = NULL, // Riga di dati estratta: array o FALSE
		$sql = ''; // Stringa con la query SQL che ha generato


	// Costanti per il tipo di dati restituito dalle funzioni di fetch dei dati delle query
	const 
		DB_ASSOC = 1, // Array associativo con nome campo come indice
		DB_NUM = 2, // Indici numerici
		DB_BOTH = 3; // Indici numerici ed etichetta testuale
	
	
	/**
	 * Crea il resultset da una query.
	 * Invoca {@link initialize} per inizializzare l'oggetto
	 * 
	 * @param DatabaseDriver $db_driver la connessione al server che ha generato la query
	 * @param resource $query_id la risorsa della query
	 * @param string $sql la query SQL lanciata
	 * @throws DBException in caso di errore di inizializzazione
	*/
	public function __construct(DatabaseDriver $db_driver, $query_id, $sql = '') {
		$this->database_driver = $db_driver;
		$this->query_id = $query_id;
		$this->current_record = -1;
		$this->record_count = -1;
		$this->affected_rows = -1;
		$this->sql = $sql;
		$this->setDefaultFetchMode();
		$this->initialize();
	}
	
	/**
	* Ritorna la risorsa-query da cui è stata generato questo ResultSet
	* 
	* @return resource la risorsa da cui è stato generato il resultset
	*/
	public function getQueryID() {
		return $this->query_id;
	}
	
	public function isValidRS() {
		return is_resource($this->query_id);
	}

	/**
	 * Inizializza l'oggetto coi dati specifici del database
	 * Usata dal costruttore
	 * @throws DBException in caso di errore di inizializzazione
	 */
	public function initialize() {
	}

	/**
	 * Ritorna la stringa SQL che ha generato questo ResultSet
	 * @return string stringa SQL
	 */
	public function getSQL() {
		return $this->sql;
	}

	/**
	 * Libera le risorse allocate
	 */
	public function __destruct() {
		try {
			if (is_resource($this->query_id)) {
				$this->free();
			}
		} catch(DBException $e) {
		}
	}
	
	/**
	 * Ritorna l'istanza di DatabaseDriver che ha 
	 * generato questo ResultSet
	 * @return DatabaseDriver istanza di DatabaseDriver
	 * */
	public function getDatabaseDriver() {
		return $this->database_driver;
	}
	
	
	/**
	 * Iterator: avanza il puntatore dei risultati alla prossima riga
	 * @throws DBException in caso di errore
	*/
	public function next() {
		$this->current_row = $this->fetchRow($this->query_id, $this->current_record++);
	}
	
	/**
	 * Iterator: indica se ci sia un record dopo una chiamata a rewind() o next()
	 * @return boolean TRUE o FALSE
	*/
	public function valid() {
		return is_array($this->current_row);
	}
	
	/**
	 * Iterator: ritorna la riga di dati corrente
	 * @return array una riga di dati
	*/
	public function current() {
		return $this->current_row;
	}
	
	/**
	 * Iterator: ritorna l'indice del record corrente
	 * @return integer
	*/
	public function key() {
		return $this->current_record;
	}
	
	/**
	 * Ritorna il numero di campi che ha ritornato una query
	 * @return integer un intero col numero dei campi
	 * @throws DBException se c'è un errore
	*/
	public function countFields() {
		return 0;
	}

	/**
	 * Ritorna il numero di record che ha generato una query. Usarlo quando si tratta di una SELECT
	 * 
	 * @return integer un intero col numero dei record
	 * @throws DBException se c'è un errore
	*/
	public function recordCount() {
		return $this->record_count;
	}

	/**
	 * Ritorna il numero di record che ha coinvolto una query. Usarlo quando la query
	 * è di tipo UPDATE, DELETE
	 * 
	 * @return integer un intero col numero dei record
	 * @throws DBException se c'è un errore
	*/
	public function affectedRows() {
		return $this->affected_rows;
	}

	/**
	 * Ritorna un array con una riga del risultato di una query. Il modo in cui vengono ritornati i dati
	 * può essere cambiato con una chiamata a setFetchMode
	 * 
	 * @param integer $row Riga di record da prendere (ignorato da diversi DB server - es. MySQL)
	 * @return array un array di dati o FALSE se non ci sono dati o c'è un errore
	 * 
	*/
	abstract function fetchRow($row = 0);
	
	/**
	 * Ritorna un array con tutti i risultati di una query. Il modo in cui vengono ritornati i dati
	 * può essere cambiato con una chiamata a setFetchMode.
	 * 
	 * Prima di cominciare l'estrazione non azzera il puntatore alla riga corrente,
	 * che va fatto a mano.
	 * 
	 * @return array un array multidimensionale con i dati o NULL se non ci sono dati
	 * 
	*/
	abstract function fetchAll();
	
	/**
	 * Sposta il puntatore del record corrente alla posizione specificata
	 * (non è disponibile in tutti i database server)
	 * 
	 * @param integer $where nuova posizione del puntatore. 0 indica l'inizio
	 * @return boolean TRUE o FALSE a seconda dell'esito dell'operazione
	*/
	public function seek($where) { return FALSE; }
	
	/**
	 * Libera la memoria occupata dalla query: è praticamente il distruttore dell'oggetto
	 *
	 * @throws DBException se c'è un errore
	*/
	public function free() {
		$this->query_id = NULL;
	}

	/**
	 * Invocata dal costruttore imposta la modalità di estrazione dei dati di
	 * default per questo resultset, di solito come array associativo
	*/
	public function setDefaultFetchMode() {
		$this->setFetchMode(self::DB_ASSOC);
	}

	/**
	 * Imposta il modo in cui vengono prelevati i dati
	 * E' una costante tra queste:
	 * 
	 * self::DB_ASSOC un array associativo
	 * self::DB_NUM un array con indici numerici
	 * self::DB_BOTH un array con indici sia associativi che numerici.
	 *
	 * @param $mode il modo in cui prelevare i dati.
	*/
	public function setFetchMode($mode) {
		$this->fetchmode = $mode;
	}
	
	/**
	 * Ritorna il modo in cui vengono estratti i dati corrente
	 * @return integer una delle costanti DB_ASSOC, DB_NUM o DB_BOTH
	*/
	final public function getFetchMode() {
		return $this->fetchmode;
	}
	
	/**
	 * Informa se il ResultSet sia vuoto
	 *
	 * @return boolean TRUE se non ci sono dati, FALSE altrimenti
	*/
	public function isEmpty() { 
		return ($this->record_count <= 0); 
	}
	
}

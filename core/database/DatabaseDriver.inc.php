<?php
/**
 * (c) 2008, 2009, 2010 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Classe base per i driver che interagiscono con un database.
 * 
 * @author Luca Gambetta <l.gambetta@bluenine.it>
 */
abstract class DatabaseDriver {
	protected 
		$dsn_data,
		$persistent, // boolean TRUE la connessione è persistente
		$db, // Connessione al Database Server
		$last_query_id = NULL, // Risorsa dell'ultima query eseguita: permette di creare un resultset
		$last_sql = ''; // Ultima query eseguita da execQuery


	/**
	 * Inizializza l'oggetto con i dati DSN inglobati dal parser.
	 * Esegue anche la connessione al database usando {@link connect}
	 *
	 * @param DSNParser $dsn_data dati per la connessione
	 * @throws DBException se non è stato possibile connettersi al db
	 */
	public function __construct(DSNParser $dsn_data) {
		$this->dsn_data = $dsn_data;
		$this->persistent = FALSE;
		$this->connect($dsn_data);
	}
	
	// abstract public function createStatement($sql = '');
	
	/**
	 * Si connette al database indicato dal parser DSN
	 * @param DSNParser $dsn_data i dati per la connessione
	 * @throws DBException in caso di fallimento della connessione
	 */
	protected function connect(DSNParser $dsn_data) {}

	/**
	 * Informa che la connessione effettuata con connect è di tipo persistente
	 * @param boolean $persistent TRUE la connessione è persistente, FALSE altrimenti
	 */
	protected function setPersistentConnection($persistent) {
		$this->persistent = $persistent;
	}

	/**
	 * Informa se la connessione corrente sia persistente
	 * @return boolean TRUE se la connessione corrente è persistente, FALSE altrimenti
	 */
	public function hasPersistentConnection() {
		return $this->persistent;
	}

	/**
	 * Imposta il link la connessione corrente al database
	 * @param resource $db il link alla connessione al database server
	 */
	protected function setDBConnection($db) {
		$this->db = $db;
	}

	/**
	 * Ritorna i dati DSN (contenuti in un oggetto DSNParser) usati per la connessione
	 * corrente.
	 * @return DSNParser il parser di dsn 
	 */
	public function getDSN() {
		return $this->dsn_data;
	}
	
	
	/**
	 * Imposta il set di caratteri da utilizzare nella comunicazione client-server.
	 * 
	 * @param string $charset stringa col nome del set di caratteri
	 * @throws DBException in caso di errore
	*/
	abstract public function setCharset($charset);
	
	/**
	 * Esegue la query SQL.
	 * 
	 * Una volta che la query è stata eseguita estrarre un ResultSet per gestire
	 * i risultati o utilizzare una delle funzioni di conteggio.
	 *
	 * Viene salvato l'handle della query che può essere utilizzato invocando {@link getLastQueryHandle}
	 * 
	 * @param string $query stringa con la query SQL da eseguire
	 * @return ResultSet una istanza di ResultSet (specifico per il db driver usato)
	 *
	 * @throws DBQueryException se la query causa errore
	*/
	public function execQuery($query) {
		
		$this->last_sql = $query;
		$this->last_query_id = NULL;
		Logger::getInstance()->debug(get_class($this)."::execQuery : ".$query);
		$this->last_query_id = $this->execSQL($query);
		$rs = $this->newResultSetFromQuery($this->last_query_id, $query);
		return $rs;
	}
	
	/**
	 * Esegue un insieme di query. Si ferma al primo errore.
	 * 
	 * @param array $query un array di stringhe con le query SQL da eseguire
	 * @return ResultSet NULL in caso di errore o una istanza di ResultSet col risultato dell'ultima query eseguita
	 *
	 * @throws DBQueryException se una query dà errore
	*/
	public function execQueries($sqlqueryarr) {
		$this->last_sql = '';
		$this->last_query_id = NULL;
		$rs = NULL;
		if (is_array($sqlqueryarr)) {
			Logger::getInstance()->debug("--> ".get_class($this)."::execQueries start");
			foreach($sqlqueryarr as $sql) {
				$this->last_sql = $sql;
				Logger::getInstance()->debug(get_class($this)."::execQueries : ".$sql);
				$this->last_query_id = $this->execSQL($sql);
			}
			Logger::getInstance()->debug(" <-- ".get_class($this)."::execQueries end");
			$rs = $this->newResultSetFromQuery($this->last_query_id, $this->last_sql);
		}
		return $rs;
	}
	
	/**
	 * Esegue la query SQL e ritorna la risorsa generata (interagisce a basso livello col db server)
	 * 
	 * @return resource una risorsa relativa ad una query
	 *
	 * @throws DBQueryException in caso di errore
	 * @throws DBDuplicateRecordException
	*/
	abstract public function execSQL($sql);
	
	/**
	 * Data una risorsa di una query ritorna un ResultSet
	 * 
	 * @param resource $query_id resource della query
	 * @param string $sql stringa col codice SQL della query
	 * 
	 * @return ResultSet una istanza di ResultSet o NULL se la query non è valida
	 * @throws DBQueryException in caso di errore
	*/
	abstract protected function newResultSetFromQuery($query_id, $sql = '');

	/**
	 * Ritorna l'ultima query SQL eseguita
	 * 
	 * @return string una stringa con la query SQL
	*/
	public final function getLastQuery() {
		return $this->last_sql;
	}
	
	/**
	 * Ritorna l'handle dell'ultima query SQL eseguita
	 * 
	 * @return resource una risorsa al risultato dell'ultima query eseguita
	*/
	public function getLastQueryHandle() {
		return $this->last_query_id;
	}
	

	/**
	 * Ritorna il link al server database
	 * 
	 * @return resource la connessione al database server
	*/
	public final function getDBConnection() {
		return $this->db;
	}
	
	/**
	 * Invocata di solito dal distruttore chiude la connessione al database,
	 * rendendo l'oggetto inutilizzabile
	*/
	abstract public function close();

	/**
	 * 
	 * Ritorna una stringa con la clausola LIMIT relativa alle righe da estrarre
	 * 
	 * @param integer $recordcount numero di record da estrarre
	 * @param integer $startrecord indice del record da cui cominciare l'estrazione, se NULL viene ignorato: il comportamento
	 * varia a seconda del driver DB
	 * 
	 * @return string la stringa con la clausola LIMIT
	*/
	public function getLimit($recordcount, $startrecord = NULL) {
		return 'LIMIT '.$recordcount.(is_null($startrecord) ? '' : ' OFFSET '.strval($startrecord) );
	}

	/**
	 * Comincia una transazione
	 * 
	 * @return boolean TRUE se l'operazione è riuscita, FALSE altrimenti
	 * @throws DBException se fallisce
	*/
	abstract public function begin();

	/**
	 * Fa il commit della transazione corrente
	 *
	 * @return boolean TRUE se l'operazione è riuscita, FALSE altrimenti
	 * @throws DBException se fallisce
	*/
	abstract public function commit();

	/**
	 * Fa il rollback della transazione corrente
	 *
	 * @return boolean TRUE se l'operazione è riuscita, FALSE altrimenti
	 * @throws DBException se fallisce
	*/
	abstract public function rollback();
	
	/**
	 * Racchiude la stringa tra apici, mettendo gli slash dove servono.
	 * Utile per inserire valori stringa nel db
	 * 
	 * @param string $str stringa a cui aggiungere gli slash
	 * @return string la stringa con gli slash
	 *
	 * @throws DBException se c'è un errore nella comunicazione al db
	*/
	public function addSlashes($str) {
		if (get_magic_quotes_runtime() == 1) $str = stripslashes($str);
		return "'".addslashes($str)."'";
	}
	
	/**
	 * Seleziona un database come corrente. Non tutti i server db supportano questa
	 * operazione
	 * 
	 * @param string $dbname stringa col nome del database da selezionare
	 * @return boolean TRUE se l'operazione è riuscita, FALSE altrimenti
	 *
	 * @throws DBException in caso di errore
	*/
	abstract public function useDatabase($dbname);
	
	/**
	 * Esegue una (o più) query  in una transazione. 
	 * 
	 * 
	 * In caso di errore delle query prima prova
	 * ad eseguire il rollback, poi ritorna.
	 * Se tutto è ok esegue il commit.
	 * 
	 * @param string|array $sql stringa o array di stringhe con le query
	 * 
	 * @return ResultSet|boolean istanza di ResultSet o FALSE se la query è vuota
	 * @throws DBException in caso di errore col db
	 * @throws DBQueryException in caso di errore nelle query
	*/
	public function runQuery($sql) {
		if (empty($sql)) return FALSE;
		
		$this->begin();
		
		try {
			if (is_array($sql)) $rs = $this->execQueries($sql);
			else $rs = $this->execQuery($sql);
			$this->commit();
			return $rs;
		}
		catch(DBException $e) {
			$this->rollback();
			throw $e;
		}
	}

	/**
	 * Informa se ci sia o meno una connessione col db server
	 * @return boolean TRUE se c'è una connessione, FALSE altrimenti
	 */
	abstract public function isConnected();

	/**
	 * Esegue la o le query specificate e ritorna il primo valore del primo
	 * campo del Resultset generato.
	 *
	 *
	 * @param string|array $sql stringa o array di stringhe col codice SQL da eseguire
	 * @return mixed FALSE se non ci sono dati, altrimenti una stringa o NULL col valore estratto dal database
	 * @throws DBException
	 */
	public function launchQueryAndGetFirstValue($sql) {
		if (is_array($sql)) {
			$rs = $this->execQueries($sql);
		} else {
			$rs = $this->execQuery($sql);
		}

		$row = $rs->fetchRow();
		
		$rs->free();
		if (empty($row)) {
			return FALSE;
		} else {
			return array_shift($row);
		}
	}
}

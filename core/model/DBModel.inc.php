<?php
/**
 * (c) 2008-2012 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Implementa tutte le operazioni CRUD su di un database.
 * 
 * In questa implementazione si presume che gli oggetti manipolati implementino l'interfaccia WebAppObjectInterface.
 * 
 * @author Luca Gambetta <l.gambetta@bluenine.it>
*/
class DBModel extends Model {
	protected
		$_read_in_transaction, // Flag che indica se la lettura deve avvenire in una transazione
		$_count_in_transaction, // Flag che indica se il conteggio deve avvenire in una transazione
		$db, // Istanza di DatabaseDriver
		$object; // Oggetto interno
		
	/**
	 * Inizializza il Model.
	 *
	 * Il parametro è il database da utilizzare.
	 *
	 * @param DatabaseDriver $db database a cui connettersi
	*/
	public function __construct(DatabaseDriver $db) {
		parent::__construct();
		$this->setDB($db);
		$this->_read_in_transaction = FALSE;
		$this->_count_in_transaction = FALSE;
	}
	
	/**
	 * Regola le letture in transazioni.
	 * 
	 * Permette di far eseguire o meno il codice SQL per le
	 * letture e i conteggi in una transazione o meno.
	 * 
	 * @param boolean $use_read_transaction TRUE usa una transazione in lettura, FALSE altrimenti
	 * @param boolean $use_count_transaction TRUE usa una transazione nei conteggi, FALSE altrimenti
	 * @see countByCriteria
	 * @see readByCriteria
	 * */
	public function setReadAndCountTransactional($use_read_transaction = FALSE, $use_count_transaction = FALSE) {
		$this->_read_in_transaction = $use_read_transaction;
		$this->_count_in_transaction = $use_count_transaction;
	}
	
	/**
	 * Imposta il driver per il database
	 * 
	 * @param DatabaseDriver $db connessione da utilizzare
	 * @return DatabaseDriver l'istanza usata come parametro
	*/
	public function setDB(DatabaseDriver $db) {
		$this->db = $db;
		return $db;
	}
	
	/**
	 * Ritorna il driver database impostato
	 * @return DatabaseDriver istanza di DatabaseDriver
	 */
	public function getDB() {
		return $this->db;
	}
	
	/**
	 * Verifica che l'oggetto che sarà manipolato dalle operazioni
	 * di tipo CRUD appartenga alla classe corretta.
	 * 
	 * In caso di classi non valide le operazioni richieste non verranno eseguite.
	 * 
	 * @param object $object l'oggetto da verificare
	 * @return boolean TRUE se appartiene alla giusta classe, FALSE altrimenti
	 * */
	public function isValidObject($object) {
		return ($object instanceof WebAppObjectInterface);
	}
	
	// -------------------- Conteggio degli elementi
	
	/**
	 * Dati dei criteri di selezione ritorna una stringa o un array di stringhe
	 * SQL che eseguite portano al conteggio.
	 * L'ultima stringa dovrebbe essere una select che ritorna una sola riga
	 * con un solo record che è l'effettivo conteggio
	 * 
	 * Usata da {@link countByCriteria}.
	 * 
	 * @param Criteria $criteria i criteri di selezione da utilizzare per il conteggio
	 * @return string stringa o array di stringhe SQL
	*/
	protected function getCountSQL(Criteria $criteria) {
		return '';
	}
	
	/**
	 * Esegue il conteggio degli elementi che rispondono a determinati criteri.
	 * Ricava il codice SQL per eseguire il conteggio da {@link getCountSQL}.
	 * Le operazioni avvengono in una transazione, se specificato.
	 * 
	 * @return integer il numero di elementi o FALSE se non può inizializzare il processo o eseguire le operazioni
	 * @throws DBException in caso di errore di accesso al database
	 * @see setReadAndCountTransactional
	 * @see getCountSQL
	*/
	public function getTotalCount(Criteria $criteria) {
		$sql = $this->getCountSQL($criteria);
		
		if (empty($sql)) return FALSE;
		
		try {
			if ($this->_count_in_transaction) $this->db->begin();
			$rs = (is_array($sql) ? $this->db->execQueries($sql) : $this->db->execQuery($sql) );
			$cnt = $this->getRecordCountFromResultSet($rs);
			$rs->free();
			if ($this->_count_in_transaction) $this->db->commit();
			return $cnt;
		}
		catch(DBException $e) {
			if ($this->_count_in_transaction) $this->db->rollback();
			throw $e;
		}
		return FALSE;
	}
	
	/**
	 * Estrae dai dati del ResultSet il conteggio del numero di elementi generati
	 * dalla query di conteggio.
	 *
	 * In effetti estrae il primo elemento della prima riga di dati, che si suppone
	 * essere il numero di elementi di una query di conteggio.
	 *
	 * Usata da {@link countByCriteria}.
	 * 
	 * @return integer un intero col numero di elementi.
	 * @throws DBException in caso di errore di accesso al database
	*/
	protected function getRecordCountFromResultSet(ResultSet $rs) {
		$data = $rs->fetchAll();
		$data = array_shift($data);
		if (is_array($data)) {
			$r = array_shift($data);
		} else {
			$r = $data;
		}
		return $r;
	}
	
	// ----------------------------- Lettura di elementi
	
	/**
	 * Metodo di utilità: esegue una lettura di tipo "prendi tutti", senza impostare
	 * i criteri dell'oggetto
	 * 
	 * Usa {@link read} per eseguire la lettura
	 * 
	 * @return array un array vuoto se non ha trovato niente o un array di istanze dell'oggetto
	 * @throws DBException in caso di errori di accesso al db
	*/
	public function readAll() {
		return $this->read( Criteria::newCatchAll($this) );
	}
	
	/**
	 * Legge un oggetto in base alla sua chiave primaria
	 * 
	 * Usa {@link read} per eseguire la lettura
	 * 
	 * @param mixed $pk la chiave primaria (di solito è un intero senza segno)
	 * @return object NULL se l'oggetto non esiste o l'oggetto
	 * @throws Exception in caso di errore di inizializzazione
	 * @throws DBException in caso di errore di accesso al DB
	*/
	public function readByPK($pk) {
		return $this->read( Criteria::newWithPK($this, $pk) );
	}
	
	/**
	 * Legge più oggetti in base alle loro chiavi primarie
	 * 
	 * Usa {@link read} per eseguire la lettura
	 * 
	 * @param array $pks array di chiavi primarie (di solito è un array di interi)
	 * @return array un array di istanze dell'oggetto (array vuoto se non è stato trovato nulla)
	 * @throws Exception in caso di errore di inizializzazione
	 * @throws DBException in caso di errore di accesso al DB
	*/
	public function readByPKS(array $pks) {
		return $this->read( Criteria::newWithPKS($this, $pks) );
	}
	
	
	/**
	 * Genera degli oggetti leggendoli dal database selezionandoli
	 * attraverso dei criteri.
	 * 
	 * Utilizza il codice SQL generato da {@link getReadSQL} per la lettura, poi crea
	 * gli oggetti usando {@link buildObjectsFromResultSet}, infine termina
	 * le operazioni invocando {@link finalizeReadedObjects}.
	 * 
	 * Tutta l'operazione avviene in una transazione, se specificato: in 
	 * caso di errore viene prima fatto il rollback
	 * e poi rilanciata l'eccezione.
	 * 
	 * @param Criteria $criteria i criteri di seleizone degli oggetti
	 * @return WebAppObjectInterface|boolean|array FALSE se non può inizializzare il processo, NULL o un array vuoto
	 * se non trova l'oggetto o gli oggetti, altrimenti l'istanza dell'oggetto letto o un array di oggetti.
	 * @throws DBException in caso di errore di accesso al DB
	 * @see setReadAndCountTransactional
	 * @see buildObjectsFromResultSet
	*/
	public function read(Criteria $criteria) {
		$sql = $this->getReadSQL($criteria);
		if (empty($sql)) return FALSE;

		if ($this->_read_in_transaction) $this->db->begin();
		try {
			$rs = (is_array($sql) ? $this->db->execQueries($sql) : $this->db->execQuery($sql) );
			if ($this->_read_in_transaction) $this->db->commit();
			
			$obj = $this->buildObjectsFromResultSet($criteria, $rs);
			$this->finalizeReadedObjects($obj);
			
			return $obj;
		}
		catch(DBException $e) {
			if ($this->_read_in_transaction) $this->db->rollback();
			throw $e;
		}
		return FALSE;
	}
	
	/**
	 * Crea gli oggetti usando i dati generati dall'esecuzione del codice
	 * ritornato da {@link getReadSQL}.
	 *
	 * Viene usata da {@link readByCriteria} dopo aver lanciato la query di selezione.
	 * 
	 * Compito di questo metodo è quello di estrarre i dati dal {@link ResultSet}, comporli in un array e passare
	 * queste informazioni a {@link buildObject} per creare il o gli oggetti necessari.
	 * 
	 * Se non ci sono dati utili ritorna NULL in caso di creazione di un singolo oggetto o 
	 * un array vuoto nel caso di oggetti multipli.
	 * 
	 * @param Criteria $criteria i criteri di selezione usati per scegliere l'oggetto da estrarre
	 * @param ResultSet $rs il ResultSet coi dati
	 * @return WebAppObjectInterface|array ritorna l'oggetto o gli oggetti creati dai dati. 
	 * @throws DBException in caso di errore di accesso al database
	*/
	protected function buildObjectsFromResultSet(Criteria $criteria, ResultSet $rs) {
		$data = $rs->fetchAll();
		$rs->free();
		if ($criteria->isSinglePK() ) {
			if (!empty($data)) return $this->buildObject(array_shift($data));
			else return NULL;
		} else {
			$obj = array();
			foreach($data as $row) {
				$obj[] = $this->buildObject($row);
			}
			return $obj;
		}
	}

	/**
	 * Esegue il passo finale dopo la lettura di un oggetto, impostando
	 * alcuni valori standard.
	 *
	 * Viene eseguita da {@link read}.
	 *
	 * Imposta il flag di oggetto nuovo a FALSE e quello di oggetto rimosso a FALSE.
	 *
	 * @param object|array $obj un oggetto WebAppObject o un array di oggetti WebAppObject
	 * 
	 */
	protected function finalizeReadedObjects($obj) {
		if (is_array($obj)) {
			foreach($obj as $object) {
				$object->setNew(FALSE);
				$object->setDeleted(FALSE);
			}
		} elseif (is_object($obj)) {
			$obj->setNew(FALSE);
			$obj->setDeleted(FALSE);
		}
	}
	
	/**
	 * Ritorna il codice SQL necessario per leggere i dati secondo i criteri di selezione.
	 *
	 * Usata da {@link readByCriteria}.
	 * 
	 * Ritorna una stringa o un array di stringhe contenente il codice
	 * SQL necessario per estrarre gli oggetti indicati dai criteri.
	 * Se ritorna un array l'ultima query deve generare i dati per
	 * creare l'oggetto.
	 * 
	 * @param Criteria $criteria istanza di Criteria per la selezione degli elementi da leggere
	 * @return stringa o array di stringhe col codice SQL
	*/
	protected function getReadSQL(Criteria $criteria) {
		return '';
	}
	
	// --------------------------- Creazione di elementi
	
	/**
	 * Salva un oggetto nel database.
	 *
	 * Crea un nuovo oggetto nel database, usando i dati del WebAppObject.
	 * In seguito ad una creazione corretta, imposta l'ID dell'oggetto.
	 * 
	 * Per eseguire l'operazione usa {@link doCreate}.
	 * 
	 * La validità dell'oggetto da creare viene verificata usando {@link isValidObject}.
	 * 
	 * @param WebAppObject $object oggetto da salvare nel database.
	 * @param array $params parametri aggiuntivi
	 * @return boolean TRUE se l'operazione è riuscita, FALSE altrimenti
	 * @throws DBException se c'è un errore di accesso al DB
	 * @see doCreate
	 * @see isValidObject
	*/
	public function create($object, $params = array()) {
		if (!$this->isValidObject($object)) {
			Logger::getInstance()->debug(get_class($this)."::create : Invalid object type");
			return FALSE;
		}
		
		$id = $this->doCreate($object, $params);
		if ($id !== FALSE) {
			$object->setID($id);
			$object->setNew(FALSE);
			$object->setDeleted(FALSE);
			return TRUE;
		} 
		return FALSE;
	}
	
	/**
	 * Ritorna il codice SQL per salvare un oggetto nel database.
	 *
	 * Ritorna una stringa o un array di stringhe contenente il codice
	 * SQL necessario per creare i record nel db usando un oggetto.
	 * L'ultima query deve leggere l'ID dell'oggetto creato o far generare comunque
	 * un ResultSet utilizzabile per tale scopo.
	 *
	 * Usata da {@link doCreate}
	 *
	 * @param WebAppObject $object oggetto che si vuole salvare nel DB
	 * @param array $params parametri aggiuntivi
	 * @return string|array stringa o array di stringhe col codice SQL
	 * @see doCreate
	*/
	protected function getCreateSQL($object, $params = array()) {
		return '';
	}
	
	/**
	 * Esegue il salvataggio dell'oggetto nel database, ritornando l'ID dell'oggetto creato.
	 * 
	 * L'oggetto viene creato usando il codice SQL generato da {@link getCreateSQL}, che dovrebbe
	 * ritornare un array di stringe SQL in cui l'ultima istruzione è una SELECT che ritorna la
	 * chiave primaria del record creato.
	 *
	 * Dopo aver lanciato le query invoca {@link finalizeCreation} per terminare la creazione.
	 *
	 * Tutte le operazioni vengono eseguite in una transazione (in caso di errore fa prima il rollback e poi
	 * rilancia l'eccezione).
	 * @param object $object oggetto da creare
	 * @param array $params parametri aggiuntivi
	 * @return mixed FALSE se c'è un errore, altrimenti un valore che è l'id dell'oggetto creato
	 * @throws DBException se c'è un errore di accesso al DB
	 * @see getCreateSQL
	*/
	protected function doCreate($object, $params = array()) {
		$sql = $this->getCreateSQL($object, $params);
		
		if (empty($sql)) return FALSE;
		
		$this->db->begin();
		try {
			$rs = (is_array($sql) ? $this->db->execQueries($sql) : $this->db->execQuery($sql) );
			if ( $this->finalizeCreation($object, $rs, $params) ) {
				$this->db->commit();
				return $object->getID();
			} else {
				$this->db->rollback();
				return FALSE;
			}
		}
		catch(DBException $e) {
			$this->db->rollback();
			throw $e;
		}
	}
	
	/**
	 * Termina il processo di creazione di un oggetto nel database.
	 * 
	 * Usata da {@link doCreate}, popola le proprietà dell'oggetto usando le informazioni del ResultSet,
	 * in particolare per estrarre l'ID dell'oggetto creato (che dovrebbe corrispondere alla chiave
	 * primaria dei record creati).
	 *
	 * Normalmente estrae il primo elemento della prima riga di risultati del ResultSet, nel quale
	 * si aspetta di trovare l'id dell'oggetto creato.
	 * 
	 * Se ritorna FALSE il processo di creazione viene valutato come fallito,
	 * e l'oggetto non viene creato (viene eseguito il rollback della transazione).
	 * 
	 * Ritorna TRUE se tutto è a posto.
	 * @param object $object (di solito una istanza di WebAppObject) oggetto di cui impostare le proprietà
	 * @param ResultSet $rs Il resultset generato dalla query SQL di creazione
	 * @param array $params parametri aggiuntivi
	 * @return boolean TRUE il processo è terminato correttamente, FALSE il processo è fallito
	*/
	protected function finalizeCreation($object, ResultSet $rs, $params = array()) {
		$data = $rs->fetchAll();
		$rs->free();
		if (is_array($data)) {
			$id = array_shift($data);
			if (is_array($id)) {
				$id = reset($id);
			}
			$object->setID( $id );
			return TRUE;
		}
		return FALSE;
	}
	
	// --------------------------- Aggiornamento di elementi
	
	/**
	 * Aggiorna i record nel database che descrivono l'oggetto.
	 * 
	 * 
	 * E' necessario che l'oggetto non sia nuovo, ossia invocando {@link WebAppObject::isNew} venga
	 * restituito TRUE.
	 * 
	 * Usa {@link doUpdate} per eseguire l'effettivo aggiornamento.
	 * 
	 * @param WebAppObjectInterface $object oggetto da aggiornare
	 * @param array $params parametri aggiuntivi
	 * @return boolean TRUE se ha aggiornato l'oggetto, FALSE se non ha proceduto
	 * @throws DBException se c'è un errore di accesso al DB
	 * @throws DBQueryException se c'è un errore di accesso al DB
	 * @see doUpdate
	 * @see isValidObject
	*/
	public function update($object, $params = array()) {
		if (!$this->isValidObject($object)) {
			Logger::getInstance()->debug(get_class($this)."::update : Invalid object type");
			return FALSE;
		}
		
		if ($object->isNew()) {
			return FALSE;
		} else {
			return $this->doUpdate($object, $params);
		}
	}
	
	/**
	 * Aggiorna più oggetti utilizzando i criteri di selezione.
	 *
	 * @param Criteria $criteria criteri di selezione degli oggetti
	 * @return boolean TRUE se tutto ok, FALSE altrimenti
	 * @throws DBException se c'è un errore di accesso al DB
	 * @throws DBQueryException se c'è un errore di accesso al DB
	*/
	public function updateByCriteria(Criteria $criteria) {
		return FALSE;
	}
	
	/**
	 * Ritorna il codice SQL necessario per fare l'update dei dati
	 * dell'oggetto nel database.
	 * 
	 * Usata da {@link doUpdate}.
	 * 
	 * @param object $object oggetto da usare per generare il codice SQL
	 * @param array $params parametri aggiuntivi
	 * @return string|array una stringa o un array di stringhe SQL
	*/
	protected function getUpdateSQL($object, $params) {
		return '';
	}
	
	/**
	 * Aggiorna un oggetto nel database.
	 * 
	 * Utilizza il codice SQL generato da {@link getUpdateSQL}, dopo aver
	 * eseguito la query invoca {@link finalizeUpdate} per decidere se 
	 * procedere o meno con le operazioni
	 * 
	 * Tutte le operazioni avvengono in una transazione.
	 * 
	 * @param WebAppObject $object istanza di WebAppObject su cui operare
	 * @param array $params parametri aggiuntivi
	 * @return boolean FALSE se non ha eseguito l'aggiornamento, TRUE altrimenti
	 * @throws DBException se c'è un errore di accesso al DB
	 * @throws DBQueryException se c'è un errore di accesso al DB
	 * @see finalizeUpdate
	 * @see getUpdateSQL
	*/
	protected function doUpdate($object, $params = array()) {
		$sql = $this->getUpdateSQL($object, $params);
		if (empty($sql)) return FALSE;
		
		$this->db->begin();
		
		try {
			$rs = (is_array($sql) ? $this->db->execQueries($sql) : $this->db->execQuery($sql) );
			if ( $this->finalizeUpdate($object, $rs, $params) ) {
				$this->db->commit();
				return TRUE;
			} else {
				$this->db->rollback();
				return FALSE;
			}
		}
		catch (DBException $e) {
			$this->db->rollback();
			throw $e;
		}
	}
	
	
	/**
	 * Termina le operazioni di aggiornamento dell'oggetto nel database.
	 * 
	 * Usata da {@link doUpdate}: se ritorna FALSE il processo di aggiornamento
	 * viene interrotto e fatto il rollback della transazione, altrimenti procede.
	 * 
	 * Il ResultSet passato come parametro è quello generato dall'esecuzione
	 * del codice SQL per l'aggiornamento
	 * 
	 * @param object $object oggetto su cui operare (di solito una istanza di WebAppObject)
	 * @param ResultSet $rs ResultSet generato
	 * @return boolean TRUE se tutto ok e può procedere, FALSE se deve fare il rollback dell'operaizone
	 * @throws DBException se c'è un errore di accesso al DB
	 * @throws DBQueryException se c'è un errore di accesso al DB
	*/
	protected function finalizeUpdate($object, ResultSet $rs, $params = array()) {
		return TRUE;
	}
	
	// --------------------------- Rimozione di elementi
	
	/**
	 * Rimuove i record che descrivono un oggetto dal database.
	 * 
	 * Per operare usa {@link deleteByCriteria}
	 * 
	 * L'oggetto viene marcato come rimosso (invocando $obj->isDeleted otterrete TRUE ).
	 * @param WebAppObjectInterface $obj oggetto da rimuovere dal database
	 * @return boolean TRUE se tutto ok, FALSE se non ha operato
	 * @throws DBException in caso di errore col DB
	*/
	public function delete($obj) {
		if (!$this->isValidObject($obj)) {
			Logger::getInstance()->debug(get_class($this)."::delete : Invalid object type");
			return FALSE;
		}
		
		if ( !$obj->isNew() ) {
			$ok = $this->deleteByCriteria( Criteria::newWithPK($this, $obj->getID())  );
			if ($ok) $obj->setDeleted(TRUE);
			return $ok;
		}
		return FALSE;
	}
	
	/**
	 * Rimuove un record in base alla chiave primaria.
	 * Invoca {@link deleteByCriteria} con un Criteria di tipo chiave primaria.
	 * 
	 * @param mixed $pk chiave primaria dei record
	 * @return boolean TRUE se tutto ok, FALSE se non ha operato
	 * @throws DBException in caso di errore col DB
	*/
	public function deleteByPK($pk) {
		return $this->deleteByCriteria( Criteria::newWithPK($this, $pk)  );
	}
	
	/**
	 * Rimuove dei record in base alla loro chiave primaria.
	 * 
	 * Invoca {@link deleteByCriteria} con un Criteria di tipo chiave primaria.
	 * @param array $pks array con le chiavi primarie dei record
	 * @return boolean TRUE se tutto ok, FALSE se non ha operato
	 * @throws DBException in caso di errore col DB
	*/
	public function deleteByPKS($pks) {
		return $this->deleteByCriteria( Criteria::newWithPKS($this, $pks) );
	}
	
	/**
	 * Rimuove tutti i record.
	 * 
	 * Usa {@link deleteByCriteria} con modalità MODE_ALL
	 * 
	 * @return boolean TRUE se tutto ok, FALSE se non ha operato o c'è un errore
	 * @throws DBException se c'è un errore di accesso al database
	*/
	public function deleteAll() {
		return $this->deleteByCriteria( Criteria::newCatchAll($this) );
	}

	/**
	 * Rimuove gli elementi in base a criteri di selezione
	 * 
	 * Le operazioni vengono eseguite usando il codice generato da {@link getDeleteSQL}.
	 * Tutta l'operazione avviene all'interno di una transazione
	 * @param Criteria $criteria criteri di selezione dei record
	 * @return boolean TRUE l'operazione è riuscita, FALSE altrimenti
	 * @throws DBException in caso di errore col DB
	*/
	public function deleteByCriteria(Criteria $criteria) {
		$sql = $this->getDeleteSQL($criteria);
		
		if (empty($sql)) return FALSE;
		
		$this->db->begin();
		try {
			if (is_array($sql)) $this->db->execQueries($sql); 
			else $this->db->execQuery($sql);
			
			$this->db->commit();
			return TRUE;
		}
		catch (DBException $e) {
			$this->db->rollback();
			throw $e;
		}
		return FALSE;
	}
	
	/**
	 * 
	 * Ritorna una stringa o un array di stringhe SQL che effettuano la rimozione
	 * degli elementi indicati dai criteri di selezione
	 *
	 * Usata da {@link deleteByCriteria()}.
	 *
	 * @param Criteria $criteria i criteri di selezione
	 * @return mixed stringa o array di stringhe SQL
	 */
	protected function getDeleteSQL(Criteria $criteria) {
		return '';
	}
	
	// --------------------------- Metodi di utilità
	
	/**
	 * Ritorna i limiti impostati nei criteri di selezione come codice SQL.
	 *
	 * @param Criteria $criteria criteri di selezione dei record
	 * @return string una stringa SQL con i limiti (la stringa potrebbe essere vuota se non ci sono limiti)
	*/
	protected function getLimit(Criteria $criteria) {
		return ($criteria->hasLimit() ? ' '.$this->db->getLimit($criteria->getRecordCount(), $criteria->getStartRecord()) : '');
	}
	
	/**
	 * Ritorna le clausole di ordinamento per un criterio di selezione come codice SQL
	 * 
	 * Eventualmente applica una mappa di corrispondenza tra una chiave ed un'altra.
	 * 
	 * La mappa di corrispondenza è un array associativo così definito:
	 * array(
	 *	'nome_criterio_ordinamento' => 'nuovo_nome'
	 * )
	 * 
	 * @param Criteria $criteria criteri di selezione dei record
	 * @param array $mapping array associativo la mappa di corrispondenza
	 * @return string una stringa vuota o una stringa che comincia per ORDER BY con le clausole
	*/
	protected function getOrderBy(Criteria $criteria, $mappings = array()) {
		$o = $criteria->getOrderBy();
		foreach($o as $k => $v) {
			$o[$k] = (array_key_exists($v[0], $mappings) ? $mappings[ $v[0] ] : $v[0]).' '.($v[1] == Criteria::ORDER_ASC ? 'ASC' : 'DESC');
		}
		return (!empty($o) ? ' ORDER BY ' : '').implode($o, ',').' ';
	}
	
	/**
	 * Esegue del codice SQL in una transazione.
	 * 
	 * Se specificata esegue una callback all'interno della transazione
	 * utilizzando i dati generati dalle query
	 * 
	 * La callback accetta come parametri il ResultSet generato 
	 * dall'esecuzione della o delle query SQL.
	 * Se la callback ritorna FALSE viene fatto il rollback 
	 * della transazione, altrimenti viene fatto il commit e ritornato
	 * il valore della callback
	 * 
	 * In caso di errore col DB esegue il rollback
	 * 
	 * @param string|array $sql stringa o array di stringhe di codice SQL da eseguire
	 * @param callback $result_callback callback da eseguire o NULL per ignorare
	 * @return mixed TRUE se tutto ok, FALSE in caso di errori, in presenza
	 * di calllback ritorna il valore ritornato dalla stessa.
	 * 
	 * @throws DBException in caso di errore col database
	 **/
	public function executeSQL($sql, $result_callback = NULL) {
		
		if (empty($sql)) return FALSE;
		
		$this->db->begin();
		try {
			if (is_array($sql)) {
				$resultset = $this->db->execQueries($sql);
			} else {
				$resultset = $this->db->execQuery($sql);
			}
			
			// Invoca la callback per gestire i risultati
			if (is_callable($result_callback)) {
				$ret = call_user_func($result_callback, $resultset);
				if ( $ret === FALSE ) {
					$this->db->rollback();
					return FALSE;
				} else {
					$this->db->commit();
					return $ret;
				}
			} else {
				$this->db->commit();
				return TRUE;
			}
		}
		catch (DBException $e) {
			$this->db->rollback();
			throw $e;
		}
		return FALSE;
	}
	
	/**
	 * Data una stringa in ingresso, ritorna una stringa da porre nella clausola
	 * WHERE per eseguire una ricerca testuale sulle parole che la compongono
	 * @param string $str stringa con le parole chiave
	 * @param array $fields array i nomi dei campi in cui eseguire la ricerca
	 * @param boolean $use_and TRUE usa AND per collegare le varie stringhe di ricerca, altrimenti usa OR
	 * @return string una stringa da utilizzare nella clausola WHERE: la stringa potrebbe essere vuota
	 * @see cleanSearchString
	 * */
	public function getSearchEngineSQL($str, $fields, $use_and = FALSE) {
		if (empty($fields)) return '';
		// Ripulisce la stringa di ricerca, in modo da creare un array di parole
		
		// Toglie gli spazi doppi ed i caratteri spuri
		$s = preg_replace('/[\s,:;\.]+/u', ' ', $this->cleanSearchString($str) );
		$s = preg_replace('/[^%\s\w\pL\pN]+/u', '_',$s);
		// rimuove i duplicati: al massimo un carattere sconosciuto
		$s = preg_replace('/__+/u', '',$s);
		$s = str_replace('%', '\\%', $s);
		
		if (strlen(trim($s)) > 0) {
			$parti = explode(' ', $s);
			
			// prepara la stringa base
			$f2 = array();
			foreach($fields as $field) $f2[] = strval($field).' like #V#A#L#U#E#';
			$base = '('.implode($f2, ' or ').')';
			
			foreach($parti as $k => $v) {
				$v = trim($v);
				if (empty($v) || strcmp($v, '_') == 0) unset($parti[$k]);
				else {
					$parti[$k] = str_replace('#V#A#L#U#E#', $this->getDB()->addSlashes('%'.$v.'%'), $base);
				}
			}

			// Se può applicare la selezione, ritorna la giusta stringa SQL
			if (!empty($parti)) {
				return implode($parti, $use_and ? ' and ' : ' or ');
			}
		}
		return '';
	}
	
	/**
	 * Rimuove le parole non volute dalla stringa di ricerca prima di processarla.
	 * 
	 * @param string $s stringa di ricerca
	 * @return string la stringa ripulita
	 * @see getSearchEngineSQL
	 */
	protected function cleanSearchString($s) {
		return $s;
	}
}

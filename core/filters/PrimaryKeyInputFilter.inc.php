<?php
/**
 * (c) 2008, 2009 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Verifica se una o più chiavi primarie nella richiesta HTTP esistano
 * in una tabella.
 * La chiave primaria dovrebbe essere un intero senza segno.
 *
 * E' la base per altre classi.
 *
 * @author Luca Gambetta <l.gambetta@bluenine.it>
*/
class PrimaryKeyInputFilter extends InputFilter {
	protected
		$dberror,
		$db,$table_name,$primary_key_name,
		$value_is_array;
	
	/**
	 * @param DatabaseDriver $db la connessione DB su cui lavorare
	 * @param string $table_name nome della tabella in cui verificare la chiaver primaria
	 * @param string $primary_key_name nome della colonna con la chiave primaria
	 * @param boolean $value_is_array TRUE il valore è un array, FALSE il valore è una singola chiave
	 * @param string $fieldname nome della variabile HTTP da cui prendere i valori
	 * @param boolean $allow_null TRUE accetta NULL come valore, FALSE altrimenti
	*/
	public function __construct(DatabaseDriver $db, $table_name, $primary_key_name, $value_is_array, $fieldname, $allow_null = FALSE) {
		$this->dberror = NULL;
		$this->db = $db;
		$this->table_name = $table_name;
		$this->primary_key_name = $primary_key_name;
		$this->value_is_array = $value_is_array;
		parent::__construct($fieldname, $allow_null);
	}
	
	public function checkValue($value) {
		// Verifica la correttezza del valore
		if (is_array($value) && !$this->value_is_array) return FALSE;

		if (is_array($value)) {
			// Se l'array è vuoto ritorna TRUE
			if (empty($value)) return TRUE;

			foreach($value as $v) if (!$this->checkPrimaryKey($v)) return FALSE;
		} else {
			if (!$this->checkPrimaryKey($value)) return FALSE;
		}
		
		// verifica usando il database
		return $this->checkPKonDB($value);
	}
	
	/**
	 * Verifica se una chiave primaria sia sintatticamente corretta
	 * 
	 * @param integer $value valore da verificare
	 * @return boolean TRUE se è corretta, FALSE altrimenti
	*/
	public function checkPrimaryKey($value) {
		return preg_match('/^\d+$/', strval($value)) == 1;
	}
	
	/**
	 * Lancia una query SQL per verifcare l'esistenza dei valori nella
	 * tabella di database. Dopodichè estrae i valori validi.
	 *
	 * In caso di errore imposta salva l'eccezione, recuperabile con {@link getDBError}
	 *
	 * @param mixed $value valore di cui verificare l'esistenza sul DB: è un intero o un array di interi
	 * @return boolean TRUE se il valore esiste nel database, FALSE altrimenti (o in caso di errore)
	*/
	public function checkPKOnDB($value) {
		$this->dberror = NULL;
		try {
			$rs = $this->db->execQuery( $this->getSQL($value) );
			$data = $rs->fetchAll();
			$rs->free();
			if (is_array($data)) {
				$data = array_shift($data);
				$good = array_shift($data);
				return $good == 't';
			}
			return FALSE;
		}
		catch(DBException $e) {
			$this->dberror = $e;
			return FALSE;
		}
	}
	
	/**
	 * Ritorna il codice SQL per eseguire la verifica della rispondenza dei valori.
	 * 
	 * Deve generare un ResultSet in cui il primo valore sia:
	 * 		't' se i dati sono validi, 'f' altrimenti
	 * 	La verifica viene fatta sul tutto, ossia tutti i valori devono corrispondere
	 * @param mixed $value è un intero o un array di interi con le chiavi da verificare
	 * @return string stringa con la query SQL
	*/
	public function getSQL($value) {
		if (is_array($value)) {
			return 'select if(count(*) = '.strval(count($value)).', \'t\', \'f\') as primary_key_matches from '.
			$this->table_name.' WHERE '.$this->table_name.'.'.$this->primary_key_name.' in ('.implode($value, ',').')';
		} else {
			return 'select if(count(*) = 1, \'t\', \'f\') as primary_key_matches from '.
			$this->table_name.' WHERE '.$this->table_name.'.'.$this->primary_key_name.' = '.strval($value);
		}
	}
	
	/**
	 * Ritorna la connessione al database
	 *
	 * @return DatabaseDriver la connessione al database
	*/
	public function getDB() {
		return $this->db;
	}
	
	/**
	 * Informa se ci siano errori di accesso al database
	 *
	 * @return boolean TRUE se ci sono errori, FALSE altrimenti
	*/
	public function hasDBError() {
		return is_object($this->dberror);
	}

	/**
	 * Ritorna l'eventuale errore generato dalle query SQL
	 * @return DBException l'eccezione generata dall'errore SQL
	 */
	public function getDBError() {
		return $this->dberror;
	}
}

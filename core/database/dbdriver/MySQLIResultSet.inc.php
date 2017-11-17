<?php
/**
 * (c) 2012 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */


/**
 * ResultSet per database MySQL, driver mysqli
 * 
 * @author Luca Gambetta <l.gambetta@bluenine.it>
 **/
class MySQLIResultSet extends ResultSet {
	private
		$do_stripslashes = FALSE, // Flag: esegue o meno stripslash
		$stripslashes_function = NULL, // callback che rimuove gli slash dal resulset
		$my_fetchmode = NULL;
	
	public function setFetchMode($mode) {
		$this->my_fetchmode = MYSQLI_ASSOC;
		
		if ($mode == self::DB_ASSOC) $this->my_fetchmode = MYSQLI_ASSOC;
		elseif ($mode == self::DB_NUM) $this->my_fetchmode = MYSQLI_NUM;
		elseif ($mode == self::DB_BOTH) $this->my_fetchmode = MYSQLI_BOTH;
		parent::setFetchMode($mode);
	}
	
	public function setStripslahesFunc($func) {
		$this->stripslashes_function = $func;
	}
	
	public function initialize() {
		// Analizza il tipo di query per ritornare il corretto numero
		// di elementi affetti o ritornati
		$this->affected_rows = -1;
		$this->record_count = -1;
		
		if ($this->query_id === TRUE || ($this->query_id instanceof mysqli_result)  ) {
			$this->affected_rows = @mysqli_affected_rows( $this->query_id );
			if ($this->affected_rows == -1) throw new DBException(@mysqli_error($this->getDatabaseDriver()->getDBConnection()), @mysqli_errno( $this->getDatabaseDriver()->getDBConnection() ));
			
			$this->record_count = @mysqli_num_rows( $this->query_id );
			if ($this->record_count == -1) throw new DBException(@mysqli_error($this->getDatabaseDriver()->getDBConnection()), @mysqli_errno( $this->getDatabaseDriver()->getDBConnection() ));
		} else {
			throw new DBException(@mysqli_error($this->getDatabaseDriver()->getDBConnection()), @mysqli_errno( $this->getDatabaseDriver()->getDBConnection() ));
		}
		
		$this->do_stripslashes = (ini_get('magic_quotes_runtime') === TRUE);
		$this->stripslashes_function = array($this, 'my_stripslashes');
	}
	
	// ----------- Implementa l'iteratore
	public function rewind() {
		if (@mysqli_data_seek($this->getQueryID(), 0) == FALSE) {
			$this->current_row = NULL;
			$this->current_record = -1;
		} else {
			$this->current_row = $this->fetchRow($this->getQueryID(), 0);
			$this->current_record = 0;
		}
	}
	
	public function fetchRow($row = 0) {
		$r = @mysqli_fetch_array($this->getQueryID(), $this->my_fetchmode);
		if (is_null($r)) {
			return FALSE;
		} else {
			return array_map($this->stripslashes_function, $r);
		}
	}
	
	public function fetchAll() {
		$data = array();
		do {
			$r = @mysqli_fetch_array($this->getQueryID(), $this->my_fetchmode);
			if (is_array($r)) {
				$data[] = array_map($this->stripslashes_function, $r);
			}
		} while (!is_null($r));
		
		return $data;
	}
	
	/**
	 * Metodo per eseguire lo stripping degli slash dai risultati
	 * 
	 * @param string $v stringa col valore o NULL
	 * @return string la stringa col valore senza slash (o NULL se in input è arrivato NULL)
	 */
	public function my_stripslashes($v) {
		if ($this->do_stripslashes) {
			return (is_null($v) ? NULL : stripslashes($v));
		} else {
			return $v;
		}
	}

	// ---------- Altri metodi
	public function seek($where) {
		if (@mysqli_data_seek($this->getQueryID(), $where) === FALSE) {
			throw new DBException("MySQLIResultSet::seek(): can't seek to record ".strval($where));
		} else {
			$this->current_record = $where;
			return TRUE;
		}
	}
	
	public function free() {
		@mysqli_free_result($this->getQueryID());
		$this->query_id = NULL;
	}

	public function countFields() {
		if (is_object($this->getQueryID())) {
			return @mysqli_num_fields( $this->getQueryID() );	
		} else {
			throw new DBException("MySQLResultSet::countFields(): Invalid query handle");
		}
	}

}

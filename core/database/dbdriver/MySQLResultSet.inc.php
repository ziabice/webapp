<?php
/**
 * (c) 2008, 2009 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */


/**
 * ResultSet per database MySQL
 * 
 * @author Luca Gambetta <l.gambetta@bluenine.it>
 **/
class MySQLResultSet extends ResultSet {
	private
		$stripslashes_function = NULL, // callback che rimuove gli slash dal resulset
		$do_stripslashes = FALSE, // Flag: esegue o meno stripslash
		$my_fetchmode = NULL;
	
	public function setFetchMode($mode) {
		$this->my_fetchmode = MYSQL_ASSOC;
		
		if ($mode == self::DB_ASSOC) $this->my_fetchmode = MYSQL_ASSOC;
		elseif ($mode == self::DB_NUM) $this->my_fetchmode = MYSQL_NUM;
		elseif ($mode == self::DB_BOTH) $this->my_fetchmode = MYSQL_BOTH;
		parent::setFetchMode($mode);
	}
	
	/**
	 * Imposta la funzione che eseguirà lo stripping degli slash dai risultati.
	 * 
	 * La callback accetta in input una stringa o il valore NULL e ritorna (nel caso
	 * il parametro sia una stringa) il valore senza slash.
	 * 
	 * @param callback $func
	 */
	public function setStripslahesFunc($func) {
		$this->stripslashes_function = $func;
	}
	
	public function initialize() {
		// Analizza il tipo di query per ritornare il corretto numero
		// di elementi affetti o ritornati
		$this->affected_rows = -1;
		$this->record_count = -1;
		$q = strtolower(trim($this->getSQL()));
		if (strncmp('select ', $q, 7) == 0 || strncmp('show ', $q, 5) == 0) {
			$num = @mysql_num_rows($this->getQueryID());
			if ($num === FALSE) {
				throw new DBException(@mysql_error($this->getDatabaseDriver()->getDBConnection()), @mysql_errno( $this->getDatabaseDriver()->getDBConnection() ));
			} else {
				$this->record_count = $num;
			}
		} else {
			$num = @mysql_affected_rows($this->getQueryID());
			if ($num < 0) {
				throw new DBException(@mysql_error($this->getDatabaseDriver()->getDBConnection()), @mysql_errno( $this->getDatabaseDriver()->getDBConnection() ));
			} else {
				$this->affected_rows = $num;
			}
		}

		$this->do_stripslashes = (ini_get('magic_quotes_runtime') === TRUE);
		$this->stripslashes_function = array($this, 'my_stripslashes');	
	}
	
	// ----------- Implementa l'iteratore
	public function rewind() {
		if (@mysql_data_seek($this->getQueryID(), 0) == FALSE) {
			$this->current_row = NULL;
			$this->current_record = -1;
		} else {
			$this->current_row = $this->fetchRow($this->getQueryID(), 0);
			$this->current_record = 0;
		}
	}
	
	public function fetchRow($row = 0) {
		if (($r = @mysql_fetch_array($this->getQueryID(), $this->my_fetchmode)) !== FALSE) {
			return array_map($this->stripslashes_function, $r);
		} else {
			return FALSE;
		}
	}
	
	public function fetchAll() {
		$data = array();
		while (($r = @mysql_fetch_array($this->getQueryID(), $this->my_fetchmode)) !== FALSE) {
			$data[] = array_map($this->stripslashes_function, $r);
		}
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
		if (@mysql_data_seek($this->getQueryID(), $where) === FALSE) {
			throw new DBException("MySQLResultSet::seek(): can't seek to record ".strval($where));
		} else {
			$this->current_record = $where;
			return TRUE;
		}
	}
	
	public function free() {
		if (@mysql_free_result($this->getQueryID()) == FALSE) {
			$errno = @mysql_errno($this->getDatabaseDriver()->getDBConnection());
			if ($errno != 0) throw new DBException(@mysql_error($this->getDatabaseDriver()->getDBConnection()), $errno );
		}
		$this->query_id = NULL;
	}

	public function countFields() {
		if (is_resource($this->getQueryID())) {
			$n = @mysql_num_fields($this->getQueryID());
			if ($n === FALSE) {
				throw new DBException(@mysql_error($this->getDatabaseDriver()->getDBConnection()), @mysql_errno($this->getDatabaseDriver()->getDBConnection()));
			} else {
				return $n;
			}
		} else {
			throw new DBException("MySQLResultSet::countFields(): Invalid query handle");
		}
	}

}

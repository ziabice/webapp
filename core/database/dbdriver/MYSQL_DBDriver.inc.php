<?php
/**
 * (c) 2008, 2009 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Driver per gestire l'interazione col db server MySQL
 *
 * @author Luca Gambetta <l.gambetta@bluenine.it>
 *
*/
class MYSQL_DBDriver extends DatabaseDriver {
	
	public function useDatabase($dbname) {

		Logger::getInstance()->debug(get_class($this)."::useDatabase ".$dbname);

		if (@mysql_select_db($dbname, $this->getDBConnection()) === FALSE) {
			throw new DBException(@mysql_error(), @mysql_errno());
		}
		return TRUE;
	}

	protected function connect(DSNParser $dsn_data) {
		// Ripulisce i dati di connessione
		if ($dsn_data->getParams()->hasKey('persistent')) {
			$persistent = $dsn_data->getParams()->hasKey('persistent') == 'true';
		} else {
			$persistent = FALSE;
		}
		$host = $dsn_data->getHost().(strlen($dsn_data->getPort()) > 0 ?':'.$dsn_data->getPort() : '');

		Logger::getInstance()->debug(get_class($this)."::connect HOST: ".(empty($host) ? 'empty' : $host).' USER: '.$dsn_data->getUser().' PERSISTENT: '.($persistent?'yes':'no'));

		$db_conn = ($persistent ? @mysql_pconnect($host, $dsn_data->getUser(), $dsn_data->getPassword()) : @mysql_connect($host, $dsn_data->getUser(), $dsn_data->getPassword()));
		$this->setDBConnection($db_conn);
		if ($db_conn === FALSE) {
			throw new DBException(@mysql_error(), @mysql_errno());
		}
		$this->setPersistentConnection($persistent);
		$this->useDatabase($dsn_data->getDatabase());
		if ($dsn_data->getParams()->hasKey('charset')) $this->setCharset($dsn_data->getParams()->get('charset'));
		
	}

	/**
	 * Chiude la connessione al db
	 * @throws DBException se fallisce
	*/
	public function __destruct() {
		if (is_resource($this->getDBConnection())) {
			// Chiude solo se la connessione non è persistente...
			if (!$this->hasPersistentConnection() ) {
				Logger::getInstance()->debug(get_class($this)."::__destruct");
				if (@mysql_close($this->getDBConnection()) === FALSE) {
					// throw new DBException(mysql_error(), mysql_errno());
				}
			}
		}
	}
	
	private function raiseDBException() {
		throw new DBException(@mysql_error($this->getDBConnection()), @mysql_errno($this->getDBConnection()));
	}
	
	public function setCharset($charset) {
		Logger::getInstance()->debug(get_class($this)."::setCharset ".$charset);

		if (@mysql_query("SET NAMES '".$charset."'", $this->getDBConnection()) === FALSE) {
			$this->raiseDBException();
		}
		if (@mysql_query("SET CHARACTER SET '".$charset."'", $this->getDBConnection()) === FALSE) {
			$this->raiseDBException();
		}
	}

	public function isConnected() {
		return @mysql_ping($this->db);
	}

	public function addSlashes($str) {
		if (get_magic_quotes_runtime() == 1) $str = stripslashes($str);
		$str = @mysql_real_escape_string($str, $this->getDBConnection());
		if ($str === FALSE) {
			$this->raiseDBException();
		} else {
			return "'".$str."'";
		}
	}
	
	
	public function execSQL($sql) {
		if ( ($qid = @mysql_query($sql, $this->getDBConnection())) === FALSE) {
			// Verifica se sia un errore di record duplicato
			$err_num = @mysql_errno($this->getDBConnection());
			if ($err_num == 1062) {
				throw new DBDuplicateRecordException($sql, @mysql_error($this->getDBConnection()), $err_num);
			} else {
				throw new DBQueryException($sql, @mysql_error($this->getDBConnection()), $err_num);
			}
		} else {
			return $qid;
		}
	}
	
	protected function newResultSetFromQuery($query_id, $sql = '') {
		return new MySQLResultSet($this, $query_id, $sql);
	}
	

	public function close() {
		Logger::getInstance()->debug(get_class($this)."::close ");
		if (@mysql_close($this->getDBConnection()) === FALSE) {
			$this->raiseDBException();
		}
	}
	
	/**
		Comincia una transazione
		@throws DBException se fallisce
	*/
	public function begin() {
		return $this->doSQL("BEGIN WORK");
	}

	/**
		Fa il commit della transazione corrente
		@throws DBException se fallisce
	*/
	public function commit() {
		return $this->doSQL("COMMIT");
	}

	/**
		Fa il rollback della transazione corrente
		@throws DBException se fallisce
	*/
	public function rollback() {
		return $this->doSQL("ROLLBACK");
	}

	private function doSQL($sql) {
		Logger::getInstance()->debug(get_class($this)."::doSQL Query: ".$sql);
		if (@mysql_query($sql, $this->getDBConnection()) === FALSE) {
			$this->raiseDBException();
		}
		return TRUE;
	}
}


<?php
/**
 * (c) 2008, 2009 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Driver per gestire l'interazione col db server PostgreSQL
 *
 * @author Luca Gambetta <l.gambetta@bluenine.it>
 *
*/
class PGSQL_DBDriver extends DatabaseDriver {
	
	public function __destruct() {
		if (is_resource($this->getDBConnection())) {
			// Chiude solo se la connessione non è persistente...
			if (!$this->hasPersistentConnection() ) {
				Logger::getInstance()->debug(get_class($this)."::__destruct");
				if (@pg_close($this->getDBConnection()) === FALSE) {
					// throw new DBException(mysql_error(), mysql_errno());
				}
			}
		}
	}

	protected function connect(DSNParser $dsn_data) {
		// prepara la stringa di connessione
		$conn_string = sprintf("dbname='%s' user='%s' password='%s'", addslashes( $dsn_data->getDatabase() ), addslashes($dsn_data->getUser()), addslashes($dsn_data->getPassword()) );

		if (strlen($dsn_data->getPort()) > 0) $conn_string .= " port=".strval($dsn_data->getPort());
		if (strlen($dsn_data->getHost()) > 0) $conn_string .= " host='".addslashes($dsn_data->getHost())."'";

		$this->setPersistent( $dsn_data->getParams()->hasKey('persistent') );
		foreach($dsn_data->getParams() as $p => $v) if ($p != 'persistent') $conn_string .= " ".strval($p).'=\''.addslashes($v).'\'';

		Logger::getInstance()->debug('PGSQL_DBDriver::connect: connecting with connection string: '.$conn_string);

		if ($this->hasPersistentConnection()) {
			$db = @pg_pconnect($conn_string);
		} else {
			$db = @pg_connect($conn_string);
		}
		if ($db !== FALSE) {
			$this->setDBConnection($db);
		} else {
			throw new DBException('Connection failed', 0);
		}
	}

	public function setCharset($encoding) {
		Logger::getInstance()->debug(get_class($this)."::setCharset ".$encoding);
		if (@pg_set_client_encoding($this->getDBConnection(), $encoding) != 0) {
			throw new $this->get_pg_exception();
		}
	}


	public function isDuplicateRecord(DBException $exception) {
		if (strncasecmp($error->getMessage(), 'ERROR:', 6) == 0) {
			return (stripos($error->getMessage(), 'duplicate key value') !== FALSE);
		}
		return FALSE;
	}

	public function execSQL($sql) {
		/*
		 * E se usassi connessione asincrone?
		 */
		$result = @pg_query($this->getDBConnection(), $sql);
		if ($result !== FALSE) {
			return $result;
		} else {
			$msg = @pg_last_error($this->getDBConnection());
			if ($msq === FALSE) {
				throw new DBException("Can't connect to get the error message", 0);
			}
			// Verifica se si tratti di record duplicato
			if (stripos($msg, 'duplicate key value') !== FALSE) {
				throw new DBDuplicateRecordException();
			} else {
				// altrimenti errore normale
				throw new DBQueryException($sql, $msg);
			}
		}
	}

	protected function newResultSetFromQuery($query_id, $sql = '') {
		return new PgSQLResultSet($this, $query_id, $sql);
	}

	/*
	 * Solleva un'eccezione in base alla connessione corrente.
	 * 
	 * @return DBException l'eccezione
	 */
	private function get_pg_exception() {
		return new DBException(pg_last_error($this->getDBConnection()), 0);
	}

	public function addSlashes($str) {
		if (get_magic_quotes_runtime() == 1) $str = stripslashes($str);
		return "'".pg_escape_string($this->getDBConnection(), $str)."'";
	}

	private function raiseException($result) {
		throw new DBException(pg_result_error($result), 0);
	}

	public function close() {
		pg_close($this->getDBConnection());
	}

	public function useDatabase($dbname) {
		return TRUE;
	}

	public function commit() {
		$this->execSQL('COMMIT');
	}

	public function begin() {
		$this->execSQL('BEGIN WORK');
	}
	public function isConnected() {
	}

	public function rollback() {
		$this->execSQL('ROLLBACK');
	}
}


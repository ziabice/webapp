<?php
/**
 * (c) 2012 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Driver per gestire l'interazione col db server MySQL, interfaccia MySQLI
 *
 * @author Luca Gambetta <l.gambetta@bluenine.it>
 *
*/
class MYSQLI_DBDriver extends DatabaseDriver {
	
	public function useDatabase($dbname) {

		Logger::getInstance()->debug("MySQLI::useDatabase ".$dbname);

		if (@mysqli_select_db( $this->getDBConnection(), $dbname) === FALSE) {
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
		$host = $dsn_data->getHost();

		Logger::getInstance()->debug("MySQLI::connect HOST: ".(empty($host) ? 'empty' : $host).' USER: '.$dsn_data->getUser().' PERSISTENT: '.($persistent?'yes':'no'));

		$db_conn = @mysqli_connect($host, $dsn_data->getUser(), 
				$dsn_data->getPassword(), $dsn_data->getDatabase(),
				(strlen($dsn_data->getPort()) > 0 ? $dsn_data->getPort() : ini_get("mysqli.default_port") ));
		$this->setDBConnection($db_conn);
		if ($db_conn === FALSE) {
			throw new DBException(@mysqli_connect_error(), @mysqli_connect_errno());
		}
		$this->setPersistentConnection($persistent);
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
				Logger::getInstance()->debug("MySQLI::__destruct");
				if (@mysqli_close($this->getDBConnection()) === FALSE) {
			
				}
			}
		}
	}
	
	private function raiseDBException() {
		throw new DBException(@mysqli_error($this->getDBConnection()), @mysqli_errno($this->getDBConnection()));
	}
	
	public function setCharset($charset) {
		Logger::getInstance()->debug(get_class($this)."::setCharset ".$charset);
		
		if (@mysqli_set_charset($this->getDBConnection(), $charset) === FALSE) {
			$this->raiseDBException();	
		}
		
		if (@mysqli_query($this->getDBConnection(), "SET NAMES '".$charset."'") === FALSE) {
			$this->raiseDBException();
		}
		if (@mysqli_query($this->getDBConnection(), "SET CHARACTER SET '".$charset."'" ) === FALSE) {
			$this->raiseDBException();
		}
	}

	public function isConnected() {
		return @mysqli_ping($this->getDBConnection());
	}

	public function addSlashes($str) {
		if (get_magic_quotes_runtime() == 1) $str = stripslashes($str);
		$str = @mysqli_real_escape_string($this->getDBConnection(), $str);
		if ($str === FALSE) {
			$this->raiseDBException();
		} else {
			return "'".$str."'";
		}
	}
	
	
	public function execSQL($sql) {
		$qid = @mysqli_query($this->getDBConnection(), $sql);
		if ($qid === FALSE) {
			$err_num = @mysqli_errno($this->getDBConnection());
			if ($err_num == 1062) {
				throw new DBDuplicateRecordException($sql, @mysqli_error($this->getDBConnection()), $err_num);
			} else {
				throw new DBQueryException($sql, @mysqli_error($this->getDBConnection()), $err_num);
			}
		} else {
			return $qid;
		}
	}
	
	protected function newResultSetFromQuery($query_id, $sql = '') {
		return new MySQLIResultSet($this, $query_id, $sql);
	}
	

	public function close() {
		Logger::getInstance()->debug("MySQLI::close ");
		if (@mysqli_close($this->getDBConnection()) === FALSE) {
			$this->raiseDBException();
		}
	}
	
	/**
	 * 
	 * Comincia una transazione
	 * 
	 * @throws DBException se fallisce
	 * */
	public function begin() {
		if (@mysqli_autocommit( $this->getDBConnection(), FALSE ) == FALSE) {
			$this->raiseDBException();	
		}
		return TRUE;
	}

	/**
	 * 
	 * Fa il commit della transazione corrente
	 * 
	 * @throws DBException se fallisce
	 * */
	public function commit() {
		if (@mysqli_commit( $this->getDBConnection() ) == FALSE) {
			$this->raiseDBException();
		}
	}

	/**
	 * 
	 * Fa il rollback della transazione corrente
	 * 
	 * @throws DBException se fallisce
	 * */
	public function rollback() {
		if (@mysqli_rollback( $this->getDBConnection() ) == FALSE) {
			$this->raiseDBException();
		}
	}
}


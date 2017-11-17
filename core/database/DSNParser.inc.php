<?php
/**
 * (c) 2008, 2009 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */


/**
 * Esegue il parsing di stringhe DSN (Data Source Name)
 *
 * La stringa deve essere nella forma:
 *
 * motoredb://[[nome_utente][:password]@]host[:porta]/database?opzione=valore&opzione=valore&...
 *
 * I seguenti caratteri all'interno degli elementi vanno convertiti nelle
 * rispettive entità (con l'encoding esadecimale delle URI tipico di urlencode):
 *
 * : = %3a   / = %2f   @ = %40
 * + = %2b   ( = %28   ) = %29
 * ? = %3f   = = %3d   & = %26
 *
 * Tutte le opzioni aggiuntive vengono poste in un {@link HashMap}.
 * @author Luca Gambetta <l.gambetta@bluenine.it>
 */
class DSNParser {
	protected
		
		$db_engine = '',
		$user = '',
		$password = '',
		$database = '',
		$host = '',
		$port = '',
		$protocol = '',

		$is_valid,
		$params;

    /**
	 * Costruisce con il DSN: effettua il parsing.
	 * @param string $dsn stringa col DSN
	 */
	public function __construct($dsn) {
		$this->params = new HashMap();
		$this->is_valid = $this->parse($dsn);
	}

	/**
	 * Effettua il parsing di una stringa DSN, ponendo i valori
	 * nelle variabili interne
	 *
	 * @param string $dsn stringa da elaborare
	 * @return boolean TRUE se la stringa è valida, FALSE altrimenti
	 */
	public function parse($dsn) {
		$this->clear();
		$valid = FALSE;
		// estrae il motore DB
		$p = strpos($dsn, '://');
		
		if ($p !== FALSE) {
			$this->db_engine = substr($dsn, 0, $p);
			// spezza la stringa ed estrae il resto
			$dsn = substr(strstr($dsn, '://'), 3);
			$parts = array();
			if (preg_match('/^(((?P<user>[^:]+)(:(?P<password>[^@]+))?)@)?(?P<dsn>.*)/', $dsn, $parts) == 1) {
				// in un colpo solo ha user e password
				if (array_key_exists('user', $parts)) $this->user = urldecode($parts['user']);
				if (array_key_exists('password', $parts)) $this->password = urldecode($parts['password']);
				if (array_key_exists('dsn', $parts)) {
					$dsn = $parts['dsn'];
					if (preg_match('/^((?P<host>[\w\.\-]+)(:(?P<port>[^\/]+))?)\/(?P<database>[^\?]+)(\?(?P<options>.*))?/', $dsn, $parts)) {
						if (array_key_exists('host', $parts)) $this->host = urldecode($parts['host']);
						if (array_key_exists('database', $parts)) $this->database = urldecode($parts['database']);
						if (array_key_exists('options', $parts)) {
							$o = array();
							$opt = preg_match_all('/&?(([^=]+)=([^&]*))/', $parts['options'], $o);
							if ($opt > 0) {
								foreach($o[2] as $idx => $key) {
									$this->params->set($key, urldecode($o[3][$idx]));
								}
								$valid = TRUE;
							}
						} else {
							$valid = TRUE;
						}
					}
				}

			}
		}

		return $valid;
	}

	/**
	 * Data una stringa DSN ritorna l'engine db che essa indica,
	 * se non valida ritorna FALSE
	 * @param string $dsn stringa col DSN della connessione
	 * @return string|boolean ritorna la stringa con l'engine, FALSE se la stringa non è un DSN valido
	 */
	public static function getDSNEngine($dsn) {
		$p = strpos($dsn, '://');

		if ($p !== FALSE) return substr($dsn, 0, $p);
		return FALSE;
	}

	/**
	 * Ripulisce la cache dei dati interni
	 */
	protected function clear() {
		$this->db_engine = '';
		$this->user = '';
		$this->password = '';
		$this->database = '';
		$this->host = '';
		$this->port = '';
		$this->protocol = '';
	}

	/**
	 * Informa se la stringa DSN sia valida
	 * @return boolean TRUE se la stringa è valida, FALSE altrimenti
	 */
	public function isValid() {
		return $this->is_valid;
	}

	/**
	 * Ritorna il nome utente definito nella DSN (ripulito da eventuali codici
	 * di escape esadecimali)
	 * @return string il nome utente
	 */
	public function getUser() {
		return $this->user;
	}

	/**
	 * Ritorna la password posta nella DSN
	 * @return string la password
	 */
	public function getPassword() {
		return $this->password;
	}

	/**
	 * Ritorna il nome del database a cui connettersi
	 * @return string il nome del database
	 */
	public function getDatabase() {
		return $this->database;
	}

	/**
	 * Ritorna il database engine da utilizzare
	 * @return string il nome del db engine
	 */
	public function getDatabaseDriver() {
		return $this->db_engine;
	}

	/**
	 * Ritorna il nome host a cui connettersi
	 * @return string il nome host
	 */
	public function getHost() {
		return $this->host;
	}

	/**
	 * Ritorna la porta a cui connettersi
	 * @return string il numero di porta
	 */
	public function getPort() {
		return $this->port;
	}

	/**
	 * Ritorna gli eventuali parametri aggiuntivi presenti nel DSN,
	 * come associazioni di etichetta con valore
	 * @return HashMap l'hashmap con i valori
	 */
	public function getParams() {
		return $this->params;
	}


	/**
	 * Ritorna il protocollo di connessione
	 * 
	 * @return string il protocollo
	 */
	public function getProtocol() {
		return $this->protocol;
	}
}

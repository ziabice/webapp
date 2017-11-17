<?php
/**
 * (c) 2008, 2009 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Gestisce i driver per i database, permettendo di costruire il giusto driver
 *
 * @author Luca Gambetta <l.gambetta@bluenine.it>
 */
class DatabaseDriverManager {

	/**
	 * Ritorna il driver di database che può gestire la connessione indicata
	 * nel DSN.
	 *
	 * Il valore FALSE viene ritornato se il DSN non è valido o se non esiste
	 * una classe di gestione per l'engine specifico.
	 *
	 * La classe database cercata deve avere per nome quello dell'engine, ma in maiuscolo,
	 * seguito da '_DBDriver'. Ad esempio il DSN: 'mysql://localhost/db' indica un engine 'mysql'
	 * perciò la classe DatabaseDriver cercata sarà 'MYSQL_DBDriver'.
	 *
	 * Ogni engine db potrebbe aver bisogno di un perser di DSN specifico, perciò viene
	 * usata {@link getDSNParser} per ricavarlo.
	 *
	 * @param string $dsn la stringa di connessione
	 * @return DatabaseDriver il driver per la connessione o FALSE se non è possibile crearlo
	 */
	public static function getDatabaseDriver($dsn) {
		$dsn_parser = self::getDSNParser($dsn);
		if (is_object($dsn_parser)) {
			if ($dsn_parser->isValid()) {
				$class_name = strtoupper($dsn_parser->getDatabaseDriver()).'_DBDriver';
				if (class_exists($class_name)) {
					$db = new $class_name ( $dsn_parser );
					return $db;
				}
			}
		}
		return FALSE;
	}

	/**
	 * Ritorna il parser di DSN specifico per un certo engine db.
	 * @param string $dsn la stringa DSN da cui estrarre l'engine
	 * @return DSNParser il parser di DSN o NULL se l'engine non è supportato
	 */
	public static function getDSNParser($dsn) {
		$engine = DSNParser::getDSNEngine($dsn);
		if ($engine !== FALSE) {
			return new DSNParser($dsn);
		}
		return NULL;
	}
}


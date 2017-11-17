<?php
/**
 * (c) 2008, 2009 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Gestisce il logging delle operazioni.
 *
 * Può esserci solo un logger per applicazione, con differenti profili.
 *
 * Il logger va messo nella classe Logger.
 *
 * @author Luca Gambetta <l.gambetta@bluenine.it>
 */
class BaseLogger {
    
	const
		LOG_ALERT = 0, // Errore critico
		LOG_ERROR = 1, // Errore molto grave
		LOG_WARNING = 2, // Errore che richiede attenzione
		LOG_NOTICE = 3, // Notizia (non necessariamente un errore)
		LOG_INFO = 4, // Informazione (un semplice messaggio informativo)
		LOG_DEBUG = 5; // Messaggio di debug

	protected
		$loggers = array(), // i logger degli eventi
		$log_level = self::LOG_ERROR, // il livello di logging attuale
		$log_labels = array( // Etichette associate ai livelli di logging
			self::LOG_ALERT => 'alert',
			self::LOG_ERROR => 'error',
			self::LOG_WARNING => 'warning',
			self::LOG_NOTICE => 'notice',
			self::LOG_INFO => 'info',
			self::LOG_DEBUG => 'debug'
		);

	protected static
		$logger = NULL; // Il singleton del logger

	/**
	 * Ritorna l'istanza del logger.
	 *
	 * Il logger è un Singleton, che se non esiste viene creato.
	 * Viene creato un oggetto dalla classe chiamata Logger.
	 *
	 * @return BaseLogger
	 */
	public static function getInstance() {
		if (!is_object(self::$logger)) {
			self::$logger = new Logger();
			// if (WebAppConfig::has('WEBAPP_LOG_LEVEL')) self::$logger->setLogLevel(WebAppConfig::get('WEBAPP_LOG_LEVEL'));
			// else
			self::$logger->setLogLevel(self::LOG_WARNING);
			self::$logger->initialize();
		}
		return self::$logger;
	}

	/**
	 * Inizializza l'oggetto subito dopo la creazione.
	 *
	 */
	protected function initialize() {
		$this->loggers = array();
	}

	/**
	 * Aggiunge un logger di eventi.
	 *
	 * Quando viene generato un evento questi viene mandato a tutti i logger
	 * registrati, nell'ordine di registrazione.
	 *
	 * @param LoggerInterface $logger
	 */
	public function registerLogger(LoggerInterface $logger) {
		$this->loggers[] = $logger;
	}

	/**
	 * Imposta il livello di logging.
	 * 
	 * Quando un messaggio di logging è di un livello inferiore a quello attuale, 
	 * viene registrato nei log.
	 *
	 * I livelli sono (dal più grave al meno grave):
	 *
	 * LOG_ALERT - Errore critico
	 * LOG_ERROR - Errore molto grave
	 * LOG_WARNING - Errore che richiede attenzione
	 * LOG_NOTICE - Notizia (non necessariamente un errore)
	 * LOG_INFO - Informazione (un semplice messaggio informativo)
	 * LOG_DEBUG - Messaggio di debug
	 *
	 *
	 * @param integer $level
	 */
	public function setLogLevel($level) {
		$this->log_level = $level;
	}

	/**
	 * Ritorna il livello di logging corrente.
	 *
	 * Ritorna un intero col valore di una delle costanti di classe.
	 *
	 * @return integer
	 * @see setLogLevel
	 */
	public function getLogLevel() {
		return $this->log_level;
	}

	/**
	 * Registra un messaggio per i log.
	 *
	 * Quando un messaggio di logging è di un livello inferiore a quello attuale,
	 * viene registrato nei log. Il livello di logging va impostato
	 * usando {@link setLogLevel}.
	 *
	 * I livelli sono (dal più grave al meno grave):
	 *
	 * LOG_ALERT - Errore critico
	 * LOG_ERROR - Errore molto grave
	 * LOG_WARNING - Errore che richiede attenzione
	 * LOG_NOTICE - Notizia (non necessariamente un errore)
	 * LOG_INFO - Informazione (un semplice messaggio informativo)
	 * LOG_DEBUG - Messaggio di debug
	 *
	 * @param string $message messaggio da registrare
	 * @param integer $level livello del messaggio
	 * @see setLogLevel
	 */
	public function log($message, $level = self::LOG_INFO) {
		if ($level <= $this->log_level) {
			foreach($this->loggers as $l) {
				$l->log((string)$message, $level, $this->log_labels[$level]);
			}
		}
	}

	/**
	 * Genera un messaggio del livello LOG_ALERT.
	 *
	 * @param string $message il testo del messaggio
	 */
	public function alert($message) {
		$this->log($message, self::LOG_ALERT);
	}

	/**
	 * Genera un messaggio del livello LOG_ERROR.
	 *
	 * @param string $message il testo del messaggio
	 */
	public function error($message) {
		$this->log($message, self::LOG_ERROR);
	}

	/**
	 * Genera un messaggio del livello LOG_WARNING.
	 *
	 * @param string $message il testo del messaggio
	 */
	public function warning($message) {
		$this->log($message, self::LOG_WARNING);
	}

	/**
	 * Genera un messaggio del livello LOG_INFO.
	 *
	 * @param string $message il testo del messaggio
	 */
	public function info($message) {
		$this->log($message, self::LOG_INFO);
	}

	/**
	 * Genera un messaggio del livello LOG_NOTICE.
	 *
	 * @param string $message il testo del messaggio
	 */
	public function notice($message) {
		$this->log($message, self::LOG_NOTICE);
	}

	/**
	 * Genera un messaggio del livello LOG_DEBUG.
	 *
	 * @param string $message il testo del messaggio
	 */
	public function debug($message) {
		$this->log($message, self::LOG_DEBUG);
	}

	/**
	 * Viene eseguita alla chiusura dell'applicazione (e di conseguenza del logging).
	 *
	 * Nomralmente invoca i metodi shutdown dei vari logger registrati.
	 *
	 */
	public function shutdown() {
		foreach($this->loggers as $l) {
			$l->shutdown();
		}
	}
}

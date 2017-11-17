<?php
/**
 * (c) 2008, 2009 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Esegue il logging su un file
 *
 * @author Luca Gambetta <l.gambetta@bluenine.it>
 */
class FileLogger implements LoggerInterface {
    
	protected
		$app_name,
		$log_file;

	/**
	 * Inizializza il logger.
	 *
	 * @param string $application_name nome dell'applicazione
	 * @param string $filename path del file su cui loggare
	 */
	public function __construct($application_name, $filename) {
		$this->app_name = (string)$application_name;
		$this->log_file = fopen( $filename, 'a' );
	}

	public function log($message, $level, $level_desc) {
		if (is_resource($this->log_file)) {
			flock($this->log_file, LOCK_EX);
			fprintf($this->log_file, "%s %s [%s] %s%s", strftime('%b %d %H:%M:%S'), $this->app_name, $level_desc, (string)$message, (DIRECTORY_SEPARATOR == '\\' ? "\r\n" : "\n"));
			flock($this->log_file, LOCK_UN);
		}
	}
	
	public function shutdown() {
		if (is_resource($this->log_file)) {
			fclose($this->log_file);
		}
	}
}


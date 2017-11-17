<?php
/**
 * (c) 2008, 2009 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */


/**
 * Usa il logger di PHP, ossia la funzione error_log
 *
 * Vanno usati i vari metodi factory.
 *
 * @author Luga Gambetta <l.gambetta@bluenine.it>
 */
class PHPErrorLogger implements LoggerInterface {

	protected
		$message_type = 0,
		$destination = '',
		$extra_headers = '';

	/**
	 * Inizializza il logger.
	 *
	 * @param integer $message_type il tipo di logging usato
	 * @param string $destination la destinazione (il path di un file o un indirizzo email)
	 * @param string $extra_headers eventuali header aggiuintivi quando la destinazione è un'email
	 */
	protected function  __construct($message_type, $destination, $extra_headers) {
		$this->message_type = $message_type;
		$this->destination = $destination;
		$this->extra_headers = $extra_headers;
	}

	/**
	 * Crea un logger che usa i meccanismi di sistema.
	 *
	 * Dal manuale:
	 * <quote>
	 * message is sent to PHP's system logger, using the Operating System's system logging mechanism or a file,
	 * depending on what the error_log  configuration directive is set to.
	 * </quote>
	 *
	 * @return PHPErrorLogger
	 */
	public static function factory() {
		return new PHPErrorLogger(0, '', '');
	}

	/**
	 * Invia i messaggi per email.
	 *
	 * @param string $destination email del destinatario
	 * @param string $extra_headers eventuali header aggiuntivi da usare nell'email
	 * @return PHPErrorLogger
	 */
	public static function emailLogger($destination, $extra_headers='') {
		return new PHPErrorLogger(1, $destination, $extra_headers);
	}

	/**
	 * Logging verso un file.
	 *
	 * Le righe vengono aggiunte in coda al file.
	 *
	 * @param string $file_path il path del file
	 * @return PHPErrorLogger
	 */
	public static function fileLogger($file_path) {
		return new PHPErrorLogger(3, $file_path, '');
	}

	public function log($message, $level, $level_desc) {
		$message = sprintf("%s [%s] %s", 'webapp', $level_desc, (string)$message);
		if ($this->message_type == 3) $message .= (DIRECTORY_SEPARATOR == '\\' ? "\r\n" : "\n");
		error_log($message, $this->message_type, $this->destination, $this->extra_headers);
	}

	public function shutdown() {
	}
}


<?php
/**
 * (c) 2008, 2009 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Interfaccia che descrive un logger di messaggi.
 *
 * Un logger viene usato da BaseLogger per registrare effettivamente un messaggio
 * nel registro.
 *
 * @author Luca Gambetta <l.gambetta@bluenine.it>
 */
interface LoggerInterface {

	/**
	 * Registra un messaggio.
	 *
	 * @param string $message il messaggio da registrare
	 * @param integer $level il livello di priorità del messaggio
	 * @param string $level_desc stringa che descrive il livello di priorità
	 */
	public function log($message, $level, $level_desc);

	/**
	 * Viene eseguita dall'istanza di BaseLogger al momento
	 * dello spegnimento del sistema di logging.
	 */
	public function shutdown();
}

<?php
/**
 * (c) 2008, 2009 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Azione eseguita quando è vietato l'accesso ad una pagina
 *
 * Mostra il messaggio di errore HTTP "403 Forbidden"
*/
class Error_403Action extends WebAppAction {
	
	public function execute() {
		$this->getResponse()->setHTTPStatus(403, 'Forbidden');
		echo "<h1>",tr('Forbidden.'),"</h1>\n";
	}
}


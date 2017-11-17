<?php
/**
 * (c) 2008, 2009 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Azione eseguita quando una azione richiesta non è stata trovata.
 *
 * Mostra il messaggio di errore HTTP "404 Not Found"
*/
class Error_404Action extends WebAppAction {
	
	public function execute() {
		$this->getResponse()->setHTTPStatus(404, 'Not Found');
		echo "<h1>",tr('Page not found.'),"</h1>\n";
	}
}


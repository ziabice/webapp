<?php
/**
 * (c) 2008, 2009 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Azione di default 
 */
class IndexAction extends WebAppAction {
	public function execute() {
		echo "<h1>WebApp: Welcome!</h1>\n";
	}
}


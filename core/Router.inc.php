<?php
/**
 * (c) 2008-2012 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * The default router
 */
class Router extends WebAppRouter {
	public function getDefaultDestination() {
		return 'index';
	}
}

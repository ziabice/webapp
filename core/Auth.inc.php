<?php
/**
 * (c) 2008-2012 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Gestore standard, utilizza la classe User per creare
 * l'utente corrente.
 *
 * @author Luca Gambetta <l.gambetta@bluenine.it>
*/
class Auth extends WebAppAuth {

	protected function guestLogin() {
		$this->login( new User(), TRUE );
	}
	
}


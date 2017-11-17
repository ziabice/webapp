<?php
/**
 * (c) 2008, 2009 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Un gestore di sessione che maschera quello base di PHP.
 * 
 * In pratica interagisce con l'array $_SESSION e le funzioni di gestione
 * della sessione.
 *
 * @author Luca Gambetta <l.gambetta@bluenine.it>
*/
class StandardSessionManager extends BaseSessionManager {

	public function __construct($realm) {
		parent::__construct($realm);
		session_start();
		$this->switchRealm($realm);
	}
	
	public function set($name, $value, $visibility = FALSE) {
		if ($visibility !== FALSE) {
			if (!is_array($visibility)) $visibility = array(strval($visibility));
		}
		$_SESSION[$this->realm][$name] = array(
			'val' => $value,
			'vis' => $visibility
		);
	}
	
	public function getVisibility($name) {
		return $_SESSION[$this->realm][$name]['vis'];
	}
	
	public function get($name) {
		return $_SESSION[$this->realm][$name]['val'];
	}
	
	public function has($name) {
		return array_key_exists($name, $_SESSION[$this->realm]);
	}
	
	public function del($name) {
		if (array_key_exists($name, $_SESSION[$this->realm])) {
			unset($_SESSION[$this->realm][$name]);
		}
	}
	
	public function clear() {
		$_SESSION[$this->realm] = array();
	}
	
	public function getAllNames() {
		return array_keys($_SESSION[$this->realm]);
	}
	
	public function switchRealm($realm) {
		parent::switchRealm($realm);
		if (!isset($_SESSION[$realm])) {
			$_SESSION[$realm] = array();
		}
	}
	
	public function destroySession() {
		$_SESSION = array();
		session_destroy();
	}
	
	public function restartSession() {
		session_start();
		session_regenerate_id();
		$this->switchRealm($this->realm);
	}
	
	public function getSessionID() {
		return session_id();
	}
	
	public function getSessionName() {
		return session_name();
	}
}

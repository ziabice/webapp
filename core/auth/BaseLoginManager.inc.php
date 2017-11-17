<?php
/**
 * (c) 2008-2013 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Astrae le operazioni di login di un utente.
 * 
 * Il metodo {@link doLogin} esegue tutte le operazioni.
 *
 * @author Luca Gambetta <l.gambetta@bluenine.it>
 */
class BaseLoginManager {
	
	const
		STATUS_IDLE = 0,
		STATUS_SUCCESS = 10,
		STATUS_FAIL = 20,
		STATUS_INVALID_AUTHENTICATION = 30,
		STATUS_INVALID_USER = 40,
		STATUS_USER_NOT_FOUND = 45,
		STATUS_WRONG_CREDENTIALS = 50,
		STATUS_INVALID_PRIVILEGES = 60,
		STATUS_DATABASE_ERROR = 100;
	
	protected
			/**
			 * @var User l'utente sul quale si sta lavorando
			 */
			$user,
		/**
		 * @var integer stato dell'autenticazione
		 */
			$status;
	
	/**
	 * Costruisce impostando lo stato iniziale a STATUS_IDLE.
	 * 
	 */
	public function __construct() {
		$this->setStatus(self::STATUS_IDLE);
	}
	
	/**
	 * Ritorna lo stato corrente del processo.
	 * 
	 * Ritorna una delle costanti:
	 * 
	 * 	STATUS_IDLE - il processo non è ancora iniziato
	 *  STATUS_SUCCESS - il processo è andato a buon fine
	 *  STATUS_FAIL - fallimento generico del processo
	 * 	STATUS_INVALID_AUTHENTICATION - autenticazione non valida
	 *  STATUS_INVALID_USER - utente non valido
	 *  STATUS_WRONG_CREDENTIALS - credenziali errate
	 *  STATUS_INVALID_PRIVILEGES - privilegi di accesso non validi
	 *  STATUS_USER_NOT_FOUND - l'utente richiesto non è stato trovato
	 * 
	 * @return integer lo stato del processo 
	 */
	public function getStatus() {
		return $this->status;
	}
	
	/**
	 * Imposta lo stato corrente del processo.
	 * 
	 * Usare una delle costanti:
	 * 
	 * 	STATUS_IDLE - il processo non è ancora iniziato
	 *  STATUS_SUCCESS - il processo è andato a buon fine
	 *  STATUS_FAIL - fallimento generico del processo
	 * 	STATUS_INVALID_AUTHENTICATION - autenticazione non valida
	 *  STATUS_INVALID_USER - utente non valido
	 *  STATUS_WRONG_CREDENTIALS - credenziali errate
	 *  STATUS_INVALID_PRIVILEGES - privilegi di accesso non validi
	 * STATUS_USER_NOT_FOUND - l'utente richiesto non è stato trovato
	 * 
	 * @param integer $status lo stato del processo 
	 */
	protected function setStatus($status) {
		$this->status = $status;
	}

	/**
	 * Verifica che le credenziali dell'utente che si vuole
	 * far entrare siano valide
	 * @return boolean TRUE se le credenziali sono valide, FALSE altrimenti
	 * @see doLogin
	*/
	protected function verifyCredentials() {
		return TRUE;
	}
	
	/**
	 * Ritorna l'utente salvato internamente.
	 * 
	 * @return WebAppUserInterface un utente
	 */
	public function getUser() {
		return $this->user;
	}
	
	/**
	 * Imposta l'utente su cui operare.
	 * 
	 * @param WebAppUserInterface $user l'utente
	 */
	protected function setUser(WebAppUserInterface $user) {
		$this->user = $user;
	}
	
	/**
	 * Informa se sia presente un utente.
	 * 
	 * @return boolean TRUE se ha un utente, FALSE altrimenti
	 * @see setUser
	 * @see getUser
	 */
	public function hasUser() {
		return is_object($this->user);
	}

	/**
	 * Esegue il processo di ingresso identificazione ed autorizzazione di un utente
	 * nel sistema.
	 *
	 * In caso di insuccesso, verificare lo stato usando {@see getStatus}.
	 * 
	 * Prima verifica le credenziali.
	 * In caso di successo estrae l'utente dal database
	 * In caso di successo autentica l'utente estratto
	 * In caso di successo autorizza l'utente estratto
	 * In caso di successo e se espresso, esegue il login nel sistema
	 * Se tutto i precedenti passi hanno successo, allora il processo ha successo
	 * 
	 * @param boolean $login_user TRUE esegue anche il login dell'utente, FALSE altrimenti
	 * @return boolean TRUE in caso di successo, FALSE altrimenti
	 * @see verifyCredentials
	 * @see loadRequestedUser
	 * @see authenticateUser
	 * @see authorizeUser
	 * @see loginUser
	 * */
	public function doLogin($login_user = TRUE) {
		
		$this->setStatus(self::STATUS_IDLE);
		
		// Verifica le credenziali di accesso
		if (!$this->verifyCredentials()) {
			$this->setStatus( self::STATUS_WRONG_CREDENTIALS );
			return FALSE;
		}
		
		// Carica l'utente richiesto in base alle credenziali
		// il metodo ha salvato internamente l'utente estratto
		if (!$this->loadRequestedUser()) {
			$this->setStatus(self::STATUS_USER_NOT_FOUND);
			return FALSE;
		}
		
		if (!$this->authenticateUser()) {
			return FALSE;
		}
		
		if ($this->authorizeUser() ) {
			if ($login_user) {
				if ( $this->loginUser($this->getUser()) ) {
					$this->setStatus(self::STATUS_SUCCESS);
					return TRUE;
				} 
			} else {
				$this->setStatus(self::STATUS_SUCCESS);
				return TRUE;
			}
		} 
		
		return FALSE;
	}

	/**
	 * Estrae dallo storage (tipicamente un database) l'utente richiesto,
	 * utilizzando le credenziali impostate nell'istanza e lo salva internamente.
	 * 
	 * 
	 * @return boolean TRUE se l'utente è stato estratto con successo, FALSE altrimenti.
	 * @see setUser
	 * @see setStatus
	 **/
	protected function loadRequestedUser() {
		return FALSE;
	}

	/**
	 * Autentica l'utente in modo da permettere l'ingresso. Al termine
	 * del processo informa se l'utente possa essere o meno autorizzato
	 * ad entrare nel sistema.
	 * Verifica se l'utente possegga o meno le credenziali necessarie.
	 * 
	 * In caso di utente non valido imposta lo stato interno in accordo al problema
	 * riscontrato.
	 * 
	 * @return boolean TRUE se l'utente può essere autorizzato, FALSE altrimenti
	 * @see setStatus
	 * */
	protected function authenticateUser() {
		if ($this->hasUser()) {
			return TRUE;
		} else {
			$this->setStatus(self::STATUS_INVALID_USER);
			return FALSE;
		}
	}

	/**
	 * Autorizza l'utente ad entrare nel sistema.
	 * 
	 * @return boolean TRUE se il processo è andato a buon fine, FALSE altrimenti
	 * 
	*/
	protected function authorizeUser() {
		if ($this->hasUser()) {
			return TRUE;
		} else {
			$this->setStatus(self::STATUS_INVALID_USER);
			return FALSE;
		}
	}

	/**
	 * Esegue il login dell'utente autenticato
	 *
	 * Eseguita al termine del processo di login, dopo che {@link authorizeUser} ha restituito 
	 * esito positivo.
	 *
	 * @param WebAppUserInterface $user utente autorizzato
	 * @return boolean TRUE se l'operazione ha avuto successo, FALSE altrimenti.
	 */
	protected function loginUser(WebAppUserInterface $user) {
		WebApp::getInstance()->getAuth()->login( $user );
		if ($user->hasLanguage()) {
			WebApp::getInstance()->setLanguage($user->getLanguage(), TRUE);
		}
		return TRUE;
	}
}


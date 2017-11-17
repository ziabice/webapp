<?php
/**
 * (c) 2012 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Gestisce l'autenticazione ed i privilegi d'accesso degli utenti.
 * 
 * Viene costruito ed inizializzato dal front controller.
 * 
 * Dopo aver
 * 
 * @see WebAppFrontController::initializeSecurityManager
 * @author Luca Gambetta <l.gambetta@bluenine.it>
*/
class WebAppAuth {
	
	protected
		/**
		 * @var WebAppUserInterface l'utente autenticato
		 */
		$user; 
	
	/**
	 * Costruisce l'oggetto.
	 * 
	 * É poi necessario invocare {@link initialize} dopo aver inizializzato il gestore
	 * di sessione per poter gestire effettivamente le richieste di autenticazione
	 * usando un utente salvato nella sessione.
	 * 
	 */
	public function __construct() {
		$this->user = NULL;
	}
	
	/**
	 * Esegue l'inizializzazione dopo che la sessione è stata creata.
	 *
	 * Si occupa di creare un utente "guest" se non ce n'è uno in sessione.
	 * 
	 * @see WebApp::initialize
	 */
	public function initialize() {
		$this->user = NULL;
		if ($this->sessionHasUser()) {
			Logger::getInstance()->debug(__CLASS__."::initialize: initialized with user from session.");
			$this->loginUserFromSession();
		}
		
		if (!is_object($this->user)) {
			Logger::getInstance()->debug(__CLASS__."::initialize: initialized with guest from session.");
			$this->guestLogin();
		}
	}
	
	/**
	 * Fa entrare un utente nel sistema: salva l'oggetto nella sessione e ne
	 * imposta il flag di "loggato", a meno che non sia un utente ospite.
	 * Nel qual caso all'utente non vengono concessi i privilegi di ingresso.
	 * 
	 * @param WebAppUserInterface $user un oggetto con l'utente corrente
	 * @param boolean $user_is_guest TRUE l'utente è ospite, FALSE altrimenti
	 *
	*/
	public function login(WebAppUserInterface $user, $user_is_guest = FALSE) {
		if (!$user_is_guest) $user->login();
		$this->putUserIntoSession($user);
		$this->user = $user;
		$this->onUserLogin($user, $user_is_guest);
	}

	/**
	 * Eseguita da {@link login()} quando un utente entra nel sistema
	 * @param WebAppUserInterface $user un utente del sistema
	 * @param boolean $user_is_guest TRUE l'utente è stato loggato come guest, FALSE altrimenti
	 */
	protected function onUserLogin(WebAppUserInterface $user, $user_is_guest) {
	}

	/**
	 * Fa "uscire" l'utente corrente dal sistema.
	 *
	 * Rimuove l'utente dalla sessione e fa entrare al posto suo l'utente "guest".
	 *
	 * @see guestLogin
	*/
	public function logout() {
		WebApp::getInstance()->getSessionManager()->del($this->getUserSessionName());
		$this->user->logout();
		$this->guestLogin();
	}
	
	/**
	 * Reinizializza il gestore delle autorizzazioni, rimuovendo l'utente
	 * corrente ed eventualmente reinizializzando anche il gestore di sessione.
	 * Al termine del processo viene fatto entrare l'utente di default.
	 * E' in pratica una {@link logout()} potenziata.
	 *
	 * @param boolean $destroy_session TRUE reinizializza anche il gestore di sessione, FALSE no
	 */
	public function reInitialize($destroy_session = FALSE) {
		if (is_object($this->user)) $this->user->logout();
		if ($destroy_session) {
			WebApp::getInstance()->getSessionManager()->destroySession();
			WebApp::getInstance()->getSessionManager()->restartSession();
		} else {
			WebApp::getInstance()->getSessionManager()->del($this->getUserSessionName());
		}
		$this->guestLogin();
	}
	
	/**
	 * Pone l'utente "ospite" come utente corrente.
	 * 
	 * L'utente è il "generico ospite" di un sito, un utente coi permessi di default.
	 * 
	 * Viene usata una istanza della classe {@link User}.
	 *
	 */
	protected function guestLogin() {
		$this->login( new User(), TRUE );
	}
	
	/**
	 * Ritorna l'utente attualmente registrato come autenticato.
	 * 
	 * @return WebAppUserInterface una istanza di BaseUser
	*/
	public function getUser() {
		return $this->user;
	}
	
	/**
	 * Imposta l'utente sul quale gestire l'autenticazione/autorizzazione
	 * @param WebAppUserInterface $user l'utente che si vuole impostare
	 * @return WebAppUserInterface l'istanza passata come parametro
	*/
	protected function setUser(WebAppUserInterface $user) {
		$this->user  = $user;
		return $user;
	}
	
	/**
	 * Informa se nella sessione sia presente un utente che è
	 * possibile utilizzare.
	 * @return boolean TRUE se l'utente è presente, FALSE altrimenti
	*/
	protected function sessionHasUser() {
		return WebApp::getInstance()->getSessionManager()->has($this->getUserSessionName());
	}
	
	/**
	 * Ricava l'utente correntemente loggato dalla sessione e lo pone
	 * come utente corrente dell'applicazione.
	 * Non verifica se l'utente sia effettivamente presente nella sessione, operazione
	 * che va fatta usando {@link sessionHasUser()}.
	 *
	*/
	protected function getUserFromSession() {
		$this->user = WebApp::getInstance()->getSessionManager()->get($this->getUserSessionName());

	}

	/**
	 * Esegue il login dell'utente prelevandolo dalla sessione.
	 * Proprio come per il login, esegue {@link onUserLogin()}.
	 * 
	 * Non verifica se l'utente sia presente nella sessione.
	 */
	protected function loginUserFromSession() {
		$this->getUserFromSession();
	}
	
	/**
	 * Registra l'utente nella sessione
	 * @param BaseUser $user l'utente da salvare nella sessione
	*/
	protected function putUserIntoSession(WebAppUserInterface $user) {
		WebApp::getInstance()->getSessionManager()->set($this->getUserSessionName(), $user);
	}


	/**
	 * Viene usata da WebApp per verificare se l'utente abbia i permessi
	 * per accedere alla URL specificata: la url indica sempre una azione 
	 * dell'applicazione
	 * 
	 * @param WebAppUserInterface $user utente da verificare
	 * @param URL $url istanza di URL con la sezione a cui si vuole accedere
	 * @return boolean TRUE l'utente ha i permessi, FALSE altrimenti
	*/
	public function hasPermission(WebAppUserInterface $user, URL $url) {
		return TRUE;
	}
	
	/**
	 * Per un utente per il quale {@link hasPermission()} ritorna FALSE,
	 * ritorna una {@link URL} a cui saltare.
	 *
	 * Usata da WebApp.
	 * 
	 * @param BaseUser $user istanza di User
	 * @param URL $url istanza di URL con la sezione a cui si voleva accedere
	 * @return URL l'istanza di URL con la destinazione a cui andare
	*/
	public function getNoPermissionURL(WebAppUserInterface $user, URL $url) {
		$u = clone $url;
		return $u;
	}

	/**
	 * Ritorna il nome del tag della sessione in cui viene conservato l'utente corrente.
	 * @return string una stringa con l'etichetta
	 */
	public function getUserSessionName() {
		return '_AUTH_USER_';
	}

}


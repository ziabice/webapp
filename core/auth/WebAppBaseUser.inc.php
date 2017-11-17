<?php

/**
 * (c) 2012 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Classe base che descrive un utente dell'applicazione
 *
 * @author Luca Gambetta <lgambetta@bluenine.it>
 *
 */
class WebAppBaseUser extends EditableObject implements WebAppUserInterface {
	const
		
		// Stato dell'utente
		STATUS_ENABLED = 10,
		STATUS_DISABLED = 20,
		STATUS_SUSPENDED = 30,
		STATUS_HOLD = 40,
		STATUS_DELETED = 50,
		STATUS_TRASH = 60;
		
	
	protected
		$name,
		$surname,
		$login = '',
		$password = '', // hash della password 
		$real_password = '', // password in chiaro
		$language = '', // stringa con la lingua preferita dall'utente
		$current_language = '', // Stringa con la lingua corrente dell'utente
		$is_logged;

	/**
	 *
	 *  Costruisce un nuovo utente
	 *
	 * Imposta lo stato dell'utente a STATUS_HOLD ed inoltre imposta il flag
	 * di login a falso (ossia utente non loggato nel sistema)
	 *
	 * @param integer $id ID dell'utente (o NULL)
	 * @param integer $creator eventuale ID dell'utente che ha creato questo utente.
	 */
	public function __construct($id = NULL, $creator = NULL) {
		parent::__construct($id, $creator);
		$this->setStatus(self::STATUS_HOLD);
		$this->is_logged = FALSE;
	}
	
	// ---------- Metodi modificatori e accessori

	/**
	 * Imposta il nome dell'utente
	 * @param string $name stringa col nome
	*/
	public function setName($name) {
		$this->name = strval($name);
		$this->touch();
	}
	
	/**
	 * Imposta il cognome dell'utente
	 * @param string $surname stringa col cognome
	*/
	public function setSurname($surname) {
		$this->surname = strval($surname);
		$this->touch();
	}
	
	/**
	 * Legge il nome di questo utente
	 * @return string una stringa col nome
	*/
	public function getName() {
		return $this->name;
	}
	
	/**
	 * Legge il cognome di questo utente
	 * @return string una stringa col cognome
	*/
	public function getSurname() {
		return $this->surname;
	} 
	
	// ----------------- Gestione credenziali di accesso
	
	/**
	 * Imposta l'identificativo di login.
	 * Non verifica la correttezza sintattica dello stesso.
	 * 
	 * @param string $login stringa con la login
	*/
	public function setLogin($login) {
		$this->login = $login;
		$this->touch();
	}
	
	/**
	 * Ritorna la stringa di login dell'utente
	 * @return string una stringa con la login
	*/
	public function getLogin() {
		return $this->login;
	}
	
	/**
	 * Imposta la password crittata.
	 *
	 * Le password crittate vengono generate dal modello di dati.
	 * 
	 * @param string $password stringa con la password
	 * @param boolean $from_real_password TRUE la password è in chiaro e va codificata, FALSE la password è già crittata
	 **/
	public function setPassword($password) {
		$this->password = $password;
		$this->touch();
	}
	
	/**
	 * Restituisce la password crittata.
	 *
	 * @return string col la password
	 */
	public function getPassword() {
		return $this->password;
	}
	
	/**
	 * Informa se l'utente abbia eseguito correttamente la login nel sistema
	 *
	 * @return boolean TRUE l'utente è loggato correttamente nel sistema, FALSE altrimenti
	*/
	public function isLogged() {
		return $this->is_logged;
	}
	
	/**
	 * Fa il login dell'utente.
	 * Imposta lo stato di "loggato"
	*/
	public function login() {
		$this->is_logged = TRUE;
	}
	
	/**
	 * Fa il logout dell'utente
	 * Rimuove lo stato di "loggato"
	*/
	public function logout() {
		$this->is_logged = FALSE;
	}
	
	// ---------------------- Lingua
	
	/**
	 * Ritorna una stringa con la codifica della lingua corrente dell'utente.
	 *
	 * @return string una stringa con la codifica del linguaggio, del tipo xx_XX, ad es. it_IT.
	*/
	public function getCurrentLanguage() {
		return $this->current_language;
	}
	
	/**
	 * Imposta la linga corrente per questo utente
	*/
	public function setCurrentLanguage($lang) {
		$this->current_language = $lang;
	}
	
	/**
	 * Ritorna una stringa con la codifica della lingua di preferenza dell'utente.
	 *
	 * Ogni utente può avere una lingua di preferenza, utilizzata nei siti multilingua
	 * per scegliere quella da usare.
	 *
	 * @return string una stringa con la codifica del linguaggio, del tipo xx_XX, ad es. it_IT.
	*/
	public function getLanguage() {
		return $this->language;
	}
	
	/**
	 * Imposta la lingua di preferenza di questo utente.
	 * Una stringa con la lingua è nella forma xx_YY, es. it_IT per l'italiano
	 * @param string $lang una stringa con la lingua
	*/
	public function setLanguage($lang) {
		$this->language = $lang;
		$this->touch();
	}
	
	/**
	 * Informa se un utente abbia una lingua di preferenza
	 * 
	 * @return boolean TRUE se ha una lingua, FALSE altrimenti
	*/
	public function hasLanguage() {
		return !empty($this->language);
	}

}

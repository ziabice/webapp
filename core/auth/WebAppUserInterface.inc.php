<?php

/**
 * (c) 2012 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Descrive un utente del sistema.
 * 
 * @author Luca Gambetta <l.gambetta@bluenine.it>
 */
interface WebAppUserInterface {
	
	
	// ----------------- Gestione credenziali di accesso
	
	/**
	 * Imposta l'identificativo di login.
	 * Non verifica la correttezza sintattica dello stesso.
	 * 
	 * @param string $login stringa con la login
	*/
	public function setLogin($login);
	
	/**
	 * Ritorna la stringa di login dell'utente
	 * @return string una stringa con la login
	*/
	public function getLogin();
	
	/**
	 * Imposta la password crittata.
	 *
	 * Le password crittate vengono generate dal modello di dati.
	 * 
	 * @param string $password stringa con la password
	 * 
	 **/
	public function setPassword($password);
	
	/**
	 * Restituisce la password crittata.
	 *
	 * @return string col la password
	 */
	public function getPassword();
	
	/**
	 * Informa se l'utente abbia eseguito correttamente la login nel sistema
	 *
	 * @return boolean TRUE l'utente è loggato correttamente nel sistema, FALSE altrimenti
	*/
	public function isLogged();
	
	/**
	 * Fa il login dell'utente.
	 * Imposta lo stato di "loggato"
	*/
	public function login();
	
	/**
	 * Fa il logout dell'utente
	 * Rimuove lo stato di "loggato"
	*/
	public function logout();
	
	// ---------------------- Lingua
	
	/**
	 * Ritorna una stringa con la codifica della lingua corrente dell'utente.
	 *
	 * @return string una stringa con la codifica del linguaggio, del tipo xx_XX, ad es. it_IT.
	*/
	public function getCurrentLanguage();
	
	/**
	 * Imposta la linga corrente per questo utente
	 * 
	 * @param string $lang stringa con la lungua
	*/
	public function setCurrentLanguage($lang);
	
	/**
	 * Ritorna una stringa con la codifica della lingua di preferenza dell'utente.
	 *
	 * Ogni utente può avere una lingua di preferenza, utilizzata nei siti multilingua
	 * per scegliere quella da usare.
	 *
	 * @return string una stringa con la codifica del linguaggio, del tipo xx_XX, ad es. it_IT.
	*/
	public function getLanguage();
	
	/**
	 * Imposta la lingua di preferenza di questo utente.
	 * Una stringa con la lingua è nella forma xx_YY, es. it_IT per l'italiano
	 * @param string $lang una stringa con la lingua
	*/
	public function setLanguage($lang);
	
	/**
	 * Informa se un utente abbia una lingua di preferenza
	 * 
	 * @return boolean TRUE se ha una lingua, FALSE altrimenti
	*/
	public function hasLanguage();
	
}



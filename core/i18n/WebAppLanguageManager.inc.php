<?php
/**
 * (c) 2008, 2009, 2010, 2011 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Gestore della lingua per l'applicazione corrente, ricava automaticamente
 * le informazioni dalla richiesta HTTP o dall'utente corrente.
 *
 * @author Luca Gambetta <l.gambetta@bluenine.it>
 */
class WebAppLanguageManager {
	public 
		$i18n;
	protected
		$i18n_paths,
		$app_lang,
		$current_encoding;

	/**
	 * Inizializza impostando l'encoding standard
	 * Dopo aver creato l'oggetto va inizializzato con una chiamata al metodo
	 * {@link initialize}
	 *
	 * @param string $encoding encoding da utilizzare
	 */
	public function __construct($encoding = 'utf-8') {
		$this->setEncoding($encoding);
	}
	
	/**
	 * Verifica se nella richiesta HTTP (non nei cookie) ci sia una variabile
	 * che indichi che si vuole cambiare la lingua corrente.
	 *
	 * La stringa con la lingua va gestita a livello di applicazione.
	 *
	 * Deve verificare che la lingua sia tra quelle accettate dall'applicazione
	 *
	 * @return string una stringa con la lingua nella richiesta o FALSE in caso di errore o lingua non presente
	*/
	public function getLanguageFromRequest() {
		return FALSE;
	}
	
	/**
	 * Ritorna il nome del cookie in cui eventualmente salvare o leggere
	 * la lingua corrente.
	 * 
	 * Di default ritorna se definito il valore della costante WEBAPP_LANG_COOKIE
	 * 
	 * @return string stringa col nome del cookie da usare o FALSE se non è stato impostato
	*/
	public function getLanguageCookieName() {
		return WebAppConfig::getParams()->hasKey('WEBAPP_LANG_COOKIE') ? WebAppConfig::getParams()->get('WEBAPP_LANG_COOKIE') : FALSE;
		
	}
	
	/**
	 * Informa se sia stato definito un cookie col nome della lingua
	 * @return boolean TRUE se è stato definito il nome del cookie, FALSE altrimenti
	*/
	public function hasLanguageCookieName() {
		return ($this->getLanguageCookieName() !== FALSE);
	}
	
	/**
	 * Ritorna il gestore di traduzioni corrente
	 * @return I18NManager istanza di I18NManager
	*/
	public function getI18NManager() {
		return $this->i18n;
	}
	
	/**
	 * Inzializza il gestore di lingua e traduzioni
	 *
	 * Prova a cercare una lingua di default per l'applicazione e la
	 * imposta nel front controller. Per poter funzionare ha bisogno che sia
	 * stato inizializzato il gestore di autenticazione e sia disponibile un
	 * utente di sistema.
	 *
	 * Se l'utente ha una lingua di preferenza imposta quella, a meno che non
	 * sia stata definita una lingua da usare o nella richiesta HTTP o nei cookies.
	 *
	 * Se l'utente non ha una lingua di preferenza, prima cerca di ricavare una
	 * lingua dalla richiesta HTTP o dai cookie, altrimenti usa la lingua di default.
	 *
	 * La lingua dalla richiesta viene ricavata con {@link getLanguageFromRequest},
	 * quella dai cookie con {@link getLanguageFromCookie}.
	 * La lingua di defautl con {@link WebAppConfig::getDefaultLanguage}.
	 *
	 * Dopo aver scelto la lingua corrente inizializza il gestore
	 * di traduzioni usando {@link initializeI18NManager}.
	*/
	public function initialize() {
		// Prende una lingua che "potrebbe andare bene" analizzando
		// richiesta HTTP e cookies
		$proposed_lang = $this->getLanguageFromRequest();
		
		if ($proposed_lang === FALSE) {
			// Niente lingua, verifica se ci sia un coookie con la lingua
			$proposed_lang = $this->getLanguageFromCookie();
			
		}

		if (WebApp::getInstance()->getUser()->hasLanguage()) {
			// Se non c'è una lingua, mette quella dell'utente...
			if ($proposed_lang === FALSE) {
				$proposed_lang = WebApp::getInstance()->getUser()->getLanguage();
			} 
		} else {
			// L'utente non ha lingua, mette quella di default
			if ($proposed_lang === FALSE) {
				$proposed_lang = WebAppConfig::getDefaultLanguage();
			} 
		}

		// Inizializza il gestore della lingue
		$this->initializeI18NManager();
		$this->initializeTranslations();
		
		// Imposta la lingua corrente
		$this->setLanguage($proposed_lang);
	}

	/**
	 * Crea il gestore di traduzioni e lo associa a questo oggetto
	 */
	protected function initializeI18NManager() {
		$this->i18n = new I18NManager_Text();
		
	}

	/**
	 * Carica i path base per le traduzioni nel gestore di traduzione.
	 *
	 * Carica solo quelle relative all'applicazione ed ai plugin
	 * Non vengono caricate le traduzioni per il modulo corrente, viene fatto
	 * nel front controller.
	 */
	protected function initializeTranslations() {
		$this->i18n->addApplicationTrans(WebApp::getInstance()->getName(), WebApp::getInstance()->getPath());

		$plugins = WebApp::getInstance()->getPlugins();
		$path = FileUtils::slashPath(WebApp::getInstance()->getPluginsPath());

		foreach($plugins as $plugin) $this->i18n->addPluginTrans($plugin, $path.$plugin);
	}

	/**
	 * Imposta il gestore di traduzioni corrente
	 * @param I18NManage $i18n il gestore di traduzioni
	 */
	protected function setI18NManager(I18NManager $i18n) {
		$this->i18n = $i18n;
		return $i18n;
	}

	/**
	 * Ritorna la lingua settata per questa applicazione
	 * @return string una stringa con la lingua
	*/
	public function getLanguage() {
		return $this->app_lang;
	}

	/**
	 * Ritorna la lingua conservata nel cookie dell'applicazione
	 * 
	 * @return string la stringa con la lingua o FALSE se non c'è nessuna lingua definita
	*/
	public function getLanguageFromCookie() {
		if ( $this->hasLanguageCookieName() ) {
			$l = Request::getInstance()->getCookie( $this->getLanguageCookieName() );
			return (preg_match('/^[a-z]{2}_[A-Z]{2}$/', strval($l)) == 1 ? $l : FALSE);
		} 
		return FALSE;
	}

	/**
	 * Imposta il cookie per la lingua nella risposta corrente
	 * 
	 * @param string $lang stringa con la lingua
	*/
	public function setLanguageCookie($lang) {
		if ( $this->hasLanguageCookieName() ) {
			// WebApp::getInstance()->getResponse()->setCookie($this->getLanguageCookieName(), $lang, time() + 1314000, '/', Request::getInstance()->getHost());
			WebApp::getInstance()->getResponse()->setCookie($this->getLanguageCookieName(), $lang, time() + 1314000, '/');
		}
	}

	/**
	 * Rimuove (fa scadere) il cookie per la lingua
	*/
	public function unsetLanguageCookie() {
		if ( $this->hasLanguageCookieName() ) WebApp::getInstance()->getResponse()->expireCookie( $this->getLanguageCookieName() );
	}

	/**
	 * Imposta la lingua dell'applicazione globalmente.
	 * La lingua deve essere nel formato xx_YY, ad esempio it_IT per l'italiano
	 * 
	 * @param string $lang stringa con la lingua
	 * @param boolean $set_cookie TRUE imposta anche il cookie per la lingua, FALSE solo la lingua corrente
	 *
	 */
	public function setLanguage($lang, $set_cookie = FALSE) {
		$this->app_lang = $lang;
		// $this->i18n->setCurrentLanguage($lang);
		$this->i18n->setLocale($lang);
		WebApp::getInstance()->getUser()->setCurrentLanguage($this->app_lang);
		if ($set_cookie) {
			$this->setLanguageCookie($lang);
		}
	}
	
	/**
	 * Ritorna l'encoding dei caratteri da utilizzare con la lingua corrente
	 * @return string una stringa con l'encoding
	*/
	public function getEncoding() {
		return $this->current_encoding;
	}
	
	/**
	 * Imposta l'encoding interno dei caratteri
	 *
	 * @param string $encoding stringa con l'encoding
	*/
	public function setEncoding($encoding) {
		$this->current_encoding = strtolower($encoding);
		if (function_exists('mb_internal_encoding')) {
			mb_internal_encoding(strtoupper( $encoding ));
			mb_http_output(strtoupper( $encoding ));
		}
	}

}

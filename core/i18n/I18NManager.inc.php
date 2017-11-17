<?php
/**
 * (c) 2008, 2009 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Classe base per la gestione dell'internazionalizzazione di una
 * applicazione.
 * Principalmente gestisce la traduzione del testo dell'interfaccia utente
 * ma in future implementazioni deve fornire anche funzionalità
 * contestuali alla lingua/cultura in cui si vuole tradurre.
 *
 * @author Luca Gambetta <l.gambetta@bluenine.it>
 */
class I18NManager {

	protected
		$locale;

	public function __construct() {
		
	}


	/**
	 * Imposta il locale corrente: setta anche la lingua per le traduzioni.
	 *
	 * La stringa di locale è nella forma 'xx_YY', ad esempio
	 * per l'italiano 'it_IT'.
	 *
	 * @param string $locale una stringa con il tag di locale
	 */
	public function setLocale($locale) {
		$this->locale = $locale;
	}

	/**
	 * Ritorna la stringa col locale corrente.
	 *
	 * Una stringa nella forma "xx_YY" per l'italiano ad esempio "it_IT".
	 * @return string stringa col locale
	 */
	public function getLocale() {
		return $this->locale;
	}

	/**
	 * Traduce la stringa nella lingua correntemente indicata mediante {@link setLocale}
	 * @param string $str la stringa da tradurre
	 * @return string la stringa tradotta (se disponibile una traduzione)
	 */
	public function tr($str) {
		return $str;
	}

	/**
	 * Aggiunge le traduzioni per una applicazione
	 *
	 * Il formato delle traduzioni varia a seconda dell'implementazione
	 *
	 * @param string $appname nome dell'applicazione
	 * @param string $path percorso base dell'applicazione
	 */
	public function addApplicationTrans($appname, $path) {

	}

	/**
	 * Aggiunge la traduzione relativa ad un plugin
	 *
	 * Il formato delle traduzioni varia a seconda dell'implementazione
	 *
	 * @param string $plugin_name
	 * @param string $path percorso base del plugin
	 */
	public function addPluginTrans($plugin_name, $path) {

	}

	/**
	 * Aggiunge le traduzioni relative ad un moodulo
	 *
	 * Il formato delle traduzioni varia a seconda dell'implementazione
	 *
	 * @param string $module_name
	 * @param string $path percorso base del modulo
	 */
	public function addModuleTrans($module_name, $path) {

	}

	/**
	 * Rimuove le stringhe di traduzione di un modulo da quelle disponibili
	 * 
	 * @param string $module nome del modulo di cui rimuovere le traduzioni
	 */
	public function discardModuleTrans($module) {

	}
}

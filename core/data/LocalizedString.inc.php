<?php
/**
 * (c) 2008-2012 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Una stringa multilingua.
 *
 * Una stringa che offre una rappresentazione in più lingue del suo contenuto
 *
 * Una lingua viene indicata con una stringa del tipo: xx_XX (ad esempio it_IT per l'italiano).
 *
 * Implementa un iteratore che cicla sulla lingua (chiave) e la stringa (valore).
 *
 * @author Luca Gambetta <l.gambetta@bluenine.it>
*/
class LocalizedString extends ObjectConnectAdapter implements Iterator {
	protected
		$str;

	/**
	 * Crea un nuova stringa con un valore di default per la lingua indicata
	 *
	 * @param string $lang lingua della traduzione (nella forma xx_YY)
	 * @param string $str testo della stringa
	 * @param ObjectConnectInterface $obj connette all'oggetto indicato
	 */
	public function __construct($lang = '', $str = '', ObjectConnectInterface $obj = NULL) {
		$this->str = array();
		if (!empty($lang)) {
			$this->str[$lang] = $str;
		}
		if (is_object($obj)) $this->connect($obj);
	}
	
	/**
	 * Costruttore di copia che si occupa di creare un nuovo oggetto LocalizedString
	 * copiando i dati da uno precedente e associandolo direttamente
	 * con un altro oggetto.
	 * 
	 * @param LocalizedString $parent oggetto da cui copiare le informazioni
	 * @param mixed $obj oggetto a cui collegare la nuova istanza di LocalizedString, se NULL non collega.
	 * @return LocalizedString la nuova istanza creata
	 */
	public static function copyConstructor(LocalizedString $parent, ObjectConnectInterface $obj = NULL) {
		$l = new LocalizedString();
		$l->copy($parent);
		if (is_object($obj)) $l->connect($obj);
		return $l;
	}
	
	/**
	 * Ritorna un array con le lingue in cui la stringa è disponibile.
	 * Viene ritornato un array di stringhe del tipo xx_YY che indicano la lingua
	 *
	 * @return array un array di stringhe
	*/
	public function getAvailableTranslations() {
		return array_keys($this->str);
	}
	
	/**
	 * Imposta la stringa per la lingua specificata
	 * @param string $lang lingua della stringa
	 * @param string $str la stringa tradotta
	*/
	public function set($lang, $str) {
		if (!empty($lang)) {
			$this->str[$lang] = $str;
			$this->touch();
		}

	}

	/**
	 * Ritorna la traduzione per la lingua specificata
	 *
	 * @param string $lang lingua per la quale si vuole la traduzione
	 * @return string|boolean FALSE se non c'è una traduzione per la lingua, oppure la stringa
	 */
	public function get($lang) {
		return (array_key_exists($lang, $this->str) ? $this->str[$lang] : FALSE);
	}

	/**
	 * Informa se sia disponibile una traduzione per la lingua specificata
	 * 
	 * @param string $lang lingua per la quale si vuole la traduzione
	 * @return boolean TRUE è disponibile una traduzione, FALSE altrmenti
	 */
	public function has($lang) {
		return array_key_exists($lang, $this->str);
	}
	
	/**
	 * Ritorna la stringa tradotta nella lingua corrente del front controller,
	 * interrogando il suo language manager.
	 * La stringa viene ritornata invocando {@link getTranslated}.
	 *
	 * @return string la stringa tradotta
	*/
	public function getCurrent() {
		return $this->getTranslated( WebApp::getInstance()->getLanguage() );
	}
	
	/**
	 * Imposta la traduzione per la lingua corrente dell'applicazione.
	 * La lingua corrente viene ricavata dal language manager del front controller
	 *
	 * @param string $str stringa con la traduzione
	*/
	public function setCurrent($str) {
		$this->set( WebApp::getInstance()->getLanguage(), $str );
	}

	/**
	 * Ripulisce tutte le traduzioni
	 */
	public function clear() {
		$this->str = array();
		$this->touch();
	}
	
	/**
	 * Ritorna una stringa per una certa lingua, ma se questa lingua
	 * non è presente allora ritorna la prima traduzione disponibile.
	 *
	 * Il parametro lang indica una lingua o un insieme di lingue di preferenza
	 * se nessuna delle quali è presente, improvvisa ;)
	 *
	 * @param array|string $lang stringa o array di stringhe con le lingue di preferenza
	 * @return string una stringa con la traduzione
	*/
	public function getTranslated($lang) {
		if (!is_array($lang)) $lang = array($lang);

		foreach($lang as $l) {
			if (array_key_exists($l, $this->str)) return $this->str[$l];
		}
		
		// Se siamo qui non ha trovato lingue utili, ritorna la prima disponibile
		$l = reset($this->str);
		return ($l === FALSE ? '' : $l );
	}

	/**
	 * Ritorna un array associativo con le stringhe e le traduzioni, nella forma
	 *
	 * array(
	 * 	'lingua' => 'stringa'
	 * )
	 * 
	 * @return array le traduzioni
	 */
	public function getAll() {
		return $this->str;
	}

	/**
	 * Copia da un'altra stringa.
	 *
	 * Il testo corrente viene rimosso, rende una copia esatta della sorgente.
	 *
	 * @param LocalizedString  $source stringa da cui copiare
	 */
	public function copy(LocalizedString $source) {
		$this->str = array();
		foreach($source->str as $k => $v) $this->str[$k] = $v;
		$this->touch();
	}

	/**
	 * Fonde coi valori di un'altra stringa.
	 *
	 * Il testo corrente viene sovrascritto.
	 *
	 * @param LocalizedString  $source stringa da cui copiare
	 */
	public function merge(LocalizedString $source) {
		$this->str = array_merge( $this->str, $source->str );
		$this->touch();
	}
	
	/**
	 * Informa se ci siano o meno elementi.
	 * 
	 * @return boolean TRUE se la stringa è vuota, FALSE altrimenti
	 */
	public function isEmpty() {
		return count($this->str) == 0;
	}

	// ----------- Iterator
	public function current() {
		return current($this->str);
	}

	public function key() {
		return key($this->str);
	}

	public function valid() {
		return (current($this->str) !== FALSE);
	}
	
	public function rewind() {
		reset($this->str);
	}

	public function next() {
		next($this->str);
	}

}


<?php
/**
 * (c) 2008, 2009 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Associa una chiave ad un valore, permettendo di iterare su di esse.
 * Maschera un array associativo.
 * @author Luca Gambetta <l.gambetta@bluenine.it>
*/
class HashMap extends ObjectConnectAdapter implements Iterator {
	private
		$it_el, // elemento dell'iterator
		$hash;

	/**
	 * Inizializza il contenitore di associazioni
	 */
	public function __construct() {
		$this->hash = array();
	}
	
	
	/**
	 * Imposta il valore per una chiave
	 * 
	 * @param string $key stringa con la chiave
	 * @param mixed $value valore da associare alla chiave
	 *
	 * @return mixed il valore associato
	*/
	public function set($key, $value) {
		$this->hash[strval($key)] = $value;
		$this->touch();
		return $value;
	}
	
	/**
	 * Ritorna il valore associato alla chiave
	 * Solleva una eccezione se la chiave è inesistente
	 * 
	 * @param string $key chiave di cui si vuole il valore
	 * @return mixed il valore associato alla chiave
	 * @throws Exception se la chiave non esiste
	 *
	*/
	public function get($key) {
		if (array_key_exists($key, $this->hash)) {
			return $this->hash[$key];
		} else {
			throw new Exception("HashMap: no key defined.");
		}
	}
	
	/**
	 * Ritorna il valore associato alla chiave
	 * ritorna NULL se la chiave è inesistente
	 *
	 * @param string $key chiave di cui si vuole il valore
	 * @return mixed il valore associato alla chiave o NULL se la chiave non esiste
	*/
	public function getValue($key) {
		if (array_key_exists($key, $this->hash)) {
			return $this->hash[$key];
		} else {
			return NULL;
		}
	}
	
	/**
	 * Rimuove la chiave dall'hashmap
	 * 
	 * @param string $key chiave che si vuole rimuovere
	 * @return boolean TRUE se ha rimosso la chiave, FALSE altrimenti
	*/
	public function del($key) {
		if (array_key_exists($key, $this->hash)) {
			unset($this->hash[$key]);
			$this->touch();
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	/**
	 * Informa se la chiave sia presente
	 * 
	 * @param string $key nome della chiave
	 * @return boolean TRUE se la chiave esiste, FALSE altrimenti
	*/
	public function hasKey($key) {
		return array_key_exists($key, $this->hash);
	}
	
	/**
	 * Rimuove tutte le associazioni definite
	*/
	public function clear() {
		$this->hash = array();
		$this->touch();
	}
	
	/**
	 * Ritorna tutte le chiavi definite
	 * @return array un array di stringhe con le chiavi definite
	*/
	public function allKeys() {
		return array_keys($this->hash);
	}
	
	/**
	 * Ritorna tutti i valori definiti
	 * @return array un array con tutti i valori definiti
	*/
	public function allValues() {
		return array_values($this->hash);
	}
	
	/**
	 * Ritorna l'hashmap in un array associativo
	 * @return array un array associativo con tutte le chiavi ed i valori definiti
	*/
	public function getAll() {
		return $this->hash;
	}
	
	/**
	 * Informa se questa hashmap sia vuota, ossia non contenga associazioni nome-valore
	 * @return boolean TRUE se l'hashmap è vuota, FALSE altrimenti
	*/
	public function isEmpty() {
		return count($this->hash) == 0;
	}

	/**
	 * Copia i dati da un'altra HashMap, sovrascrive gli hash già presenti
	 * @param HashMap $source sorgente della copia
	 */
	public function copy(HashMap $source) {
		foreach($source->hash as $k => $v) $this->hash[$k] = $v;
	}

	/**
	 * Copia i dati da un array associativo, sovrascrive gli hash già presenti
	 * @param array $source sorgente della copia
	 */
	public function copyArray($source) {
		foreach($source as $k => $v) $this->hash[$k] = $v;
	}
	
	// Iteratore
	public function next() {
		$this->it_el = each($this->hash);
	}
	
	public function rewind() {
		reset($this->hash);
		$this->it_el = each($this->hash);
	}
	
	public function key() {
		return $this->it_el['key'];
	}
	
	public function current() {
		return $this->it_el['value'];
	}
	
	public function valid() {
		return $this->it_el !== FALSE;
	}

}

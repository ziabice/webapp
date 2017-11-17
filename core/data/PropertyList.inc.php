<?php
/**
 * (c) 2008, 2009 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Un contenitore di proprietà (ossia stringhe)
 *
 * @author Luca Gambetta <l.gambetta@bluenine.it>
*/
class PropertyList extends ObjectConnectAdapter implements Iterator {
	protected
		$iterator_valid, // flag di validità dell'iteratore
		$it_el, // elemento dell'iterator
		$properties;

	/**
	 * Inizializza azzerando le proprietà
	 */
	public function __construct() {
		$this->properties = array();
		$this->iterator_valid = FALSE;
	}
	
	/**
	 * Rimuove tutte le proprietà definite
	*/
	public function clear() {
		$this->properties = array();
		$this->touch();
	}
	
	/**
	 * Aggiunge una proprietà all'oggetto.
	 *
	 * Una proprietà è una stringa che definisce una caratteristica
	 * dell'oggetto. Il significato viene attribuito dall'applicazione.
	 *
	 * Vengono aggiunte solo le proprietà che non sono già presenti, evitando
	 * duplicati.
	 * 
	 * @param string $property una stringa con la proprietà da aggiungere
	*/
	public function add($property) {
		if (!array_key_exists( strtoupper($property), $this->properties ) ) {
			$this->properties[strtoupper($property)] = $property;
			$this->touch();
		}
	}

	/**
	 * Aggiunge più proprietà in una volta sola.
	 *
	 * @param array $properties un array di stringhe
	 */
	public function addMany($properties) {
		if (is_array($properties)) {
			foreach($properties as $p) $this->add($p);
		}
	}
	
	/**
	 * Rimuove una proprietà
	 * 
	 * @param string $property una stringa con la proprietà da rimuovere
	 * @return boolean TRUE se ha rimosso la proprietà, FALSE altrimenti
	*/
	public function del($property) {
		if (array_key_exists( strtoupper($property), $this->properties ) ) {
			unset($this->properties[strtoupper($property)]);
			$this->touch();
			return TRUE;
		}
		return FALSE;
	}
	
	/**
	 * Ritorna l'array con le proprietà
	 * 
	 * @return array un array di stringhe
	*/
	public function getAll() {
		return $this->properties;
	}
	
	/**
	 * Verifica se sia presente una proprietà
	 * 
	 * @param string $property stringa col nome della proprietà
	 * @return boolean TRUE se l'oggetto ha la proprietà, FALSE altrimenti
	*/
	public function has($property) {
		return array_key_exists(strtoupper($property), $this->properties);
	}
	
	/**
	 * Informa se non sono state definite proprietà.
	 * 
	 * @return boolean TRUE se è vuoto, FALSE altrimenti
	*/
	public function isEmpty() {
		return empty($this->properties);
	}
	
	/**
	 * Verifica se un oggetto possegga tutte le proprietà
	 * elencate.
	 * 
	 * @param array $property_arr un array di stringhe
	 * 
	 * @return boolean|array TRUE se ha tutte le proprietà, o un un array con le proprietà che non possiede
	 *
	*/
	public function hasProperties($property_arr) {
		if (count($this->properties) == 0 || count($property_arr) == 0) return FALSE;
		$i = array_diff($property_arr, $this->properties);
		if (count($i) == 0) {
			return TRUE;
		} else {
			return $i;
		}
	}
	
	// Iteratore
	public function next() {
		$this->iterator_valid = (next($this->properties) !== FALSE);
	}
	
	public function rewind() {
		$this->iterator_valid = (reset($this->properties) !== FALSE);
	}
	
	public function key() {
		return key($this->properties);
	}
	
	public function current() {
		return current($this->properties);
	}
	
	public function valid() {
		return $this->iterator_valid;
	}

}

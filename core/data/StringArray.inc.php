<?php
/**
 * (c) 2008, 2009, 2010 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Array di stringhe
 */
class StringArray extends ObjectConnectAdapter implements Iterator,Countable  {
	protected
		$read_only, // Flag, rende la stringa non modificabile
		$it_el, // elemento dell'iteratore
		$elements = array(), // Il testo vero e proprio
		$glue = ''; // stringa usata per comporre gli elementi

	/**
	 * Costruisce con una stringa vuota.
	 *
	 * E' possibile impostarla come in sola lettura: dopo la creazione non sarà più modificabile.
	 *
	 * @param string|array $str stringa o array di stringhe da cui creare l'oggetto
	 * @param boolean $read_only TRUE imposta come in sola lettura, FALSE altrimenti
	 */
	public function __construct($str = '', $read_only = FALSE) {
		if (!empty($str)) {
			if (is_array($str)) $this->elements = array_map('strval', $str);
			else $this->elements = array(strval($str));
		} else {
			$this->elements = array();
		}
		$this->read_only = $read_only;
	}

	/**
	 * Imposta il flag di sola lettura.
	 *
	 * Quando uno StringArray è in sola lettura non può essere modificato.
	 *
	 * @param boolean $is_readonly TRUE l'oggetto sarà in sola lettura, FALSE altrimenti
	 */
	public function setReadOnly($is_readonly = TRUE) {
		$this->read_only = (bool)$is_readonly;
	}

	/**
	 * Informa se l'oggetto sia in sola lettura.
	 *
	 * Quando uno StringArray è in sola lettura non può essere modificato.
	 *
	 * @return boolean TRUE l'oggetto è in sola lettura, FALSE altrimenti
	 */
	public function isReadOnly() {
		return $this->read_only;
	}
	
	/**
	 * Imposta la stringa usata per comporre gli elementi in __toString
	 * @param string $glue un stringa
	*/
	public function setGlue($glue) {
		$this->glue = $glue;
	}
	
	/**
	 * Ritorna la stringa usata per comporre gli elementi in __toString
	 * @return string una stringa
	*/
	public function getGlue() {
		return $this->glue;
	}

	/**
	 * Ritorna le stringhe concatenate usando la stringa  "collante"
	 * impostata con setGlue
	*/
	public final function toString() {
		return implode($this->elements, $this->glue);
	}

	/**
	 * Ritorna una rappresentazione stringa dell'oggetto, concatenando
	 * le varie stringhe usando una stringa "collante".
	 * @param string $glue stringa da utilizzare per incollare le stringhe
	 * @return string la stringa composta
	*/
	public final function str($glue = '') { 
		return implode($this->elements, $glue); 
	}

	/**
	 * Ritorna l'array di stringhe
	 * @return array un array di stringhe
	*/
	public final function elements() { 
		return $this->elements; 
	}

	/**
	 * Azzera l'oggetto, cancellando le stringhe
	*/
	public final function clear() {
		if ($this->read_only) return FALSE;
		
		$this->elements = array();
		$this->touch();
	}

	/**
	 * Carica del testo nell'oggetto, sovrascrivendo quello che c'è già.
	 * 
	 * @param string|array $value una stringa o un array di stringhe
	*/
	public function load($value) {
		if ($this->read_only) return FALSE;
		
		if (is_array($value)) {
			$this->elements = array_map('strval', $value);
		} else {
			$this->elements = array(strval($value));
		}
		$this->touch();
	}

	/**
	 * Aggiunge del testo nell'oggetto
	 * @param string|array $value una stringa o un array di stringhe
	*/
	public function merge($value) {
		if ($this->read_only) return FALSE;
		
		$this->elements = array_merge($this->elements, is_array($str) ? array_map('strval', $str) : array(strval($str)) );
		$this->touch();
	}

	/**
	 * Copia il testo da un altro oggetto StringArray.
	 *
	 * Il testo viene aggiunto a quello già esistente.
	 * 
	 * @param StringArray $arrstr istanza di StringArray
	*/
	public function copy(StringArray $arrstr) {
		if ($this->read_only) return FALSE;

		$this->elements = array_merge($this->elements, $arrstr->elements);
		$this->touch();
	}

	/**
	 * Aggiunge una stringa o un array di stringhe di testo in coda
	 * @param string|array $str stringa o array di stringhe
	*/
	public function add($str) {

		if ($this->read_only) return FALSE;

		if (is_array($str)) {
			$this->elements = array_merge($this->elements, array_map('strval', $str) );
		} else {
			$this->elements[] = strval($str);
		}
		$this->touch();
	}

	/**
	 * Aggiunge una stringa o un array di stringhe di testo in cima
	 * @param string|array $str stringa o array di stringhe
	*/
	public function insert($str) {
		if ($this->read_only) return FALSE;

		if (is_array($str)) {
			$this->elements = array_merge(array_map('strval', $str), $this->elements);
		} else {
			array_unshift($this->elements, strval($str) );
		}
		$this->touch();
	}

	/**
	 * Aggiunge una stringa di testo nella posizione specificata
	 * 
	 * @param integer $index intero con la posizione in cui aggiungere il testo (a partire da 0)
	 * @param string $str stringa di testo
	*/
	public function insertAt($index, $str) {
		if ($this->read_only) return FALSE;
		
		array_splice($this->elements, $index, 0, strval($str));
		$this->touch();
	}

	/**
	 * Imposta la stringa alla posizione specificata
	 * 
	 * @param integer $index intero con la posizione in cui aggiungere il testo (a partire da 0)
	 * @param string $str stringa di testo
	*/
	public function update($index, $str) {
		if ($this->read_only) return FALSE;

		array_splice($this->elements, $index, 1, strval($str));
		$this->touch();
	}

	/**
	 * Rimuove la stringa alla posizione specificata
	 * @param integer $index intero con la posizione (a partire da 0)
	 *
	*/
	public function remove($index) {
		if ($this->read_only) return FALSE;
		
		array_splice($this->elements, $index, 1);
		$this->touch();
	}

	/**
	 * Rimuove la stringa in testa, ritornandola.
	 *
	 * @return string la stringa rimossa, NULL se non presente o FALSE se è in modalità sola lettura
	 */
	public function removeHead() {
		if ($this->read_only) return FALSE;
		
		return array_shift($this->elements);
	}

	/**
	 * Rimuove la stringa in coda, ritornandola.
	 *
	 * @return string la stringa rimossa,NULL se non presente o FALSE se in modalità sola lettura
	 */
	public function removeTail() {
		if ($this->read_only) return FALSE;

		return array_pop($this->elements);
	}

	/**
	 * Ritorna l'ultima stringa
	 * @return string|boolean FALSE se non ci sono stringhe, altrimenti una stringa
	*/
	public function last() {
		if ($this->read_only) return FALSE;
		
		return end($this->elements);
	}

	/**
	 * Ritorna la prima stringa
	 * @return string FALSE se non ci sono stringhe, altrimenti una stringa
	*/
	public function first() {
		if ($this->read_only) return FALSE;
		
		return reset($this->elements);
	}
	
	/**
	 * Ritorna la stringa con un determinato ordine
	 * 
	 * @param integer $index intero con l'indice della stringa (0 per il primo)
	 * @return string|boolean FALSE se l'indice non esiste, altrimenti una stringa col valore
	*/
	public function get($index) {
		if (array_key_exists($index, $this->elements)) return $this->elements[$index];
		else return FALSE;
	}

	/**
	 * Verifica se sia vuoto.
	 * 
	 * Un array di stringhe è vuoto se non ci sono stringhe o se quelle
	 * presenti sono di lunghezza nulla, ossia se trasformando l'array
	 * in una stringa questa sarà vuota
	 *
	 * @return boolean TRUE se la stringa sarà vuota, FALSE altrimenti
	*/
	public function isEmpty() {
		if (count($this->elements) == 0) return TRUE;
		else {
			foreach($this->elements as $k => $v) {
				if (strlen($v) > 0) return FALSE;
			}
			return TRUE;
		}
	}

	/**
	 * Rimuove dall'array tutte le stringhe vuote, ossia le stringhe
	 * che per la funzione di PHP empty sono vuote.
	 */
	public function pack() {
		if ($this->read_only) return FALSE;

		$this->elements = array_filter($this->elements);
	}
	
	// -------------------- Iteratore
	public function next() {
		$this->it_el = each($this->elements);
	}
	
	public function rewind() {
		reset($this->hash);
		$this->it_el = each($this->elements);
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

	// --------------- Countable
	public function count() {
		return count($this->elements);
	}

}

<?php
/**
 * (c) 2011 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Una collezione di oggetti derivati da WebAppObject.
 *
 * @author Luca Gambetta <l.gambetta@bluenine.it>
*/
class ObjectCollection extends ObjectConnectAdapter implements Iterator, Countable {
	protected
		$iterator_valid, // flag di validità dell'iteratore
		$is_strict, 
		$valid_classes,
		$objects;

	/**
	 * Inizializza impostando i tipi di classi accettate.
	 * 
	 * Indicare un elenco di classi da poter aggiungere: vengono 
	 * accettate anche le sottoclassi, a meno che non sia specificato
	 * il flag "strict".
	 * 
	 * In ogni caso l'oggetto deve essere di tipo WebAppObject.
	 * 
	 * Se non vengono indicate classi, viene impostato di default 'WebAppObject'.
	 * 
	 * @param array $valid_classes array con i nomi delle classi da accettare
	 * @param boolean $strict TRUE accetta solo le classi indicate, FALSE accetta anche sottoclassi
	 */
	public function __construct($valid_classes, $strict = FALSE) {
		$this->objects = array();
		$this->iterator_valid = FALSE;
		if (empty($valid_classes) ) {
			$this->valid_classes = array( 'WebAppObject' );
		} else {
			$this->valid_classes = array_map('strval', $valid_classes);
		}
		
		$this->is_strict = $strict;
	}
	
	/**
	 * Svuota la collezione.
	 * */
	public function clear() {
		$this->objects = array();
		$this->touch();
	}
	
	private function check_class($obj) {
		if ($this->is_strict) {
			$cname = get_class($obj);
			foreach($this->valid_classes as $c) {
				if (strcasecmp($c, $cname) == 0) {
					return TRUE;
				}
			}
			
		} else {
			foreach($this->valid_classes as $c) {
				if ($obj instanceof $c) {
					return TRUE;
				}
			}
		}
		return FALSE;
	}
	
	/**
	 * Aggiunge un oggetto in coda.
	 * 
	 * Verifica che sia delle classi accettate.
	 * 
	 * @return mixed ritorna FALSE se non ha aggiunto l'oggetto, altrimenti l'oggetto
	 * 
	 * */
	public function push($obj) {
		if (!$this->check_class($obj)) return FALSE;
		$this->objects[] = $obj;
		$this->touch();
		return $obj;
	}
	
	/**
	 * Aggiunge un oggetto in testa.
	 * 
	 * Verifica che sia delle classi accettate.
	 * 
	 * @return mixed ritorna FALSE se non ha aggiunto l'oggetto, altrimenti l'oggetto
	 * 
	 * */
	public function unshift($obj) {
		if (!$this->check_class($obj)) return FALSE;
		array_unshift($this->objects, $obj);
		$this->touch();
		return $obj;
	}
	
	/**
	 * Rimuove un oggetto
	 * 
	 * @param string $object l'oggetto da rimuovere
	 * @return boolean TRUE se ha rimosso l'oggetto, FALSE altrimenti
	*/
	public function del($object) {
		foreach($this->objects as $k => $obj) {
			if ($obj === $object) {
				unset($this->objects[$k]);
				$this->touch();
				return TRUE;
			}
		}
		return FALSE;
	}
	
	/**
	 * Estrae l'oggetto in coda.
	 * 
	 * @return WebAppObject NULL se la collezione è vuota, altrimenti l'oggetto
	 * */
	public function pop() {
		$o = array_pop($this->objects);
		$this->touch();
		return $o;
	}
	
	/**
	 * Estrae l'oggetto in testa.
	 * 
	 * @return WebAppObject NULL se la collezione è vuota, altrimenti l'oggetto
	 * */
	public function shift() {
		$o = array_shift($this->objects);
		$this->touch();
		return $o;
	}
	
	/**
	 * Ritorna un array con gli oggetti
	 * 
	 * @return array un array di oggetti
	*/
	public function getAll() {
		return $this->objects;
	}
	
	/**
	 * Ritorna tutti gli ID salvati, eliminando eventualmente i duplicati.
	 * 
	 * @param boolean $no_duplicates TRUE rimuove i duplicati, FALSE altrimenti
	 * @return array un array con gli ID
	 * */
	public function getIDs($no_duplicates = FALSE) {
		$ids = array();
		foreach($this->objects as $o) $ids[] = $o->getID();
		return ($no_duplicates ? array_unique($ids) : $ids);
	}
	
	/**
	 * Verifica se sia presente almeno un oggetto con l'ID specificato.
	 * 
	 * @param mixed $id ID da cercare
	 * @return boolean TRUE se ha trovato almeno un oggetto, FALSE altrimenti
	*/
	public function has($id) {
		foreach($this->objects as $obj) {
			if ($obj->getID() == $id) return TRUE;
		}
		return FALSE;
	}
	
	/**
	 * Ritorna tutti gli oggetti con un certo ID.
	 * 
	 * @return array un array con gli oggetti che corrispondono 
	 * */
	public function getObjectsByID($id) {
		$out = array();
		foreach($this->objects as $obj) {
			if ($obj->getID() == $id) $out[] = $obj;
		}
		return $out;
	}
	
	/**
	 * Informa se la collezione sia vuota.
	 * 
	 * @return boolean TRUE se è vuoto, FALSE altrimenti
	*/
	public function isEmpty() {
		return empty($this->objects);
	}

	// Countable
	public function count() {
		return count($this->objects);
	}
	
	// Iterator
	public function next() {
		$this->iterator_valid = (next($this->objects) !== FALSE);
	}
	
	public function rewind() {
		$this->iterator_valid = (reset($this->objects) !== FALSE);
	}
	
	public function key() {
		return key($this->objects);
	}
	
	public function current() {
		return current($this->objects);
	}
	
	public function valid() {
		return $this->iterator_valid;
	}

}

<?php
/**
 * (c) 2008, 2009 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Verifica che nella richiesta HTTP sia presente la chiave di tipo stringa
 * e controlla se questo valore sia presente nel database, di solito come
 * chiave primaria in una tabella.
 *
 * Bisogna implementare il metodo checkPrimaryKey per evitare SQL injection.
 *
 * @author Luca Gambetta <l.gambetta@bluenine.it>
 */
class PrimaryKeyStringInputFilter extends PrimaryKeyInputFilter {

	/**
	 * Verifica se una chiave primaria sia sintatticamente corretta.
	 *
	 * Attualmente verifica una parola senza spazi di qualsiasi lunghezza
	 * composta da numeri, lettere e '_'.
	 * 
	 * @param mixed $value valore da verificare
	 * @return boolean TRUE se è corretta, FALSE altrimenti
	*/
	public function checkPrimaryKey($value) {
		return (preg_match('/^\w*$/', $value) == 1);
	}

	public function getSQL($value) {
		if (is_array($value)) {
			return 'select if(count(*) = '.strval(count($value)).', \'t\', \'f\') as primary_key_matches from '.
			$this->table_name.' WHERE '.$this->table_name.'.'.$this->primary_key_name.' in ('.implode( array_map(array($this->getDB(), 'addSlashes'), $value), ',').')';
		} else {
			return 'select if(count(*) = 1, \'t\', \'f\') as primary_key_matches from '.
			$this->table_name.' WHERE '.$this->table_name.'.'.$this->primary_key_name.' = '.$this->getDB()->addSlashes(strval($value));
		}
	}

}

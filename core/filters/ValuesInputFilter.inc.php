<?php
/**
 * (c) 2008, 2009 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Verifica che il valore della variabile nella richiesta appartenga all'insieme specificato.
 */
class ValuesInputFilter extends InputFilter {
	protected
		$valid_values = array();
		
	/**
	 * @param string $fieldname nome del campo
	 * @param array $values array di stringhe coi valori accettati
	 * @param boolean $allow_null accetta NULL come valore
	*/
	function __construct($fieldname, $values, $allow_null = FALSE) {
		if (!is_array($values)) $this->valid_values = array();
		else $this->valid_values = $values;
		parent::__construct($fieldname, $allow_null);
	}
	
	public function checkValue($value) {
		return in_array($value, $this->valid_values);
	}
}

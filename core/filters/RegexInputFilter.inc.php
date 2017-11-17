<?php
/**
 * (c) 2008, 2009 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Verifica un valore usando una espressione regolare.
 * L'espressione regolare deve essere compatibile con PCRE.
*/
class RegexInputFilter extends InputFilter {
	
	protected
		$regex;
	
	/**
	 *
	 * @param string $fieldname nome della variabile nella richiesta HTTP da controllare
	 * @param string $regex stringa con l'espressione regolare
	 * @param boolean $allow_null TRUE permette che il valore non sia presente, FALSE altrimenti
	*/
	function __construct($fieldname, $regex, $allow_null = FALSE) {
		$this->regex = $regex;
		parent::__construct($fieldname, $allow_null);
	}
	
	protected function checkValue($value) {
		return preg_match($this->regex, strval($value)) == 1;
	}
}

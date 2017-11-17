<?php
/**
 * (c) 2008, 2009 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Verifica se la variabile contenga un numero intero compreso tra
 * due valori.
 *
*/
class IntMinMaxFilter extends InputFilter {
	protected
		$min, // Limite inferiore
		$max; // Limite superiore

	/**
	 * 
	 * @param string $fieldname nome della variabile nella richiesta HTTP
	 * @param integer $min limite inferiore
	 * @param integer $max limite superiore
	 * @param boolean $allow_null TRUE la variabile può valere NULL, FALSE altrimenti
	 */
	public function __construct($fieldname, $min, $max, $allow_null = FALSE) {
		$this->min = $min;
		$this->max = $max;
		parent::__construct($fieldname, $allow_null);
	}
	
	protected function checkValue($value) {
		if (preg_match('/^[+\-]?[0-9]{1,'.strval(strlen(strval(PHP_INT_MAX)) - 1).'}$/', strval($value)) == 1) {
			$value = intval($value);
			return $value >= $this->min && $value <= $this->max;
		}
	}
}

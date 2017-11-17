<?php
/**
 * (c) 2008, 2009 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Verifica che il numero intero senza segno sia entro un range di valori
*/
class UIntMinMaxFilter extends IntMinMaxFilter {
	
	protected function checkValue($value) {
		if (preg_match('/^[0-9]{1,'.strval(strlen(strval(PHP_INT_MAX)) - 1).'}$/', strval($value)) == 1) {
			$value = intval($value);
			return $value >= $this->min && $value <= $this->max;
		}
	}
}


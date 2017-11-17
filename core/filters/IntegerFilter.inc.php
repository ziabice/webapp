<?php
/**
 * (c) 2008, 2009 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Verifica che la variabile nella richiesta HTTP contenga un numero intero
*/
class IntegerFilter extends InputFilter {
	protected function checkValue($value) {
		return preg_match('/^[+\-]?[0-9]{1,'.strval(strlen(strval(PHP_INT_MAX)) - 1).'}$/', strval($value)) == 1;
	}
}


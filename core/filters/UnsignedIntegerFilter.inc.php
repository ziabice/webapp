<?php
/**
 * (c) 2008, 2009 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Verifica che il numero sia un intero senza segno
*/
class UnsignedIntegerFilter extends InputFilter {
	protected function checkValue($value) {
		return preg_match('/^[0-9]{1,'.strval(strlen(strval(PHP_INT_MAX)) - 1).'}$/', strval($value)) == 1;
	}
}

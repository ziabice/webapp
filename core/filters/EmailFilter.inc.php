<?php
/**
 * (c) 2008, 2009 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Verifica che il testo sia un indirizzo email sintatticamente valido
 * @author Luca Gambetta <l.gambetta@bluenine.it>
 */
class EmailFilter extends InputFilter {
	protected function checkValue($value) {
		return preg_match('/^\w[\w\._\-0-9]*@\w[\w\._\-0-9]*$/', $value) == 1;
	}
}


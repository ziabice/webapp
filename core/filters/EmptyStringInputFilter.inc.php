<?php

/**
 * (c) 2012 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Un filtro che verifica che il valore in ingresso sia una stringa vuota.
 *
 * @author Luca Gambetta <l.gambetta@bluenine.it>
 */
class EmptyStringInputFilter extends InputFilter {
	
	protected function checkValue($value) {
		return (strlen($value) == 0);
	}
	
}


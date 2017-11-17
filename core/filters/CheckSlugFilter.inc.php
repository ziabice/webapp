<?php
/**
 * (c) 2008, 2009 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Un filtro per verificare uno slug, ossia una stringa univoca da poter
 * utilizzare nelle url, come ad esempio: 'questo_e_quello_123', 'la-mia-collezione-di-francobolli'.
 *
 * E' una stringa che non presenta spazi, che sono sostituiti da '-' o '_', inizia per lettera o numero
 * ed è composta solo da lettere e numeri. Non presenta caratteri accentati, ma presenta lettere maiuscole e minuscole.
 *
 * @author Luca Gambetta <l.gambetta@bluenine.it>
 */
class CheckSlugFilter extends RegexInputFilter {

	/**
	 * Costruisce il filtro.
	 *
	 * @param string $fieldname nome del campo
	 * @param integer $min_length lunghezza minima, deve essere >= a 1
	 * @param integer $max_length lunghezza massima, deve essere maggiore della lunghezza minima
	 * @param boolean $allow_null TRUE permette NULL come valore, FALSE altrimenti
	 */
	public function __construct($fieldname, $max_length = 255, $min_length = 1, $allow_null = FALSE) {
		parent::__construct($fieldname, '/^([A-Za-z0-9]+([_\-][a-zA-Z0-9]+)*){'.strval($min_length).','.strval($max_length).'}$/u', $allow_null);
	}
}


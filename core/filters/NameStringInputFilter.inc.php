<?php
/**
 * (c) 2008, 2009 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Verifica che il testo sia un nome stringa valido, ossia un nome
 * tipo: questo_qui_valido, sezione123, a-100-passi-da-te, 1_2_3.
 * Sono i marcatori utilizzati per referenziare nomi di sezioni o nomi di
 * documenti.
 * Sono ammessi solo i caratteri ASCII
 * */
class NameStringInputFilter extends TextFieldInputFilter {
	/**
	 * @param string $fieldname nome della variabile nella richiesta HTTP
	 * @param integer $maxlength lunghezza massima della stringa (deve essere maggiore di 1)
	 * @param boolean $required TRUE il campo deve essere presente, FALSE può essere omesso
	 * @param boolean $ignore_case TRUE la stringa può contenere lettere maiuscole, FALSE ammesse solo lettere minuscole (default)
	 * */
	public function __construct($fieldname, $maxlength = 255, $required = TRUE, $ignore_case = FALSE) {
		parent::__construct($fieldname, $required ? 1 : 0, $maxlength, TRUE, '/^[a-z0-9]([a-z0-9]+[_-]?)*[^-_\s\W]$/'.($ignore_case ? 'i' : ''));
	}
}


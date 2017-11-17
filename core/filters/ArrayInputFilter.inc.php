<?php
/**
 * (c) 2012 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */
 
/**
 * Verifica che il valore passato sia un array e che il valore dei suoi elementi
 * sia tra quelli indicati.
 *
 * @author Luca Gambetta <l.gambetta@bluenine.it>
 */
class ArrayInputFilter extends InputFilter {
	protected
		// Flag: consente o meno un array in ingresso vuoto
		$allow_empty,
		// valori validi per gli elementi dell'array
		$valid_values;

	/**
	 * Inizializza con i valori accettati.
	 *
	 *
	 *
	 * @param array|string $fieldname nome del campo nella richiesta HTTP
	 * @param array $valid_values valori validi
	 * @param bool $allow_empty TRUE permette che l'array in ingresso sia vuoto, FALSE altrimenti
	 * @param bool $allow_null TRUE accetta che il campo non possa esistere nella richiesta, FALSE altrimenti
	 */
	function __construct($fieldname, $valid_values = array(), $allow_empty = FALSE, $allow_null = FALSE) {
		$this->valid_values = $valid_values;
		$this->allow_empty = $allow_empty;
		parent::__construct($fieldname, $allow_null);
	}

	protected function checkValue($value) {
		if (!is_array($value)) return FALSE;
		if (empty($this->valid_values)) return TRUE;
		if (!$this->allow_empty && empty($value)) return FALSE;

		foreach($value as $v) {
			if (!in_array($v, $this->valid_values)) return FALSE;
		}
		return TRUE;
	}

}

<?php

/**
 * (c) 2012 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */
 
/**
 * Verifica se nella richiesta sia presente un campo e che abbia
 * un determinato valore, associando diversi valori di ritorno.
 * 
 * Utile per verificare l'esistenza di valori derivanti da singole checkbox.
 * 
 * @author Luca Gambetta <l.gambetta@bluenine.it>
 *
 */
class CheckboxInputFilter extends InputFilter {
	protected
		$retval1, $retval2, $required_value;
	
	/**
	 * Inizializza l'oggetto.
	 * 
	 * Verifica che esiste nella richiesta il campo indicato e che abbia come
	 * valore $required_value.
	 * Se il campo esiste ed ha il valore indicato, viene ritornato come valore
	 * quello indicato da $retval1.
	 * Se il campo non esiste nella richiesta, allora viene ritornato il valore
	 * indicato da $retval2.
	 * 
	 * Se il campo esiste, ma non ha il valore richiesto, viene ritornato un errore.
	 * 
	 * Il campo $required_value normalmente è una stringa, ma può essere anche un array
	 * di stringhe: in questo caso la verifica dei possibili valori avviene
	 * su tutti gli elementi
	 * 
	 * @param string $fieldname nome del campo
	 * @param string|array $required_value il o i valori che deve avere il campo
	 * @param mixed $retval1 valore di ritorno 1
	 * @param mixed $retval2 valore di ritorno 2
	 */
	public function __construct($fieldname, $required_value, $retval1 = TRUE, $retval2 = FALSE) {
		$this->retval1 = $retval1;
		$this->retval2 = $retval2;
		$this->required_value = $required_value;
		parent::__construct($fieldname, TRUE);
	}
	
	protected function checkValue($value) {
		if (is_array($this->required_value)) return in_array($value, $this->required_value);
		else return (strcmp($value, $this->required_value) == 0);
	}
	
	protected function mustProcessValue() {
		return TRUE;
	}
	
	protected function processValue($value) {
		if (is_null($value)) return $this->retval2;
		else return $this->retval1;
	}
}

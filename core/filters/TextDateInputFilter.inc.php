<?php

/**
* Verifica la validità di una data in forma testuale
* e la converte in un oggetto CDateTime.
* 
* La stringa in ingresso deve seguire la struttura indicata dal formato specificato.
* Il separatore tra le cifre può essere sia '/' (slash), che '-', quindi se il formato
* è FMT_DDMMYYYY le date "10/11/2012" e "10-11-2012" sono entrambe valide.
* 
* Viene verificato che la data sia corretta utilizzando la funzione checkdate.
*
* Formati per la data:
* FMT_DDMMYYYY - data nel formato dd/mm/YYYY
* FMT_MMDDYYYY - data nel formato mm/dd/YYYY
* FMT_DDMMYYYY_SHORT - data nel formato dd/mm/YY (il secolo è quello corrente) 
* FMT_MMDDYYYY_SHORT - data nel formato mm/dd/YY (il secolo è quello corrente)
* 
* @author Luca Gambetta <l.gambetta@bluenine.it>
***/
class TextDateInputFilter extends InputFilter {
	const
		// Formati per la data
		FMT_DDMMYYYY = 1, // data nel formato dd/mm/YYYY
		FMT_MMDDYYYY = 2, // data nel formato mm/dd/YYYY
		FMT_DDMMYYYY_SHORT = 11, // data nel formato dd/mm/YY (il secolo è quello corrente) 
		FMT_MMDDYYYY_SHORT = 12; // data nel formato mm/dd/YY (il secolo è quello corrente)
		
	private
		$the_date = NULL, // oggetto CDateTime con la data
		$regex; // regex per verificare la data
		
	public function __construct($fieldname, $format = self::FMT_DDMMYYYY, $allow_null = FALSE) {
		$this->regex = '';
		
		if ($format == self::FMT_DDMMYYYY || $format == self::FMT_DDMMYYYY_SHORT) {
			$this->regex = '/^(?P<day>[0-9]{1,2})[\-\/]{1}(?P<month>[0-9]{1,2})[\-\/]{1}(?P<year>[0-9]{1,'.($format == self::FMT_DDMMYYYY_SHORT ? '2' : '4').'})$/';
		} elseif($format == self::FMT_MMDDYYYY || $format == self::FMT_MMDDYYYY_SHORT) {
			$this->regex = '/^(?P<month>[0-9]{1,2})[\-\/]{1}(?P<day>[0-9]{1,2})[\-\/]{1}(?P<year>[0-9]{1,'.($format == self::FMT_DDMMYYYY_SHORT ? '2' : '4').'})$/';
		}
		parent::__construct($fieldname, $allow_null);
	}
	
	protected function checkValue($value) {
		if (empty($this->regex)) return FALSE;
		if (preg_match($this->regex, $value, $parts) == 1) {
			if (checkdate($parts['month'], $parts['day'], $parts['year'])) {
				$this->the_date = new CDateTime();
				$this->the_date->set($parts['day'], $parts['month'], $parts['year'], 0, 0, 0);
				return TRUE;
			}
		} 
		return FALSE;
	}
	
	protected function mustProcessValue() {
		return TRUE;
	}
	
	protected function processValue($value) {
		return $this->the_date;
	}
}

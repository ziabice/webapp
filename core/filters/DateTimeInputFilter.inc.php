<?php
/**
 * (c) 2008, 2009 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Verifica l'input di un controllo DateTimeFormInput.
 * Usato per verificare una data.
 *
 * Va aggiunto ad un oggetto InputValidator usando addWithLabel, dato che richiama
 * più campi in ingresso.
 *
 * Il valore di ritorno è un oggetto CDateTime, quando possibile.
 * @author Luca Gambetta <l.gambetta@bluenine.it>
 * @see DateTimeFormInput
*/
class DateTimeInputFilter extends InputFilter {
	protected
		$datestyle = 0,
		$date_is_empty = FALSE,
		$time_is_empty = FALSE,
		$verify_date,
		$fname;
	
	/**
	 * Inizializza l'oggetto.
	 * 
	 * Lo stile dei controlli è definito dalle costanti:
	 * STYLE_DATE - la data
	 * STYLE_TIME - l'ora (ora, minuti e secondi)
	 * STYLE_MONTHNAME - il selettore per la data presenta i nomi dei mesi invece del numero del mese
	 * STYLE_TIME_SHORT - orario con solo ora e minuti (senza secondi)
	 * STYLE_WITHEMPTY_DATE - data con campo vuoto
	 * STYLE_WITHEMPTY_TIME - ora con campo vuoto
	 * STYLE_WITHEMPTY - aggiungi il campo vuoto
	 * STYLE_DEFAULT - data e ora con campo vuoto
	 * STYLE_DEFAULT_SHORT - data e ora breve con campo vuoto
	 * 
	 * La verifica viene fatta su data e ora indipendenti: quindi potrebbe essere
	 * che la data sia impostata, mentre l'ora non lo sia. Se si vuole
	 * una data completa e valida, occorre specificarlo usando
	 * il terzo parametro $verify_date.
	 * La verifica funziona solo se si estraggono data e ora insieme.
	 *
	 * @param string $fieldname nome del campo
	 * @param integer $datestyle stile dei controlli (campo bit a bit)
	 * @param boolean $verify_date TRUE effettua la verifica della correttezza di data e ora, FALSE lascia indipendenti
	*/
	public function __construct($fieldname, $datestyle, $verify_date = TRUE) {
		$this->verify_date = $verify_date;
		$this->fname = $fieldname;
		$fl = array();
		if ($datestyle & DateTimeFormInput::STYLE_DATE) {
			$fl[] = $fieldname.'_dd'; $fl[] = $fieldname.'_mm'; $fl[] = $fieldname.'_yy';
		}
		if ($datestyle & DateTimeFormInput::STYLE_TIME || $datestyle & DateTimeFormInput::STYLE_TIME_SHORT) {
			$fl[] = $fieldname.'_hh'; $fl[] = $fieldname.'_ii'; 
			if ($datestyle & DateTimeFormInput::STYLE_TIME) $fl[] = $fieldname.'_ss';
		}
		$this->datestyle = $datestyle;
		parent::__construct($fl, FALSE);
	}

	
	protected function checkValue($value) {
		$date_ok = FALSE;
		$time_ok = FALSE;
		$this->date_is_empty = FALSE;
		$this->time_is_empty = FALSE;
		if ($this->datestyle & DateTimeFormInput::STYLE_DATE) {
			if (is_numeric($value[$this->fname.'_mm']) && is_numeric($value[$this->fname.'_dd']) && is_numeric($value[$this->fname.'_yy'])) {
				$date_ok = checkdate($value[$this->fname.'_mm'], $value[$this->fname.'_dd'], $value[$this->fname.'_yy']);
			} else {
				if ($this->datestyle & DateTimeFormInput::STYLE_WITHEMPTY_DATE) {
					if ($value[$this->fname.'_mm'] === '-' && $value[$this->fname.'_dd'] === '-' && empty($value[$this->fname.'_yy'])) {
						$date_ok = TRUE;
						$this->date_is_empty = TRUE;
					}
				}
			}
		} else {
			$date_ok = TRUE;
		}
		
		if ($this->datestyle & (DateTimeFormInput::STYLE_TIME | DateTimeFormInput::STYLE_TIME_SHORT) ) {
			
			if (is_numeric($value[$this->fname.'_hh']) && is_numeric($value[$this->fname.'_ii']) ) {
				$time_ok = (($value[$this->fname.'_hh'] >= 0 && $value[$this->fname.'_hh'] < 24) && ($value[$this->fname.'_ii'] >= 0 && $value[$this->fname.'_ii'] < 60) );
				if ($this->datestyle & DateTimeFormInput::STYLE_TIME) {
					$time_ok = $time_ok && is_numeric($value[$this->fname.'_ss']) && ($value[$this->fname.'_ss'] >= 0 && $value[$this->fname.'_ss'] < 60);
				}
			} else {
				if ($this->datestyle & DateTimeFormInput::STYLE_WITHEMPTY_TIME) {
					
					if ($value[$this->fname.'_hh'] === '-' && $value[$this->fname.'_ii'] === '-') {
						if ($this->datestyle & DateTimeFormInput::STYLE_TIME ) {
							$time_ok = ($value[$this->fname.'_ss'] === '-');
							$this->time_is_empty = $time_ok;
						} else {
							$time_ok = TRUE;
							$this->time_is_empty = TRUE;
						}
					}
				}
			}
		} else {
			$time_ok = TRUE;
		}
		
		// Deve verificare che la data risultante sia valida
		if ($this->verify_date) {
			if (($this->datestyle & DateTimeFormInput::STYLE_WITHEMPTY_DATE)
			&& ($this->datestyle & DateTimeFormInput::STYLE_WITHEMPTY_TIME)) {
				if (!($this->date_is_empty xor $this->time_is_empty)) return $date_ok && $time_ok; 
			} elseif (($this->datestyle & DateTimeFormInput::STYLE_WITHEMPTY_DATE) ) {
				return $date_ok;
			} elseif (($this->datestyle & DateTimeFormInput::STYLE_WITHEMPTY_TIME) ) {
				return $time_ok;
			} else {
				return ($date_ok && $time_ok);
			}
			
			return FALSE;
		} else {
			if (($this->datestyle & DateTimeFormInput::STYLE_WITHEMPTY_DATE) == 0
				&& ($this->datestyle & DateTimeFormInput::STYLE_WITHEMPTY_TIME) == 0) {
				return !($this->date_is_empty xor $this->time_is_empty);
			} elseif (($this->datestyle & DateTimeFormInput::STYLE_WITHEMPTY_DATE) == 0) {
				return !$this->date_is_empty;
			} elseif (($this->datestyle & DateTimeFormInput::STYLE_WITHEMPTY_TIME) == 0) {
				return !$this->time_is_empty;
			} 
			return TRUE;
		}
	}


	protected function mustProcessValue() {
		return TRUE;
	}

	/**
	 * Ritorna un oggetto CDateTime.
	 * 
	 * Se il controllo accetta valori vuoti, ritorna una data vuota.
	 * 
	 * @return CDateTime la data
	*/
	protected function processValue($value) {
		$dt = CDateTime::newEmpty();
		
		// Se siamo qui il valore è già stato convalidato
		if (!$this->date_is_empty) {
			$dt->setDate($value[$this->fname.'_dd'], $value[$this->fname.'_mm'], $value[$this->fname.'_yy']);
		}
		
		if (!$this->time_is_empty) {
			$dt->setTime($value[$this->fname.'_hh'], $value[$this->fname.'_ii'], ($this->datestyle & DateTimeFormInput::STYLE_TIME ? $value[$this->fname.'_ss'] : 0));
		}
		
		/*
		// Se non accetta la data vuota
		if (($this->datestyle & DateTimeFormInput::STYLE_WITHEMPTY_DATE) == DateTimeFormInput::STYLE_WITHEMPTY_DATE) {
			if (!$this->date_is_empty) {
				$dt->setDate($value[$this->fname.'_dd'], $value[$this->fname.'_mm'], $value[$this->fname.'_yy']);
			}
		}
		
		// Se non accetta ora vuota
		if (($this->datestyle & DateTimeFormInput::STYLE_WITHEMPTY_TIME) == DateTimeFormInput::STYLE_WITHEMPTY_TIME) {
			if (!$this->time_is_empty) {
				$dt->setTime($value[$this->fname.'_hh'], $value[$this->fname.'_ii'], ($this->datestyle & DateTimeFormInput::STYLE_TIME ? $value[$this->fname.'_ss'] : 0));
			}
		}
		*/
		return $dt;
	}
}

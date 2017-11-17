<?php
/**
 * (c) 2008, 2009 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Verifica un campo di testo (o più in generale una stringa di testo).
 * 
 * @author Luca Gambetta <l.gambetta@bluenine.it>
*/
class TextFieldInputFilter extends InputFilter {
	protected
		$min_length, $max_length, $regex, $post_process, 
		$no_leading_spaces, $post_process_callback;
	
	const
		STR_TRIM = 1,
		STR_TOLOWER = 2,
		STR_TOUPPER = 4,
		STR_LTRIM = 8,
		STR_RTRIM = 16,
		STR_UCFIRST = 32,
		STR_UCWORDS = 64,
		STR_CALLBACK = 128;
	
	/**
	 * Permette di verificare che una variabile HTTP sia composta da testo.
	 *
	 * Il filtro funziona così:
	 *  - innanzitutto verifica che la stringa sia della lunghezza richiesta
	 *  - se necessario controlla con una regex
	 *  - post processa il valore della stringa
	 *
	 * Le operazioni possibili per il postprocessing vengono decise da una valore
	 * bit a bit. Le seguenti costanti indicano l'azione possibile:
	 *
	 * STR_TRIM - rimuove gli spazi in testa e in coda
	 * STR_TOLOWER - pone in minuscolo
	 * STR_TOUPPER - pone in maiuscolo
	 * STR_LTRIM - rimuove gli spazi in testa
	 * STR_RTRIM -  rimuove gli spazi in coda
	 * STR_UCFIRST - mette in maiuscolo la prima lettera
	 * STR_UCWORDS - mette in maiuscolo tutte le iniziali di parola
	 * STR_CALLBACK - invoca la callback
	 *
	 * La callback deve accettare in ingresso un parametro stringa e restituire una stringa.
	 * 
	 * @param string $fieldname nome del campo da verificare
	 * @param integer $min_length lunghezza minima della stringa.
	 * @param integer $max_length lunghezza massima della stringa
	 * @param boolean $no_leading_spaces TRUE la stringa non deve cominciare con uno spazio, FALSE non valuta
	 * @param string $regex Perl Regex con cui eventualmente verificare un valore corretto
	 * @param integer $post_process campo bit a bit con operazioni da eseguire sulla stringa valida (costanti STR_*)
	 * @param callback $post_process_callback callback eseguita quanto $post_process è STR_CALLBACK
	*/
	public function __construct($fieldname, $min_length, $max_length, $no_leading_spaces = FALSE, $regex = '', $post_process = 0, $post_process_callback = NULL) {
		$this->min_length = $min_length;
		$this->max_length = $max_length;
		$this->regex = $regex;
		$this->post_process = $post_process;
		$this->no_leading_spaces = $no_leading_spaces;
		$this->post_process_callback = $post_process_callback;
		parent::__construct($fieldname, FALSE);
	}
	
	public function checkValue($value) {
		if (!is_string($value)) return FALSE;
		$l = strlen($value);
		if($this->min_length == 0 && $l == 0) return TRUE;
		// verifica la lunghezza della stringa
		if ($l >= $this->min_length && $l <= $this->max_length) {
			if ($this->no_leading_spaces) {
				if (preg_match('/^\s/u', $value) == 1) return FALSE;
			}
			// Se la lunghezza è giusta, verifica eventualmente con la regex
			if (!empty($this->regex)) return (preg_match($this->regex, $value) == 1);
			return TRUE;
		}
		return FALSE;
	}
	
	protected function mustProcessValue() {
		return !empty($this->post_process);
	}
	
	protected function processValue($value) {
		if ($this->post_process & self::STR_TRIM) $value = trim($value);
		if ($this->post_process & self::STR_TOLOWER) $value = strtolower($value);
		if ($this->post_process & self::STR_TOUPPER) $value = strtoupper($value);
		if ($this->post_process & self::STR_LTRIM) $value = ltrim($value);
		if ($this->post_process & self::STR_RTRIM) $value = rtrim($value);
		if ($this->post_process & self::STR_UCFIRST) $value = ucfirst($value);
		if ($this->post_process & self::STR_UCWORDS) $value = ucwords($value);
		if ($this->post_process & self::STR_CALLBACK) $value = call_user_func($this->post_process_callback, $value);
		return $value;
	}
}

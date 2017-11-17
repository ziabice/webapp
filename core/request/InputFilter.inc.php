<?php
/**
 * (c) 2008, 2009 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Gestisce la validazione dei dati in ingresso di una richiesta HTTP.
 * 
 * E' un filtro sui dati in ingresso.
 * 
 * La validazione dei valori avviene utilizzando l'istanza di Request.
 * 
 * Normalmente il valore in ingresso è una stringa o un array di stringhe, ma in alcuni casi il 
 * valore in ingresso potrebbe essere NULL: ad esempio quando stiamo
 * interagendo con controlli form di tipo checkbox. In questo caso il valore viene 
 * considerato valido.
 * 
 * L'oggetto può lavorare su più valori contemporaneamente.
 * 
 * Dopo aver controllato il valore in ingresso può processarlo prima
 * di restituirlo.
 * 
 * Viene mantenuto sia il valore grezzo dei parametri dalla richiesta HTTP, che 
 * quello elaborato.
 *
 *
 * @author Luca Gambetta <l.gambetta@bluenine.it>
*/
class InputFilter {
	protected
		$field = '', // Stringa col nome del campo da verificare
		$allow_NULL = FALSE, // Flag che indica se il campo ammette il valore NULL (ad esempio i checkbox)
		$raw_value = '', // valore del controllo come arriva dalla richiesta HTTP
		$value = '', // valore corrente del controllo
		$input_validator = NULL, // istanza di InputValidator a cui è collegato l'oggetto
		$validation_status; // stato della validazione
		
	const
		VALID_VALUE = 0, // il valore del campo è valido
		WRONG_VALUE = 1, // il valore del campo non è corretto
		NO_VALUE = 2; // un campo richiesto non è presente
		
	/**
	 * Costruisce il filtro di input
	 * 
	 * Invoca {@link initialize} per eseguire l'inizializzazione e controllare il valore in input.
	 * 
	 * Al momento della costruzione estrae il valore dalla richiesta HTTP e ne esegue la validazione.
	 * 
	 * Il nome del campo da verificare può essere una stringa o un array di stringhe che contiene i nomi
	 * dei parametri della richiesta HTTP da verificare: in caso di array il valore grezzo viene popolato
	 * con un array di valori.
	 * 
	 * @param string|array $fieldname stringa o array di stringhe contenente il nome del campo per cui si applica questa regola
	 * @param boolean $allow_null TRUE il campo accetta NULL come valore valido, FALSE altrimenti
	*/
	function __construct($fieldname, $allow_null = FALSE) {
		$this->field = $fieldname;
		$this->allow_NULL = $allow_null;
		
		$this->initialize();
	}
	
	/**
	 * Ritorna lo stato della validazione corrente.
	 * 
	 * Ritorna una delle costanti:
	 * 
	 * VALID_VALUE - il valore del campo è valido
	 * WRONG_VALUE - il valore del campo non è corretto
	 * NO_VALUE - un campo richiesto non è presente
	 * 
	 * @return integer intero con una delle costanti di stato 
	*/
	public function getStatus() {
		return $this->validation_status;
	}
	
	/**
	 * Inizializza il filtro di input.
	 *
	 * Viene utilizzato dal costruttore.
	 *
	 * Popola il valore grezzo e poi esegue la validazione.
	 * @see populateRawValue
	 * @see validate
	*/
	protected function initialize() {
		$this->clearValues();
		$this->populateRawValue();
		$this->validation_status = $this->validate();
	}
	
	/**
	 * Inizializza le varabili interne che mantengono i valori.
	*/
	protected function clearValues() {
		$this->value_is_null = TRUE;
		$this->value = NULL;
		$this->raw_value = NULL;
		$this->validation_status = self::NO_VALUE;
	}
	
	/**
	 * Informa se il valore della variabile HTTP è NULL (indipendentemente dalla sua validità).
	 *
	 * @return boolean TRUE se è NULL, FALSE altrimenti
	*/
	public function isNULL() {
		return $this->value_is_null;
	}
	
	/**
	 * Verifica se il valore passato sia pari a NULL
	 * 
	 * @param mixed $value valore da verificare
	 * @return boolean TRUE se il valore è NULL, FALSE altrimenti
	*/
	protected function valueIsNULL($value) {
		return is_null($value);
	}
	
	/**
	 * Informa se questa regola accetti NULL come valore valido.
	 *
	 * @return boolean TRUE se NULL è un valore valido, FALSE altrimenti
	*/
	public function allowNULL() {
		return $this->allow_NULL;
	}
	
	/**
	 * Popola il contenitore dei valori grezzi.
	 * 
	 * Estrae dalla richiesta HTTP il valore dei campi da controllare definiti
	 * nel costruttore.
	 *
	 * Se i campi da controllare sono più di uno (quindi il nome del campo è un array di stringhe) 
	 * popola il valore grezzo con un array associativo composto da nomecampo = valore.
	 * 
	*/
	protected function populateRawValue() {
		if (is_array($this->field)) {
			$this->raw_value = array();
			foreach($this->field as $k) $this->raw_value[$k] = Request::getInstance()->get($k);
		} else {
			$this->raw_value = Request::getInstance()->get($this->getFieldName());
		}
	}
	
	/**
	 * Convalida il valore grezzo e lo elabora se necessario.
	 *
	 * La validazione viene eseguita in questo modo:
	 * Verifica il valore con verifyValue, se è valido
	 * e deve precessarlo lo elabora, quindi lo salva internamente.
	 *
	 * Ritorna una delle costanti:
	 *
	 * VALID_VALUE - il valore del campo è valido
	 * WRONG_VALUE - il valore del campo non è corretto
	 * NO_VALUE - un campo richiesto non è presente
	 *
	 * @return integer intero con lo stato dell'elaborazione
	 * @see verifyValue
	 * @see mustProcessValue
	 * @see processValue
	 * @see setValue
	*/
	public function validate() {
		// verifica che il valore in ingresso sia valido
		$ok = $this->verifyValue($this->raw_value);
		if ($ok == self::VALID_VALUE) {
			if ($this->mustProcessValue()) {
				$this->setValue($this->processValue($this->raw_value));
			} else {
				$this->setValue($this->raw_value);
			}
		} else {
			$this->setValue(NULL);
		}
		return $ok;
	}
	
	/**
	 * Informa se il valore sia corretto.
	 *
	 * Nel caso di valore corretto, può essere estratto con getValue.
	 *
	 * @return boolean TRUE se il valore è valido, FALSE altrimenti
	 * @see getValue
	*/
	public function isValid() {
		return ($this->validation_status == self::VALID_VALUE);
	}

	/**
	 * Ritorna il nome del campo per cui vale questa regola
	 * @return string|array una stringa o un array di stringhe col nome del campo per cui vale questa regola
	 * */
	public function getFieldName() {
		return $this->field;
	}
	/**
	 * Imposta il nome del campo per cui vale questa regola
	 * @param string|array $fname una stringa o un array di stringhe col nome del campo per cui vale questa regola
	 * */
	public function setFieldName($fname) {
		$this->field = $fname;
	}

	/**
	 * Informa se il valore validato prima di poter essere utilizzato debba essere processato.
	 * 
	 * Viene usata da validate.
	 * 
	 * Indica se il valore verificato con verifyValue() deve passare per processValue() prima di essere
	 * salvato internamente.
	 *
	 * @return boolean TRUE se il valore validato deve essere processato, FALSE altrimenti
	 *
	 * @see validate
	 * @see processValue
	 * @see setValue
	 */
	protected function mustProcessValue() {
		return FALSE;
	}

	/**
	 * Esegue delle elaborazioni sul valore dopo che ha superato i controlli di verifyValue.
	 * 
	 * Viene eseguita da {@link validate} solo se {@link mustProcessValue()} ritorna TRUE.
	 * 
	 * Il valore passato come parametro è il valore grezzo estratto dalla richiesta HTTP.
	 * Nel caso il filtro operi su più parametri viene passato un array associativo
	 * nella forma "parametro = valore".
	 * 
	 * Il valore ritornato viene salvato internamente invocando {@link setValue}.
	 * 
	 * @param mixed $value il valore del dato dalla richiesta HTTP
	 * @return mixed il valore dopo essere stato elaborato
	 *
	 * @see verifyValue
	 * @see validate
	 * @see mustProcessValue
	*/
	protected function processValue($value) {
		return $value;
	}
	
	/**
	 * Esegue la verifica di validità del valore grezzo estratto dalla richiesta HTTP
	 *
	 * Il valore passato come parametro è il valore grezzo estratto dalla richiesta HTTP.
	 * Nel caso il filtro operi su più parametri viene passato un array associativo
	 * nella forma "parametro = valore".
	 *
	 * Se il filtro accetta valori NULL ed il valore vale NULL, ritorna TRUE.
	 * Altrimenti usa {@link checkValue} per verificare la validità.
	 *
	 * Ritorna una delle costanti:
	 *
	 * VALID_VALUE - il valore del campo è valido
	 * WRONG_VALUE - il valore del campo non è corretto
	 * NO_VALUE - un campo richiesto non è presente
	 * 
	 * @param mixed $value stringa o array di stringhe col valore della variabile HTTP
	 * @return integer esito della verifica
	 * @see checkValue
	*/
	protected function verifyValue($value) {
		$this->value_is_null = $this->valueIsNULL($value);
		if ( $this->allow_NULL ) {
			if ( $this->value_is_null ) {
				// il campo non è presente, va bene ;)
				return self::VALID_VALUE;
			} else {
				// Il campo è presente
				return $this->checkValue($value) ? self::VALID_VALUE : self::WRONG_VALUE;
			}
		} else {
			// Se il valore è NULL il campo non è presente, lo ignora
			if ( $this->value_is_null ) {
				return self::NO_VALUE;
			} else {
				// Altrimenti è presente
				return $this->checkValue($value) ? self::VALID_VALUE : self::WRONG_VALUE;
			}
		}
	}
	
	/**
	 * Verifica se il valore sia valido oppure no, ossia se corrisponda
	 * a determinati criteri.
	 * 
	 * Il valore passato come parametro è il valore grezzo estratto dalla richiesta HTTP.
	 * Nel caso il filtro operi su più parametri viene passato un array associativo
	 * nella forma "parametro = valore".
	 * 
	 * @param mixed $value il valore da verificare (di solito una stringa o un array di stringhe)
	 * @return boolean TRUE se il valore è valido, FALSE altrimenti
	 * @see verifyValue
	 * @see populateRawValue
	*/
	protected function checkValue($value) {
		return TRUE;
	}
	
	/**
	 * Imposta il valore effettivo del parametro controllato.
	 *
	 * Viene eseguita da {@link validate()}, quando il valore in ingresso è valido.
	 *
	 * @param mixed $value di solito una stringa col valore
	 * @see validate
	 * @see processValue
	*/
	protected function setValue($value) {
		$this->value = $value;
	}
	
	/**
	 * Ritorna il valore del parametro controllato.
	 * 
	 * Valido solo dopo una chiamata a setValue().
	 *
	 * @return mixed il valore salvato
	 * @see validate
	 * @see setValue
	*/
	public function getValue() {
		return $this->value;
	}
	
	/**
	 * Ritorna il valore come arriva dalla richiesta HTTP
	 * 
	 * @return mixed valore del parametro in ingresso (di solito una stringa o un array di stringhe)
	 * @see populateRawValue
	*/
	public function getRawValue() {
		return $this->raw_value;
	}
	
	/**
	 * Imposta il valore del dato della richiesta HTTP
	 * 
	 * @param mixed $value valore del campo (di solito una stringa o array di stringhe)
	 * @see populateRawValue
	*/
	protected function setRawValue($value) {
		$this->raw_value = $value;
	}
	
	/**
	 * Imposta l'istanza di InputValidator che sta utilizzando questo oggetto
	 * 
	 * @param InputValidator $iv istanza di che sta utilizzando questo oggetto
	*/
	public function setInputValidator(InputValidator $iv) {
		$this->input_validator = $iv;
	}
	
	/**
	 * Ritorna l'istanza di InputValidator a cui è collegato questo filtro
	 * @return InputValidator l'istanza a cui è collegato o NULL
	*/
	public function getInputValidator() {
		return $this->input_validator;
	}
	
}

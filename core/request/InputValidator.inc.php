<?php
/**
 * (c) 2008-2012 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Convalida i dati di una richiesta HTTP, se possibile usando usando filtri di input.
 * 
 * La validazione avviene mediante una chiamata a validate(): all'interno
 * di questo metodo è possibile utilizzare sia una validazione automatizzata usando
 * una serie di istanze di InputFilter che controlli "a mano".
 *
 * Tutti i valori validi vengono salvati internamente con {@link saveValue}.
 * 
 * E' possibile associare un oggetto del quale si possono impostare le proprietà
 * usando i dati validi provenienti dalla richiesta HTTP. Inoltre questo oggetto può
 * essere usato come sorgente per i dati.
 *
 * E' possibile associare per ogni controllo una callback che esegua una azione, sia per
 * l'accesso al valore che per il suo salvataggio.
 *
 * Viene tenuta traccia degli errori e delle omissioni della richiesta, in modo
 * da poter intervenire.
 *
 * Un uso tipico di InputValidator è in congiunzione ad un oggetto Form: grazie ad esso
 * è possibile popolare i campi di un modulo coi valori dell'oggetto associato a InputValidator.
 *
 * Uso:
 * <code>
 * 
 * $iv = new InputValidator;
 *  
 * // Aggiunge filtri di validazione
 * $iv->add(new InputFilter('nomefiltro') ...);
 * $iv->addWithLabel('nomefiltro', new InputFilter() ...);
 * 
 * // Lega un oggetto ed i suoi metodi
 * $iv->bindObject( $miooggetto );
 * $iv->bind('frm_example', 'getExample', 'setExample');
 * 
 * $iv->validate();
 * 
 * if ( $iv->isValid()  ) {
 *   echo "Tutto valido";
 * } else {
 *  echo "Errori!";
 * }
 * </code>
 * 
 * Si può lavorare anche senza associare un oggetto usando i parametri interni: 
 * usando {@link registerParam} si possono registrare delle associazioni tra
 * un nome di campo e un parametro interno, a cui si può accedere poi usando
 * {@link getParams}.
 *
 * @author Luca Gambetta <l.gambetta@bluenine.it>
*/
class InputValidator {
	
	const
		// Costanti per la registrazione dei parametri
		REGISTER_PARAM_GETTER = 1, // Registra il getter
		REGISTER_PARAM_SETTER = 2, // Registra il setter
		REGISTER_PARAM_BOTH = 3, // Registra getter e setter
	
		// Costanti per la funzione di associazione callback
		FUNC_GET = 0, // Referenzia il getter
		FUNC_SET = 1; // Referenzia il setter

	protected
		/**
		 * @var HashMap parametri interni
		 */
		$_params,
		$object, // Oggetto associato al validatore
		$filters = array(), // Contiene i filtri
		$errors = array(), // Contiene gli errori di validazione: nomecampo => stringa con errore
		$saved_values = array(), // Contiene i valori validati 
		$is_valid, // flag che indica se tutto è valido
		$bind_table, // tabella con associazioni campo -> metodo getter/setter ad eseguire
		$callback_table; // tabella con associazioni campo -> callback per i getter/setter
		
	/**
	 * Costruisce l'oggetto
	 */
	public function __construct() {
		$this->_params = new HashMap();
		$this->object = NULL;
		$this->filters = array();
		$this->errors = array();
		$this->saved_values = array();
		$this->is_valid = FALSE;
		
		$this->bind_table = array();
		$this->callback_table = array();
	}
	
	/**
	 * Ritorna l'hashmap con i parametri.
	 * 
	 * @return HashMap i parametri
	 */
	public function getParams() {
		return $this->_params;
	}
	
	/**
	 * Registra l'associazione tra un campo e un nome di parametro.
	 * 
	 * Dopo la registrazione si avrà nei parametri una chiave corrispondente
	 * a $param_name (ossia sarà possibile fare qualcosa tipo $this->getParams()->get($param_name) ).
	 * 
	 * Il parametro $method indica quale metodo registrare, se il setter (imposta il valore) o 
	 * il getter (ritorna il valore). É possibile registrare anche solo un metodo.
	 * Il parametro $method è un campo bit a bit, valori possibili:
	 * REGISTER_PARAM_SETTER - imposta il setter
	 * REGISTER_PARAM_GETTER - imposta il getter
	 * REGISTER_PARAM_BOTH - imposta setter e getter
	 * 
	 * NB:
	 * associando con questo metodo si sovrascrivono eventuali associazioni
	 * tra nome campo e getter/setter precedentemente stabilite.
	 * 
	 * @param string $field_name nome del campo
	 * @param string $param_name nome del parametro
	 * @param integer $method metodo da associare
	 * @see setParam
	 * @see getParam
	 */
	public function registerParam($field_name, $param_name, $method = self::REGISTER_PARAM_BOTH) {
		if ($method & self::REGISTER_PARAM_SETTER) $this->bindCallback2 (self::FUNC_SET, $field_name, array($this, 'setParam'), array($param_name) );
		if ($method & self::REGISTER_PARAM_GETTER) $this->bindCallback2 (self::FUNC_GET, $field_name, array($this, 'getParam'), array($param_name) );
	}
	
	/**
	 * Rimuove l'associazione tra un campo ed un parametro.
	 * 
	 * @param string $field_name nome del campo
	 * @param integer $method metodo da associare
	 * @see registerParam
	 */
	public function unregisterParam($field_name, $method = self::REGISTER_PARAM_BOTH) {
		if ($method & self::REGISTER_PARAM_SETTER) unbindCallback2(self::FUNC_SET, $field_name);
		if ($method & self::REGISTER_PARAM_GETTER) unbindCallback2(self::FUNC_GET, $field_name);
	}
	
	/**
	 * Ritorna il valore di un parametro.
	 * 
	 * Usato da registerParam per associare un campo ad un parametro.
	 * 
	 * @param string $name nome del parametro
	 * @return mixed NULL se il parametro non esiste, altrimenti il valore del parametro.
	 * @see registerParm
	 */
	public function getParam($name) {
		return $this->_params->getValue($name);
	}
	
	/**
	 * Imposta il valore di un parametro.
	 * 
	 * Usato da registerParam per associare un campo ad un parametro
	 * 
	 * @param mixed $value il valore 
	 * @param string $name il nome del parametro
	 * @see registerParm
	 */
	public function setParam($value, $name) {
		$this->_params->set($name, $value);
	}
	
	/**
	 * Convalida i dati provenienti dalla richiesta HTTP.
	 * 
	 * Inizializza l'oggetto e poi esegue la validazione.
	 * 	
	 * Imposta o meno le proprietà dell'eventuale oggetto collegato a seconda del flag $setvalues.
	 * Salva sempre i valori validati internamente, indipendentemente da questo parametro.
	 * 
	 * Per controllare l'esito della validazione utilizare {@ļink isValid}.
	 * 
	 * La verifica dei valori della richiesta viene eseguita usando doValidate, che di
	 * solito usa le eventuali regole associate per controllare i valori.
	 * 
	 * Se ci sono errori è possibile reperire un report dal valore di ritorno di {@link getErrors()}.
	 *
	 *
	 * @param boolean $setvalues TRUE imposta le proprietà dell'oggetto collegato coi valori validi, FALSE altrimenti
	 * @see doValidate
	 * @see setValidity
	 * @see clearSavedValues
	 * @see clearAllErrors
	*/
	public function validate($setvalues = TRUE) {
		$this->setValidity(FALSE);
		$this->clearSavedValues();
		$this->clearAllErrors();
		$this->doValidate($setvalues);
	}
	
	/**
	 * Convalida i valori della richiesta HTTP.
	 * 
	 * Usata da {@link validate()} per eseguire il processo di validazione dopo
	 * averlo inizializzato. Salva i valori sempre internamente e poi eventualmente nell'oggetto collegato.
	 *
	 * Di default imposta lo stato di validazione col valore di ritorno di {@link validateByRules()}
	 *
	 * @param boolean $setvalues TRUE imposta le proprietà dell'oggetto collegato coi valori validi, FALSE altrimenti
	 * @see validate
	 * @see applyFilters
	 * @see setValidity
	*/
	protected function doValidate($setvalues) {
		$this->setValidity( $this->applyFilters($setvalues) );
	}

	/**
	 * Imposta il flag di validità globale dei dati.
	 * @param boolean $valid TRUE tutti i dati sono validi, FALSE ci sono degli errori
	*/
	protected function setValidity($valid) {
		$this->is_valid = (bool)$valid;
	}

	/**
	 * Ritorna il risultato dell'ultima validazione dei dati
	 *
	 * @return boolean TRUE la form è valida, FALSE ci sono errori
	 * @see validate
	 * @see setValidity
	*/
	public function isValid() {
		return $this->is_valid;
	}

	/**
	 * Convalida i dati provenienti dalla richiesta HTTP usando le regole inserite nel validatore.
	 * 
	 * Se ci sono errori è possibile reperire un report dal valore di
	 * ritorno di {@link getErrors()}. Per un controllo più accurato invocare prima clearAllErrors().
	 *
	 * Quando un valore è valido viene salvato internamente, associandolo al nome del filtro (che poi è il nome
	 * del parametro).
	 *
	 * Se il valore indicato dal filtro non è valido (lo stato ritornato dal filtro è InputFilter::NO_VALUE):
	 * - se il campo è richiesto imposta un errore usando il messaggio di errore "campo richiesto" o la callback per generare il messaggio
	 * - salva il valore di fallback (o quello generato dalla callback per il valore di fallback)
	 *
	 * Se il valore indicato dal filtro è errato (lo stato ritornato dal filtro è InputFilter::WRONG_VALUE):
	 * - imposta l'errore per il filtro usando il messaggio di errore o la callback per il messaggio di errore
	 * - salva il valore di fallback (o quello generato dalla callback per il valore di fallback)
	 *
	 * Al termine della validazione se deve impostare i valori dell'oggetto collegato usando quelli
	 * salvati invoca copyValues.
	 *
	 * @param $setvalues se TRUE salva i valori validati nei campi (mediante setValue()), FALSE non fa nulla
	 * @return boolean TRUE se non ci sono errori, FALSE altrimenti.
	 * @see add
	 * @see addWithLabel
	 * @see copyValues
	*/
	protected function applyFilters($setvalues = TRUE) {
		foreach($this->filters as $filter) {
			$e = $filter['filter']->getStatus();
			if ($e == InputFilter::VALID_VALUE) {
				$this->saveValue($filter['label'], $filter['filter']->getValue());
			} elseif ($e == InputFilter::NO_VALUE) {
				// Il campo non è stato specificato, verifica se sia richiesto
				if ($filter['required']) $this->setError($filter['label'], $filter['required_callback'] ? call_user_func($filter['reqmsg']) : $filter['reqmsg']);
				$this->saveValue($filter['label'], $filter['fallback_callback'] ? call_user_func($filter['fallback']) : $filter['fallback']);
			} else {
				// Il campo non è valido
				// Errore "standard": messaggio con valore di fallback
				$this->setError($filter['label'], $filter['errmsg_callback'] ? call_user_func($filter['errmsg']) :  $filter['errmsg']);
				$this->saveValue($filter['label'], $filter['fallback_callback'] ? call_user_func($filter['fallback']) : $filter['fallback']);
			}
		}
		
		if ($setvalues) {
			/* 
				Copia tutti valori salvati nell'oggetto collegato, validi o meno, 
				dato che se non sono validi si avrà il valore di fallback
			*/
			$this->copyValues();
		}
		return ($this->hasErrors() == FALSE);
	}
	
	/**
	 * Copia tutti i valori salvati nell'istanza mediante {@link saveValue} nell'oggetto
	 * collegato.
	 * @see saveValue
	 * @see setValue
	*/
	protected function copyValues() {
		foreach($this->saved_values as $k => $v) {
			$this->setValue($k, $v);
		}
	}
	
	/**
	 * Salva un valore di un parametro validato delle richiesta HTTP internamente.
	 * 
	 * Di solito il nome del parametro corrisponde a quello di un filtro di input.
	 * 
	 * @param string $param nome del parametro
	 * @param mixed $value valore da salvare
	 * @see applyFilter
	*/
	public function saveValue($param, $value) {
		$this->saved_values[$param] = $value;
	}

	/**
	 * Ritorna il valore salvato con saveValue.
	 *
	 * @param string $param nome del parametro
	 * @return mixed NULL se non c'è nessun valore, altrimenti il dato (di solito una stringa)
	*/
	public function getSavedValue($param) {
		return (array_key_exists($param, $this->saved_values) ? $this->saved_values[$param] : NULL );
	}
	
	/**
	 * Ritorna tutti i valori salvati con saveValue
	 * 
	 * Ritorna un array associativo del tipo nomecontrollo => valore
	 * 
	 * @return array un array associativo coi valori
	*/
	public function getSavedValues() {
		return $this->saved_values;
	}

	/**
	 * Cancella tutti i valori salvati internamente con saveValue.
	*/
	public function clearSavedValues() {
		$this->saved_values = array();
	}
	
	/**
	 * Toglie un valore salvato per un parametro.
	 * 
	 * Di solito parametro corrisponde al nome di una regola di filtraggio.
	 * 
	 * @param string $param nome del parametro da rimuovere
	*/
	public function unsetSavedValue($param) {
		if (array_key_exists($param, $this->saved_values)) unset( $this->saved_values[$param] );
	}

	/**
	 * Imposta una proprietà dell'oggetto associato.
	 * 
	 * Il parametro $control indica il nome di un parametro della richiesta HTTP (o di una regola
	 * di filtraggio dello stesso) per cui si vuole impostare il valore.
	 * Il nome è solo una marcatore univoco per una proprietà.
	 * 
	 * Il parametro $value di solito non è ciò che arriva direttamente dalla richiesta HTTP, ma il dato dopo
	 * che è stato elaborato da una regola di validazione.
	 * 
	 * L'impostazione di una proprietà può avvenire anche utilizzando una callback.
	 * 
	 * @param string $control stringa con uno mnemonico (di solito il nome della variabile nella richiesta HTTP)
	 * @param mixed $value valore da impostare
	 * @return mixed TRUE o il valore di una callback
	 * @see bindObject
	 * @see bind
	 * @see bindCallback
	*/
	public function setValue($control, $value) {
		if (array_key_exists($control, $this->bind_table)) {
			if (!empty( $this->bind_table[$control]['set'] )) {
				if (method_exists($this->object, $this->bind_table[$control]['set']))
					return call_user_func( array($this->object, $this->bind_table[$control]['set']), $value );
			}
		}
		if (array_key_exists($control, $this->callback_table)) {
			if (is_callable($this->callback_table[$control]['set']['callback'])) {
				return call_user_func_array($this->callback_table[$control]['set']['callback'], array_merge( array($value), $this->callback_table[$control]['set']['params']));
			}
		}
		return TRUE;
	}

	/**
	 * Ritorna il valore di una proprietà dell'oggetto associato in base
	 * ad una etichetta.
	 * 
	 * Viene utilizzato di solito per pre-compilare un modulo coi valori correnti
	 * dell'oggetto associato.
	 * 
	 * Il dato ritornato non è direttamente inseribile nel controllo, ma va elaborato
	 * in qualche modo (essendo un valore interno).
	 * 
	 * @param string $control stringa col nome del controllo
	 * @return mixed col valore del controllo
	 * @see bindObject
	 * @see bind
	 * @see bindCallback
	*/
	public function getValue($control) {
		if (array_key_exists($control, $this->bind_table)) {
			if (!empty( $this->bind_table[$control]['get'] )) 
				if (method_exists($this->object, $this->bind_table[$control]['get']))
					return call_user_func( array($this->object, $this->bind_table[$control]['get']) );
		}
		if (array_key_exists($control, $this->callback_table)) {
			if (is_callable($this->callback_table[$control]['get']['callback'])) {
				if (empty($this->callback_table[$control]['get']['params'])) return call_user_func($this->callback_table[$control]['get']['callback']);
				else return call_user_func_array($this->callback_table[$control]['get']['callback'], $this->callback_table[$control]['get']['params']);
			}
		}
		return NULL;
	}

	/**
	 * Ritorna un array associativo con i campi che hanno generato un errore
	 * di compilazione.
	 * 
	 * Se non ci sono errori ritorna un array vuoto.
	 *
	 * Array nella forma:
	 * array['nomecampo'] => 'messaggio di errore'
	 * 
	 * @return array array coi messaggi di errore
	*/
	public function getErrors() {
		return $this->errors;
	}

	/**
	 * Imposta una condizione di errore per il campo specificato
	 * 
	 * @param string $field stringa con il nome del campo
	 * @param string $errormsg stringa col testo dell'errore
	*/
	public function setError($field, $errormsg) {
		$this->errors[$field] = $errormsg;
	}

	/**
	 * Rimuove tutti gli errori presenti nel validatore
	*/
	public function clearAllErrors() {
		$this->errors = array();
	}

	/**
	 * Rimuove gli errori relativi ad un campo.
	 *
	 * @param string $fieldname stringa col nome del campo su cui operare
	*/
	public function clearError($fieldname) {
		if (array_key_exists($fieldname, $this->errors)) unset($this->errors[$fieldname]);
	}

	/**
	 * Informa se un campo contenga errori di compilazione o meno
	 * 
	 * @param string $fieldname stringa col nome del campo
	 * @return boolean TRUE se il campo specificato è stato compilato erroneamente, FALSE se è valido
	*/
	public function hasError($fieldname) {
		return array_key_exists($fieldname, $this->errors);
	}
	
	/**
	 * Ritorna il messaggio di errore associato ad un campo
	 *
	 * @param string $fieldname string nome del campo
	 * @return string La stringa col messaggio o FALSE se il campo non presenta errore
	*/
	public function getError($fieldname) {
		return array_key_exists($fieldname, $this->errors) ? $this->errors[$fieldname] : FALSE;
	}

	/**
	 * Informa se ci siano errori di compilazione.
	 * 
	 * Va invocata dopo aver eseguito {@link validate}.
	 * 
	 * @return TRUE se ci sono errori, FALSE altrimenti
	*/
	public function hasErrors() {
		return (count($this->errors) > 0);
	}
	
	/**
	 * Aggiunge un filtro per i dati in ingresso per il controllo automatico.
	 * 
	 * I dati in ingresso vengono controllati utilizzando istanze di InputFilter.
	 * 
	 * L'etichetta a cui viene associato il controllo corrisponde al nome del 
	 * filtro ottenuto mediante chiamata a {@link InputFilter::getFieldName}.
	 * Se il filtro si riferisce a più parametri della richiesta, utilizzare addWithLabel.
	 * 
	 * L'associazione label -> filtro viene usata anche da saveValue, setValue, getValue e per gli errori
	 * di compilazione. La label è una etichetta univoca.
	 * 
	 * Riferirsi a addWithLabel per il formato delle callback
	 * 
	 * @param InputFilter $filter istanza di InputFilter
	 * @param string|callback $error_message messaggio di errore o callback in caso errata compilazione
	 * @param mixed|callback $fallback_value valore da usare in caso di errore di compilazione (o callback)
	 * @param boolean $is_required TRUE il campo deve essere presente, FALSE altrimenti
	 * @param string|callback $required_error_message messaggio di errore se il campo richiesto non è presente (o callback che lo genera)
	 * @param boolean $error_message_is_callback TRUE il parametro $error_message è una callback, FALSE altrimenti
	 * @param boolean $fallback_value_is_callback TRUE il parametro $fallback_value è una callback, FALSE altrimenti
	 * @param boolean $required_error_message_is_callback TRUE il parametro $required_error_message è una callback, FALSE altrimenti
	 *
	 * @see addWithLabel
	*/
	public function add(InputFilter $filter, $error_message = '', $fallback_value = NULL, $is_required = FALSE, $required_error_message = '', $error_message_is_callback = FALSE, $fallback_value_is_callback = FALSE, $required_error_message_is_callback = FALSE) {
		$this->addWithLabel($filter->getFieldName(), $filter, $error_message, $fallback_value, $is_required, $required_error_message, $error_message_is_callback, $fallback_value_is_callback, $required_error_message_is_callback);
	}
	
	/**
	 * Aggiunge un filtro per i dati in ingresso per il controllo automatico.
	 *
	 * I dati in ingresso vengono controllati utilizzando istanze di InputFilter.
	 *
	 * L'etichetta a cui viene associato il controllo corrisponde al nome del
	 * filtro ottenuto mediante chiamata a {@link InputFilter::getFieldName}.
	 * Se il filtro si riferisce a più parametri della richiesta, utilizzare addWithLabel.
	 *
	 * L'associazione label -> filtro viene usata anche da saveValue, setValue, getValue e per gli errori
	 * di compilazione. La label è una etichetta univoca.
	 *
	 * Formato delle callback per i messaggi di errore: ritornano semplicemente una stringa
	 * Formato per la callback del valore fallback: ritorna il valore
	 * Non accettano parametri.
	 *
	 * @param string $label etichetta da associare al filtro
	 * @param InputFilter $filter istanza di InputFilter
	 * @param string|callback $error_message messaggio di errore o callback in caso errata compilazione
	 * @param mixed|callback $fallback_value valore da usare in caso di errore di compilazione (o callback)
	 * @param boolean $is_required TRUE il campo deve essere presente, FALSE altrimenti
	 * @param string|callback $required_error_message messaggio di errore se il campo richiesto non è presente (o callback che lo genera)
	 * @param boolean $error_message_is_callback TRUE il parametro $error_message è una callback, FALSE altrimenti
	 * @param boolean $fallback_value_is_callback TRUE il parametro $fallback_value è una callback, FALSE altrimenti
	 * @param boolean $required_error_message_is_callback TRUE il parametro $required_error_message è una callback, FALSE altrimenti
	 *
	 *
	*/
	public function addWithLabel($label, InputFilter $filter, $error_message = '', $fallback_value = NULL, $is_required = FALSE, $required_error_message = '', $error_message_is_callback = FALSE, $fallback_value_is_callback = FALSE, $required_error_message_is_callback = FALSE) {
		// Aggiunge il filtro
		$this->filters[] = array(
			'filter' => $filter,
			'errmsg' => $error_message,
			'fallback' => $fallback_value,
			'required' => $is_required,
			'reqmsg' => $required_error_message,
			'label' => $label,
			'errmsg_callback' => $error_message_is_callback,
			'fallback_callback' => $fallback_value_is_callback,
			'required_callback' => $required_error_message_is_callback
		);
		// collega il filtro all'istanza
		$filter->setInputValidator($this);
	}

	/**
	 * Imposta il messaggio di errore per un filtro
	 *
	 * @param string $filter_name nome del filtro
	 * @param string|callback $error_message stringa col messaggio di errore
	 * @param boolean $error_message_is_callback TRUE il parametro $error_message è una callback, FALSE altrimenti
	 */
	public function setErrorMessage($filter_name, $error_message, $error_message_is_callback = FALSE) {
		foreach($this->filters as $k => $f) {
			if ($f['label'] == $filter_name) {
				$this->filters[$k]['errmsg'] = strval($error_message);
				$this->filters[$k]['errmsg_callback'] = $error_message_is_callback;
				break;
			}
		}
	}
	
	/**
	 * Imposta il messaggio di errore per un filtro richiesto
	 *
	 * @param string $filter_name nome del filtro
	 * @param string|callback $error_message stringa col messaggio di errore
	 * @param boolean $required_error_message_is_callback TRUE il parametro $error_message è una callback, FALSE altrimenti
	 */
	public function setRequiredErrorMessage($filter_name, $error_message, $required_error_message_is_callback = FALSE) {
		foreach($this->filters as $k => $f) {
			if ($f['label'] == $filter_name) {
				$this->filters[$k]['reqmsg'] = strval($error_message);
				$this->filters[$k]['required_callback'] = $required_error_message_is_callback;
				break;
			}
		}
	}
	
	/**
	 * Imposta il valore di fallback per un filtro
	 *
	 * @param string $filter_name nome del filtro
	 * @param mixed|callback $fallback_value stringa col valore
	 * @param boolean $fallback_value_is_callback TRUE il parametro $fallback_value è una callback, FALSE altrimenti
	 */
	public function setFallbackValue($filter_name, $value, $fallback_value_is_callback = FALSE) {
		foreach($this->filters as $k => $f) {
			if ($f['label'] == $filter_name) {
				$this->filters[$k]['fallback'] = $fallback_value;
				$this->filters[$k]['fallback_callback'] = $fallback_value_is_callback;
				break;
			}
		}
	}

	/**
	 * Imposta un campo come richiesto.
	 *
	 * @param string $filter_name nome del filtro
	 * @param boolean $is_required TRUE il campo è richiesto, FALSE altrimenti
	 */
	public function setRequired($filter_name, $is_required) {
		foreach($this->filters as $k => $f) {
			if ($f['label'] == $filter_name) {
				$this->filters[$k]['required'] = $is_required;
				break;
			}
		}
	}
	
	/**
	 * Ritorna il filtro di input associato ad un certo nome
	 *
	 * @param string $filter_name nome del filtro da estrarre
	 * @return InputFilter il filtro o NULL se non trovato
	 */
	public function get($filter_name) {
		foreach($this->filters as $k => $f) {
			if ($f['label'] == $filter_name) {
				return $f['filter'];
			}
		}
		return NULL;
	}
	
	/**
	 * Rimuove tutte le regole di validazione impostate.
	*/
	public function clear() {
		$this->filters = array();
	}

	/**
	 * Rimuove un filtro di input in base alla sua etichetta
	 * 
	 * @param string $label stringa col nome della regola da rimuovere
	 * @return boolean TRUE se ha rimosso la regola, FALSE altrimenti
	*/
	public function del($label) {
		foreach( $this->filters as $k => $rule) {
			if ($rule['label'] == $label) {
				unset($this->filters[$k]);
				return TRUE;
			}
		}
		return FALSE;
	}

	// ------------------------------ binding di oggetti
	
	/**
	 * "Aggancia" un oggetto all'istanza.
	 *
	 * Il modo in cui le proprietà vengono lette o scritte viene regolato
	 * da bind e bindCallback.
	 *
	 * Invoca {@link onBindObject} in caso di successo.
	 * 
	 * @param object $object object un oggetto
	 * @return object l'istanza agganciata o NULL se non si è passato un oggetto
	 * @see setValue
	 * @see getValue
	 * @see bindCallback
	 * @see bind
	 * @see onBindObject
	*/
	public function bindObject($object) {
		if (is_object($object)) {
			$this->object = $object;
			$this->onBindObject($this->object);
			return $object;
		}
		return NULL;
	}

	/**
	 * Eseguita quando viene collegato con successo un oggetto.
	 *
	 * @param mixed $object oggetto che è stato collegato
	 * @see bindObject
	 */
	public function onBindObject($object) {

	}
	
	/**
	 * Disassocia questo validatore dall'oggetto
	 * @return object l'istanza dell'oggetto associato o NULL
	*/
	public function unbindObject() {
		if (is_object($this->object)) {
			$this->clearBindTable();
			$o = $this->object;
			$this->object = NULL;
			return $o;
		}
		return NULL;
	}

	/**
	 * Ritorna l'oggetto associato.
	 * @return object l'istanza dell'oggetto associato
	*/
	public function getObject() {
		return $this->object;
	}

	/**
	 * Informa se ci sia un oggetto associato.
	 * 
	 * @return boolean TRUE se ha un oggetto associato, FALSE altrimenti
	*/
	public function hasObject() {
		return is_object($this->object);
	}
	
	
	/**
	 * Associa il nome di un controllo ad un metodo getter o setter dell'oggetto associato.
	 * 
	 * Verrà poi utilizzato tale metodo per impostare o leggere il valore.
	 * 
	 * Il metodo getter deve essere pubblico e ritornare un valore e non accettare parametri
	 * 
	 * Il metodo setter deve essere pubblico e accettare un unico parametro, che è quello del valore del controllo
	 * 
	 * Passare un parametro NULL per non impostare quella callback
	 * 
	 * Questa tabella verrà poi utilizzata da getValue e da setValue
	 * 
	 * @param string $name nome del controllo da associare
	 * @param string $getter stringa col nome del metodo getter da invocare
	 * @param string $setter stringa col nome del metodo setter da invocare
	 * @see bindObject
	 * @see setValue
	 * @see getValue
	*/
	public function bind($name, $getter, $setter = NULL) {
		if (!empty($getter) || !empty($setter)) {
			$this->bind_table[$name] = array( 'get' => strval($getter), 'set' => strval($setter)  );
		}
	}
	
	/**
	 * Rimuove l'associazione tra un metodo dell'oggetto agganciato ed una etichetta
	 * @param $name string nome dell'etichetta
	*/
	public function unbind($name) {
		if (array_key_exists($name, $this->bind_table)) unset($this->bind_table[$name]);
	}
	
	/**
	 * Associa il nome di un controllo ad una callback usata come getter o setter.
	 * 
	 * Verrà poi utilizzato tale metodo per impostare o leggere il valore.
	 * 
	 * Il metodo getter deve ritornare un valore e non accettare parametri
	 * Il metodo setter deve accettare un unico parametro, che è quello del valore del controllo
	 * 
	 * Questa tabella verrà poi utilizzata da getValue e da setValue
	 * 
	 * @param string $name nome del controllo da associare
	 * @param callback $getter callback per il metodo getter da invocare
	 * @param callback $setter callback per il metodo setter da invocare
	 * @see bindObject
	 * @see setValue
	 * @see getValue
	*/
	public function bindCallback($name, $getter, $setter = NULL) {
		if (!empty($getter)) $this->bindCallback2(self::FUNC_GET, $name, $getter);
		if (!empty($setter)) $this->bindCallback2(self::FUNC_SET, $name, $setter);
	}
	
	/**
	 * Rimuove l'associazione tra un controllo ed una callback
	 * @param string $name nome dell'etichetta
	 */
	public function unbindCallback($name) {
		$this->unbindCallback2(self::FUNC_GET, $name);
		$this->unbindCallback2(self::FUNC_SET, $name);
	}
	
	/**
	* Associa il nome di un controllo ad una callback.
	*
	* Il parametro $func è una delle costanti:
	* FUNC_GET - indica il getter
	* FUNC_SET - indica il setter
	*
	* Se i parametri sono vuoti, il metodo viene invocato senza inviare
	* parametri.
	*
	* La callback getter deve ritornare il valore da inserire nel campo.
	* La callback per il setter deve accettare come primo parametro il valore del campo,
	* quelli successivi sono i parametri specificati.
	*
	* @param integer $func un intero con la funzione da impostare
	* @param string $control nome del parametro
	* @param callback $callback la callback da invocare
	* @param array $params parametri per la callback
	*/
	public function bindCallback2($func, $control, $callback, $params = array()) {
		if (!is_array($params)) return FALSE;
		if (!array_key_exists($control, $this->callback_table)) {
			$this->callback_table[$control] = array(
				'get' => array('callback' => NULL, 'params' => NULL),
				'set' => array('callback' => NULL, 'params' => NULL)
			);
		}
		if ($func == self::FUNC_GET || $func == self::FUNC_SET) {
			if ($func == self::FUNC_GET) $e = 'get';
			else $e = 'set';
			$this->callback_table[$control][$e]['callback'] = $callback;
			$this->callback_table[$control][$e]['params'] = $params;
		} 
	}
	
	/**
	* Rimuove l'associazione di un controllo da una callback specifica
	*
	* Il parametro $func è una delle costanti:
	* FUNC_GET - indica il getter
	* FUNC_SET - indica il setter
	*
	* @param integer $func un intero con la funzione da impostare
	* @param string $control nome del parametro
	*/
	public function unbindCallback2($func, $control) {
		if (array_key_exists($control, $this->callback_table) && ($func == self::FUNC_GET || $func == self::FUNC_SET)) {
			if ($func == self::FUNC_GET) $e = 'get';
			else $e = 'set';
			$this->callback_table[$control][$e]['callback'] = NULL;
			$this->callback_table[$control][$e]['params'] = NULL;
		}
	}
	
	/**
	* Rimuove tutte le associazioni di callback
	*/
	public function clearCallbackTable() {
		$this->callback_table = array();
	}
	
	/**
	* Rimuove tutte le associazioni di callback con l'oggetto associato
	*/
	public function clearBindTable() {
		$this->bind_table = array();
	}
}

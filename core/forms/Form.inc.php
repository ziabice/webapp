<?php
/**
 * (c) 2008, 2009 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Astrazione di una form (X)HTML.
 *
 * Gestione delle Form xhtml secondo un approccio MVC
 * 
 * La verifica della attivazione (ossia l'invio) avviene mediante un controllo
 * sulla richiesta HTTP della presenza di parametri "attivatori": ossia variabili con
 * i nomi dei pulsanti di submit registrati nella form.
 * 
 * Ogni form è composta da controlli e da uno o più pulsanti di submit che fanno
 * da attivatori del modulo e vengono chiamati "activator".
 * 
 * L'oggetto deve conoscere quali siano questi attivatori mediante chiamate a addActivator().
 * Si possono aggiungere dei bottoni come istanze di FormButton che la classe FormView
 * curerà poi di aggiungere in fondo al modulo in xhtml, questi bottoni aggiungono
 * degli attivatori.
 * Viene tenuto un elenco di campi "hidden" con il valore ad esso associato.
 *
 * Uso tipico:
 * <code>
 * // Crea il modulo
 * $form = new Form( ... );
 *
 * // Imposta un view, di solito una sottoclasse di FormView
 * $form->setView( new FormView( ... ) );
 *
 * // imposta un validator
 * $validator = $form->setValidator( new InputValidator(...) );
 *
 * // Aggancia un oggetto al validator, in modo che fornisca i dati alla View
 * $validator->bingObject( $obj );
 *
 * // Aggiunge dei pulsanti
 * // 1. metodo preferito: un pulsante standard
 * $form->addButton( FormButtonFactory::getButton( FormButtonFactory::BTN_SEND ) )
 * // 2. pulsanti custom
 * $form->addButton( new FormButton(...) );
 *
 * // Lancia la form
 * $ok = $form->launch();
 *
 * if ($ok == Form::FORM_IS_GOOD) {
 *   // La form è corretta e validata usando il validatore
 *   // che ha anche popolato i campi dell'oggetto associato
 *   // adesso di solito salva l'oggetto in un database
 *   ...
 * } else {
 *	 // Mostra il modulo.
 *   // Il modulo potrebbe non essere stato lanciato o essere stato lanciato, ma
 *   // presentare errori di compilazione, che sono stati
 *   // automaticamente copiati nella FormView associata
 *
 *   $form->show();
 *
 * }
 * </code>
*/
class Form {
	const
		// Valore di ritorno di launch()
		FORM_NOT_LAUNCHED = 0, //il modulo non è stato lanciato
		FORM_HAS_ERRORS = 300, //il modulo è stato lanciato e contiene errori
		FORM_IS_GOOD = 200, //il modulo è stato lanciato e non contiene errori,
		FORM_STOPPED = 100; //il modulo è stato lanciato, ma fermato (con un attivatore di termine operazioni)
		
	
	public
		$buttons = array(), // Contiene le istanze di FormButton
		$hiddens; // HashMap coi campi nascosti della form
		
	protected
		$form_view = NULL, // istanza di BaseFormView che è il View dell'oggetto 
		$action, // Istanza di URL che indica dove punta il modulo
		$method, // Metodo HTTP da utilizzare per l'invio del modulo (una delle costanti Request::HTTP_POST_REQUEST o Request::HTTP_GET_REQUEST)
		$formid, // Stringa con l'id xhtml del modulo
		$activators, // Array di stringhe coi nomi degli attivatori
		$validator = NULL, // Instanza di FormValidator associata
		$activator = '', // Attivatore corrente del modulo (dopo una invocazione a isSubmitted)
		$enctype = '', // Modo in cui i dati vengono forniti al server
		$xhtml_attr = '', // Stringa xhtml con attributi del tag <form>
		$launch_submit_handler = FALSE, // Flag usato dal validator per sapere se deve compiere operazioni quando la form è agganciata
		$is_upload_form = FALSE, // flag: TRUE la form fa l'upload di file, FALSE no
		$accept, // Lista separata da virgole di contenuti accettati dal modulo (tipi MIME)
		$accept_charset; // Lista separata da spazi o virgole di codifiche di caratteri accettate 

	/**
	 * Crea un nuovo modulo xhtml.
	 * 
	 * Nel parametro $action la parte host viene usata per l'action vera e propria,
	 * mentre tutti gli altri campi vanno nella sezione hidden.
	 * Al momento della costruzione tra i campi hidden vengono aggiunti anche i parametri di sessione
	 * 
	 * @param URL $action istanza di URL
	 * @param string $method metodo di invio della Form, una delle costanti di Request: HTTP_POST_REQUEST o HTTP_GET_REQUEST
	 * @param string $formid stringa contenente l'id (CSS) del modulo
	*/
	public function __construct(URL $action, $method = Request::HTTP_POST_REQUEST, $formid = '') {
		$this->hiddens = new HashMap();
		$this->activators = array();
		$this->activator = NULL;
		$this->formUploadsFiles(FALSE);
		$this->setAccept('');
		
		$this->setAction($action);
		$this->setMethod($method);
		$this->setFormID($formid);
		$this->addSession();
	}

	// ---------------------------------- Campi nascosti della form
	/**
	 * Ritorna l'hashmap coi controlli nascosti
	 * La chiave degli elementi verrà utilizzata per il nome dei campi
	 * @return HashMap istanza di HashMap con le associazioni chiave-valore
	*/
	public function getHiddenControls() {
		return $this->hiddens;
	}

	// ---------------------- Interazione con l'ambiente
	
	/**
	 * Aggiunge i valori della sessione corrente alla form
	*/
	public function addSession() { 
		WebApp::getInstance()->getSessionManager()->toForm($this);
		$this->touch();
	}
	
	/**
	 * Toglie i valori della sessione corrente dalla Form
	*/
	public function delSession() { 
		WebApp::getInstance()->getSessionManager()->removeFromForm($this);
		$this->touch();
	}

	/**
	 * Copia i parametri dell'oggetto URL nei campi nascosti
	 * @param URL $url un oggetto di tipo URL
	*/
	public function importURL(URL $url) {
		foreach($url->getParams() as $k => $v) $this->hiddens->set($k, $v);
	}

	/**
	 * Imposta l'URL a cui inviare il modulo.
	 * 
	 * 
	 * Rimuove ogni dato impostato con una precedente invocazione
	 * del metodo.
	 * @param URL $action istanza di URL
	*/
	public function setAction(URL $action) {
		// Rimuove i vecchi valori se ci sono...
		if (is_object($this->action)) {
			foreach($this->action->getParams() as $k => $v) $this->hiddens->del($k);
		}
		$this->action = $action;
		$this->importURL($action);
		$this->touch();
	}

	/**
	 * Ritorna l'URL a cui inviare il modulo
	 * 
	 * @return URL una istanza di URL
	*/
	public function getAction() {
		return $this->action;
	}

	/**
	 * Imposta il metodo di invio della form
	 * @param string $method uno dei valori Request::HTTP_*_REQUEST
	 * @see Request
	*/
	public function setMethod($method) {
		$this->method = $method;
		$this->touch();
	}

	/**
	 * Ritorna il metodo di invio della form
	 * @return string una stringa col metodo di invio: una delle costanti FORM_METHOD_POST o FORM_METHOD_GET
	*/
	public function getMethod() {
		return $this->method;
	}

	/**
	 * Imposta l'ID della form
	 * @param string $formid una stringa con l'id
	*/
	public function setFormID($formid) {
		$this->formid = $formid;
		$this->touch();
	}

	/**
	 * Ritorna l'ID della form
	 * @return string una stringa con l'id
	*/
	public function getFormID() {
		return $this->formid;
	}

	/**
	 * Informa se la form preveda l'upload di file
	 *
	 * @return boolean TRUE se è una form di upload, FALSE altrimenti
	*/
	public function isUploadForm() {
		return $this->is_upload_form;
	}

	/**
	 * Permette al modulo di accettare l'upload di file.
	 * Chiamarla prima di agganciare un FormView
	 * @param boolean $accept TRUE la form accetta i file, FALSE non accetta i file
	 **/
	public function formUploadsFiles($accept) {
		$this->is_upload_form = $accept;
		if ($accept) {
			$this->enctype = 'multipart/form-data';
		} else {
			$this->enctype = 'application/x-www-form-urlencoded';
		}
		$this->touch();
	}
	
	/**
	 * Ritorna la dimensione massima in byte di un file in upload
	 * @return integer intero con la dimensione.
	*/
	public static function getMaxUploadFileSize() {
		$v = ini_get('upload_max_filesize');
		$p = ini_get('post_max_size');
		switch (strtolower($v[strlen($v)-1])) {
			case 'k': $v *= 1024;break;
			case 'm': $v *= 1048576;break;
		}

		switch (strtolower($p[strlen($p)-1])) {
			case 'k': $p *= 1024;break;
			case 'm': $p *= 1048576;break;
		}

		return ($v < $p ? $v : $p);
	}
	
	/**
	 * Ritorna il tipo di contenuto che verrà inviato al server
	 *
	 * @return string una stringa col valore
	*/
	public function getContentType() {
		return $this->enctype;
	}
	
	/**
	 * Imposta gli attributi aggiuntivi della form.
	 * Rimuove tutti quelli impostati precedentemente
	 * @param string $xhtml codice xhtml degli attributi (aggiunti al tag <form>)
	*/
	public function setXHTMLAttr($xhtml) {
		$this->xhtml_attr = $xhtml;
		$this->touch();
	}

	/**
	 * Ritorna gli attributi xhtml aggiuntivi della form
	 * @return string una stringa con gli attributi
	*/
	public function getXHTMLAttr() {
		return $this->xhtml_attr;
	}
	
	/**
	 * Ritorna i contenuti accettati dal modulo
	 * 
	 * @return string una stringa contenente una lista separata da virgola di contenuti validi (tipi MIME)
	*/
	public function getAccept() {
		return $this->accept;
	}
	
	/**
	 * Imposta i contenuti accettati dal modulo
	 * 
	 * @param string $accept una stringa contenente una lista separata da virgola di contenuti validi (tipi MIME)
	*/
	public function setAccept($accept) {
		$this->accept = $accept;
		$this->touch();
	}
	
	/**
	 * Ritorna le codifiche dei caratteri accettati dal modulo
	 * 
	 * @return string una stringa contenente una lista separata da virgola (o spazi) delle codifiche dei caratteri
	*/
	public function getAcceptCharset() {
		return $this->accept_charset;
	}
	
	/**
	 * Imposta le codifiche dei caratteri accettate modulo
	 *
	 * @param string $charset una stringa contenente una lista separata da virgola (o spazi) delle codifiche dei caratteri
	*/
	public function setAcceptCharset($charset) {
		$this->accept_charset = $charset;
		$this->touch();
	}

	// ------------------------- Gestione validazione e Model
	/**
	 * Ritorna l'oggetto validatore per questo form.
	 *
	 * @return InputValidator istanza associata o NULL
	*/
	public function getValidator() {
		return $this->validator;
	}
	
	/**
	 * Imposta un oggetto InputValidator per questa form.
	 * 
	 * L'InputValidator associato fa anche da Model, fornendo i dati associati
	 * ai vari campi, oltre che una interfaccia tra i metodi di un oggetto ad esso
	 * associato ed il campo che va a popolare.
	 * 
	 * @param InputValidator $validator istanza di InputValidator
	 * @return InputValidator l'istanza passata come parametro
	*/
	public function setValidator(InputValidator $validator) {
		$this->validator = $validator;
		$this->touch();
		return $this->validator;
	}
	
	/**
	 * Ritorna il valore associato ad un campo, utilizzando l'oggetto InputValidator
	 * associato.
	 * 
	 * @param string $fieldname stringa col nome del campo di cui si vuole il valore
	 * @return mixed il valore del campo o NULL.
	*/
	public function getValue($fieldname) {
		return (is_object($this->validator) ? $this->validator->getValue($fieldname) : NULL);
	}
	
	// -------------------------- Pulsanti di azione
	
	/**
	 * Aggiunge un pulsante di azione alla form. Un pulsante di azione è
	 * un pulsante di submit, che porta con se anche un activator della form
	 * 
	 * @param FormButton$btn istanza della classe FormButton
	 * @return FormButton l'istanza del pulsante
	*/
	public function addButton(FormButton $btn) {
		$btn->setForm($this);
		$this->buttons[] = $btn;
		$a = $btn->getActivator();
		if (!empty($a)) {
			$this->addActivator($a);
		}
		$this->touch();
		return $btn;
	}


	/**
	 * Rimuove un pulsante di azione dal modulo.
	 * 
	 * @param string $btn_name stringa col nome del pulsante da rimuovere
	*/
	public function delButton($btn_name) {
		foreach($this->buttons as $k => $btn) {
			if (strcmp($btn->getName(), $btn_name) == 0) {
				$this->delActivator( $btn->getActivator() );
				unset($this->buttons[$k]);
				$this->touch();
			}
		}
	}
	
	/**
	 * Informa se sono presenti dei bottoni di azione
	 * 
	 * @return boolean TRUE se sono presenti bottoni, FALSE altrimenti
	*/
	public function hasButtons() {
		return count($this->buttons) > 0;
	}
	
	/**
	 * Ritorna un iteratore sui pulsanti associati al modulo.
	 * 
	 * @return ArrayIterator l'iteratore
	 * 
	 */
	public function getFormButtonIterator() {
		return new ArrayIterator($this->buttons);
	}
	
	// -------------- Interazione con la richiesta HTTP
	
	/**
	 * Verifica se il modulo sia stato lanciato, convalida i dati inviati.
	 * 
	 * La validazione dei dati avviene usando l'istanza di InputValidator associata
	 * Popola l'oggetto BaseFormView associato con gli errori di compilazione.
	 * E' possibile definire degli attivatori come "stop" ossia dei pulsanti che interrompono
	 * l'editazione nel modulo senza richiedere il salvataggio dei dati.
	 * E' possibile poi disabilitare la notifica degli errori al view.
	 * 
	 * Salva l'attivatore del modulo, che può essere ottenuto con {@link getActivator}
	 * Il valore ritornato è una delle seguenti costanti:
	 * 
	 * FORM_NOT_LAUNCHED - il modulo non è stato lanciato
	 * FORM_HAS_ERRORS - il modulo è stato lanciato e contiene errori
	 * FORM_IS_GOOD - il modulo è stato lanciato e non contiene errori
	 * FORM_STOPPED - il modulo è stato lanciato, ma attivato da un attivatore di stop (la validazione non è stata eseguita)
	 * 
	 * @param boolean $set_values TRUE copia i valori validati nell'oggetto associato all'InputValidator, FALSE ignora i valori
	 * @param mixed $stop_activators string o array di stringhe coi nomi degli attivatori che valgono come stop
	 * @param boolean $notify_errors TRUE copia gli errori nel view, FALSE non copia nulla.
	 * @return integer stato della validazione
	*/
	public function launch($set_values = TRUE, $stop_activators = array(), $notify_errors = TRUE) {
		if (!is_array($stop_activators)) $stop_activators = array(strval($stop_activators));
		// Verifica se sia stata inviata
		if ( $this->isSubmitted() ) {
			// Blocca se l'utente ha bloccato
			if (in_array($this->getActivator(), $stop_activators)) {
				return self::FORM_STOPPED;
			}
			
			// Se c'è un validatore convalida i dati
			if ( is_object($this->validator) ) {
				$this->validator->validate( $set_values );
				// Se il modulo non è valido, propaga gli errori al View
				if ( $this->validator->isValid() == FALSE ) {
					if (is_object($this->form_view) && $notify_errors) {
						// Invoca una funzione per popolare gli errori
						$this->form_view->copyValidationErrors();
						$this->touch();
					}
					return self::FORM_HAS_ERRORS;
				} else {
					return self::FORM_IS_GOOD;
				}
			} else {
				return self::FORM_IS_GOOD;
			}
		}
		return self::FORM_NOT_LAUNCHED;
	}
	
	/**
	 * Indica se la form sia stata realmente inviata oppure no.
	 * 
	 * Una form è stata inviata quando il metodo della richiesta HTTP è lo stesso
	 * della form ed inoltre è presente una variabile
	 * con il nome di uno dei suoi pulsanti di submit (ossia uno dei suoi attivatori)
	 * @return boolean TRUE se la form è stata inviata, FALSE altrimenti
	*/
	public function isSubmitted() {
		return !empty($this->activator);
	}
	
	/**
	 * Controlla nella richiesta HTTP se ci sia un attivatore di questo
	 * modulo e nel caso popola $this->activator.
	 * Verifica inoltre che il metodo della richiesta sia lo stesso richiesto dalla form.
	 * @return boolean TRUE se ha trovato un attivatore, FALSE altrimenti
	*/
	protected function verifyFormSubmission() {
		$this->activator = Request::getInstance()->getFormActivator($this->method, $this->activators);
		if ($this->activator !== FALSE) return TRUE;
		return FALSE;
	}

	/**
	 * Ritorna l'attivatore del modulo
	 * @return string una stringa col nome dell'attivatore
	*/
	public function getActivator() {
		return $this->activator;
	}

	/**
	 * Aggiunge uno o più attivatori del modulo.
	 * 
	 * Viene verificato immediatamente se gli attivatori effettivamente attivino
	 * il modulo mediante chiamata a {@link verifyFormSubmission}
	 * @param string $activators stringa o array di stringhe col nome del controllo che può attivare il modulo
	*/
	public function addActivator($activators) {
		if (is_array($activators)) {
			$this->activators = array_merge($this->activators, $activators);
		} else {
			$this->activators[] = $activators;
		}
		$this->verifyFormSubmission();
	}

	/**
	 * Rimuove uno o più attivatori della form.
	 * 
	 * @param string $activators stringa o array di stringhe col nome degli attivatori da rimuovere
	*/
	public function delActivator($activators) {
		
		if (is_array($activators)) {
			// ritorna tutti gli elementi di $this->activators non presenti in $activators
			$this->activators = array_diff($this->activators, $activators);
		} else {
			$p = array_search($activators, $this->activators);
			if ($p !== FALSE) {
				unset($this->activators[$p]);
			}
		}
		$this->verifyFormSubmission();
	}

	/**
	 * Azzera tutti gli attivatori per questa form
	*/
	public function freeActivators() {
		$this->activators = array();
		$this->activator = NULL;
	}
	
	// ---------------------------- Interazione col View
	/**
	 * Imposta il View attraverso una classe BaseFormView.
	 * Al momento dell'aggancio viene invocata sul view il metodo setForm.
	 *
	 * @param $view istanza di BaseFormView agganciata
	 * @return BaseFormView l'istanza agganciata
	*/
	public function setView(BaseFormView $view) {
		$this->form_view = $view;
		// Notifica al view l'aggancio
		$this->form_view->setForm($this);
		$this->touch();
		return $view;
	}
	
	/**
	 * Ritorna l'istanza di BaseFormView associata
	 * @return BaseFormView istanza di BaseFormView
	*/
	public function getView() {
		return $this->form_view;
	}
	
	/**
	 * Informa se ci sia un View agganciata
	 * @return boolean TRUE se c'è una View, FALSE altrimenti
	*/
	public function hasView() {
		return is_object($this->form_view);
	}
	
	/**
	 * Manda al browser il rendering di questa Form effettuato
	 * mediante l'oggetto BaseFormView agganciato
	*/
	public function show() {
		echo $this->getRenderedHTML();
	}
	
	/**
	 * Ritorna il codice XHTML generato dalla View agganciata.
	 * Eventualmente (basandosi su \ref viewNeedsUpdate) aggiorna l'oggetto View.
	 * @return string una stringa col codice XHTML generato dal view, o una stringa vuota se non c'è un View
	*/
	public function getRenderedHTML() {
		if ( $this->hasView() ) {
			if ($this->viewNeedsUpdate()) $this->updateView();
			return $this->form_view->getHTML();
		} else {
			return '';
		}
	}
	
	/**
	 * Aggiorna la View in seguito a cambiamenti dell'istanza di Form
	*/
	protected function updateView() {
		$this->form_view->update();
	}
	
	/**
	 * Notifica che ci sono cambiamenti nello stato dell'oggetto
	 * che necessitano un ridisegno del View
	*/
	public function touch() {
		$this->view_needs_update = TRUE;
	}
	
	/**
	 * Informa se la Form è cambiata e perciò il View abbia bisogno
	 * di aggiornarsi. Una volta letta la prooprietà viene riportata
	 * a FALSE.
	 * Le chiamate a \ref touch() influenzano il valore di ritorno
	 * @return boolean TRUE se il View deve essere aggiornato, FALSE altrimenti
	*/
	protected function viewNeedsUpdate() {
		if ($this->hiddens->isChanged() || $this->view_needs_update) {
			$this->view_needs_update = FALSE;
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * Copia gli errori di validazione nel View e marca il modulo
	 * come modificato, in modo da aggiornare il view.
	*/
	public function copyValidationErrorsToView() {
		if (is_object($this->form_view)) {
			// Invoca una funzione per popolare gli errori
			$this->form_view->copyValidationErrors();
			$this->touch();
		}
	}
	
	/**
	 * Rimuove tutti gli errori di compilazione, dal validatore e dalla view
	*/
	public function clearAllErrors() {
		if (is_object($this->validator)) $this->validator->clearAllErrors();
		if (is_object($this->form_view)) $this->form_view->clearErrors();
	}
	
}

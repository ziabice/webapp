<?php
/**
 * (c) 2008-2013 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Una azione eseguita dall'utente: un oggetto che reagisce all'input
 * della richiesta HTTP.
 *
 * Viene eseguito da WebApp a seconda dell'azione richiesta o il metodo {@link execute} oppure
 * un metodo executeXXXX per classi che gestiscono azioni multiple.
 *
 * Un gestore di azione può o contenere azioni multiple (vari metodi executeXXXX) oppure una sola
 * azione: in questo caso il nome della classe indica quale sia l'azione e va solo ridefinito execute.
 *
 * Una azione gestisce anche la risposta e la visualizzazione della stessa, istruendo
 * la WebApp a inizializzare il gestore di pagina al momento opportuno.
 *
 * Una azione quando eseguita pone tutto il testo in più buffer di output, indicati da una etichetta.
 * Tali buffer verranno poi posti nel layout grafico del View.
 * Per scrivere del testo utilizzare perciò write e write_to.
 * 
 * Una azione viene di solito eseguita in modo sincrono: viene attivato il caching dell'output
 * di PHP (ob_start()) e lanciato uno dei metodi executeXXX(). Il testo emesso viene poi passato al gestore
 * di risposta che lo invia al browser.
 * Quando un'azione invece è asincrona ha la responsabilità di produrre l'output dalla risposta. Si può
 * utilizzare una azione asincrona per svolgere compiti lunghi, come ad esempio la generazione di un file
 * e l'invio al client. Una azione asincrona viene eseguita nella risposta, quindi nell'ultimo stadio
 * della generazione di una risposta al client.
 * Viene prima eseguito il metodo executeXXX() e poi dalla risposta (l'oggetto Response)
 * viene lanciato il metodo WebAppAction::asyncResponseHandler().
 * Ricordarsi che il metodo finalizeReponse() viene invocato sempre prima di eseguire la risposta: è il posto giusto
 * per eseguire operazioni tipo l'inserimento di header specifici o altro.
 * Solo una risposta di tipo Response::RESPONSE_COMPLETE, Response::RESPONSE_TEXTDATA, Response::RESPONSE_HEADERS può essere asincrona.
 * 
 * Come procedere per una risposta asincrona:
 * <code>
 * public function execute() {
 * 		// Imposta la risposta asincrona
 * 		$this->setAsync(TRUE);
 * 		
 * 		// Esegue altro eventuale codice
 * 		...
 * }
 * 
 * public function asyncResponseHandler() {
 * 		// Codice di esempio
 * 		// Gli header per la risposta vanno impostati prima
 * 		// usando execute() o in finalizeResponse()
 * 
 * 		// Richiede parecchio tempo
 * 		set_time_limit(0);
 * 		
 * 		// Chiude la sessione
 * 		$this->getResponse()->closeSession();
 * 		
 * 		// Fa quello che deve fare
 * 		...
 * }
 * </code>
 * */
class WebAppAction {
	const
		MAIN_OUTPUT_BUFFER = '__main__'; // nome dell'output buffer principale


	protected
		$_is_async = FALSE, // Flag per azione asincrona
		$_action_return_value, // valore di ritorno dell'esecuzione
		$_output_buffers; // array di StringArray

	/**
	 * Costruisce inizializzando i buffer di testo e 
	 * impostando una azione sincrona.
	 */
	public function __construct() {
		$this->_output_buffers = array(
			self::MAIN_OUTPUT_BUFFER => new StringArray()
		);
		$this->_is_async = FALSE;
	}
	
	/**
	 * Informa se l'azione viene eseguita in modo asincrono.
	 * 
	 * @return boolean TRUE l'azione è asincrona, FALSE altrimenti
	 * */
	public function isAsync() {
		return $this->_is_async;
	}
	
	/**
	 * Imposta l'esecuzione dell'azione in modo sincrono o asincrono.
	 * 
	 * @param boolean $is_async TRUE l'azione è asincrona, FALSE l'azione è sincrona
	 * */
	public function setAsync($is_async) {
		$this->_is_async = (bool) $is_async;
	}

	/**
	 * Handler per le azioni asincrone.
	 * 
	 * Viene invocato dalla risposta.
	 * */
	public function asyncResponseHandler() {
	}
	
	/**
	 * Eseguita dalla WebApp quando si è pronti a lanciare
	 * l'esecuzione dell'azione.
	 * 
	 * Di solito carica semplicemente il PageLayout associato all'azione.
	 * 
	 * @see initializePageLayout
	 * @throws WebAppActionLaunchException
	 */
	public function webappIsReady() {
		// Carica il layout standard di pagina
		$this->initializePageLayout();
	}
	
	/**
	 * Inizializa (eventualmente carica) il PageLayout associato a questa azione.
	 * 
	 * @throws WebAppActionLaunchException
	 */
	protected function initializePageLayout() {
		if (!WebApp::getInstance()->getView()->initialize()) throw new WebAppActionLaunchException(WebApp::getInstance()->getCurrentModule(), WebApp::getInstance()->getCurrentAction() );
	}
	
	/**
	 * Metodo eseguito di default dal Front Controller quando
	 * non ci sono azioni nella richiesta HTTP.
	 *
	 * @return mixed il valore di ritorno viene utilizzato
	 * @see launch
	 * @see WebApp::launchAction
	 * @throws WebAppException
	*/
	public function execute() {
	}
	
	/**
	 * Eseguita dal Front Controller prima di lanciare executeXXXX
	*/
	public function preRun() {
	}
	
	/**
	 * Eseguita dal Front Controller dopo executeXXXX
	*/
	public function postRun() {
	}
	
	/**
	 * Passa in modo silente ad un'altro modulo/azione. L'utente non viene notificato
	 * dell'operazione
	 * 
	 * E' un proxy per {@link WebApp::forward}, che genera un'eccezione gestita dal 
	 * front controller stesso
	 * 
	 * @param string $module stringa col modulo verso il quale andare
	 * @param string $action stringa con l'azione da eseguire
	 * @throws WebAppForwardException
	*/
	public function forward($module, $action = '') {
		WebApp::getInstance()->forward($module, $action);
	}
	
	/**
	 * Rinvia all'azione di errore HTTP 404 "pagina non trovata"
	 *
	 * E' un proxy per {@link WebApp::forward404}, che genera un'eccezione gestita
	 * dal front controller.
	 *
	 * @throws WebAppHTTPException
	*/
	public function forward404() {
		WebApp::getInstance()->forward404();
	}
	
	/**
	 * Ritorna l'istanza Request dell'applicazione.
	 * 
	 * E' un proxy per {@link Request::getInstance}
	 * 
	 * @return Request una istanza di Request
	*/
	public function getRequest() {
		return Request::getInstance();
	}
	
	/**
	 * Fa il redirect verso una URL.
	 *
	 * E' un proxy per {@link WebApp::redirect}
	 * 
	 * @param string $location stringa con la url completa verso cui andare o una destinazione interna
	 * @throws WebAppRedirectException
	*/
	public function redirect($location) {
		WebApp::getInstance()->redirect($location);
	}
	
	/**
	 * Imposta la lingua corrente per le traduzioni.
	 *
	 * La lingua viene cambiata temporaneamente, non a livello globale.
	 * 
	 * @param string $lang stringa con la lingua nella forma 'xx_YY' es. it_IT
	*/
	public function setLanguage($lang) {
		WebApp::getInstance()->getI18N()->setLocale($lang);
	}
	
	/**
	 * Fornisce l'istanza di {@link Response} per questa azione.
	 *
	 * L'istanza viene inizializzata in {@link initializeResponse}
	 *
	 * @return Response
	*/
	public function getResponse() {
		return WebApp::getInstance()->getResponse();
	}
	
	/**
	 * Ritorna il router delle URL utilizzato dall'applicazione
	 * 
	 * E' un proxy per {@link WebApp::getRouter}
	 * 
	 * @return WebAppRouter
	*/
	public function getRouter() {
		return WebApp::getInstance()->getRouter();
	}
	
	/**
	 * Ritorna il Front Controller attuale.
	 * 
	 * Proxy per {@link WebApp::getInstance()}
	 * 
	 * @return WebApp il front controller
	*/
	public function WebApp() {
		return WebApp::getInstance();
	}
	
	/**
	 * Data una URL ne ritorna una rappresentazione testuale
	 *
	 * Proxy per il metodo {@link WebApp::link()}
	 * 
	 * @param URL $url di cui si vuole la rappresentazione testuale
	 * @param boolean $as_html TRUE ritorna come codice HTML (solo le entità), FALSE come testo
	 * @param boolean $with_session TRUE acclude il SID di sessione, FALSE non acclude il SID di sessione
	 * @param array $params array associativo con parametri addizionali
	 * @return string una URL testuale
	*/
	public function link(URL $url, $as_html = FALSE, $with_session = TRUE, $params = array()) {
		return WebApp::getInstance()->link($url, $as_html, $with_session);
	}
	
	/**
	 * Data una destinazione ne ritorna una rappresentazione testuale in forma di URL.
	 * 
	 * Proxy per {@link WebApp::link_to()}
	 * 
	 * @param string $destination stringa con la destinazione
	 * @param boolean $as_html TRUE ritorna come codice HTML (solo le entità), FALSE come testo
	 * @param boolean $with_session TRUE acclude il SID di sessione, FALSE non acclude il SID di sessione
	 * @param array $params array associativo con parametri addizionali
	 * @return string una URL testuale
	*/
	public function link_to($destination, $as_html = FALSE, $with_session = TRUE, $params = array()) {
		return WebApp::getInstance()->link_to($destination, $as_html, $with_session, $params);
	} 
	
	/**
	 * Data una stringa di destinazione, ne ritorna l'oggetto URL
	 * 
	 * Proxy per WebApp::getURL().
	 * 
	 * @param string $destination una stringa di destinazione
	 * @return URL della destinazione
	*/
	public function getURL($destination) {
		return WebApp::getInstance()->getURL($destination);
	}
	
	/**
	 * Ritorna una URL per una destinazione di cui specifichiamo solo l'azione.
	 * 
	 * Permette di scrivere destinazioni senza specificare il modulo, ma solo 
	 * l'azione. Il modulo è quello corrente. In questo modo è possibile
	 * scrivere azioni che è più facile spostare.
	 * Esempio: 
	 * <code>
	 *  $u = $this->getActionURL('foo?some_data=foodata');
	 * </code>
	 * 
	 * Se il modulo corrente è 'bar' ritorna una azione la cui destinazione
	 * effettiva è:
	 * bar/foo?some_data=foodata
	 * 
	 * @param string $action l'azione da eseguire
	 * @param boolean $as_destination TRUE ritorna una stringa con la destinazione, FALSE ritorna una URL
	 * @return URL|string la URL della destinazione o la stringa di destinazione
	 */
	public function getActionURL($action, $as_destination = FALSE) {
		if ($as_destination) {
			return WebApp::getInstance()->getCurrentModule().'/'.$action;
		} else {
			return WebApp::getInstance()->getURL( WebApp::getInstance()->getCurrentModule().'/'.$action );
		}
	}
	
	/**
	 * Proxy: prende il PageLayout corrente dal front controller invocando {@link WebApp::getPageLayout}
	 *
	 * @return PageLayout
	*/
	public function getPageLayout() {
		return WebApp::getInstance()->getPageLayout();
	}
	
	/**
	 * Ritorna l'utente corrente.
	 * 
	 * E' un proxy per {@link WebApp::getUser()}
	 *
	 * @return User una istanza di User
	*/
	public function getUser() {
		return WebApp::getInstance()->getUser();
	}
	
	/**
	 * Ritorna la stringa di destinazione corrente, ossia il percorso
	 * interno dell'azione attuale.
	 * 
	 * E' un proxy per {@link WebApp::getCurrentDestination()}
	 *
	 * @return string una stringa col percorso attuale
	*/
	public function getCurrentDestination() {
		return WebApp::getInstance()->getCurrentDestination();
	}
	
	/**
	 * Prepara la risposta.
	 *
	 * Invocata da {@link WebApp::executeResponse} prima di lanciare la risposta con {@link Response::execute()}.
	 * Esegue i passi necessari prima di eseguire effettivamente l'instanza di {@link Response}
	 * attuale. NB: a questo punto le istanze di {@link Response} in {@link WebApp} sono le stesse.
	 *
	 * Di default (riposta di tipo {@link Response::RESPONSE_COMPLETE}) compone il template XHTML usando il
	 * PageLayout e lo invia al browser.
	 *
	 * Nel caso di risposta di tipo {@link Response::RESPONSE_TEXTDATA} invia solo il testo contenuto nel
	 * buffer di output principale: usarla ad esempio per risposte ad eventi AJAX.
	 *
	*/
	public function finalizeResponse() {
		if ( $this->getResponse()->getResponseType() == Response::RESPONSE_COMPLETE) {
			$this->getResponse()->setContentType($this->getPageLayout()->getPageMIME(), $this->getPageLayout()->getEncoding() );
			$this->getResponse()->setContent( $this->getRenderedLayout() );
		} elseif($this->getResponse()->getResponseType() == Response::RESPONSE_TEXTDATA) {
			$this->getResponse()->setContent( $this->getText(self::MAIN_OUTPUT_BUFFER) );
		}
	}
	
	/**
	 * Ritorna una stringa col layout di pagina
	 * @return string col codice xhtml
	 */
	public function getRenderedLayout() {
		return WebApp::getInstance()->getRenderedLayout();
	}
	
	/**
	 * Ritorna una connessione al database.
	 * 
	 * Proxy per {@link WebApp::getDatabase}
	 *
	 * @param string $database nome della connessione, vuoto prende quella di default
	 * @return DatabaseDriver connessione al database
	*/
	public function getDB($database = '') {
		return WebApp::getInstance()->getDatabase($database);
	}
	
	/**
	 * Scrive la stringa nel buffer di default.
	 * 
	 * @param string|array $str la stringa o l'array di stringhe da stampare
	 */
	public function write($str) {
		$this->_output_buffers[self::MAIN_OUTPUT_BUFFER]->add($str);
	}

	/**
	 * Inserisce la stringa nel buffer di default, in testa.
	 *
	 * @param string|array $str la stringa o l'array di stringhe da stampare
	 */
	public function insert($str) {
		$this->_output_buffers[self::MAIN_OUTPUT_BUFFER]->insert($str);
	}

	/**
	 * Aggiunge il testo in un buffer di output.
	 *
	 * Se il buffer non esiste viene creato.
	 *
	 * @param string $template_zone stringa con la posizione del template in cui porre le informazioni
	 * @param string|array $str stringa o array di stringhe col testo da inserire
	 */
	public function write_to($buffer_name, $str) {
		if (!array_key_exists($buffer_name, $this->_output_buffers)) $this->_output_buffers[$buffer_name] = new StringArray();
		$this->_output_buffers[$buffer_name]->add($str);
	}

	/**
	 * Inserisce in testa il testo nel buffer di output.
	 *
	 * Se il buffer non esiste viene creato.
	 *
	 * @param string $buffer_name nome del buffer di output
	 * @param string|array $str stringa o array di stringhe col testo da inserire
	 */
	public function insert_into($buffer_name, $str) {
		if (!array_key_exists($buffer_name, $this->_output_buffers)) $this->_output_buffers[$buffer_name] = new StringArray();
		$this->_output_buffers[$buffer_name]->insert($str);
	}
	
	/**
	 * Sostituisce il testo del buffer di output.
	 *
	 * Se il buffer non esiste viene creato.
	 *
	 * @param string $buffer_name nome del buffer di output
	 * @param string|array $str stringa o array di stringhe col testo da inserire
	 */
	public function update_buffer($buffer_name, $str) {
		if (!array_key_exists($buffer_name, $this->_output_buffers)) $this->_output_buffers[$buffer_name] = new StringArray();
		$this->_output_buffers[$buffer_name]->load($str);
	}

	/**
	 * Ritorna il buffer di output specificato.
	 *
	 * Per operare il buffer principale, utilizzare la costante self::MAIN_OUTPUT_BUFFER
	 *
	 * Non verifica l'esistenza della zona del template.
	 *
	 * @param string $buffer_name nome del buffer
	 * @return StringArray il contenuto del buffer
	 */
	public function getOutputBuffer($buffer_name) {
		return $this->_output_buffers[$buffer_name];
	}

	/**
	 * Ritorna i nomi delle zone di output definite.
	 *
	 * @return array un array di stringhe
	 */
	public function getOutputBuffers() {
		return array_keys($this->_output_buffers);
	}

	/**
	 * Informa se il buffer di output sia stato definito.
	 *
	 * @param string $buffer_name il nome del buffer di cui verificare l'esistenza
	 * @return boolean TRUE se il buffer esiste, FALSE altrimenti
	 */
	public function hasOutputBuffer($buffer_name) {
		return array_key_exists($buffer_name, $this->_output_buffers);

	}

	/**
	 * Ritorna il testo contenuto in un buffer di output.
	 *
	 * Se il buffer non esiste, ritorna una stringa vuota.
	 *
	 * @param string $buffer_name nome del buffer di testo
	 * @return string il testo nel buffer
	 */
	public function getText($buffer_name) {
		return (array_key_exists($buffer_name, $this->_output_buffers) ? $this->_output_buffers[$buffer_name]->toString() : '');
	}

	/**
	 * Lancia l'azione.
	 *
	 * Viene usata da WebApp per eseguire l'azione: ossia lanciare il metodo
	 * indicato come parametro.
	 *
	 * Lancia di solito un metodo chiamato executeXXX dove XXX è il
	 * valore di $module_method.
	 *
	 * @param string $module_method nome del metodo da lanciare
	 * @return boolean FALSE se il metodo non esiste, TRUE se tutto ok
	 */
	public function launch($module_method) {
		$this->_action_return_value = NULL;
		try {
			if (is_callable(array($this, $module_method))) {
				ob_start();
				$this->preRun();
				$this->_action_return_value = call_user_func( array($this, $module_method) );
				$this->postRun();
				$this->write(ob_get_clean());
			} else {
				// non ha trovato il metodo da eseguire, ritorna FALSE
				return FALSE;
			}
		}
		catch(Exception $e) {
			// in caso di eccezione, annulla l'output buffering
			ob_end_clean();
			throw $e;
		}
		return TRUE;
	}

	/**
	 * Fornisce il valore ritornato dall'esecuzione dell'azione.
	 *
	 * @return mixed il valore ritornato dall'azione
	 * @see launch
	 */
	public function getLaunchReturnValue() {
		return $this->_action_return_value;
	}
}


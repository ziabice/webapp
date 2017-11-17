<?php
/**
 * (c) 2008-2012 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Controller per la visualizzazione paginata di dati.
 * 
 * Esegue i compiti di un paginatore di risultati:
 *
 * - estrae e verifica le informazioni di navigazione dalla richiesta HTTP
 * - istruisce il Model in base alle informazioni ed estrae i dati
 * - presenta le informazioni usando il View (una istanza di PagerView)
 * 
 * Come si usa:
 * 
 * $c = new PagerController('pager', 'my/dest');
 * $c->setModel( new Model() );
 * $v->setView( new PagerView() );
 * $c->execute();
 *
 * La gestione del processo di presentazione dei dati è regolato da una
 * macchina a stati finiti, che indica come agire. Gli stati possibili sono:
 * STATUS_PROCEED - Estrae i dati e mostra il pager
 * STATUS_IDLE - Non estrarre i dati, ma mostra il pager in attesa di input
 * STATUS_INPUT_ERROR -Dati in input errati, non estrae i dati e mostra un errore
 *
 * Funzionamento dell'estrazione dei dati:
 * 
 * I dati vengono estratti dal Model associato e salvati all'interno dell'oggetto
 * della proprietà 'model_data', dopo di che sono a disposizione
 * dell'istanza di PagerView associata.
 * Usando i dati dalla richiesta HTTP o quelli forniti direttamente costruisce un oggetto
 * Criteria con cui selezionare i dati dal Model.
 * Si può forzare una preselezione dei dati con {@link setDefaultCriteria}.
 *
 * @author Luca Gambetta <l.gambetta@bluenine.it>
 *
*/
class PagerController {
	const
		// Costanti per lo stato interno
		STATUS_PROCEED = 10, // Estrae i dati e mostra il pager
		STATUS_IDLE = 20, // Non estrarre i dati, ma mostra il pager in attesa di input
		STATUS_INPUT_ERROR = 30; // Dati in input errati, non estrae i dati e mostra un errore
		
	public
		/**
		 * @var HashMap attributi del pager controller
		 */
		$_attributes,
		/**
		 * @var PagerView il view
		 */
		$view,
		/**
		 * @var Model il model
		 */
		$model,
		/**
		 * @var array i dati estratti dal model
		 */
		$model_data;
		
	protected
		/**
		 * @var Criteria criteri con cui verranno selezionati i dati
		 */
		$criteria,
		
		$pager_name,
		$default_page,
		$criteria_updated_by_request, // flag indica se i criteri di selezione sono stati aggiornati dalla richiesta HTTP
		$requested_page, // Pagina richiesta via HTTP
		$pager, // istanza di Pager
		
		$model_error, // Eccezione sollevata dal model nelle varie operazioni
		
		$validator, // InputValidator usato da setPagerFromHTTP

		$status, // Stato interno
		$selected_items, // array con gli ID degli elementi marcati come selezionati
		$pager_destinations, // array con le destinazioni delle varie pagine
		$base_destination; // stringa con la destinazione base

	/**
	 * Inizializza l'oggetto.
	 * 
	 * Pone come pagina visualizzata quella 0.
	 * 
	 * @param string $pager_name stringa col nome della variabile HTTP che contiene la pagina richiesta dall'utente
	 * @param string $destination stringa con la destinazione base delle varie URL generate (nella forma module/action?param=value&param=value)
	 * @param integer $page_size intero con il numero di default di elementi per pagina
	 * @param integer $group_size intero con la dimensione di default dei gruppi di pagine
	*/
	public function __construct($pager_name, $destination = '', $page_size = 15, $group_size = 10) {
		$this->_attributes = new HashMap();
		$this->pager_name = $pager_name;
		$this->default_page = 0;
		$this->clearRequestedPage();
		$this->pager = new Pager($page_size, $group_size);
		$this->model_error = NULL;
		$this->model_data = NULL;
		$this->status = self::STATUS_PROCEED;
		$this->selected_items = array();
		
		$this->criteria_updated_by_request = FALSE;
		$this->setBaseDestination($destination);
		
		
		$this->initializeCriteria();

	}
	
	/**
	 * Inizializza i criteri di selezione.
	 * 
	 * Viene invocata dal costruttore, occorre poi associare un model.
	 */
	public function initializeCriteria() {
		$this->criteria = Criteria::newInstance();
		$this->criteria->setMode(Criteria::MODE_ALL);
	}
	
	/**
	 * Ritorna la dimensione in righe della pagina.
	 * 
	 * @return integer il numero di elementi per pagina
	 * */
	public function getPageSize() {
		return $this->page_size;
	}
	
	/**
	 * Ritorna il numero di pagine per gruppo di pagine.
	 * 
	 * @return integer il numero di elementi
	 * */
	public function getGroupSize() {
		return $this->group_size;
	}

	/**
	 * Ritorna l'hashmap degli attributi.
	 * 
	 * @return HashMap l'hashmap associato
	 */
	public function getAttributes() {
		return $this->_attributes;
	}

	/**
	 * Imposta il Model
	 * @param Model $model istanza di Model
	 * @return Model l'istanza passata come parametro
	*/
	public function setModel(Model $model) {
		$this->model = $model;
		$this->model_error = NULL;
		$this->model_data = NULL;
		$this->model->onBind($this);
		
		$this->criteria->setModel($model);
		
		return $this->model;
	}
	
	/**
	 * Imposta il PagerView
	 * 
	 * @param PagerView $view istanza di PagerView
	 * @return PagerView l'istanza passata come parametro
	*/
	public function setView(PagerView $view) {
		$this->view = $view;
		$this->view->onBind($this);
		return $this->view;
	}
	
	/**
	 * Ritorna l'istanza di Model associata
	 * @return Model istanza di Model
	*/
	public function getModel() {
		return $this->model;
	}
	
	/**
	 * Informa se sia stata associata una istanza di Model
	 * @return boolean TRUE se presente, FALSE altrimenti
	*/
	public function hasModel() {
		return is_object($this->model);
	}
	
	/**
	 * Ritorna l'istanza di PagerView associata
	 * @return PagerView istanza di PagerView
	*/
	public function getView() {
		return $this->view;
	}
	
	/**
	 * Ritorna una istanza del Pager utilizzato internamente.
	 * @return Pager una istanza di Pager
	*/
	public function getPager() {
		return $this->pager;
	}
	
	/**
	 * Ritorna l'istanza di criteria usata per selezionare i dati
	 * 
	 * @return Criteria una istanza di Criteria
	*/
	public function getCriteria() {
		return $this->criteria;
	}
	
	/**
	 * Imposta i criteri di selezione dei dati.
	 * 
	 * @param Criteria $criteria una istanza di Criteria
	*/
	public function setCriteria(Criteria $criteria) {
		$this->criteria = clone $criteria;
		if ($this->hasModel()) $this->criteria->setModel( $this->getModel () );
	}
	
	/**
	 * Imposta lo stato dell'oggetto.
	 *
	 * Viene usata da {@link execute} per le transizioni di stato. I valori
	 * possibili sono:
	 * STATUS_PROCEED - Estrae i dati e mostra il pager
	 * STATUS_IDLE - Non estrarre i dati, ma mostra il pager in attesa di input
	 * STATUS_INPUT_ERROR -Dati in input errati, non estrae i dati e mostra un errore
	 *
	 * @param integer $status intero con lo stato
	*/
	protected function setStatus($status) {
		$this->status = $status;
	}
	
	/**
	 * Informa sullo stato interno
	 * STATUS_PROCEED - Estrae i dati e mostra il pager
	 * STATUS_IDLE - Non estrarre i dati, ma mostra il pager in attesa di input
	 * STATUS_INPUT_ERROR -Dati in input errati, non estrae i dati e mostra un errore
	 * 
	 * @return integer lo stato interno
	*/
	public function getStatus() {
		return $this->status;
	}

	/**
	 * Aggiorna il Model.
	 *
	 * Dopo aver scelto i criteri di selezione con {@link setCriteriaBeforeUpdate},
	 * esegue il conteggio dei record usando {@link getTotalCount} e ricalcola i dati del
	 * pager (usando l'oggetto {@link Pager} interno).
	 *
	 * Se la pagina da visualizzare è corretta prende i dati dal Model usando
	 * {@link updateModelData}.
	 *
	 * Se ci sono errori di accesso al database ritorna FALSE ed imposta l'errore
	 * interno.
	 * 
	 * @return boolean TRUE se tutto è a posto e si può continuare, FALSE se c'è un problema
	 * @see updatePager
	*/
	protected function updateModel() {
		// Impostandoli, automaticamente il Model lega se stesso ai criteri
		// $this->setCriteriaBeforeUpdate();
		$this->model_data = NULL;
		$this->model_error = NULL;
		
		// Legge il totale degli elementi
		$total = $this->getTotalCount();
		if ( $total !== FALSE ) {
			$total = intval($total);
			// Procede, ma solo se ci sono dati da visualizzare
			if ($total > 0) {
				// Informa il pager del totale delle pagine, così
				// da sapere in risposta quali e quanti record estrarre
				$this->updatePager($total);
				// Se non è stata richiesta una pagina via HTTP, usa quella di default
				$p = $this->getPager()->getPager( $this->hasRequestedPage() ? $this->getRequestedPage() : $this->default_page );
				// Solo adesso so se la pagina richiesta non esiste
				// TODO: potrebbe rendere meglio la condizione di errore
				if (array_key_exists('wrongpagetoshow', $p) ) {
					$this->setModelError(new WrongPageException());
					return FALSE;
				} else {
					$this->getCriteria()->setLimit( $p['startrecord'], $p['recordcount'] );
					return $this->updateModelData( $p['startrecord'], $p['recordcount']);
				}
			} else {
				return TRUE;
			}	
		} else {
			// L'errore è stato impostato da getTotalCount
			return FALSE;
		}
	}

	/**
	 * Aggiorna l'oggetto Pager interno dopo che ha estratto il totale di elementi.
	 *
	 * @param integer $total_count numero totale di elementi
	 * @see updateModel
	 * 
	 */
	protected function updatePager($total_count) {
		$this->getPager()->setTotalCount( $total_count );
	}
	
	/**
	 * Ritorna il numero totale di elementi del Model che rispecchiano i criteri di seleziona attuali
	 * 
	 * In caso di errore di accesso al databse imposta l'errore con {@link setModelError}.
	 * 
	 * @return integer un intero col numero di elementi, FALSE se c'è un errore di accesso ai dati
	*/
	protected function getTotalCount() {
		try {
			$total = $this->getModelTotalCount();
			Logger::getInstance()->debug('PagerController::getTotalCount - found '.var_export($total, TRUE).' items.');
		}
		catch (Exception $e) {
			$this->setModelError($e);
			return FALSE;
		}
		return $total;
	}
	
	/**
	 * Esegue l'effettivo conteggio dei dati dal Model
	 * 
	 * Usata da {@link getTotalCount}
	 *
	 * Usa {@link setCriteriaBeforeUpdate} per impostare i criteri di selezione corretti
	 * prima del conteggio.
	 *
	 * @return integer intero col numero di elementi totale
	 * @throws Execption in caso di errore
	*/
	protected function getModelTotalCount() {
		return $this->getModel()->getTotalCount( $this->getCriteria() );
	}
	
	/**
	 * Estrae i dati e li salva internamente.
	 * 
	 * Usata da {@link updateModel}.
	 *
	 * Si occupa di leggere i dati dal Model e salvarli internamente. I dati
	 * vengono effettivamente letti usando {@link readModelData}.
	 * 
	 * La lettura dei dati avviene in base ai criteri di selezione impostati.
	 * 
	 * Vengono letti solo il numero minimo di record necessari alla paginazione.
	 * 
	 * In caso di errore ritorna FALSE e salva l'eccezione usando {@link setModelError}.
	 * 
	 * @return boolean TRUE se i dati sono stati letti correttamente, FALSE se c'è un errore
	 * @see updateModel
	 * @see readModelData
	*/
	protected function updateModelData() {
		$this->model_data = NULL;
		$this->model_error = NULL;
		try {
			$this->readModelData();
		}
		catch (Exception $e) {
			$this->setModelError($e);
			return FALSE;
		}
		return TRUE;
	}
	
	/**
	 * Legge i dati del model usando i criteri di selezione impostati.
	 * 
	 * Usata da {@link updateModelData}.
	 * 
	 * Esegue l'effettiva lettura dei dati del model, salvandoli internamente (usando {@link setModelData}).
	 *
	 * La lettura viene fatta usando {@link Model::read} usando i criteri di selezione ottenuti da {@link getCriteria}.
	 * @throws Execption solleva eccezioni in caso di errore
	*/
	protected function readModelData() {
		$this->model_data = $this->getModel()->read( $this->getCriteria() );
	}
	
	/**
	 * Imposta i dati letti dal model.
	 * @param array $data NULL se non ci sono dati o un array coi dati
	*/
	public function setModelData($data) {
		$this->model_data = $data;
	}
	
	/**
	 * Aggiorna il Controller dalla richiesta HTTP.
	 *
	 * Innanzitutto cerca di convalidare i dati in ingresso usando {@link validateHTTPRequest}.
	 *
	 * Successivamente individua la pagina richiesta usando {@link getPageFromHTTP}, quindi ricava
	 * gli eventuali criteri di selezione dalla richiesta HTTP usando {@link getCriteriaFromHTTP}.
	 * In presenza di valori validi imposta le variabili interne opportune (pagina e criteri di selezione).
	 *
	 * Infine imposta il Pager coi dati dalla richiesta HTTP usando {@link setPagerFromHTTP}.
	 *
	 * Usata da {@link execute}.
	 *
	 * @see validateHTTPRequest
	 * @see updatePageFromHTTPRequest
	 * @see updateCriteriaFromHTTPRequest
	 * @see setPagerFromHTTP
	*/
	protected function updateFromHTTP() {
		// Convalida usando il validatore
		$this->validateHTTPRequest();

		// Aggiorna la pagina corrente dalla richiesta HTTP
		$this->updatePageFromHTTPRequest();

		// Aggiorna i criteri dalla richiesta HTTP
		$this->updateCriteriaFromHTTPRequest();
		
		// Prendi gli altri dati per il pager dalla richiesta HTTP
		$this->setPagerFromHTTP();
	}

	/**
	 * Imposta la pagina pagina da visualizzare prendendola dalla richiesta HTTP.
	 *
	 * Usa il metodo {@link @getPageFromHTTP} per estrarre la pagina.
	 *
	 * @see getPageFromHTTP
	 * @see updateFromHTTP
	 */
	protected function updatePageFromHTTPRequest() {
		$http_page = $this->getPageFromHTTP();

		if ($http_page !== FALSE) {
			$this->setRequestedPage($http_page);
		} else {
			$this->clearRequestedPage();
		}
	}

	/**
	 * Aggiorna i criteri di selezione dei record usando la richiesta HTTP.
	 *
	 * Preleva i criteri usando {@link getCriteriaFromHTTP}, quindi se questi sono validi
	 * li salva internamente usando {@link setRequestedCriteria}. Notifica se i criteri
	 * di selezione provengano o meno dalla rischiesta HTTP usando {@link setRequestUpdatedCriteria}.
	 *
	 * I criteri provenienti dalla richiesta vengono poi utilizzati (eventualmente
	 * manipolati) in un secondo momento.
	 *
	 * @see getCriteriaFromHTTP
	 * @see setRequestUpdateCriteria
	 * @see clearRequestedCriteria
	 *
	 */
	protected function updateCriteriaFromHTTPRequest() {
		$http_criteria = $this->getCriteriaFromHTTP();
		if ( is_object($http_criteria) ) {
			$this->setRequestUpdatedCriteria(TRUE);
			$this->setCriteria($http_criteria);
		} else {
			$this->setRequestUpdatedCriteria(FALSE);
		}
	}
	
	/**
	 * Notifica al View che si sta per visualizzare i dati
	 *
	 * Usata da {@link showData} prima di invocare {@link PagerView::show},
	 * invoca di solito {@link PagerView::controllerIsReady()} per aggiornare
	 * lo stato del View.
	*/
	protected function updateView() {
		$this->view->controllerIsReady();
	}
	
	/**
	 * Mostra il paginatore usando l'istanza di PagerView.
	 *
	 * Mostra i dati paginati o un messaggio di errore usando l'oggetto PagerView
	 * associato. Aggiorna prima lo stato del PagerView con una chiamata a {@link updateView},
	 * quindi mostra le informazioni invocando @link PagerView::show}.
	 *
	 * @throws Exception se non è stato agganciato un oggetto PagerView
	*/
	protected function show() {
		if ( is_object($this->view) ) {
			$this->updateView();
			$this->view->show();
		} else {
			throw new Exception("PagerController::showData: you forgot to bind a PagerView.");
		}
	}
	
	
	/**
	 * Lancia il paginatore.
	 * 
	 * Il metodo principale: esegue effettivamente l'interazione, estraendo i dati
	 * e visualizzandoli. Inoltre interagisce con la richiesta HTTP per mostrare automaticamente
	 * la pagina corretta.
	 * 
	 * Prima di lanciarla occorre aver agganciato un Model ed un PagerView.
	 * 
	 * Dopo aver verificato i dati provenienti dalla richiesta HTTP (usando {@link updateFromHTTP}),
	 * controlla lo stato interno usando {@link getStatus} per capire come procedere.
	 * 
	 * @param boolean $cache_output TRUE non stampa nulla, ma ritorna una stringa con l'output, FALSE stampa direttamente (default)
	 * @return string una stringa con l'output o NULL
	 * @throws Exception di solito quando non sono stati forniti un Model o un PagerView
	*/
	public function execute($cache_output = FALSE) {
		if ($cache_output) ob_start();
		// Estrae e verifica i dati dalla richiesta HTTP
		$this->updateFromHTTP();
		if ($this->status == self::STATUS_PROCEED) {
			if ( $this->hasModel() ) {
				if ($this->updateModel() ) {
					/*
						Se il conteggio del totale dei record ci dà 0,
						updateModel ritorna TRUE, ma noi non dobbiamo eseguire
						tutti i passi.
					*/
					if ($this->hasData()) {
						$this->makePagerDestinations();
					}
				} 
				$this->show();
			} else {
				throw new Exception('No Model binded.');
			}
		} elseif ($this->status == self::STATUS_IDLE) {
			$this->executeIdleStatus();
		} elseif ($this->status == self::STATUS_INPUT_ERROR) {
			$this->executeInputError();
		} else {
			throw new Exception('PagerController::execute: unknow internal status.');
		}
		if ($cache_output) return ob_get_clean();
	}
	
	/**
	 * Mostra il pager in modalità "attesa".
	 *
	 * Invocata da {@link execute} quando lo stato interno è pari a STATUS_IDLE, ciò 
	 * avviene quando non c'è un input via HTTP per il pager, né è stato aggiornato il
	 * Model e quindi non sono disponibili dati.
	 *
	 * Dovrebbe mostrare un pager in uno stato di attesa (e vuoto).
	 *
	 * Utilizza {@link PagerView::showIdle}
	 *
	 * @throws Exception se non è stato agganciato un PagerView
	*/
	protected function executeIdleStatus() {
		if ( is_object($this->view) ) {
			$this->view->setStatus(PagerView::STATUS_IDLE);
			$this->view->show();
		} else {
			throw new Exception("PagerController::executeIdleStatus: you forgot to bind a PagerView.");
		}
	}
	
	/**
	 * Mostra uno stato di errore.
	 * 
	 * Invocata da {@link execute} quando lo stato interno è pari a STATUS_INPUT_ERROR.
	 * Ciò avviene quando i dati forniti al controller dalla richiesta HTTP sono errati
	 * e non permettono di procedere con l'estrazione dei dati del Model e
	 * successiva visualizzazione degli stessi.
	 *
	 * Informa perciò della situazione di errore usando {@link PagerView::showInputError}.
	 * @throws Exception se non è stato agganciato un PagerView
	*/
	protected function executeInputError() {
		if ( is_object($this->view) ) {
			$this->view->setStatus(PagerView::STATUS_INPUT_ERROR);
			$this->view->show();
		} else {
			throw new Exception("PagerController::executeInputError: you forgot to bind a PagerView.");
		}
	}
	
	/**
	 * Estra la pagina da visualizzare dalla richiesta HTTP.
	 *
	 * @return integer un intero con la pagina richiesta o FALSE se non può utilizzare le informazioni
	*/
	protected function getPageFromHTTP() {
		// Se non è stata richiesta nessuna pagina, va di default la pagina 0
		$filter = new UnsignedIntegerFilter($this->pager_name, TRUE);
		if ($filter->isValid()) {
			return $filter->getValue();
		} else {
			return FALSE;
		}
	}
	
	/**
	 * Estrae ed imposta le informazioni relative alla creazione del Pager dalla richiesta HTTP.
	 *
	 * Viene invocata da {@link updateFromHTTP} al termine di tutte le operazioni.
	 * 
	 * Attualmente non fa nulla, ma fornisce un modo per impostare ad esempio il
	 * numero di elementi da visualizzare.
	 * Le informazioni estratte e validate vanno immesse nell'oggetto Pager interno (ottenuto con {@link getPager}).
	 *
	 * @see updateFromHTTP
	*/
	protected function setPagerFromHTTP() {
		
	}

	/**
	 * Convalida la richiesta HTTP usando un validatore.
	 *
	 * Usa l'istanza di InputValidator agganciata usando {@link setValidator} per convalidare
	 * la richiesta HTTP ed eseguire delle operazioni a seconda dell'esito.
	 *
	 * Se l'esito è positivo invoca {@link onValidHTTPRequest} altrimenti {@link onInvalidHTTPRequest}.
	 *
	 * E' il primo metodo eseguito da {@link updateFromHTTP}.
	 *
	 * @see updateFromHTTP
	 * @see setValidator
	 * @see onValidHTTPRequest
	 * @see onInvalidHTTPRequest
	 */
	protected function validateHTTPRequest() {
		if (is_object($this->validator)) {
			$this->validator->validate(TRUE);
			if ($this->validator->isValid()) {
				$this->onValidHTTPRequest();
			} else {
				$this->onInvalidHTTPRequest();
			}
		}
	}

	/**
	 * Eseguita da {@link validateHTTPRequest} quando la validazione va a buon fine.
	 * 
	 * @see validateHTTPRequest
	 */
	protected function onValidHTTPRequest() {

	}

	/**
	 * Eseguita da {@link validateHTTPRequest} quando la validazione fallisce.
	 *
	 * Di default imposta uno stato di errore STATUS_INPUT_ERROR.
	 * 
	 * @see validateHTTPRequest
	 */
	protected function onInvalidHTTPRequest() {
		$this->setStatus(self::STATUS_INPUT_ERROR);
	}
	
	/**
	 * Estrae i criteri di selezione dalla richiesta HTTP.
	 * 
	 * I criteri di selezione sono una serie di informazioni aggiuntive
	 * necessarie al Model per estrarre i dati.
	 * 
	 * Usata da {@link updateFromHTTP()}
	 * 
	 * @return Criteria una istanza di Criteria o NULL se non ci sono informazioni
	 *
	*/
	protected function getCriteriaFromHTTP() {
		return NULL;
	}
	
	/**
	 * Imposta la pagina richiesta dall'utente
	 * 
	 * @param integer $page intero senza segno con la pagina da visualizzare (a partire da 0)
	*/
	protected function setRequestedPage($page) {
		$this->requested_page = intval($page);
	}
	
	/**
	 * Ritorna la pagina correntemente richiesta (via HTTP)
	 * @return integer un intero con la pagina (dove 0 è la prima pagina)
	*/
	public function getRequestedPage() {
		return $this->requested_page;
	}
	
	/**
	 * Informa se ci sia una pagina da visualizzare richiesta via HTTP
	 *
	 * @return boolean TRUE se c'è la pagina, FALSE altrimenti
	*/
	public function hasRequestedPage() {
		return !is_null($this->requested_page);
	}
	
	/**
	 * Rimuove qualsiasi indicazione interna relativa alla pagina richiesta via HTTP
	 *
	 */
	protected function clearRequestedPage() {
		$this->requested_page = NULL;
	}

	
	/**
	 * Informa se sia presente una condizione di errore che interessa il Model
	 * @return boolean TRUE se c'è un errore, FALSE se non c'è
	*/
	public function hasModelError() {
		return !is_null($this->model_error);
	}
	
	/**
	 * Ritorna la condizione di errore relativa al Model
	 * 
	 * Nel caso di pagina richiesta non valida ritorna un'eccezione di tipo {@link WrongPageException},
	 * altrimenti di solito una eccezione di tipo {@link DBException}
	 *
	 * @return Exception l'eccezione relativa all'errore
	*/
	public function getModelError() {
		return $this->model_error;
	}
	
	/**
	 * Imposta la condizione di errore relativa al Model
	 * @param Exception $error una istanza di Exception
	*/
	protected function setModelError($error) {
		$this->model_error = $error;
	}
	
	/**
	 * Informa se questo Controller ha dati estratti dal Model
	 * 
	 * Si può accedere direttamente ai dati dalla proprietà $this->model_data
	 * @return boolean TRUE se ha dati, FALSE altrimenti
	*/
	public function hasData() {
		return !empty( $this->model_data );
	}
	
	/**
	 * Informa se i criteri di selezione siano stati aggiornati usando
	 * i dati dalla richiesta HTTP
	 * @return boolean TRUE se i criteri sono stati aggiornati, FALSE altrimenti
	 * */
	public function requestUpdatedCriteria() {
		return $this->criteria_updated_by_request;
	}

	/**
	 * Informa se i criteri di selezione siano stati aggiornati usando i dati dalla
	 * richiesta HTTP.
	 *
	 * @param boolean $is_updated TRUE se sono stati aggiornati dalla richiesta HTTP, FALSE altrimenti
	 * @see getCriteriaFromHTTP
	 *
	 */
	public function setRequestUpdatedCriteria($is_updated) {
		$this->criteria_updated_by_request = $is_updated;
	}
	
	/**
	 * Usa i dati del Pager corrente per creare le destinazioni delle varie pagine.
	 *
	 * Viene usata di solito da {@link execute}.
	 *
	 * E' possibile accedere ai dati così creati usando {@link getPagerDestinations}
	*/
	protected function makePagerDestinations() {
		$this->pager_destinations = $this->pager->getLastPager();
		
		$this->pager_destinations['prev_dest'] = (is_null($this->pager_destinations['prev']) ? '' : $this->getPageDestination($this->pager_destinations['prev']));
		$this->pager_destinations['next_dest'] = (is_null($this->pager_destinations['next']) ? '' : $this->getPageDestination($this->pager_destinations['next']));
		$this->pager_destinations['first_dest'] = $this->getPageDestination($this->pager_destinations['first']);
		$this->pager_destinations['last_dest'] = $this->getPageDestination($this->pager_destinations['last']);
		$this->pager_destinations['current_dest'] = $this->getPageDestination($this->pager_destinations['current']);
		
		foreach($this->pager_destinations['pages'] as $k => $v) {
			$this->pager_destinations['pages'][$k]['url_dest'] = $this->getPageDestination($v['url']);
		}
		
		foreach($this->pager_destinations['groups'] as $k => $v) {
			$this->pager_destinations['groups'][$k]['url_dest'] = $this->getPageDestination($v['url']);
		}
	}
	
	/**
	 * Data una pagina nella notazione delle URL generata da un oggetto Pager,
	 * ritorna una stringa da utilizzare in una destinazione di Router per accedere
	 * alla pagina stessa.
	 * 
	 * Nella destinazione il nome della pagina viene messo nella variabile ritornata 
	 * da {@link getName}. Eventualmente devono essere aggiunti anche i criteri
	 * attuali di selezione.
	 *
	 * @param integer $page intero con la pagina (a partire da 0)
	 * @return string una stringa con una parte della destinazione
	*/
	public function getPageDestination($page) {
		return $this->base_destination.$this->pager_name.'='.strval($page);
	}

	/**
	 * Ritorna la stringa di destinazine base utilzzata per creare le destinazioni della navigazione.
	 *
	 * Ritorna la destinazione specificata nel costruttore, aggiungendo se necessario
	 * un carattere '?' o '&' alla fine, in modo che la stringa ritornata possa essere
	 * direttamente usata per agganciarvi parametri aggiuntivi.
	 *
	 * @return string la destinazione base
	 */
	public function getBaseDestination() {
		return $this->base_destination;
	}

	/**
	 * Ritorna la stringa di destinazine base utilzzata per creare le destinazioni della navigazione.
	 *
	 * Aggiunge se necessario un carattere '?' o '&' alla fine, in modo che la stringa
	 * possa essere direttamente usata per agganciarvi parametri aggiuntivi.
	 *
	 * @param string $destination la destinazione base
	 */
	public function setBaseDestination($destination) {
		$this->base_destination = $destination;
		// Verifica se la destinazione contenga già dei parametri
		// se non li contiene aggiunge i caratteri che servono
		if (strpos($this->base_destination, '?') === FALSE) {
			// Non ha il punto interrogativo finale, presume che la stringa
			// contenga solo l'indicazione di model/action
			$this->base_destination .= '?';
		} elseif (strrpos($this->base_destination, '=') !== FALSE) { // Se ha dei parametri accoda un &
			$this->base_destination .= '&';
		}
	}
	
	
	/**
	 * Ritorna le destinazioni per generare le varie URL relative al Pager
	 * 
	 * Utilizza i dati generati da un oggetto {@link Pager}, l'array ritornato 
	 * corrisponde a quello ritornato da {@link Pager::getPager}.
	 * 
	 * Utilizzarla dopo aver invocato {@link makePagerDestinations}
	 * 
	 * @return array una array con le varie stringhe di destinazione
	*/
	public function getPagerDestinations() {
		return $this->pager_destinations;
	}
	
	/**
	 * Ritorna la destinazione relativa alla pagina corrente.
	 *
	 * @return string una stringa con la destinazione
	*/
	public function getCurrentPageDestination() {
		return $this->pager_destinations['current_dest'];
	}
	
	/**
	 * "Marca" alcuni elementi come selezionati
	 *
	 * E' possibile marcare gli ID di certi elementi estratti dal Model come
	 * "selezionati": sarà poi il PagerView a visualizzarli in qualche modo
	 * particolare.
	 *
	 * @param mixed $id di solito un intero o array di interi con l'id degli elementi selezionati
	*/
	public function selectItems($id) {
		if (!is_array($id)) $this->selected_items = array( $id );
		else $this->selected_items = $id;
	}
	
	/**
	 * Informa se un elemento (l'id dell'elemento) è tra quelli marcati
	 * come "selezionati".
	 * 
	 * @param mixed $id di solito un intero con l'id dell'elemento
	 * @return boolean TRUE se l'elemento è marcato, FALSE altrimenti
	*/
	public function isSelected($id) {
		return in_array($id, $this->selected_items);
	}
	
	/**
	 * Ritorna l'array con gli ID degli elementi selezionati
	 * @return array gli id marcati come selezionati
	*/
	public function getSelectedItems() {
		return $this->selected_items;
	}
	
	/**
	 * Rimuove tutti gli elementi selezioanti
	*/
	public function clearSelectedItems() {
		$this->selected_items = array();
	}

	/**
	 * Informa se l'errore attuale è causato da una pagina non valida
	 * @return boolean TRUE se la pagina richiesta non è valida, FALSE altrimenti
	 */
	public function isWrongPageError() {
		return ($this->model_error instanceof WrongPageException);

	}

	/**
	 * Ritorna il nome del pager
	 *
	 * Ritorna il nome della variabile nella richiesta HTTP che contiene la pagina da visualizzare.
	 *
	 * @return string Il nome della variabile
	 */
	public function getName() {
		return $this->pager_name;
	}

	/**
	 * Imposta il nome del pager
	 *
	 * Imposta il nome della variabile nella richiesta HTTP che contiene la pagina da visualizzare.
	 *
	 * @param string $pager_name Il nome della variabile
	 */
	public function setName($pager_name) {
		$this->pager_name = strval($pager_name);
	}

	/**
	 * Aggancia un InputValidator con cui validare la richiesta.
	 *
	 * @param InputValidator $validator
	 * @return InputValidator passato come parametro
	 * @see setPagerFromHTTP
	 */
	public function setValidator(InputValidator $validator) {
		$this->validator = $validator;
		return $this->validator;
	}

	/**
	 * Ritorna l'eventuale InputValidator agganciato
	 *
	 * @return InputValidator
	 * @see setValidator
	 */
	public function getValidator() {
		return $this->validator;
	}
}


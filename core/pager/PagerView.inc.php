<?php
/**
 * (c) 2008-2012 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Presenta i dati estratti da un oggetto Model sotto la supervisione di un oggetto PagerController.
 *
 * Mostra i dati paginati, usando il gestore di CSS, secondo il seguente schema
 *
 * <kbd>
 * +----------------------------+
 * + link navigazione superiore + <- header
 * +----------------------------+
 * + dati                       + <- body
 * +----------------------------+
 * + link navigazione inferiore + <- footer
 * +----------------------------+
 * </kbd>
 *
 * Tutto il pager disegnato è contenuto in un elemento block level, di solito un elemento
 * DIV.
 *
 * La visualizzazione dei dati avviene in base allo stato del PagerController
 * che contiene questo PagerView.
 *
 * E' possibile associare un oggetto Form (usando {@link setForm}) per presentare le informazioni: in questo
 * caso il testo generato viene posto come corpo del FormView ad esso associato.
 * Questo modo di utilizzo è utle quando si mostra un selettore di elementi con una checkbox.
 * */
class PagerView {
	const
		// Stato interno dell'oggetto
		STATUS_IDLE = 0, // Dormiente
		STATUS_MODEL_ERROR = 10, // Il Model associato al PagerController è in uno stato di errore
		STATUS_SHOW_MODEL_DATA = 20, // Mostra i dati del Model
		STATUS_NO_MODEL_DATA = 30, // Il Model non ha dati da visualizzare
		STATUS_WRONG_PAGE = 40, // si sta cercando di visualizzare una pagina non corretta
		STATUS_INPUT_ERROR = 42, // i dati della richiesta HTTP non sono validi
		STATUS_POST_PROCESS_ERROR = 50; // I dati ci sono, ma si è verificato un errore prima di poterli utilizzare
	
	const
		// Usate per indicare se la riga corrente sia pari o dispari
		ROW_IS_ODD = 1,
		ROW_IS_EVEN = 2;

	const
		// Bit a bit: visualizzazione delle ancore di navigazione
		DONTSHOW_NAV_ANCHORS = 0, // nessuno
		SHOW_TOP_NAV_ANCHORS = 1, // Mostra le ancore di navigazione superiori
		SHOW_BOTTOM_NAV_ANCHORS = 2, // Mostra le ancore di navigazione inferiori
		SHOW_BOTH_NAV_ANCHORS = 3; // Mostra tutte le ancore di navigazione

	protected
		/**
		 * @var PagerViewStyle delegate per il disegno
		 */
		$_style,
		$form, // Form da utilizzare per mostrare i dati (quando presenti)
		$clear_form_body = TRUE, // flag indica se deve cancellare il body della form quando disegna
		$selector_name, // stringa col nome del selettore degli elementi
		$with_selector, // flag indica se ha o meno un selettore (checkbox) per gli elementi
		$status, // Stato corrente dell'oggetto
		$row_odd_even, // Indica se la riga corrente di dati sia pari o dispari
		/**
		 * @var PagerController il controller associato
		 */
			$controller,
		$last_row_index, // Indice numerico relativo dell'ultima riga visualizzata
		$current_row,
		$global_row_count, // Indice della riga corrente in relazione al pager
		$relative_row_count, // Indice della riga corrente in relazione all'inizio della visualizzazione
		$show_nav_anchors; // Quali ancore di navigazione mostrare

	/**
	 * Inizializza il View
	 *
	 * Permette di disegnare un selettore di elementi, che di solito è una checkbox
	 * che ha per valore l'ID degli elementi estratti dal Model.
	 *
	 * Permette di associare un gestore di stili CSS, ma utilizza {@link setDefaultCSS}
	 * per inizializzarne uno di default prendendolo dal PageLayout.
	 *
	 * Il modo di visualizzazione delle ancore di navigazione tra le pagine è definito dalle costanti (bit a bit):
	 *
	 * DONTSHOW_NAV_ANCHORS - nessuna ancora per la navigazione 
	 * SHOW_TOP_NAV_ANCHORS - Mostra le ancore di navigazione superiori
	 * SHOW_BOTTOM_NAV_ANCHORS - Mostra le ancore di navigazione inferiori
	 * SHOW_BOTH_NAV_ANCHORS - Mostra tutte le ancore di navigazione
	 *
	 * @param boolean $with_selector TRUE mostra un selettore di elementi, FALSE non mostrare niente
	 * @param string $selector_name nome dell'eventuale selettore, ossia il nome della variabile HTTP
	 * @param integer $show_navigation_anchors modo di visualizzazione delle ancore di navigazione
	 * @see setDefaultCSS
	*/
	public function __construct($with_selector = FALSE, $selector_name = '', $show_navigation_anchors = self::SHOW_BOTH_NAV_ANCHORS) {
		$this->setShowNavigationLinks($show_navigation_anchors);
		$this->setStatus(self::STATUS_IDLE);
		$this->setSelector($with_selector);
		$this->setSelectorName($selector_name);
		$this->setDefaultStyle();
		$this->rewind();
	}
	
	/**
	 * Imposta lo stile di disegno.
	 * 
	 * Lo stile viene ricavato (se presente) dall'istanza corrente di PageLayout
	 */
	public function setDefaultStyle() {
		if (WebApp::getInstance()->hasPageLayout()) {
			$style = WebApp::getInstance()->getPageLayout()->getWidgetStyle()->getPagerViewStyle($this);
		} else {
			$style = new PagerViewStyle();
		}
		$this->setStyle($style);
	}
	
	/**
	 * Imposta lo stile di disegno di questo paginatore.
	 * 
	 * @param PagerViewStyle $style lo stile di disegno
	 * @return PagerViewStyle
	 */
	public function setStyle($style) {
		$this->_style = $style;
		$this->_style->setView($this);
		return $style;
	}
	
	/**
	 * Ritorna lo stile di disegno di questo paginatore
	 * 
	 * @return PagerViewStyle lo stile di questo paginatore
	 */
	public function getStyle() {
		return $this->_style;
	}
	
	/**
	 * Eseguita quando viene agganciato un PagerController
	 *
	 * @param PagerController $controller
	 */
	public function onBind(PagerController $controller) {
		$this->controller = $controller;
	}

	/**
	 * Ritorna il Model associato al PagerController
	 *
	 * @return Model
	 */
	protected function getModel() {
		return $this->controller->getModel();
	}

	/**
	 * Imposta lo stato interno.
	 * 
	 * Gli stati vengono utilizzati da {@link show} per decidere cosa fare.
	 *
	 * Gli stati base riconosciuti sono:
	 *
	 * STATUS_IDLE - Il PagerView è dormiente
	 * STATUS_MODEL_ERROR - Il Model associato al PagerController è in uno stato di errore
	 * STATUS_SHOW_MODEL_DATA - Mostra i dati del Model
	 * STATUS_NO_MODEL_DATA - Il Model non ha dati da visualizzare
	 * STATUS_WRONG_PAGE - si sta cercando di visualizzare una pagina non corretta
	 * STATUS_POST_PROCESS_ERROR - I dati ci sono, ma si è verificato un errore prima di poterli utilizzare
	 * STATUS_INPUT_ERROR - i dati della richiesta HTTP non sono validi
	 *
	 * @param integer $status una delle costanti STATUS_*
	 * @see show
	 */
	public function setStatus($status) {
		$this->status = $status;
	}

	/**
	 * Ritorna lo stato corrente dell'oggetto
	 *
	 * @return integer con lo stato, pari ad una delle costanti STATUS_*
	 */
	public function getStatus() {
		return $this->status;
	}

	/**
	 * Mostra i dati del Model
	 *
	 * E' il metodo che si occupa di mostrare i dati, decidendo l'operazione da compiere
	 * in base allo stato interno dell'oggetto.
	 * Per poter funzionare ha bisogno che questo oggetto sia stato legato
	 * ad una istanza di PegerController.
	 *
	 * A seconda dello stato invoca:
	 * STATUS_IDLE - {@link showIdle()} - Il PagerView è dormiente
	 * STATUS_MODEL_ERROR - {@link showModelError} - Il Model associato al PagerController è in uno stato di errore
	 * STATUS_SHOW_MODEL_DATA - {@link showData} -  Mostra i dati del Model
	 * STATUS_NO_MODEL_DATA -  {@link showNoData} - Il Model non ha dati da visualizzare
	 * STATUS_WRONG_PAGE -  {@link showWrongPageError} - si sta cercando di visualizzare una pagina non corretta
	 * STATUS_POST_PROCESS_ERROR -  {@link showPostProcessError} - I dati ci sono, ma si è verificato un errore prima di poterli utilizzare
	 * STATUS_INPUT_ERROR - {@link showInputError()} - i dati della richiesta HTTP non sono validi
	 *
	 * Se lo stato è sconosciuto usa {@link showCurrentStatus}.
	 * 
	 * @throws Exception se non può procedere
	*/
	public function show() {
		if (!is_object($this->controller)) {
			throw new Exception("PagerView::show: no PagerController binded.");
		}
		
		$this->preShow();
		
		switch($this->getStatus()) {
			case self::STATUS_IDLE: $this->showIdle(); break;
			case self::STATUS_MODEL_ERROR: $this->showModelError(); break;
			case self::STATUS_SHOW_MODEL_DATA: $this->showData(); break;
			case self::STATUS_NO_MODEL_DATA: $this->showNoData(); break;
			case self::STATUS_POST_PROCESS_ERROR: $this->showPostProcessError(); break;
			case self::STATUS_WRONG_PAGE: $this->showWrongPageError(); break;
			case self::STATUS_INPUT_ERROR: $this->showInputError(); break;
			default:
				$this->showCurrentStatus();
		}
		
		$this->postShow();
	}
	
	/**
	 * Invocata prima di visualizzare il pager.
	 * 
	 * Viene invocata da {@link show} prima del disegno del pager.
	 * Verificare lo stato del pager usando {@link getStatus}.
	 * */
	protected function preShow() {
		$this->_style->preShow();
	}
	
	/**
	 * Invocata dopo aver visualizzato il pager.
	 * 
	 * Viene invocata da {@link show} dopo aver disegnato il pager.
	 * Verificare lo stato del pager usando {@link getStatus}.
	 * */
	protected function postShow() {
		$this->_style->postShow();
	}

	/**
	 * Stampa un messaggio di errore quando lo stato interno è STATUS_POST_PROCESS_ERROR
	 *
	 */
	public function showPostProcessError() {
		$this->_style->showPostProcessError();
	}

	/**
	 * Mostra lo stato corrente quando non corrisponda ad uno per cui esista
	 * già un metodo che lo gestisca.
	 *
	 */
	protected function showCurrentStatus() {
	}

	/**
	 * Mostra i dati del Model salvati nel PagerController associato.
	 * 
	 * Nel caso ci sia un modulo Form associato, pone il testo
	 * nel suo corpo e visualizza il tutto usando il modulo.
	 *
	 * Per disegnare il pager utilizza {@link showPager}.
	 *
	 **/
	protected function showData() {
		if (is_object($this->form)) {
			$this->showPagerInForm();
		} else {
			$this->showPager();
		}
	}

	/**
	 * Imposta una form con cui disegnare i dati.
	 *
	 * E' fondamentale che nella form sia presente un oggetto BaseFormView.
	 * @param Form $form la form con cui disegnare
	 * @return Form la form passata come parametro
	 */
	public function setForm(Form $form) {
		$this->form = $form;
		return $form;
	}

	/**
	 * Ritorna la form associata.
	 *
	 * @return Form la form associata o NULL
	 * @see setForm
	 */
	public function getForm() {
		return $this->form;
	}

	/**
	 * Rimuove l'associazione con una form (una istanza di {@link Form}) per il disegno dei dati.
	 */
	public function unsetForm() {
		$this->form = NULL;
	}

	/**
	 * Imposta il flag che indica se bisogna cancellare il body della FormView associata
	 * alla Form.
	 *
	 * Se il flag è impostato a FALSE i contenuti del pager vengono posti in coda al modulo, senza
	 * cancellare quelli precedenti.
	 *
	 * @param boolean $must_clear TRUE deve cancellare, FALSE altrmenti
	 * @see showPagerInForm
	 */
	public function setClearFormBody($must_clear) {
		$this->clear_form_body = $must_clear;
	}

	/**
	 * Informa se debba cancellare il corpo del modulo associato.
	 *
	 * @return boolean TRUE il body della form verrà cancellato, FALSE altrimenti
	 * @see showPagerInForm
	 */
	public function mustClearFormBody() {
		return $this->clear_form_body;
	}

	/**
	 * Effettua il disegno del pager quando ci sono dei dati.
	 *
	 * Prima viene mostrata una testata, poi il corpo e quindi un footer.
	 * Il corpo di solito disegna i dati.
	 * @see showHeader
	 * @see showBody
	 * @see showFooter
	 */
	protected function showPager() {
		$this->_style->showPager();
	}

	/**
	 * Mostra il pager in una form.
	 *
	 * Viene utilizzata da showData per mostrare il pager in una form (impostata
	 * con {@link setForm}).
	 *
	 * Di solito il corpo della FormView viene cancellato ed il suo contenuto sovrascritto,
	 * ma questo comportamento può essere cambiato usanto {@link setClearFormBody}.
	 * @see setClearFormBody
	 */
	protected function showPagerInForm() {
		ob_start();
		$this->showPager();
		$out = ob_get_clean();

		if ($this->mustClearFormBody()) $this->getForm()->getView()->clearBody();
		$this->getForm()->getView()->add($out);
		$this->getForm()->show();
	}

	/**
	 * Informa che non ci sono dati del Model da poter visualizzare.
	 * 
	 * Invocata quando non ci sono dati da stampare, ma non ci sono errori
	 * nella selezione degli stessi.
	 **/
	public function showNoData() {
		$this->_style->showNoData();
	}

	/**
	 * Mostra un messaggio di errore del Model.
	 *
	 * Invocata quando il Model ha generato un errore nell'estrazione dei dati
	 **/
	public function showModelError() {
		$this->_style->showModelError();
	}

	/**
	 * Mostra un messaggio di errore quando l'utnte sta cercando di visualizzare
	 * una pagina inesistente o non corretta.
	 */
	public function showWrongPageError() {
		$this->_style->showWrongPageError();
	}

	/**
	 * Informa l'utente della presenza di errori nella richiesta HTTP che impediscono di procedere on la selezione dei dati.
	 **/
	public function showInputError() {
		$this->_style->showInputError();
	}

	/**
	 * Il PagerController non può avviare la visualizzazione: mostra il pager in uno stato
	 * di attesa di input da parte dell'utente.
	 *	*/
	public function showIdle() {
		$this->_style->showIdle();
	}

	/**
	 * Informa se deve essere presentato un selettore (di solito una checkbox
	 * all'utente.
	 * @return boolean TRUE presenta selettore, FALSE no
	 * */
	public function hasSelector() {
		return $this->with_selector;
	}

	/**
	 * Imposta la visualizzazione del selettore degli elementi
	 * @param boolean $with_selector TRUE se si deve visualizzare un selettore, FALSE altrimenti
	 */
	public function setSelector($with_selector) {
		$this->with_selector = (bool)$with_selector;
	}
	
	/**
	 * Imposta il nome della variabile HTTP per il selettore
	 *
	 * @param string $name nome del selettore
	 */
	public function setSelectorName($name) {
		$this->selector_name = strval($name);
	}
	
	/**
	 * Ritorna il nome del selettore degli elementi 
	 *
	 * @return string stringa col nome 
	 */
	public function getSelectorName() {
		return $this->selector_name;
	} 

	/**
	 * Scorre lungo tutti i dati, applicando eventualmente la callback.
	 * Durante lo scorrimento aggiorna i puntatori interni relativi a riga corrente,
	 * indice delle righe e così via.
	 * 
	 * La callback è nella forma:
	 * 
	 * callback($row)
	 * 
	 * Dove $row indica la riga di dati corrente.
	 *
	 * @param callback $callback una callback
	 */
	public function walkRows($callback = NULL) {
		$this->relative_row_count = 0;
		$p = $this->controller->getPager()->getLastPager();
		$this->last_row_index = count($this->controller->model_data) - 1;
		$this->global_row_count = $p['startrecord'];
		$even_odd = FALSE;
		foreach($this->controller->model_data as $this->current_row) {
			$this->row_odd_even = $even_odd ? self::ROW_IS_EVEN : self::ROW_IS_ODD;
			call_user_func($callback, $this->current_row);
			$this->global_row_count++;
			$this->relative_row_count++;
			$even_odd = !$even_odd;
		}
	}

	/**
	 * Ritorna l'indice relativo dell'ultima riga visualizzata
	 * 
	 * Valida solo all'interno di un ciclo di {@link walkRows}
	 *
	 * @return integer a partire da 0
	 */
	public function getLastRowIndex() {
		return $this->last_row_index;
	}

	/**
	 * Ritorna l'indice numerico della riga corrente, relativo all'inizio
	 * della visualizazzione
	 * 
	 * Valida solo all'interno di un ciclo di {@link walkRows}
	 *
	 * @return integer con l'indice (a partire da 0)
	 */
	public function getIndex() {
		return $this->relative_row_count;
	}

	/**
	 * Ritorna l'indice numerico della riga corrente, rispetto a tutti i record
	 * della paginazione
	 * 
	 * Valida solo all'interno di un ciclo di {@link walkRows}
	 *
	 * @return integer con l'indice (a partire da 0)
	 */
	public function getGlobalIndex() {
		return $this->global_row_count;
	}

	/**
	 * Ritorna la riga corrente di dati
	 * 
	 * Valida solo all'interno di un ciclo di {@link walkRows}
	 * 
	 * @return mixed la riga di dati corrente (di solito un oggetto derivato da WebAppObject)
	 */
	public function getCurrentRow() {
		return $this->current_row;
	}

	/**
	 * Informa se la riga corrente sia pari o dispari
	 * 
	 * Valida solo all'interno di un ciclo di {@link walkRows}
	 * 
	 * Ritorna:
	 * ROW_IS_ODD - se la riga è dispari
	 * ROW_IS_EVEN - se la riga è pari
	 * 
	 * @return integer 
	 */
	public function currentRowOddEven() {
		return $this->row_odd_even;
	}

	/**
	 * Informa se la riga correntemente in visualizzazione sia dispari
	 * 
	 * Valida solo all'interno di un ciclo di {@link walkRows}
	 * 
	 * @return boolean TRUE se la riga è dispari, FALSE altrimenti
	 */
	public function isOdd() {
		return $this->row_odd_even == self::ROW_IS_ODD;
	}

	/**
	 * Informa se la riga correntemente in visualizzazione sia pari
	 * 
	 * Valida solo all'interno di un ciclo di {@link walkRows}
	 * 
	 * @return boolean TRUE se la riga è pari, FALSE altrimenti
	 */
	public function isEven() {
		return $this->row_odd_even == self::ROW_IS_EVEN;
	}

	/**
	 * Azzera i contatori interni per una nuova stampa del pager
	 *
	 */
	protected function rewind() {
		$this->row_odd_even = self::ROW_IS_ODD;
		$this->current_row = NULL;
		$this->relative_row_count = 0;
	}

	/**
	 * Usata da PagerController per notificare l'inizio della visualizzazione
	 * dei dati (o dello stato della visualizzazione paginata).
	 *
	 * Invoca da PagerController quando i dati del Model sono disponibili e
	 * pronti per essere elaborati.
	 * Viene invocata dentro \ref execute(), subito prima di PagerController::showData(),
	 * che di solito invoca show()
	 * Imposta lo stato interno, che influenzerà la successiva chiamata a show().
	 *
	 */
	public function controllerIsReady() {
		if ( $this->controller->hasModelError() ) {
			if($this->controller->isWrongPageError()) $this->setStatus(self::STATUS_WRONG_PAGE);
			else $this->setStatus(self::STATUS_MODEL_ERROR);
		} else {
			if ( $this->controller->hasData() ) {
				$this->setStatus(self::STATUS_SHOW_MODEL_DATA);
			} else {
				$this->setStatus(self::STATUS_NO_MODEL_DATA);
			}
		}
	}

	/**
	 * Informa se quella corrente sia l'ultima riga di dati.
	 * Valida solo all'interno di un ciclo di {@link walkRows}
	 *
	 * @return boolean TRUE la riga corrente è l'ultima, FALSE altrimenti
	 */
	public function isLastRow() {
		return ($this->relative_row_count == $this->last_row_index);
	}
	
	/**
	 * Informa se quella corrente sia la prima riga di dati.
	 * Valida solo all'interno di un ciclo di {@link walkRows}
	 *
	 * @return boolean TRUE la riga corrente è la prima, FALSE altrimenti
	 */
	public function isFirstRow() {
		return ($this->relative_row_count == 0);
	}
		
	/**
	 * Ritorna il valore da utilizzare nel selettore per la riga.
	 * 
	 * Di default prende l'ID dell'oggetto.
	 * 
	 * @param mixed $row una riga di dati
	 * @return string una stringa col valore
	*/
	public function getRowSelectorValue($row) {
		if (is_object($row) && ($row instanceof WebAppObject)) return $row->getID();
		return '';
	}
	
	/**
	 * Ritorna l'istanza di PagerController (ossia il Controller) da 
	 * cui l'oggetto è controllato.
	 * @return PagerController istanza di PagerController
	*/
	public function getController() {
		return $this->controller;
	}

	/**
	 * Imposta quali link di navigazione siano visibili.
	 *
	 * E' un campo bit a bit.
	 *
	 * I valori possibili sono:
	 * DONTSHOW_NAV_ANCHORS - Non mostrare nessuna ancora
	 * SHOW_TOP_NAV_ANCHORS - Mostra le ancore di navigazione superiori
	 * SHOW_BOTTOM_NAV_ANCHORS - Mostra le ancore di navigazione inferiori
	 * SHOW_BOTH_NAV_ANCHORS - Mostra tutte le ancore di navigazione
	 *
	 * @param integer $show_anchors
	 */
	public function setShowNavigationLinks($show_anchors) {
		$this->show_nav_anchors = $show_anchors;
	}

	/**
	 * Ritorna quali link di navigazione siano visibili.
	 *
	 * E' un campo bit a bit.
	 * 
	 * I valori possibili sono:
	 * DONTSHOW_NAV_ANCHORS - Non mostrare nessuna ancora
	 * SHOW_TOP_NAV_ANCHORS - Mostra le ancore di navigazione superiori
	 * SHOW_BOTTOM_NAV_ANCHORS - Mostra le ancore di navigazione inferiori
	 * SHOW_BOTH_NAV_ANCHORS - Mostra tutte le ancore di navigazione
	 *
	 * @return integer stato di visualizzazione
	 */
	public function getShowNavigationLinks() {
		return $this->show_nav_anchors;
	}
	
}

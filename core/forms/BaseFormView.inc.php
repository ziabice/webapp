<?php
/**
 * (c) 2008-2013 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Classe base per realizzare una View di un oggetto {@link Form}.
 * 
 * La form non viene visualizzata mai direttamente, ma attraverso il
 * metodo {@link Form::show}. Per ottenere un modulo completo è perciò necessario
 * legare una istanza di {@link BaseFormView} a {@link Form} usando il
 * metodo {@link Form::setView}.
 * 
 * Il codice XHTML viene mantenuto in un oggetto {@link StringArray} e può essere aggiunto con
 * chiamate a {@link BaseFormView::add} oppure direttamente {@link getBody()}.
 *
 * Poichè viene utilizzata sempre in combinazione con un oggetto {@link Form} fornisce anche
 * un HashMap che conserva gli eventuali errori di compilazione. Reagisce ai cambiamenti 
 * della form associata.
 *
 * Ridefinire sempre il metodo updateBody inserendovi il codice per popolare il
 * modulo col codice XHTML dei controlli.
 *
 * E' possibile disabilitare una form in modo che non accetti più testo (e quindi
 * il contenuto aggiunto fino ad un certo punto venga sovrascritto).
 *
 * @author Luca Gambetta <l.gambetta@bluenine.it>
 *
*/
class BaseFormView extends ObjectConnectAdapter {
	// Modo in cui vengono generati i nomi dei controlli
	const
		CTRL_NAME_ID_ONLY = 0, // genera solo l'attributo id
		CTRL_NAME_NAME_ONLY = 1, // genera solo l'attributo name
		CTRL_NAME_BOTH = 10; // genera entrambi gli attributi
	
	const
		// Tipo di pulsante
		BUTTON_TYPE_SUBMIT = 'submit', // Pulsante di invio
		BUTTON_TYPE_RESET = 'reset', // Pulsante di reset
		BUTTON_TYPE_BUTTON = 'button'; // Pulsante semplice
		
	public
		$errors; // hashmap con gli errori di compilazione
	
	protected
		/**
		 * @var FormViewStyle stile della form
		 */
		$_style,
		/**
		 * @var array usata da loadTemplate
		 * */
		$_replacements = array(),
		$read_only, // Flag che indica se la form è in modalità a sola lettura
		$showerrormsg, // Flag visualizzazione dei messaggi di errore
		$control_name_style, // stile utilizzato per generare il nome dei controlli
		$rendered_html = '', // stringa con l'html
		$body, // StringArray col corpo della form
		$form = NULL; // istanza di Form di cui si fa il view
		
	
	/**
	 * Costruisce una vista di un modulo xhtml.
	 * 
	 * Il modo in cui viene generato il nome dei controlli viene deciso dalle
	 * costanti interne:
	 *
	 * CTRL_NAME_ID_ONLY - viene generato solo l'attributo "id"
	 * CTRL_NAME_NAME_ONLY - viene generato solo l'attributo "name"
	 * CTRL_NAME_BOTH - vengono generati entrambi gli attributi "id" e "name"
	 *
	 * @param integer $control_name_style stile da utilizzare per generare i nomi dei controlli (una delle costanti CTRL_NAME*
	*/
	public function __construct($control_name_style = self::CTRL_NAME_BOTH) {
		$this->control_name_style = $control_name_style;
		$this->errors = new HashMap();
		$this->errors->connect($this);
		$this->body = new StringArray();
		$this->body->connect($this);
		$this->showerrormsg = FALSE;
		$this->read_only = FALSE;
		$this->setDefaultStyle();
	}
	
	/**
	 * Imposta lo stile di disegno di default.
	 * 
	 * Interroga l'oggetto PageLayout corrente per inizializzare il delegate per il disegno della form.
	 * 
	 * @see setStyle
	 * @see FormViewStyle
	 */
	public function setDefaultStyle() {
		if (WebApp::getInstance()->hasPageLayout()) {
			$this->setStyle(WebApp::getInstance()->getPageLayout()->getWidgetStyle()->getFormViewStyle($this));
		} else {
			$this->setStyle(new FormViewStyle());
		}
	}
	
	/**
	 * Imposta lo stile di disegno di questa form.
	 * 
	 * @param FormViewStyle $style lo stile di disegno
	 * @return FormViewStyle
	 */
	public function setStyle(FormViewStyle $style) {
		$this->_style = $style;
		$this->_style->setFormView($this);
		return $style;
	}
	
	/**
	 * Ritorna lo stile di questa form view.
	 * 
	 * @return FormViewStyle lo stile di questa form
	 */
	public function getStyle() {
		return $this->_style;
	}

	/**
	 * Informa se la view sia in modalità a sola lettura (ossia non più modificabile)
	 *
	 * @return boolean TRUE la view non è modificabile, FALSE altrimenti
	 */
	public function isReadOnly() {
		return $this->read_only;
	}

	/**
	 * Rende la view non più modificabile.
	 *
	 * @param boolean $readonly TRUE la view non è più modificabile, FALSE altrimenti
	 */
	public function setReadOnly($readonly = TRUE) {
		$this->read_only = (bool)$readonly;
		$this->body->setReadOnly($this->read_only);
	}
	
	/**
	 * Restituisce una stringa XHTML con la form.
	 * 
	 * Va invocata dopo una chiamata a {@link update}, che si occupa di rigenerare completamente
	 * il codice XHTML.
	 *
	 * @return string una stringa col codice XHTML
	*/
	public function getHTML() {
		return $this->rendered_html;
	}
	
	/**
	 * Imposta l'xhtml che contiene tutta la form: è la stringa
	 * che verrà restituita da {@link getHTML}
	 * @param string $html stringa col codice xhtml per la form
	*/
	protected function setHTML($html) {
		if (!$this->read_only) $this->rendered_html = strval($html);
	}
	
	/**
	 * Forza l'aggiornamento della vista.
	 *
	 * Aggiorna il corpo del modulo usando {@see updateBody}, quindi riflette
	 * i cambiamenti della form agganciata (controlli nascosti, eventuali pulsanti, etc.).
	 * Imposta il codice XHTML della vista (usando {@see setHTML}.
	 *
	 * Se la view è disabilitata non imposta niente.
	 *
	 * @see updateBody
	 * @see setHTML
	 * @see beforeRendering
	*/
	public function update() {
		if ($this->isReadOnly()) return FALSE;
		
		$this->updateBody();
		
		$this->beforeRendering();
		
		$this->setHTML( $this->_style->render() );
	}
	
	/**
	 * Esegue delle operazioni prima del rendering della form.
	 * 
	 * Viene usata da {@link update} per eseguire delle operazioni prima
	 * di invocare il rendering della form.
	 * 
	 * Viene eseguita dopo {@link updateBody}.
	 */
	protected function beforeRendering() {
	}
	
	/**
	 * Popola o aggiorna il corpo del modulo, ossia i controlli xhtml che la compongono.
	 *
	 * Aggiorna il corpo del modulo, a seguito di un aggiornamento
	 * generale della form.
	 * Normalmente non esegue niente, ma utilizzarla per far sì che il corpo
	 * ad esempio contenga dei controlli compilati con i valori del Model in seguito
	 * alla validazione del modulo.
	 * Ricordarsi di cancellare il testo precedente nel corpo, se necessario, usando {@link clearBody()}
	*/
	public function updateBody() {}
	
	/**
	 * Imposta l'oggetto Form che questa View deve utilizzare come Model
	 *
	 * @param Form $form istanza di Form
	*/
	public function setForm(Form $form) {
		$this->form = $form;
	}
	
	/**
	 * Ritorna l'istanza di form agganciata a questo oggetto
	 * @return Form istanza di Form agganciata
	*/
	public function getForm() {
		return $this->form;
	}
	
	/**
	 * Dissocia questo oggetto da qualsiasi istanza di Form precedentemente connessa.
	*/
	public function unsetForm() {
		$this->form = NULL;
	}
	
	/**
	 * Informa se ci sia una form associata
	 * @return boolean TRUE se c'è, FALSE altrimenti
	*/
	public function hasForm() {
		return is_object($this->form);
	}
	
	/**
	 * Fornisce il valore di un campo, estraendolo dal Model.
	 * 
	 * Il model è l'oggetto InputValidator associato alla Form agganciata.
	 * Proxy: invoca getValue sull'istanza di Form agganciata
	 * 
	 * @param string $fieldname stringa col nome del parametro
	 *
	 * @return mixed NULL o il valore del campo
	*/
	public function getValue($fieldname) {
		return is_object($this->form) ? $this->form->getValue($fieldname) : NULL;
	}

	/**
	 * Fornisce il valore di un campo, estraendolo dal Model e convertendone le entità in XHTML.
	 *
	 * Il model è l'oggetto InputValidator associato alla Form agganciata.
	 * Proxy: invoca getValue sull'istanza di Form agganciata
	 *
	 * @param string $fieldname stringa col nome del parametro
	 *
	 * @return mixed NULL o il valore del campo
	*/
	public function getXHTMLValue($fieldname) {
		return is_object($this->form) ? XHTML::toHTML($this->form->getValue($fieldname)) : NULL;
	}
	
	/**
	 * Ritorna lo stato della visualizzazione dei messaggi d'errore
	 *
	 * @return boolean TRUE se i messaggi di errore vanno visualizzati con la form, FALSE altrimenti
	*/
	public function getShowErrorMsg() {
		return $this->showerrormsg;
	}

	/**
	 * Imposta lo stato della visualizzazione dei messaggi d'errore
	 * 
	 * @param boolean $show TRUE se i messaggi di errore devono essere visualizzati, FALSE altrimenti
	*/
	public function setShowErrorMsg($show) {
		$this->showerrormsg = $show;
	}
	
	/**
	 * Ritorna l'oggetto agganciato all'istanza di InputValidator associato alla form.
	 *
	 * @return object NULL se non c'è nessun oggetto reperibile, altrmenti l'oggetto
	*/
	public function getValidatorObject() {
		if ($this->hasForm()){
			$v = $this->getForm()->getValidator();
			if (is_object($v)) {
				return $v->getObject();
			}
		}
		return NULL;
	}
	
	// ----------------------- Corpo della form
	/**
	 * Ritorna l'istanza di StringArray che rappresenta il corpo della form.
	 *
	 * In questa istanza vanno aggiunte le stringhe xhtml con i controlli
	 *
	 * @return StringArray una istanza di StringArray
	*/
	public function getBody() { 
		return $this->body;
	}
	
	/**
	 * Aggiunge del testo al corpo (forma breve per $this->body()->add());
	 *
	 * @param string $str stringa col codice xhtml
	*/
	public function add($str) {
		$this->body->add($str);
	}
	
	/**
	 * Ripulisce il corpo della form dal suo contenuto
	*/
	public function clearBody() {
		$this->body->clear();
	}
	
	/**
	 * Ritorna un array con i campi hidden prelevati dalla form collegata
	 *
	 * @return array array di stringhe xhtml coi campi hidden
	*/
	public function getHiddenControls() {
		$o = array();
		if ($this->hasForm()) {
			$h = $this->form->getHiddenControls();
			foreach($h as $name => $value) $o[] = $this->hidden($name, XHTML::toHTML($value));
		} 
		return $o;
	}
	
	/**
	 * Carica un pezzo di template popolandolo con i valori dei campi.
	 * 
	 * Carica un file dai path standard per il view dell'azione e modulo corrente (vedi {@link WebAppView::getViewPath})
	 * e sostituisce le stringhe con i valori associati al validator.
	 * 
	 * Le stringhe che verranno sostituite nel template sono nel formato "@nomecampo@". 
	 * Se ad esempio nel template è presente il seguente codice XHTML:
	 * <code>
	 * 	<input id="frm_field" value="@frm_field@" />
	 * </code>
	 * 
	 * La funzione troverà 'frm_field' e sostituirà la stringa '@frm_field@' con il valore
	 * ritornato da {@link getXHTMLValue} passando come parametro 'frm_field'.
	 * 
	 * É possibile associare anche una callback per l'estrazione del valore, passando
	 * come parametro $callbacks un array associativo nel formato:
	 * array(
	 * 	'nome campo' => callback
	 * )
	 * 
	 * La callback deve accettare come parametro una stringa e ritornare una stringa, 
	 * che verrà utilizzata per la sostituzione:
	 * 
	 * function callback($nome_parametro)
	 * 
	 * Dove $nome_parametro è il nome del parametro su cui operare.
	 * 
	 * Tra i due caratteri '@' che le racchiudono, il formato delle stringhe 
	 * da sostituire è il seguente: una lettera seguita da un numero non precisato
	 * di lettere, numeri, caratteri '_' e '-'. Esempi: '@foo_bar@', '@b_@', '@baz-bar@',
	 * '@A1@'.
	 * 
	 * @param string $filename nome del file del template
	 * @param array $skip chiavi da saltare (non sostituire)
	 * @param array $callbacks array associativo con le callback
	 * @return string il codice XHTML o FALSE se il file non è stato trovato
	 * */
	public function loadTemplate($filename, $skip = array(), $callbacks = array() ) {
		$filepath = WebApp::getInstance()->getView()->getViewPath($filename);
		if ($filepath === FALSE) {
			Logger::getinstance()->debug("BaseFormView::loadTemplate: getViewPath failed on ".$filename);
			return FALSE;
		}
		
		// Carica il file
		$contents = @file_get_contents($filepath);
		if ($contents === FALSE) {
			Logger::getinstance()->debug("BaseFormView::loadTemplate: file_get_contents failed on ".$filepath);
			return FALSE;
		}
		
		$this->_replacements = array(
			'skip' => $skip,
			'callbacks' => $callbacks
		);
		
		// Effettua le sostituzioni
		$out = preg_replace_callback('/@([a-zA-Z][_\-a-zA-Z0-9]*)@/', array($this, 'replaceTemplate'), $contents);
		$this->_replacements = array();

		// Interpreta un eventuale tag FORM

		
		return $out;
	}
	
	/*
	 * Sostituisce gli elementi del template.
	 * 
	 * Usata da {@link loadTemplate}.
	 * @param array $matches array con i campi da sostituire
	 * @return string la stringa da sostituire
	 * */
	protected function replaceTemplate($matches) {

		if (in_array( $matches[1], $this->_replacements['skip'])) return $matches[0];
		
		if (array_key_exists($matches[1], $this->_replacements['callbacks'])) {
			if (is_callable( $this->_replacements['callbacks'][ $matches[1] ])) {
				return call_user_func( $this->_replacements['callbacks'][ $matches[1] ], $matches[1] );
			}
		} else {
			return $this->getXHTMLValue($matches[1]);
		}
		
		return $matches[0];
	}
	
	// ----------------------- Astrazione XHTML
	/**
	 * Ritorna il markup xhtml di apertura di una form: viene generato utilizzando
	 * l'istanza di Form agganciata.
	 *
	 * @return string una stringa col codice XHTML
	*/
	public function open() {
		if (is_object($this->form)) {
			return '<form action="'.WebApp::getInstance()->getRouter()->getFormAction($this->getForm()->getAction(), TRUE).'"'.
			' method="'.strtolower(Request::methodToString($this->form->getMethod() )).'"'.
		(strlen($this->form->getFormID()) > 0 ? ' id="'.$this->form->getFormID().'"' : '').
		' enctype="'.$this->form->getContentType().'"'.
		(strlen($this->form->getAccept()) > 0 ? ' accept="'.$this->form->getAccept().'"' : '').
		(strlen($this->form->getAcceptCharset()) > 0 ? ' accept-charset="'.$this->form->getAcceptCharset().'"' : '').
		' '.$this->_style->getFormAttributes().">\n";
		} else {
			return '<form>';
		}
	}
	
	/**
	 * Ritorna l'xhtml di chiusura di una form
	 * @return string stringa col codice xhtml
	*/
	public function close() {
		return "</form>\n";
	}
	
	/**
	 * Ritorna una stringa con un campo "select", componendola utilizzando i due array
	 * passati come parametri: essi devono avere lo stesso numero di elementi
	 * 
	 * @param array $label_arr array con le stringhe delle etichette da usare (non vengono convertite le entità in html)
	 * @param array $value_arr array con i valori da utilizzare per i controlli
	 * @param string $ctl_name stringa col nome del controllo xhtml
	 * @param array $presel array con i valori delle voci preselezionate, o un valore singolo, o NULL per nessuna voce
	 * @param boolean $multiple TRUE campo a selezione multipla, FALSE combo box
	 * @param string $append stringa con l'html da appendere al controllo select
	 * @return string codice XHTML col campo select
	*/
	public function makeSelect($label_arr, $value_arr, $ctl_name, $presel = NULL, $multiple = FALSE, $append = '') {
		$o = '';
		$label = reset($label_arr);
		$value = reset($value_arr);

		while ($label !== FALSE && $value !== FALSE) {
			$o .= $this->_style->option(strval($value), strval($label), (is_array($presel) ? in_array($value, $presel) :  (strcmp($value, $presel) == 0) ) );
			$label = next($label_arr);
			$value = next($value_arr);
		}

		return $this->_style->select($ctl_name, $o, $multiple, $append);
	}
	
	/**
	 * Ritorna un array con scelte di tipo radiobox o checkbox.
	 *
	 * TODO: <p>verificare valore corretto del nome vedi specifiche w3c:
	 *   All "on" checkboxes may be successful.
	 *   For radio buttons that share the same value of the  name attribute, only the "on" radio button may be successful.
	 * </p>
	 * 
	 * Ritorna del codice XHTML del tipo:
	 * <code>
	 * '<label><input type="radio" name="nome[]" />Etichetta di testo</label>'
	 * </code>
	 * 
	 * Il nome dei controlli viene seguito da '[]' e non viene impostato l'id dei campi: ciò perchè
	 * altrimenti il codice sorgente XHTML non verrebbe validato.
	 * 
	 * Le label non vengono marchiate con l'id degli elementi a cui si riferiscono.
	 * 
	 * @param array $label_arr array con le stringhe delle etichette da usare (non vengono convertite le entità in html)
	 * @param array $value_arr array di stringhe con i valori da utilizzare per i controlli
	 * @param string $ctl_name nome del controllo xhtml
	 * @param mixed $presel array con i valori preselezionati, o una stringa con un singolo valore o NULL per nessuna voce
	 * @param boolean $make_checkbox TRUE ritorna delle checkbox, FALSE delle radiobox
	 * @param string $append stringa con attributi xhtml da aggiungere ad ogni elemento creato
	 * @return array un array di stringhe xhtml
	*/
	public function makeCheckGroup($label_arr, $value_arr, $ctl_name, $presel = NULL, $make_checkbox = FALSE, $append = '') {
		// Imposta lo stile temporaneamente a solo nome
		$ctrl_name_style = $this->getControlNameStyle();
		$this->setControlNameStyle(self::CTRL_NAME_NAME_ONLY);
		
		$label = reset($label_arr);
		$value = reset($value_arr);
		$o = array();
		while ($label !== FALSE && $value !== FALSE) {
			$o[] = $this->_style->label(( $make_checkbox ? $this->_style->checkbox($ctl_name.'[]', is_array($presel) ? in_array($value, $presel) :  ($value == $presel), $value, FALSE, $append ) : $this->_style->radio($ctl_name, is_array($presel) ? in_array($value, $presel) :  ($value == $presel), $value, FALSE, $append ) ).' '.strval($label) );
			$label = next($label_arr);
			$value = next($value_arr);
		}
		
		$this->setControlNameStyle($ctrl_name_style);
		return $o;
	}
	
	
	/**
	 * Ritorna gli attributi di accessibilità di un campo. Di solito
	 * questra stringa viene usata come parametro $extra nelle funzioni che creano
	 * un controllo xhtml.
	 * 
	 * @param string $title stringa col titolo dell'elemento
	 * @param string $alt stringa con la descrizione alternativa dell'elemento
	 * @param string $accesskey carattere che indica il tasto di accesso rapido del controllo
	 * @param integer $tabindex intero che contiene l'indice di selezione del campo
	 * @return string una stringa con gli attributi
	*/
	public function accessibility($title, $alt = '', $accesskey = '', $tabindex = 0) {
		return (empty($title) ? '' : ' title="'.strval($title).'"').
		(empty($alt) ? '' : ' alt="'.strval($alt).'"').
		(empty($accesskey) ? '' : ' accesskey="'.strval($accesskey).'"').
		(empty($tabindex) ? '' : ' tabindex="'.strval($tabindex).'"');
	}
	
	/**
	 * Ritorna gli attributi per la lingua di un campo.
	 * Di solito la stringa viene utilizzata come parametro $extra nelle funzioni
	 * che creano un controllo xhtml
	 * 
	 * @param string $lang stringa con la lingua accettata
	 * @param string $dir stringa con la direzione utilizzata
	*/
	public function lang($lang, $dir = '') {
		return (empty($lang) ? '' : ' lang="'.$lang.'"').
		(empty($dir) ? '' : ' dir="'.$dir.'"');
	}
	
	/**
	 * Ritorna gli attributi di stile e di identificazione di un campo.
	 * Di solito la stringa viene utilizzata come parametro $extra nelle funzioni
	 * che creano un controllo xhtml
	 * 
	 * @param string $id stringa con l'id del campo
	 * @param string $css_class stringa con la classe CSS del campo
	 * @param string $style stringa col codice CSS per il campo
	*/
	public function id($id, $css_class = '', $style = '') {
		return (empty($id) ? '' : ' id="'.$id.'"').
		(empty($css_class) ? '' : ' class="'.$css_class.'"').
		(empty($style) ? '' : ' style="'.$style.'"');
	}
	
	/**
	 * Ritorna il codice xhtml degli attributi nome del controllo.
	 * La stringa ritornata è influenzata dallo stile dei nomi attuale.
	 * 
	 * @param string $name stringa col nome del controllo
	 * @return string una stringa con gli attributi col nome del controllo
	 * @see setControlNameStyle
	*/
	public function nameCtrl($name) {
		if ($this->control_name_style == self::CTRL_NAME_NAME_ONLY) return 'name="'.strval($name).'"';
		elseif ($this->control_name_style == self::CTRL_NAME_ID_ONLY) return 'id="'.strval($name).'"';
		else return 'name="'.strval($name).'" id="'.strval($name).'"';
	}
	
	/**
	 * Imposta lo stile utilizzato per generare l'attributo coi nomi dei controlli.
	 * 
	 * Esso può essere:
	 * 		CTRL_NAME_ID_ONLY - genera solo l'id
	 * 		CTRL_NAME_NAME_ONLY - genera solo il nome
	 * 		CTRL_NAME_BOTH - genera entrambi
	 * 
	 * @param integer $control_name_style intero con lo stile da utilizzare
	*/
	public function setControlNameStyle($control_name_style = self::CTRL_NAME_BOTH) {
		$this->control_name_style = $control_name_style;
	}
	
	/**
	 * Ritorna lo stile utilizzato per generare il nome dei controlli
	 *
	 * @return integer un intero col valore di una delle costanti CTRL_NAME_*
	*/
	public function getControlNameStyle() {
		return $this->control_name_style;
	}
	
	// ----------------------- ERRORI DI COMPILAZIONE
	/**
	 * Ritorna l'hashmap con gli errori di compilazione.
	 * 
	 * L'hashmap ha come chiave il nome del campo errorato e come valore il messaggio d'errore
	 * 
	 * Quando si imposta un errore per un campo,la stringa di errore deve essere del codice XHTML 
	 * del messaggio stesso: poichè potrebbe essere presentato in vari modi, non rinchiuderlo
	 * in paragrafi o altro elemento xhtml di tipo block level.
	 *
	 * @return HashMap istanza di HashMap coi messaggi di errore
	*/
	public function getErrors() {
		return $this->errors;
	}
	
	/**
	 * Popola l'hashmap con gli errori prendendoli dall'InputValidator
	 * agganciato alla Form associato.
	 * Non cancella gli eventuali errori già presenti.
	*/
	public function copyValidationErrors() {
		if (is_object($this->form)) {
			$v = $this->form->getValidator();
			if (is_object($v)) {
				$this->errors->copyArray($v->getErrors());
			}
		}
	}
	
	/**
	 * Rimuove tutti gli errori salvati.
	*/
	public function clearErrors() {
		$this->errors->clear();
	}
	
	/**
	 * Informa se ci siano degli errori di compilazione
	 * @return boolean TRUE se ci sono errori di compilazione, FALSE altrimenti
	*/
	public function hasErrors() {
		return !$this->errors->isEmpty();
	}
}

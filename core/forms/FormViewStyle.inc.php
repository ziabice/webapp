<?php

/**
 * (c) 2012 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Delegate per il disegno di oggetti FormView e BaseFormView
 *
 * @author Luca Gambetta <l.gambetta@bluenine.it>
 */
class FormViewStyle {
	
	// Etichette per gli stili CSS delle varie parti del modulo
	const
		MAIN_STYLE = 'formview_main_style', // stile da applicare al tag <form>
		CUSTOM1 = 'formview_custom1',
		CUSTOM2 = 'formview_custom2',
		CUSTOM3 = 'formview_custom3',
		CUSTOM4 = 'formview_custom4',
		CUSTOM5 = 'formview_custom5',
		CUSTOM6 = 'formview_custom6',
		FIELDSET = 'formview_fieldset', // i fieldset
		BUTTONBOX = 'formview_buttonbox', // tag CSS per bottoniera
		ERRORS_LIST = 'formview_error_list', // tag CSS per elemento della lista di errori
		ERROR_ITEM = 'formview_error_item', // tag CSS per la lista di errori
		ERRORS = 'formview_errors'; // tag CSS per il testo di errore
	
	public
		/**
		 * @var string codice XHTML di apertura della sezione con i campi nascosti
		 */
		$hidden_open_xhtml = '',
			
		/**
		 * @var string codice XHTML di chiusura della sezione con i campi nascosti
		 */
		$hidden_close_xhtml = '',
			
		/**
		 * @var string codice XHTML da preporre alla form
		 */
		$prepend_xhtml = '',
		
		/**
		 * @var string codice XHTML da postporre alla form
		 */
		$append_xhtml = '',
			
		/**
		 * @var string codice XHTML in apertura della form
		 */
		$open_form_xhtml = '',
		
		/**
		 * @var string codice XHTML di apertura della form
		 */
		$close_form_xhtml = '',
			
		/**
		 * @var string codice XHTML di apertura della sezione con gli errori di compilazione
		 */
		$open_errors_xhtml = '',
		
		/**
		 * @var string codice XHTML di chiusura della sezione con gli errori di compilazione
		 */
		$close_errors_xhtml = '',
		
		/**
		 * @var string codice XHTML di apertura della button box
		 */
		$open_buttonbox_xhtml = '',
		
		/**
		 * @var string codice XHTML di chiusura della button box
		 */
		$close_buttonbox_xhtml = '',
			
		/**
		 * @var string
		 */
		$error_message = '',
		/**
		 * @var FormView 
		 */
		$form_view,
		/**
		 * @var CSS
		 */
		$css;
	
	/**
	 * Inizializza impostando valori di default.
	 * 
	 * @see setDefaults
	 */
	public function __construct() {
		$this->initializeCSS();
		$this->setDefaults();
	}
	
	/**
	 * Inizializza i valori di default per gli elementi.
	 * 
	 * @see setOpenButtonBoxXHTML
	 * @see setCloseButtonBoxXHTML
	 * @see setOpenErrorsXHTML
	 * @see setCloseErrorsXHTML
	 * @see setAppendXHTML
	 * @see setPrependXHTML
	 * @see setOpenFormXHTML
	 * @see setCloseFormXHTML
	 * @see setOpenHiddenXHTML
	 * @see setCloseHiddenXHTML
	 */
	protected function setDefaults() {
		$this->setOpenButtonBoxXHTML( '<div'.$this->getCSS()->getAttr(self::BUTTONBOX).'>' );
		$this->setCloseButtonBoxXHTML('</div>');
		
		$this->setOpenErrorsXHTML('');
		$this->setCloseErrorsXHTML('');
		
		$this->setAppendXHTML('');
		$this->setPrependXHTML('');
		
		$this->setOpenFormXHTML('');
		$this->setCloseFormXHTML('');
		
		$this->setOpenHiddenXHTML('<div>');
		$this->setCloseHiddenXHTML("</div>\n");
		
		$this->setErrorMessage( tr('Sono stati commessi degli errori di compilazione del modulo.') );
	}

	/**
	 * Inizializza i valori di default per CSS.
	 * 
	 * @see PageLayout::getDefaultCSS
	 */
	protected function initializeCSS() {
		if (WebApp::getInstance()->hasPageLayout()) {
			$this->setCSS(WebApp::getInstance()->getPageLayout()->getWidgetStyle()->getCSS($this));
		} else {
			$this->setCSS(new CSS());
		}
	}

	/**
	 * Imposta il gestore di stili.
	 * 
	 * @param CSS $css il gestore di stili
	 * @return CSS
	 */
	public function setCSS(CSS $css) {
		$this->css = $css;
		return $this->css;
	}
	
	/**
	 * Ritorna il gestore di stili CSS.
	 * 
	 * @return CSS
	 */
	public function getCSS() {
		return $this->css;
	}
	
	/**
	 * Ritorna la FormView associata.
	 * 
	 * @return FormView
	 */
	public function getFormView() {
		return $this->form_view;
	}
	
	/**
	 * Imposta la FormView associata.
	 * 
	 * @param BaseFormView $fv la form view
	 * @return BaseFormView
	 */
	public function setFormView(BaseFormView $fv) {
		$this->form_view = $fv;
		return $fv;
	}
	
	/**
	 * Ritorna il codice xhtml di apertura della pulsantiera a fondo Form.
	 *
	 * Di solito ritorna un tag DIV a ui è applicato lo stile indicato da BUTTONBOX.
	 *
	 * @return una stringa col codice xhtml
	*/
	public function openButtonBox() { 
		return $this->open_buttonbox_xhtml;
	}
	
	/**
	 * Imposta il codice XHTML di apertura della pulsantiera a fondo Form.
	 * @param string $xhtml il codice XHTML
	 */
	public function setOpenButtonBoxXHTML($xhtml) {
		$this->open_buttonbox_xhtml = strval($xhtml);
	}
	
	/**
	 * Ritorna il codice xhtml di chiusura della pulsantiera a fondo Form
	 *
	 * Di solito ritorna un tag DIV di chiusura.
	 *
	 * @return una stringa col codice xhtml
	*/
	public function closeButtonBox() { 
		return $this->close_buttonbox_xhtml;
	}

	/**
	 * Imposta il codice XHTML di chiusura della pulsantiera a fondo Form.
	 * @param string $xhtml il codice XHTML
	 */
	public function setCloseButtonBoxXHTML($xhtml) {
		$this->close_buttonbox_xhtml = strval($xhtml);
	}
	
	/**
	 * Effettua il rendering della pulsantiera, prendendo i bottoni dalla
	 * form associata.
	 * 
	 * Se non ci sono bottoni nel modulo associato, ritorna una stringa vuota,
	 * altrimenti una zona delimitata dal codice ritornato da {@link openButtonBox} e
	 * {@link closeButtonBox}, contenente i vari pulsanti.
	 * 
	 * I pulsanti vengono disegnati usando l'istanza stessa, invocando su di essi prima 
	 * {@link FormButton::setFormView} e poi {@link FormButton::render}
	 *
	 * @return string una stringa con l'xhtml per la pulsantiera
	*/
	public function renderButtonBox(Form $form) {
		$s = '';
		
		if ($form->hasButtons()) {
			$iter = $form->getFormButtonIterator();
			$s .= $this->openButtonBox();
			$i = 0;
			foreach($iter as $btn) {
				$btn->setFormView($this->getFormView());
				$s .= ($i++ > 0 ? ' ' : '').$btn->render();
			}
			$s .= $this->closeButtonBox();
		}
		
		return $s;
	}
	
	/**
	 * Ritorna una stringa con l'xhtml dei messaggi di errore.
	 * 
	 * La presentazione effettiva dei messaggi viene delegata al metodo {@link renderErrorMessages},
	 * che di solito prenseta una lista non ordinata dei messaggi di errore.
	 * 
	 * I singoli messaggi di errore devono essere già del codice XHTML valido.
	 * 
	 * La zona dei messaggi di errore è aperta dal codice XHTML generato da {@link openErrors()} e chiusa
	 * dal codice XHTML generato da {@link closeErrors()}
	 * 
	 * Ritorna una stringa vuota nel caso in cui non ci siano messaggi di errore.
	 *
	 * @return string una stringa xhtml col codice degli errori
	 * @see openErrors
	 * @see renderErrorMessages
	 * @see closeErrors
	*/
	public function renderErrors() {
		$s = '';
		if($this->form_view->hasErrors()) {
			$s .= $this->openErrors();
			$s .= $this->renderErrorMessages();
			$s .= $this->closeErrors();
		}
		return $s;
	}
	
	/**
	 * Restituisce il codice XHTML dei messaggi di errore sui campi.
	 * 
	 * Ritorna una lista non ordinata a cui sono applicati i seguenti stili (viene indicato il tag):
	 * ERRORS_LIST - stile per il tag UL
	 * ERROR_ITEM - stile per il tag LI
	 * 
	 * @return string il codice XHTML
	 * @see openErrorMessages
	 * @see closeErrorMessages
	 */
	public function renderErrorMessages() {
		
		$s = $this->openErrorMessages();
		
		$s .= '<ul'.$this->css->getAttr(self::ERRORS_LIST).'>';
		foreach($this->form_view->getErrors() as $msg) {
			$s .= '<li'.$this->css->getAttr(self::ERROR_ITEM).'>'.$msg."</li>";
		}
		$s .= "</ul>\n";
		
		$s .= $this->closeErrorMessages();
		return $s;
	}
	
	/**
	 * Ritorna il codice XHTML di apertura della sezione con i messaggi di errore.
	 * 
	 * Ritorna un paragrafo a cui viene applicato lo stile del tag ERRORS, il messaggio
	 * viene fornito da {@link getErrorMessage}.
	 * 
	 * @return string il codice XHTML
	 * @see renderErrorMessages
	 * @see getErrorMessage
	 */
	public function openErrorMessages() {
		return '<p'.$this->css->getAttr(self::ERRORS).">".$this->getErrorMessage()."</p>\n";
	}
	
	/**
	 * Ritorna il codice XHTML di chiusura della sezione con i messaggi di errore.
	 * 
	 * @return string il codice XHTML
	 * @see renderErrorMessages
	 * @see getErrorMessage
	 */
	public function closeErrorMessages() {
		return '';
	}
	
	/**
	 * Ritorna il codice XHTML da inserire subito dopo il tag di apertura della form.
	 * 
	 * @return string il codice XHTML
	 */
	public function openForm() {
		return $this->open_form_xhtml;
	}
	
	/**
	 * Imposta il codice XHTML da inserire subito dopo il tag di apertura della form.
	 * 
	 * @param string $xhtml il codice XHTML
	 */
	public function setOpenFormXHTML($xhtml) {
		$this->open_form_xhtml = strval($xhtml);
	}
	
	/**
	 * Ritorna il codice XHTML da inserire subito prima del tag di chiusura della form.
	 * @return string il codice XHTML
	 */
	public function closeForm() {
		return $this->close_form_xhtml;
	}
	
	/**
	 * Imposta il codice XHTML da inserire subito prima del tag di chiusura della form.
	 * 
	 * @param string $xhtml il codice XHTML
	 */
	public function setCloseFormXHTML($xhtml) {
		$this->close_form_xhtml = strval($xhtml);
	}
	
	/**
	 * Ritorna il codice XHTML da preporre alla form.
	 * 
	 * @return string il codice XHTML
	 */
	public function prepend() {
		return $this->prepend_xhtml;
	}
	
	/**
	 * Imposta il codice XHTML da preporre alla form
	 * 
	 * @param string $xhtml il codice XHTML
	 */
	public function setPrependXHTML($xhtml) {
		$this->prepend_xhtml = strval($xhtml);
	}
	
	/**
	 * Ritorna il codice XHTML da posporre alla form.
	 * 
	 * @return string il codice XHTML
	 */	
	public function append() {
		return $this->append_xhtml;
	}
	
	/**
	 * Imposta il codice XHTML da posporre alla form
	 * 
	 * @param string $xhtml il codice XHTML
	 */
	public function setAppendXHTML($xhtml) {
		$this->append_xhtml = strval($xhtml);
	}
	
	/**
	 * Effettua il rendering dei campi hidden.
	 * 
	 * @return string una stringa XHTML con i campi
	 * @see hiddenOpen
	 * @see hiddenClose
	 */
	public function renderHiddenFields() {
		$ctrl = $this->form_view->getHiddenControls();
		if (!empty($ctrl)) return $this->hiddenOpen().implode("", $ctrl).$this->hiddenClose ();
		return '';
	}
	
	/**
	 * Ritorna il codice XHTML per l'apertura della zona che contiene i campi hidden.
	 * 
	 * @return string il codice XHTML
	 * @see renderHiddenFields
	 */
	public function hiddenOpen() {
		return $this->hidden_open_xhtml;
	}

	/**
	 * Imposta il codice XHTML di apertura della zone dei controlli nascosti.
	 * 
	 * @param string $xhtml il codice XHTML
	 */
	public function setOpenHiddenXHTML($xhtml) {
		$this->hidden_open_xhtml = strval($xhtml);
	}
	
	/**
	 * Ritorna il codice XHTML per la chiusura della zona che contiene i campi hidden.
	 * 
	 * @return string il codice XHTML
	 * @see renderHiddenFields
	 */	
	public function hiddenClose() {
		return $this->hidden_close_xhtml;
	}
	
	/**
	 * Imposta il codice XHTML di chiusura della zona dei controlli nascosti.
	 * 
	 * @param string $xhtml il codice XHTML
	 */
	public function setCloseHiddenXHTML($xhtml) {
		$this->hidden_close_xhtml = strval($xhtml);
	}
	
	/**
	 * Effettua il rendering della form.
	 * 
	 * @return string il codice XHTML della form
	 * @see beforeRendering
	 */
	public function render() {
		$this->beforeRendering();
		
		$s = $this->prepend(); // Prepende
		$s .= $this->form_view->open(); // apre il tag form
		$s .= $this->openForm(); // aggiunge il codice
		$s .= $this->renderHiddenFields();
		
		if ($this->getFormView() instanceof FormView) {
			if ( $this->form_view->getShowErrorMsg() ) $s .= $this->renderErrors();
		
			$s .= $this->form_view->getBody()->toString();
		
			if ($this->form_view->getShowButtonBox()) $s .= $this->renderButtonBox( $this->form_view->getForm() );
		} else {
			$s .= $this->form_view->getBody()->toString();
		}

		$s .= $this->closeForm();
		$s .= $this->form_view->close();
		$s .= $this->append();
		return $s;
	}
	
	/**
	 * Ritorna gli attributi di presentazione da inserire nel tag <form>
	 * 
	 * Attualmente ritorna solo gli eventuali stili CSS (compreso l'ID).
	 * 
	 * @return string ritorna una stringa XHTML di attributi
	 * @see BaseFormView::open
	 */
	public function getFormAttributes() {
		$s = '';
		if ($this->getFormView()->hasForm()) {
			$s .= $this->getFormView()->getForm()->getXHTMLAttr();
			if (strlen($s) > 0) $s = ' '.$s;
		}
		$s .= $this->getCSS()->getAttr(self::MAIN_STYLE);
		return $s;
	}
	
	/**
	 * Esegue delle operazioni prima del rendering.
	 * 
	 * Viene eseguita da {@link render} prima di eseguire il rendering vero e proprio,
	 * in modo da avere la possibilità di compiere delle azioni, se necessario.
	 */
	protected function beforeRendering() {}

	/**
	 * Ritorna una stringa XHTML per introdurre i messaggi di errore.
	 *
	 * @return string una stringa XHTML
	 * @see openErrorMessages
	 */
	public function getErrorMessage() {
		return $this->_error_message;
	}
	
	/**
	 * Imposta il messaggio di errore da mostrare prima
	 * dell'elenco degli errori.
	 * 
	 * Questo messaggio viene utilizzato da {@link openErrors} per introdurre gli errori.
	 * Deve essere in XHTML.
	 * 
	 * @param string $message stringa XHTML con il messaggio di errore
	 */
	public function setErrorMessage($message) {
		$this->_error_message = strval($message);
	}
	
	/**
	 * Ritorna il codice utilizzato per introdurre l'elenco dei messaggi di errore.
	 * 
	 * Ritorna un paragrafo col testo "Sono stati commessi degli errori di compilazione del modulo.", tradotto.
	 * Per lo stile del paragrafo utilizza la classe CSS etichettata con la costante self::ERRORS
	 *
	 * @return string stringa col codice xhtml
	*/
	public function openErrors() {
		return $this->open_errors_xhtml;
	}
	
	/**
	 * Imposta il codice XHTML per l'apertura dell'elenco degli errori di compilazione del form
	 * 
	 * @param string $xhtml il codice XHTML
	 * */
	public function setOpenErrorsXHTML($xhtml) {
		$this->open_errors_xhtml = strval($xhtml);
	}
	
	/**
	 * Ritorna il codice xhtml di chiusura dell'elenco dei messaggi di errore
	 *
	 * @return string una stringa col codice xhtml
	*/
	public function closeErrors() {
		return $this->close_errors_xhtml;
	}
	
	/**
	 * Imposta il codice XHTML per la chiusura dell'elenco degli errori di compilazione del form
	 * 
	 * @param string $xhtml il codice XHTML
	 * */
	public function setCloseErrorsXHTML($xhtml) {
		$this->close_errors_xhtml = strval($xhtml);
	}
	
	// ----- Method to draw complex controls composed by multiple fields
	
	/**
	 * Returns a labeled text input or password input field.
	 * 
	 * @param string $control_name the control name
	 * @param string $label the XHTML label text
	 * @param string $value the XHTML value of the text field
	 * @param integer $size the size of the control field
	 * @param integer $maxlength maximum allowed text size
	 * @param boolean $show_as_password TRUE show as a password input field, FALSE show a text input field
	 * @param boolean $readonly TRUE the field is read-only, FALSE otherwise
	 * @param string $label_extra extra attributes to append to the label field
	 * @param string $text_extra extra attributes to append to the text field
	 * @param array $extra associative array of extra parameters to use when creating the input fields
	 * @return string the XHTML string of the desired input field
	 * */
	public function labeledTextInput($control_name, $label, $value, $size, $maxlength, $show_as_password = FALSE, $readonly = FALSE, $label_extra = '', $text_extra = '', $extra = array()) {
		return $this->label($label, $control_name, $label_extra).' '.
		($show_as_password ?
		$this->text($control_name, $value, $size, $maxlength, $readonly, $text_extra)
		:
		$this->password($control_name, $value, $size, $maxlength, $readonly, $text_extra)
		);
	}
	
	/**
	 * Returns a labeled text area
	 * 
	 * @param string $control_name the control name
	 * @param string $label the XHTML label text
	 * @param string $value the XHTML value of the textarea
	 * @param integer $rows rows size
	 * @param integer $cols columns size
	 * @param boolean $readonly TRUE the field is read-only, FALSE otherwise
	 * @param string $label_extra extra attributes to append to the label field
	 * @param string $text_extra extra attributes to append to the text field
	 * @param array $extra associative array of extra parameters to use when creating the input fields
	 * @return string the XHTML string of the desired input field
	 * */
	public function labeledTextarea($control_name, $label, $value, $rows, $cols, $readonly = FALSE, $label_extra = '', $textarea_extra = '', $extra = array()) {
		return $this->label($label, $control_name, $label_extra).' '.textarea($control_name, $value, $rows, $cols, $readonly, $textarea_extra = '');
	}
	
	/**
	 * Returns a labeled checkbox.
	 * 
	 * Please remember that a checkbox (especially if grouped) has special
	 * naming conventions (see {@link BaseFormView::setControlNameStyle}).
	 * 
	 * @param string $name checkbox name
	 * @param string $text the text label
	 * @param string $value the checkbox value
	 * @param boolean $is_checked TRUE the checkbox is checked, FALSE otherwise
	 * @param boolean $readonly TRUE the checkbox is readonly, FALSE otherwise
	 * @param string $label_extra extra attributes to append to the label field
	 * @param string $checkbox_extra extra attributes to append to the text field
	 * @param array $extra associative array of extra parameters to use when creating the input fields
	 * @return string the XHTML string of the desired input field
	 * */
	public function labeledCheckbox($name, $text, $value, $is_checked = FALSE, $readonly = FALSE, $label_extra = '', $checkbox_extra = '', $extra = array() ) {
		return $this->label($text.' '.$this->checkbox($name, $is_checked, $value, $readonly, $checkbox_extra), '', $label_extra);
	}
	
	/**
	 * Returns a labeled radio button.
	 * 
	 * Please remember that a radio button (especially if grouped) has special
	 * naming conventions (see {@link BaseFormView::setControlNameStyle}).
	 * 
	 * @param string $name checkbox name
	 * @param string $text the text label
	 * @param string $value the checkbox value
	 * @param boolean $is_checked TRUE the radio is checked, FALSE otherwise
	 * @param boolean $readonly TRUE the radio is readonly, FALSE otherwise
	 * @param string $label_extra extra attributes to append to the label field
	 * @param string $radio_extra extra attributes to append to the text field
	 * @param array $extra associative array of extra parameters to use when creating the input fields
	 * @return string the XHTML string of the desired input field
	 * */
	public function labeledRadio($name, $text, $value, $is_checked = FALSE, $readonly = FALSE, $label_extra = '', $radio_extra = '', $extra = array() ) {
		return $this->label($text.' '.$this->radio($name, $is_checked, $value, $readonly, $radio_extra), '', $label_extra);
	}
	
	public function oneLine($fields) {
		return '<div>'.$fields."</div>\n";
	}
	
	
	// ----- Metodi per il disegno dei controlli
	/**
	 * Ritorna un campo "select", usare {@link option()} per generare i campi interni
	 * 
	 * @param string $name nome del campo
	 * @param string $options stringa coi campi <option>
	 * @param boolean $multiple TRUE crea un selettore multiplo, FALSE selettore singolo
	 * @param string $append stringa con gli attributi da aggiungere al tag
	 * @return string codice XHTML col campo select
	*/
	public function select($name, $options, $multiple = FALSE, $append = '') {
		return "<select ".$this->form_view->nameCtrl($name).($multiple?' multiple="multiple"':'')."{$append}>\n".$options."</select>\n";
	}

	/**
	 * Ritorna i tag di apertura di un campo "select".
	 *
	 * @param string $name nome del campo
	 * @param boolean $multiple TRUE crea un selettore multiplo, FALSE selettore singolo
	 * @param string $append stringa con gli attributi da aggiungere al tag
	 * @return string codice XHTML col campo select
	 * @see selectClose
	*/
	public function selectOpen($name, $multiple = FALSE, $append = '') {
		return "<select ".$this->form_view->nameCtrl($name).($multiple?' multiple="multiple"':'')."{$append}>\n";
	}

	/**
	 * Ritorna il tag di chiusura di un campo SELECT.
	 *
	 * @return string il tag di chiusura
	 */
	public function selectClose() {
		return "</select>";
	}
	
	/**
	 * Ritorna un campo <option> compilato con i valori opportuni
	 * 
	 * @param string $value stringa col valore del campo
	 * @param string $label stringa XHTML col testo (l'etichetta) del campo
	 * @param boolean $is_selected indica se il campo sia preselezionato (TRUE) o meno (FALSE)
	 * @return string codice XHTML col campo option
	*/
	public function option($value, $label, $is_selected = FALSE) {
		return "<option value=\"".htmlentities($value)."\"".($is_selected ? ' selected="selected"' : '').">$label</option>\n";
	}

	/**
	 * Ritorna il tag di apertura di un campo <option>.
	 *
	 * @param string $value stringa col valore del campo (va eventualmente convertita in entità HTML valide)
	 * @param boolean $is_selected indica se il campo sia preselezionato (TRUE) o meno (FALSE)
	 * @return string codice XHTML col campo option
	*/
	public function optionOpen($value, $is_selected = FALSE) {
		return "<option value=\"".strval($value)."\"".($is_selected ? ' selected="selected"' : '').">";
	}

	/**
	 * Ritorna il tag di chiusura di un campo <option>.
	 *
	 * @return string il codice XHTML col tag di chiusura
	 */
	public function optionClose() {
		return "</option>\n";
	}
	
	/**
	 * Ritorna l'xhtml per un campo di tipo testo.
	 * 
	 * @param string $name stringa col nome del campo
	 * @param string $value stringa col valore del campo
	 * @param integer $size intero con la dimensione del campo
	 * @param integer $maxlength intero con la dimensione massima del campo
	 * @param boolean $readonly TRUE il campo è a sola lettura, FALSE altrimenti
	 * @param string $extra stringa con attributi extra da aggiungere al campo
	 * @return string una stringa xhtml col campo
	*/
	public function text($name, $value = '', $size = 10, $maxlength = 10, $readonly = FALSE, $extra = '') {
		return $this->makeInput('text', $name, $value, 
			($size > 0 ? ' size="'.strval($size).'"' : '').
			($maxlength > 0 ? ' maxlength="'.strval($maxlength).'"' : '').
			($readonly ? ' readonly="readonly"' : '').$extra
		);
	}
	
	/**
	 * Ritorna l'xhtml per un campo di tipo password.
	 * 
	 * @param string $name stringa col nome del campo
	 * @param string $value stringa col valore del campo
	 * @param integer $size intero con la dimensione del campo
	 * @param integer $maxlength intero con la dimensione massima del campo
	 * @param boolean $readonly TRUE il campo è a sola lettura, FALSE altrimenti
	 * @param string $extra stringa con attributi extra da aggiungere al campo
	 * @return string una stringa xhtml col campo
	*/
	public function password($name, $value = '', $size = 10, $maxlength = 10, $readonly = FALSE, $extra = '') {
		return $this->makeInput('password', $name, $value, 
			($size > 0 ? ' size="'.strval($size).'"' : '').
			($maxlength > 0 ? ' maxlength="'.strval($maxlength).'"' : '').
			($readonly ? ' readonly="readonly"' : '').$extra
		);
	}
	
	/**
	 * Ritorna l'xhtml per un campo di immissione testo.
	 * 
	 * @param string $name stringa col nome del campo
	 * @param string $txt stringa col testo che va nel campo (va convertito in xhtml con htmlentities)
	 * @param integer $rows intero col numero di righe del campo
	 * @param integer $cols intero col numero di colonne del campo
	 * @param boolean $readonly TRUE il campo è a sola lettura, FALSE altrimenti
	 * @param string $extra stringa con attributi extra da aggiungere al campo
	 * @return string una stringa xhtml col campo
	*/
	public function textarea($name, $txt = '', $rows = 5, $cols = 15, $readonly = FALSE, $extra = '') {
		return '<textarea '.$this->form_view->nameCtrl($name).
		(empty($rows) ? '' : ' rows="'.strval($rows).'"').
		(empty($cols) ? '' : ' cols="'.strval($cols).'"').
		($readonly ? ' readonly="readonly"' : '').
		$extra.' >'.$txt.'</textarea>';
	}
	
	/**
	 * Ritorna l'xhtml per una casella di selezione
	 * 
	 * @param string $name stringa col nome del campo
	 * @param boolean $is_checked TRUE il campo è spuntato, FALSE altrimenti
	 * @param string $value stringa valore del campo
	 * @param boolean $readonly TRUE il campo è a sola lettura, FALSE altrimenti
	 * @param string $extra stringa con attributi extra da aggiungere al campo
	 * @return string una stringa xhtml col campo
	*/
	public function checkbox($name, $is_checked = FALSE, $value = '', $readonly = FALSE, $extra = '') {
		return $this->makeInput('checkbox', $name, $value, 
			($is_checked > 0 ? ' checked="checked"' : '').
			($readonly ? ' readonly="readonly"' : '').$extra
		);
	}
	
	/**
	 * Ritorna l'xhtml per una casella di selezione a radio
	 * 
	 * @param string $name stringa col nome del campo
	 * @param boolean $is_checked TRUE il campo è spuntato, FALSE altrimenti
	 * @param string $value stringa valore del campo
	 * @param boolean $readonly TRUE il campo è a sola lettura, FALSE altrimenti
	 * @param string $extra stringa con attributi extra da aggiungere al campo
	 * @return string una stringa xhtml col campo
	*/
	public function radio($name, $is_checked = FALSE, $value = '', $readonly = FALSE, $extra = '') {
		return $this->makeInput('radio', $name, $value, 
			($is_checked > 0 ? ' checked="checked"' : '').
			($readonly ? ' readonly="readonly"' : '').$extra
		);
	}
	
	/**
	 * Ritorna l'xhtml per un campo nascosto
	 * 
	 * @param string $name stringa col nome del campo
	 * @param string $value stringa valore del campo
	 * @param string $extra stringa con attributi extra da aggiungere al campo
	 * @return string una stringa xhtml col campo
	*/
	public function hidden($name, $value = '', $extra = '') {
		return $this->makeInput('hidden', $name, $value, $extra );
	}
	
	/**
	 * Ritorna l'xhtml per un pulsante di invio
	 * 
	 * @param string $name stringa col nome del campo
	 * @param string $value stringa valore del campo
	 * @param boolean $readonly TRUE il campo è a sola lettura, FALSE altrimenti
	 * @param string $extra stringa con attributi extra da aggiungere al campo
	 * @return string una stringa xhtml col campo
	*/
	public function submit($name, $value = '', $readonly = FALSE, $extra = '') {
		return $this->makeInput('submit', $name, $value, ($readonly ? ' readonly="readonly"' : '').$extra);
	}
	
	/**
	 * Ritorna l'xhtml per un pulsante di annullamento inserimento
	 * 
	 * @param string $name stringa col nome del campo
	 * @param string $value stringa valore del campo
	 * @param boolean $readonly TRUE il campo è a sola lettura, FALSE altrimenti
	 * @param string $extra stringa con attributi extra da aggiungere al campo
	 * @return string una stringa xhtml col campo
	*/
	public function reset($name, $value = '', $readonly = FALSE, $extra = '') {
		return $this->makeInput('reset', $name, $value, ($readonly ? ' readonly="readonly"' : '').$extra);
	}
	
	/**
	 * Ritorna l'xhtml per il selettore di file
	 * 
	 * @param string $name stringa col nome del campo
	 * @param string $value stringa valore del campo
	 * @param boolean $readonly TRUE il campo è a sola lettura, FALSE altrimenti
	 * @param string $extra stringa con attributi extra da aggiungere al campo
	 * @return string una stringa xhtml col campo
	*/
	public function file($name, $value = '', $readonly = FALSE, $extra = '') {
		return $this->makeInput('file', $name, $value, ($readonly ? ' readonly="readonly"' : '').$extra);
	}
	
	/**
	 * Ritorna l'xhtml un pulsante immagine
	 * 
	 * @param string $name stringa col nome del campo
	 * @param string $value stringa valore del campo
	 * @param string $src stringa con l'URI dell'immagine
	 * @param string $alt stringa col testo alternativo da utilizzare
	 * @param boolean $readonly TRUE il campo è a sola lettura, FALSE altrimenti
	 * @param string $extra stringa con attributi extra da aggiungere al campo
	 * @return string una stringa xhtml col campo
	*/
	public function image($name, $value = '', $src = '', $alt = '', $readonly = FALSE, $extra = '') {
		return $this->makeInput('image', $name, $value, ' src="'.$src.'"'.
		(empty($alt) ? '' : ' alt="'.$alt.'"').
		($readonly ? ' readonly="readonly"' : '').$extra);
	}
	
		/**
	 * Usata internamente: crea un campo di input
	 * 
	 * @param string $type stringa tipo di campo (text, password, checkbox, e così via)
	 * @param string $name stringa col nome del campo
	 * @param string $value stringa col valore del campo
	 * @param string $extra stringa con gli attributi extra del campo
	 * @return string stringa xhtml col campo
	*/
	protected function makeInput($type, $name, $value, $extra = '') {
		return '<input type="'.$type.'" '.$this->form_view->nameCtrl($name).' value="'.strval($value).'"'.$extra.' />';
	}
	
	/**
	 * Ritorna il codice di una etichetta xhtml
	 * 
	 * @param string $text stringa col codice xhtml dell'etichetta (il corpo della label)
	 * @param string $control_name stringa col nome del controllo a cui questa etichetta si riferisce (se vuota viene ignorato)
	 * @param string $extra string stringa con gli atrtibuti xhtml da aggiungere al tag di apertura
	 * @return string una stringa xhtml con la label
	*/
	public function label($text, $control_name = '', $extra = '') {
		return "<label".
		(empty($control_name) ? '' : ' for="'.$control_name.'"').
		(empty($extra) ? '' : ' '.$extra).">".$text."</label>";
	}
	
	/**
	 * Ritorna il codice xhtml per un pulsante (tag <button>)
	 * Al momento della creazione specificare una delle costanti interne:
	 * BUTTON_TYPE_SUBMIT - Pulsante di invio
	 * BUTTON_TYPE_RESET - Pulsante di reset
	 * BUTTON_TYPE_BUTTON - pulsante generico
	 * 
	 * @param string $name nome del controllo
	 * @param string $value valore generato alla pressione
	 * @param string $body codice xhtml del corpo del pulsante (l'interno del tag)
	 * @param string $type tipo del pulsante, usare una delle costanti BUTTON_TYPE_*
	 * @param string $extra codice xthml degli attributi del tag di apertura
	 * @return string codice xhtml per il pulsante
	*/
	public function button($name, $value, $body = '', $type = self::BUTTON_TYPE_SUBMIT, $extra = '') {
		return '<button '.$this->form_view->nameCtrl($name).
		(empty($value) ? '' : ' value="'.$value.'"').
		(empty($type) ? '' : ' type="'.$type.'"').
		(empty($extra) ? '' : ' '.$extra).'>'.$body.'</button>';
	}
	
	/**
	 * Genera un campo fieldset (tag xhtml <fieldset>)
	 * 
	 * @param string $legend stringa xhtml con la legenda
	 * @param string $body codice xhtml col corpo del fieldset
	 * @param string $extra attributi xhtml da utilizzare col tag di apertura
	 * @return string una stringa xhtml col codice generato
	*/
	public function fieldset($legend, $body = '', $extra = '') {
		return $this->fieldsetOpen($legend, $extra).$body.$this->fieldsetClose();
	}
	
	/**
	 * Genera il tag di apertura di un campo fieldset (tag xhtml <fieldset>)
	 * @param string $legend stringa xhtml con la legenda
	 * @param string $extra attributi xhtml da utilizzare col tag di apertura
	 * @return string una stringa xhtml col codice generato
	*/
	public function fieldsetOpen($legend, $extra = '') {
		return '<fieldset'.(empty($extra) ? '>' : ' '.$extra.'>').'<legend>'.$legend."</legend>\n";
	}
	
	/**
	 * Ritorna il codice XHTML di chiusura di un tag <fieldset>
	 * @return string la stringa col codice XHTML
	 * */
	public function fieldsetClose() {
		return "</fieldset>\n";
	}

	// -------------- WIDGET COMPLESSI
	
	/**
	 * Ritorna il codice XHTML per i controlli (che andranno validati utilizzando DateTimeInputFilter)
	 *
	 * I campi devono rispettare il naming imposto da {@link DateTimeInputFilter}
	 *
	 * @param CDateTime $date
	 * @param FormView $formview
	 * @param string $name nome base dei controlli
	 * @param integer $style stile da usare per il controllo
	 * @return string stringa col codice XHTML
	 * @see DateTimeInputFilter
	 */
	public function getDateTimeInput(CDateTime $date, $name, $style) {
		$s = '';
		
		if ($style & DateTimeFormInput::STYLE_DATE) {
			$dd = range(1,31);
			$dl = array_map(array($this, 'padder'), $dd);
			if ($style & DateTimeFormInput::STYLE_WITHEMPTY_DATE) {
				array_unshift($dd, '-');
				array_unshift($dl, '-');
			}
			$s .= $this->getFormView()->makeSelect($dl, $dd, $name.'_dd', ($date->isEmpty() ? '-' : $date->format('j')), FALSE, $this->getFormView()->accessibility(XHTML::toHTML(tr('Immettere il giorno'))));
			if ($style & (DateTimeFormInput::STYLE_MONTHNAME | DateTimeFormInput::STYLE_MONTHNAME_SHORT) ) {
				$nomi = CDateTime::getMonthNames();
				if ($style & DateTimeFormInput::STYLE_MONTHNAME) {
					foreach($nomi as $k => $v) $nomi[$k] = $v[0];
				} else {
					foreach($nomi as $k => $v) $nomi[$k] = $v[1];
				}
				if ($style & DateTimeFormInput::STYLE_WITHEMPTY_DATE) $nomi = array_merge(array( '-' => '-'), $nomi);
				$s .= $this->getFormView()->makeSelect($nomi, array_keys($nomi), $name.'_mm', ($date->isEmpty() ? '-' : $date->format('n')), FALSE, $this->getFormView()->accessibility(XHTML::toHTML(tr('Immettere il mese'))));

			} else {
				$mm = range(1,12);
				$ml = array_map(array($this, 'padder'), $mm);
				if ($style & DateTimeFormInput::STYLE_WITHEMPTY_DATE) {
					array_unshift($mm, '-');
					array_unshift($ml, '-');
				}
				$s .= ' / '.$this->getFormView()->makeSelect($ml, $mm, $name.'_mm', ($date->isEmpty() ? '-' : $date->format('n')), FALSE, $this->getFormView()->accessibility(XHTML::toHTML(tr('Immettere il mese'))));
				$s .= ' / ';
			}
			$s .= $this->getFormView()->text($name.'_yy', ($date->isEmpty() ? '' : $date->format('Y')), 4, 4, FALSE, $this->getFormView()->accessibility(XHTML::toHTML(tr('Immettere l&#39;anno'))));
			
			if ($style & DateTimeFormInput::STYLE_TIME || $style & DateTimeFormInput::STYLE_TIME_SHORT) $s .= XHTML::toHTML(tr(' alle ore '));
		}

		if ($style & DateTimeFormInput::STYLE_TIME || $style & DateTimeFormInput::STYLE_TIME_SHORT) {
			$hh = array_map(array($this, 'padder'), range(0,23));
			$m = array_map(array($this, 'padder'), range(0,59));
			$ss = array_map(array($this, 'padder'), range(0,59) );
			if ($style & DateTimeFormInput::STYLE_WITHEMPTY_TIME) {
				array_unshift($hh, '-');
				array_unshift($m, '-');
				array_unshift($ss, '-');
			}
			$s .= $this->getFormView()->makeSelect($hh, $hh, $name.'_hh', ($date->isEmpty() ? '-' : $date->format('H')), FALSE, $this->getFormView()->accessibility(tr('Immettere l&#39;ora'), '') );
			$s .= ' : '.$this->getFormView()->makeSelect($m, $m, $name.'_ii', ($date->isEmpty() ? '-' : $date->format('i')), FALSE, $this->getFormView()->accessibility(tr('Immettere i minuti'), '') );
			if ($style & DateTimeFormInput::STYLE_TIME) $s .= ' : '.$this->getFormView()->makeSelect($ss, $ss, $name.'_ss', ($date->isEmpty() ? '-' : $date->format('s')), FALSE, $this->getFormView()->accessibility(tr('Immettere i secondi'), '') );
		}
		
		return $s;
	}
	
	private function padder($n) {
		return str_pad($n, 2, '0', STR_PAD_LEFT);
	}
	
	/**
	 * DateTimeFormInput: Ritorna il codice javascript per creare una etichetta che
	 * permette di compilare i campi con la data e ora attuali.
	 * 
	 * @param string $label etichetta XHTML da utilizzare
	 * @param string $name nome dei controlli
	 * @param integer $style stile utilizzato dai controlli
	 * @return string il codice javascript
	 * @see DateTimeFormInput
	 * */
	public function getDateTimeInputNowSetter($label, $name, $style) {
		$js = '(function(){ var d = new Date(); var search_element = function(name, v){ var s = document.getElementsByName(name)[0]; if (!s) return; for(var idx = 0; idx < s.options.length; idx++) if (s.options[idx].value == v) s.selectedIndex = s.options[idx].index; };';
		
		if ($style & DateTimeFormInput::STYLE_DATE) {
			$js .= 'search_element(\''.$name.'_dd\', d.getDate());';
			$js .= 'search_element(\''.$name.'_mm\', d.getMonth() + 1);';
			$js .= 'var yy = document.getElementsByName(\''.$name.'_yy\')[0]; if (yy) yy.value = d.getFullYear();';
		}
		if ($style & DateTimeFormInput::STYLE_TIME || $style & DateTimeFormInput::STYLE_TIME_SHORT) {
			$js .= 'search_element(\''.$name.'_hh\', d.getHours());';
			$js .= 'search_element(\''.$name.'_ii\', d.getMinutes());';
			if ($style & DateTimeFormInput::STYLE_TIME) $js .= 'search_element(\''.$name.'_ss\', d.getSeconds());';
		}
		$js .= '})();';
		
		$str = "<a href=\"#\" onclick=\"".$js." return false;\">".$label."</a>";
		
		return $str;
	}
}


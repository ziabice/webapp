<?php
/**
 * (c) 2008-2012 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Un vista standardizzata di un oggetto Form.
 * 
 *
 * Implementa una form standard composta da:
 * <code>
 * +----------------------------+
 * + campi hidden               +
 * +----------------------------+
 * + errori di compilazione     + <- non sempre visualizzata
 * +----------------------------+
 * +                            +
 * + corpo della form           +
 * +                            +
 * +----------------------------+
 * + pulsantiera                + <- non sempre visualizzata
 * +----------------------------+
 * 
 * </code>
 * 
 * I vari elementi sono contenuti in elementi block level:
 * campi hidden: un <div>
 * errori di compilazione: una lista non ordinata, introdotta da un paragrafo di testo
 * pulsantiera: un <div>
 *
 * E' disponibile un oggetto {@link CSS} per gestire lo stile dei singoli
 * elementi, etichettati con le seguenti costanti:
 *
 * FIELDSET - i tag fieldset
 * BUTTONBOX - stile del tag <div> della pulsantiera
 * ERRORS_LIST - la lista di errori
 * ERROR_ITEM - elemento della lista di errori
 * ERRORS - paragrafo di testo prima degli errori
 *
 * Vengono fornite anche delle etichette per stili personalizzati (ovviamente
 * è possibile definirne di proprie):
 *
 * - CUSTOM1
 * - CUSTOM2
 * - CUSTOM3
 * - CUSTOM4
 * - CUSTOM5
 * - CUSTOM6
 *
 * Il {@link CSS} di default viene inizializzato nel metodo {@link setDefaultCSS}.
 *
 * @author Luca Gambetta <l.gambetta@bluenine.it>
*/
class FormView extends BaseFormView {
	
	protected
		$_default_error_message, // Messaggio di errore predefinito
		$show_buttonbox = TRUE; // flag che indica se nel rendering debba mostrare la button box
	
	/**
	 * Costruisce l'oggetto.
	 *
	 * Imposta uno stile CSS di defualt usando {@link setDefaultCSS}
	 * 
	 * @param boolean $showerrors TRUE visualizza la zona con gli errori, FALSE non visualizzare
	 * @param boolean $show_buttonbox TRUE mostra la pulsantiera, FALSE altrmenti
	 * @param integer $control_name_style stile dei nomi dei controlli
	*/
	public function __construct($showerrors = TRUE, $show_buttonbox = TRUE, $control_name_style = self::CTRL_NAME_BOTH) {
		parent::__construct($control_name_style);
		$this->setShowErrorMsg($showerrors);
		$this->setShowButtonBox($show_buttonbox);
	}

	/**
	 * Attiva o inibisce la visualizzazione della barra coi pulsanti a fondo form
	 * @param boolean $show TRUE mostra la barra, FALSE non mostrare
	 * */
	public function setShowButtonBox($show = TRUE) {
		$this->show_buttonbox = $show;
	}

	/**
	 * Informa se si debba mostrare o meno la pulsantiera a fondo form
	 * @return boolean TRUE se deve mostrare la pulsantiera , FALSE altrimenti
	*/
	public function getShowButtonBox() {
		return $this->show_buttonbox;
	}
	
	/**
	 * Rende inutilizzabile la form a seguito di una serie di errori irrecuperabili.
	 * 
	 * Una form disabilitata è una form che non mostra il contenuto, nè l'eventuale pulsantiera,
	 * ma un messaggio di errore.
	 *
	 * Una form disabilitata è in modalità a sola lettura e bisogna
	 * sbloccarla usando {@see setReadOnly}.
	 * 
	 * @param string $errormessage stringa XHTML col messaggio di errore
	 * @param boolean $clearcontents TRUE rimuove tutto il contenuto del corpo e lascia solo il messaggio, FALSE altrimenti
	 */
	public function disableForm($errormessage, $clearcontents = TRUE) {
		$this->setReadOnly(FALSE);
		
		if ($clearcontents) $this->clearBody();
		
		$this->setShowButtonBox(FALSE);
		$this->setHTML($errormessage);
		
		$this->setReadOnly(TRUE);
	}
	
	/**
	 * Rende inutilizzabile la form a seguito di una serie di errori irrecuperabili.
	 * 
	 * Cancella il corpo della form e lo sostituisce con i messaggi di errore.
	 * Per mostrare gli errori utilizza di default {@see renderErrors}.
	 * 
	 * @see disableForm
	 * @see renderErrors
	 */
	public function disableFormWithErrors() {
		$this->disableForm( $this->renderErrors(), TRUE );
	}

}


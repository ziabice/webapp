<?php
/**
 * (c) 2008, 2009 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */


/**
 * Unisce una combo box con un pulsante di azione
 * mostra un controllo composto da una combo box seguito da un pulsante di azione.
 * L'activator è quello generato dal nome del pulsante
 **/
class FormActionButton extends FormButton {
	protected
		$button, 
		$action_name, 
		$labels, 
		$values, 
		$combo_accel, 
		$presel = NULL,
		$combo_extra = '';

		/**
		 * 
		 * @param FormButton $button il pulsante
		 * @param string $action_name nome del controllo combo
		 * @param array $labels stringhe con le etichette delle azioni
		 * @param array $values valori da usare per le etichette (corrispondenza 1:1)
		 * @param mixed $presel valore preselezionato della combo
		 * @param char $combo_accel carattere col tasto di accesso rapido della combo
		 * 
		 */
	public function __construct(FormButton $button, $action_name, $labels, $values, $presel = NULL, $combo_accel = '') {
		$this->button = $button;
		
		$this->action_name = $action_name;
		$this->labels = $labels;
		$this->values = $values;
		$this->presel = $presel;
		$this->combo_accel = $combo_accel;
		parent::__construct($button->getName(), $button->getLabel(), $button->getAccessKey());
	}

	public function addComboExtra($txt) {
		$this->combo_extra .= $txt;
	}

	public function render() {
		return $this->getFormView()->makeSelect($this->labels, $this->values, $this->action_name, $this->presel, FALSE, $this->combo_extra).
		' '.$this->button->render();
	}
	
	protected function onFormAttach() {
		$this->button->setForm($this->getForm());
	}

	protected function onFormViewAttach() {
		$this->button->setFormView($this->getFormView());
		
		
	}
}

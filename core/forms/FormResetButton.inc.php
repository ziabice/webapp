<?php
/**
 * (c) 2008, 2009 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Un pulsante di reset del modulo.
*/
class FormResetButton extends FormButton {
	public function getActivator() {
		return '';
	}

	public function render() {
		return $this->form_view->button(
			$this->getName(),
			'',
			$this->getLabel(),
			BaseFormView::BUTTON_TYPE_RESET,
			$this->form_view->accessibility(XHTML::toHTML($this->getTitle()), '', $this->getAccessKey()).$this->extra
		);
	}
}


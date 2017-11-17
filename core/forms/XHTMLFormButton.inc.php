<?php
/**
 * (c) 2008, 2009 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Un finto pulsante per poter aggiungere codice XHTML alla
 * button box di una form
*/
class XHTMLFormButton extends FormButton {
	
	protected
		$xhtml;

	/**
	 * Inizializza l'oggetto
	 *
	 * @param string $xhtml il codice XHTML
	 * @param string $name nome del finto pulsante
	 */
	public function __construct($xhtml, $name = '') {
		parent::__construct($name,'', '', '', '');
		$this->xhtml = $xhtml;
	}
	
	public function render() {
		return $this->xhtml;
	}
}

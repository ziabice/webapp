<?php
/**
 * (c) 2008, 2009 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Un pulsante di submit di tipo immagine.
 *
 * Ricordarsi che nella richiesta il nome di un pulsante imagine viene
 * ritornato a seconda del browser scomposto in parti.
 *
 * Se 'foo' è il nome, nella successiva richiesta potrebbero essere presenti
 * 'foo', 'foo_x' e 'foo_y'.
*/
class FormImageButton extends FormButton {
	protected
		$alt,	
		$src,
		$value;

	/**
	 * Inizializza il pulsante.
	 * @param string $name nome del pulsante
	 * @param string $src URL dell'immagine
	 * @param string $alt testo alternativo dell'immagine
	 * @param string $value valore associato all'immagine
	 * @param string $extra attributi extra per il pulsante
	 */
	public function __construct($name, $src, $alt, $value = '', $extra = '') {
		$this->src = $src;
		$this->value = $value;
		$this->alt = $alt;
		parent::__construct($name, '', '', '', $extra);
	}
	
	public function getActivator() {
		return array($this->name, $this->name.'_x', $this->name.'_y');
	}
	
	public function render() {
		return $this->getFormView()->image(
			htmlspecialchars($this->getName()),
			$this->value,
			$this->src,
			$this->alt,
			FALSE,
			$this->getFormView()->accessibility(XHTML::toHTML($this->getTitle()), '', $this->getAccessKey()).$this->extra
		);
	}
}

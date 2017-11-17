<?php
/**
 * (c) 2008, 2009, 2010 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Restituisce la rappresentazione di un albero in un campo SELECT di una form.
 *
 * @author Luca Gambetta <l.gambetta@bluenine.it>
 */
class TreeNodeSelectView extends TreeNodeViewAdapter {
	public
		$name, $multiple, $append,
		$formview, $prepend_options, $append_options;

	/**
	 * Costruisce specificando un view.
	 *
	 * @param BaseFormView $formview sorgente per il disegno del controllo
	 * @param string $name nome del campo select
	 * @param boolean $multiple TRUE accetta input multiplo, FALSE altrimenti
	 * @param string $append_attr attributi aggiuntivi da utilizzare per il tag di apertura <SELECT>
	 * @param CSS $css il gestore di stili specifico
	 * @param string $prepend_options stringa con l'XHTML per campi <OPTION> da aggiungere prima del contenuto generato
	 * @param string $append_options  stringa con l'XHTML per campi <OPTION> da aggiungere dopo il contenuto generato
	 */
	public function __construct(BaseFormView $formview, $name, $multiple = FALSE, $append_attr = '', $prepend_options = '', $append_options = '') {
		$this->formview = $formview;
		$this->name = $name;
		$this->multiple = $multiple;
		$this->append = $append_attr;
		$this->append_options = $append_options;
		$this->prepend_options = $prepend_options;
		parent::__construct();
	}

	public function openFold(TreeNodeInterface $node, TreeNodeSearchInterface $search) {
		if ($search->deeplevel() == 0) {
			return $this->formview->selectOpen($this->name, $this->multiple, $this->append).$this->prepend_options;
		}
		return '';
	}

	public function openNode(TreeNodeInterface $node, TreeNodeSearchInterface $search) {
		return $this->formview->optionOpen( $this->getOptionValue($node), $search->isSelected($node) );
	}

	public function closeFold(TreeNodeInterface $node, TreeNodeSearchInterface $search) {
		if ($search->deeplevel() == 0) return $this->append_options.$this->formview->selectClose();
		return '';
	}

	public function closeNode(TreeNodeInterface $node, TreeNodeSearchInterface $search) {
		return $this->formview->optionClose();
	}

	public function renderNode(TreeNodeInterface $node, TreeNodeSearchInterface $search) {
		return str_repeat("&nbsp;", $search->deeplevel()).$this->getOptionLabel($node);
	}

	/**
	 * Ritorna la stringa da usare come valore di un campo <option>.
	 * Normalmente ritorna l'ID dell'oggetto
	 *
	 * @param Composite $node l'oggetto su cui operare
	 * @return string una stringa XHTML col valore
	 */
	public function getOptionValue(TreeNodeInterface $node) {
		return XHTML::toHTML($node->getID());
	}

	/**
	 * Dato un oggetto ne ritorna una rappresentazione xhtml
	 * E' la stringa da mostrare per l'oggetto
	 * @return string una stringa xhtml con l'etichetta
	*/
	public function getOptionLabel(TreeNodeInterface $node) {
		return XHTML::toHTML(strval($node));
	}
    
}


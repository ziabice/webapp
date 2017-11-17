<?php
/**
 * (c) 2012 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Restituisce la rappresentazione di un albero in una lista composta da checkbox (o radio button).
 *
 * @author Luca Gambetta <l.gambetta@bluenine.it>
 */
class TreeNodeCheckboxListView extends TreeNodeListView {
	public
		$name, $is_radio, $append,
		$formview, $prepend_items, $append_items;

	/**
	 * Costruisce specificando un view.
	 *
	 * Gli elementi da prependere o appendere vengono aggiunti solo alla lista principale (profondità 0).
	 * 
	 * @param BaseFormView $formview sorgente per il disegno del controllo
	 * @param string $name nome del campo 
	 * @param boolean $is_radio TRUE mostra dei controlli radio, FALSE delle checkbox
	 * @param string $prepend_items stringa con l'XHTML degli elementi <LI> da aggiungere prima
	 * @param string $append_items  stringa con l'XHTML degli elementi <LI> da aggiungere dopo
	 */
	public function __construct(BaseFormView $formview, $name, $is_radio = FALSE, $prepend_items = '', $append_items = '') {
		$this->formview = $formview;
		$this->name = $name;
		$this->is_radio = $is_radio;
		$this->append_items = $append_items;
		$this->prepend_items = $prepend_items;
		parent::__construct();
	}

	public function openFold(TreeNodeInterface $node, TreeNodeSearchInterface $search) {
		if ($search->deeplevel() == 0) {
			return parent::openFold($node, $search).$this->prepend_items;
		} else {
			return parent::openFold($node, $search);
		}
	}
	
	public function closeFold(TreeNodeInterface $node, TreeNodeSearchInterface $search) {
		
		if ($search->deeplevel() == 0) {
			return $this->append_items.parent::closeFold($node, $search);
		} else {
			return parent::closeFold($node, $search);
		}
	}
	
	public function renderNode(TreeNodeInterface $node, TreeNodeSearchInterface $search) {
		$ns = $this->formview->getControlNameStyle();
		$this->formview->setControlNameStyle(BaseFormView::CTRL_NAME_NAME_ONLY);
		if ($this->is_radio) $ctrl = $this->formview->radio($this->name.'[]', $search->isSelected($node), $this->getValue($node, $search), FALSE, $this->addExtra($node, $search));
		else $ctrl =  $this->formview->checkbox($this->name.'[]', $search->isSelected($node), $this->getValue($node, $search), FALSE, $this->addExtra($node, $search));
		$this->formview->setControlNameStyle($ns);
		
		return $this->formview->label( $ctrl.' '.$this->getLabel($node, $search) );
	}
	
	/**
	 * Ritorna il codice XHTML con l'etichetta da usare per i controlli generati.
	 * 
	 * @param TreeNodeInterface $node il nodo su cui operare
	 * @param TreeNodeSearchInterface $search il selettore
	 */
	public function getLabel(TreeNodeInterface $node, TreeNodeSearchInterface $search) {
		return strval($node->getID());
	}
	
	/**
	 * Ritorna il valore da utilizzare per i controlli radio/checkbox.
	 * 
	 * NB: il valore dovrebbe essere una stringa XHTML valida.
	 * 
	 * @param TreeNodeInterface $node il nodo su cui operare
	 * @param TreeNodeSearchInterface $search il selettore
	 * @return string il valore da utilizzare
	 */
	public function getValue(TreeNodeInterface $node, TreeNodeSearchInterface $search) {
		return strval($node->getID());
	}
	
	/**
	 * Ritorna il codice degli attributi extra da aggiungere al controllo generato.
	 * 
	 * @param TreeNodeInterface $node il nodo su cui opera
	 * @param TreeNodeSearchInterface $search il selettore
	 * @return string una stringa col codice degli attributi
	 */
	public function addExtra(TreeNodeInterface $node, TreeNodeSearchInterface $search) {
		return ""; 
	}
	
	
    
}


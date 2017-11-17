<?php
/**
 * (c) 2008-2011 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Restituisce una rappresentazione di un albero usando stili CSS.
 *
 * @author Luca Gambetta <l.gambetta@bluenine.it>
 */
class TreeNodeViewAdapter implements TreeNodeViewInterface {
	const
		TREE_NODE_CSS_MAIN_FOLD_OPEN = 'TREE_NODE_CSS_MAIN_FOLD_OPEN',
		TREE_NODE_CSS_MAIN_FOLD_CLOSE = 'TREE_NODE_CSS_MAIN_FOLD_CLOSE',
			
		TREE_NODE_CSS_FOLD_OPEN = 'TREE_NODE_CSS_FOLD_OPEN',
		TREE_NODE_CSS_FOLD_CLOSE = 'TREE_NODE_CSS_FOLD_CLOSE',

		TREE_NODE_CSS_NODE_OPEN = 'TREE_NODE_CSS_NODE_OPEN',
		TREE_NODE_CSS_NODE_CLOSE = 'TREE_NODE_CSS_NODE_CLOSE',

		TREE_NODE_CSS_SELECTED_ITEM_NODE_OPEN = 'TREE_NODE_CSS_SELECTED_ITEM_NODE_OPEN',
		TREE_NODE_CSS_SELECTED_ITEM_NODE_CLOSE = 'TREE_NODE_CSS_SELECTED_ITEM_NODE_CLOSE',

		TREE_NODE_CSS_FIRST_ITEM_NODE_OPEN = 'TREE_NODE_CSS_FIRST_ITEM_NODE_OPEN',
		TREE_NODE_CSS_FIRST_ITEM_NODE_CLOSE = 'TREE_NODE_CSS_FIRST_ITEM_NODE_CLOSE',
		TREE_NODE_CSS_LAST_ITEM_NODE_OPEN = 'TREE_NODE_CSS_LAST_ITEM_NODE_OPEN',
		TREE_NODE_CSS_LAST_ITEM_NODE_CLOSE = 'TREE_NODE_CSS_LAST_ITEM_NODE_CLOSE',

		TREE_NODE_CSS_FIRST_ITEM_SELECTED_NODE_OPEN = 'TREE_NODE_CSS_FIRST_ITEM_SELECTED_NODE_OPEN',
		TREE_NODE_CSS_FIRST_ITEM_SELECTED_NODE_CLOSE = 'TREE_NODE_CSS_FIRST_ITEM_SELECTED_NODE_CLOSE',
		TREE_NODE_CSS_LAST_ITEM_SELECTED_NODE_OPEN = 'TREE_NODE_CSS_LAST_ITEM_SELECTED_NODE_OPEN',
		TREE_NODE_CSS_LAST_ITEM_SELECTED_NODE_CLOSE = 'TREE_NODE_CSS_LAST_ITEM_SELECTED_NODE_CLOSE';

    protected
		$css;

	/**
	 * Costruisce impostando il CSS attraverso il PageLayout attuale.
	 * 
	 * 
	 * */
	public function  __construct() {
		$this->initializeCSS();
	}
	
	/**
	 * Inizializza gli stili CSS.
	 * 
	 * Usato dal costruttore.
	 */
	protected function initializeCSS() {
		if (WebApp::getInstance()->hasPageLayout()) {
			$this->setCSS( WebApp::getInstance()->getPageLayout()->getWidgetStyle()->getCSS($this) );
		} else {
			$this->setCSS(new CSS());
		}
	}

	/**
	 * Imposta gli stili CSS.
	 *
	 * @param CSS $css gli stili CSS
	 */
	public function setCSS(CSS $css) {
		$this->css = $css;
	}

	/**
	 * Ritorna gli stili CSS
	 * 
	 * @return CSS gli stili CSS impostati
	 */
	public function getCSS() {
		return $this->css;
	}

	// ------------ Interfaccia

	public function closeFold(TreeNodeInterface $node, TreeNodeSearchInterface $search) {
		return '';
	}

	public function closeNode(TreeNodeInterface $node, TreeNodeSearchInterface $search) {
		return '';
	}

	public function openFold(TreeNodeInterface $node, TreeNodeSearchInterface $search) {
		return '';
	}

	public function openNode(TreeNodeInterface $node, TreeNodeSearchInterface $search) {
		return '';
	}

	public function renderNode(TreeNodeInterface $node, TreeNodeSearchInterface $search) {
		return $node->getID();
	}
}

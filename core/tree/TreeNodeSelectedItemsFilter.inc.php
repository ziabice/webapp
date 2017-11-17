<?php

/**
 * (c) 2012 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Questo filtro accetta solo gli elementi che sono selezionati.
 * 
 * Se un nodo è tra gli elementi selezionati allora viene accettato.
 * 
 * @author Luca Gambetta <l.gambetta@bluenine.it>
 *
 */
class TreeNodeSelectedItemsFilter extends TreeNodeSearchAdapter {
	
	/**
	 * Inizializza selezionando gli elementi.
	 * 
	 * @param array $items un array di ID di elementi
	 */
	public function  __construct($items) {
		parent::__construct();
		$this->selectItems($items);
	}
	
	public function accept(TreeNodeInterface $node) {
		if (is_object($node)) {
			return in_array($node->getID(), $this->sel_items);
		}
		return FALSE;
	}
	
}


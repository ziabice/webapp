<?php
/**
 * (c) 2008, 2009, 2010 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Adapter di TreeNodeSearchInterface
 *
 * @author Luca Gambetta <l.gambetta@bluenine.it>
 */
class TreeNodeSearchAdapter implements TreeNodeSearchInterface {

	public
		$sel_items = array(),
		$is_first_node = FALSE, $is_last_node = FALSE,
		$deeplevel = 0;

    
	public function  __construct() {
		$this->sel_items = array();
		$this->is_first_node = FALSE;
		$this->is_last_node = FALSE;
		$this->deeplevel = 0;
	}

	public function accept(TreeNodeInterface $node) {
		return TRUE;
	}

	public function moreDeep() {
		$this->deeplevel++;
	}
	
	public function lessDeep() {
		$this->deeplevel--;
	}

	public function deeplevel() {
		return $this->deeplevel;
	}

	public function isFirstNode() {
		return $this->is_first_node;
	}

	public function isLastNode() {
		return $this->is_last_node;
	}


	public function setIsFirstNode($is_first) {
		$this->is_first_node = $is_first;
	}

	public function setIsLastNode($is_last) {
		$this->is_last_node = $is_last;
	}

	public function deselectAll() {
		$this->sel_items = array();
	}

	public function isSelected(TreeNodeInterface $item) {
		return in_array($item->getID(), $this->sel_items);
	}

	public function selectItems($items) {
		$this->sel_items = array_merge($this->sel_items, $items);
	}
}


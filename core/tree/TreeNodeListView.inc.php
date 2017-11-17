<?php
/**
 * (c) 2008, 2009, 2010 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Restituisce una rappresentazione di un albero in una lista non ordinata.
 *
 * @author Luca Gambetta <l.gambetta@bluenine.it>
 */
class TreeNodeListView extends TreeNodeViewAdapter {

	public function openFold(TreeNodeInterface $node, TreeNodeSearchInterface $search) {
		if ($search->deeplevel() == 0) {
			return "<ul".$this->getCSS()->getAttr(self::TREE_NODE_CSS_MAIN_FOLD_OPEN).">";
		} else {
			return "<ul".$this->getCSS()->getAttr(self::TREE_NODE_CSS_FOLD_OPEN).">";
		}
	}
	
	public function closeFold(TreeNodeInterface $node, TreeNodeSearchInterface $search) {
		return "</ul>";
	}

	public function openNode(TreeNodeInterface $node, TreeNodeSearchInterface $search) {
		return "<li".$this->addOpenNodeAttr($node, $search).">";
	}

	public function closeNode(TreeNodeInterface $node, TreeNodeSearchInterface $search) {
		return "</li>";
	}

	public function renderNode(TreeNodeInterface $node, TreeNodeSearchInterface $search) {
		return strval( $node->getID() );
	}
	
	/**
	 * Ritorna gli attributi aggiuntivi da aggiungere al tag di apertura di un nodo.
	 * 
	 * @param TreeNodeInterface $node il nodo su cui opera
	 * @param TreeNodeSearchInterface $search il filtro di ricerca
	 * @return string gli attributi del tag per il nodo
	 * @see openNode
	 * 
	 */
	public function addOpenNodeAttr(TreeNodeInterface $node, TreeNodeSearchInterface $search) {
		$s = ' ';
		if ($search->isSelected($node)) {
			if ($search->isFirstNode()) $s .= $this->getCSS()->getAttr(self::TREE_NODE_CSS_FIRST_ITEM_SELECTED_NODE_OPEN);
			elseif ($search->isLastNode()) $s .= $this->getCSS()->getAttr(self::TREE_NODE_CSS_LAST_ITEM_SELECTED_NODE_OPEN);
			else $s .= $this->getCSS()->getAttr(self::TREE_NODE_CSS_SELECTED_ITEM_NODE_OPEN);
		} else {
			if ($search->isFirstNode()) $s .= $this->getCSS()->getAttr(self::TREE_NODE_CSS_FIRST_ITEM_NODE_OPEN);
			elseif ($search->isLastNode()) $s .= $this->getCSS()->getAttr(self::TREE_NODE_CSS_LAST_ITEM_NODE_OPEN);
			else $s .= $this->getCSS()->getAttr(self::TREE_NODE_CSS_NODE_OPEN);
		}
		return $s;
	}
}

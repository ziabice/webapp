<?php
/**
 * (c) 2008, 2009, 2010 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Restituisce la rappresentazione di un albero in una lista ordinata.
 *
 * @author Luca Gambetta <l.gambetta@bluenine.it>
 */
class TreeNodeOrderedListView extends TreeNodeListView {
	public function openFold(TreeNodeInterface $node, TreeNodeSearchInterface $search) {
		if ($search->deeplevel() == 0) {
			return "<ol".$this->getCSS()->getAttr(self::TREE_NODE_CSS_MAIN_FOLD_OPEN).">";
		} else {
			return "<ol".$this->getCSS()->getAttr(self::TREE_NODE_CSS_FOLD_OPEN).">";
		}
	}

	public function closeFold(TreeNodeInterface $node, TreeNodeSearchInterface $search) {
		return "</ol>";
	}
}


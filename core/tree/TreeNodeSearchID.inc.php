<?php
/**
 * (c) 2008, 2009, 2010 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Cerca un nodo in base al suo ID.
 *
 * @author Luca Gambetta <l.gambetta@bluenine.it>
 */
class TreeNodeSearchID extends TreeNodeSearchAdapter {

	protected 
		$node_id; // ID del nodo da cercare

	/**
	 * Costuisce con l'ID del nodo da cercare.
	 *
	 * @param mixed $id ID del nodo da cercare (di solito un intero)
	 */
	public function  __construct($id) {
		$this->node_id = $id;
		parent::__construct();
	}

	public function accept(TreeNodeInterface $node) {
		if (!is_null($node)) {
			return ($node->getID() == $this->node_id);
		}
		return FALSE;
	}
}


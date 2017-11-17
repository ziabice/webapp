<?php
/**
 * (c) 2010-2012 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Implementa l'interfaccia TreeNodeInterface.
 *
 * Aggiunge anche il nodo parent per facilitare le operazioni.
 *
 * Utilizzo:
 *
 * Costruire un albero inserendo come primo nodo un nodo vuoto:
 *
 * $tree = new TreeNode();
 *
 * Quindi iniziare a popolare dal sottoalbero sinistro
 *
 * $tree->setLeftmostChild( new TreeNode(...) )->setRightSibling( new TreeNode(...) );
 *
 * Se NodeItem è una classe derivata da TreeNode, l'albero si costruisce così:
 *
 * $tree = new TreeNode(); -- usato TreeNode e non NodeItem
 * $tree->setLeftmostChild( new NodeItem(...) )->setRightsibling( new NodeItem(... ) ); -- inserire poi solo NodeItem
 *
 * @author Luca Gambetta <l.gambetta@bluenine.it>
 */
class TreeNode extends WebAppObject implements TreeNodeInterface {

	public
		$parent_node = NULL, // TreeNode parent
		$leftmost_child = NULL, // TreeNode leftmostchild
		$right_sibling = NULL, // TreeNode rightsibling
		$binded_object = NULL; // Eventuale oggetto collegato


	public function  __construct($id = NULL, $obj = NULL) {
		parent::__construct($id);
		$this->binded_object = $obj;
	}

	/**
	 * Ritorna l'oggetto collegato.
	 *
	 * @return mixed l'oggetto collegato
	 */
	public function getBindedObject() {
		return $this->binded_object;
	}

	/**
	 * Ritorna il nodo parent (genitore) di questo nodo.
	 *
	 * Ritorna NULL se il nodo è la radice dell'albero.
	 *
	 * @return TreeNode il nodo genitore
	 */
	public function parentNode() {
		return $this->parent_node;
	}

	/**
	 * Ritorna il nodo root di un albero.
	 *
	 * @param TreeNode $node il nodo da cui partire
	 * @return TreeNode il nodo root
	 */
	public function getRoot(TreeNode $node) {
		if (is_null($node->parent_node)) return $node;
		else return $this->getRoot($node->parent_node);
	}


	public function append(TreeNodeInterface $n) {
		if (is_null($this->leftmost_child)) return $this->setLeftmostChild($n);
		elseif (is_null($this->leftmost_child->right_sibling)) return $this->leftmost_child->setRightSibling($n);
		else {
			$c = $this->leftmost_child->right_sibling;
			while (!is_null($c)) {
				if (is_null($c->right_sibling)) {
					return $c->setRightSibling($n);
				}
			}
		}
	}

	public function clear() {
		$this->parent_node = NULL;
		$this->leftmost_child = NULL;
		$this->right_sibling = NULL;
	}

	public function deleteNode(TreeNodeSearchInterface $search) {
		$found = $this->getNode($search);
		if (!is_null($found)) {
			if ($found->isRoot()) {
				$this->clear();
			} elseif($found->isLeftmostChild()) {
				$found->parent_node->leftmost_child = $found->right_sibling;
				
				$found->right_sibling = NULL;
			} else {
				$prev = $found->getPreviousSibling();
				$prev->right_sibling = $found->right_sibling;
				
				$found->right_sibling = NULL;
			}
			$found->parent_node = NULL;
		}
		return $found;
	}

	/**
	 * Cerca il nodo indicato.
	 *
	 * Per accettare un nodo usa TreeNodeSearchInterface::accept
	 *
	 * @param TreeNodeSearchInterface $search il filtro di ricerca
	 * @return TreeNodeInterface il nodo cercato o NULL
	 * @see TreeNodeSearchInterface::accept
	 */
	public function getNode(TreeNodeSearchInterface $search) {
		if ($search->accept($this)) {
			return $this;
		} else {
			$c = $this->leftmost_child;
			$ok = FALSE;
			while (!is_null($c) && $ok === FALSE) {
				$ok = $c->getNode($search);
				$c = $c->right_sibling;
			}
			return $ok;
		}
	}

	public function getPreviousSibling() {
		if (is_null($this->parent_node)) return NULL;
		$next = $this->parent_node->leftmost_child->right_sibling;
		if (is_null($next)) return NULL;
		if ($this->isSameNode($next, $this)) {
			return $this->parent_node->leftmost_child;
		} else {
			while ( !is_null($next->right_sibling) ) {
				if ($this->isSameNode($next->right_sibling, $this)) {
					return $next;
				}
				$next = $next->right_sibling;
			}
		}
	}

	public function isSameNode(TreeNodeInterface $node1, TreeNodeInterface $node2) {
		if (is_null($node1) || is_null($node2)) return FALSE;
		else return ($node1->getID() == $node2->getID());
	}

	public function insert(TreeNodeInterface $n) {
		$l = $this->leftmost_child;
		$this->leftmost_child = $n;
		$this->leftmost_child->right_sibling = $l;
		$this->leftmost_child->parent_node = $l->parent_node;
		return $this->leftmost_child;
	}

	public function isEmpty() {
		return is_null($this->leftmost_child);
	}

	public function isLeftmostChild() {
		if (!is_null($this->parent_node)) {
			return $this->isSameNode($this->parent_node->leftmost_child, $this);
		}
		return FALSE;
	}

	public function isRoot() {
		return is_null($this->parent_node);
	}

	public function leftmostChild() {
		return $this->leftmost_child;
	}

	public function rightSibling() {
		return $this->right_sibling;
	}

	public function setLeftmostChild(TreeNodeInterface $n) {
		$this->leftmost_child = $n;
		$this->leftmost_child->parent_node = $this;
		return $this->leftmost_child;
	}

	public function setRightSibling(TreeNodeInterface $n) {
		$this->right_sibling = $n;
		$this->right_sibling->parent_node = $this->parent_node;
		return $this->right_sibling;
	}
	
	/**
	 * Inserisce un nodo come fratello destro.
	 * 
	 * Il nodo verrà inserito prima degli altri fratelli destri.
	 * 
	 * @param TreeNode $n il nodo da inserire
	 * @return TreeNode il nodo inserito
	 */
	public function insertRightSibling(TreeNode $n) {
		$n->right_sibling = $this->right_sibling;
		$n->parent_node = $this->parent_node;
		$this->right_sibling = $n;
		return $n;
	}

	/**
	 * Fornisce il percorso verso un nodo usando i nodi parent.
	 * 
	 * @param TreeNodeSearchInterface $where
	 * @return array un array di TreeNode 
	 */
	public function breadcrumbs(TreeNodeSearchInterface $where) {
		$out = array();

		$n = $this->getNode($where);
		if ($n !== FALSE) {
			$out[] = $n;
			$c = $n->parent_node;
			while (!is_null($c)) {
				array_unshift($out, $c);
				$c = $c->parent_node;
			}
		}

		return $out;
	}

	/**
	 * Sistema in un albero le corrispondenze padre-figlio tra i nodi.
	 *
	 * @param TreeNode $n l'albero su cui lavorare
	 * @param TreeNode $parent_node il nodo parent da associare
	*/
	public static function fixParents(TreeNode $n, $parent_node = NULL) {
		$n->parent_node = $parent_node;
		$parent_node = $n;
		$c = $n->leftmost_child;
		while (!is_null($c)) {
			TreeNode::fixParents($c, $parent_node);
			$c = $c->right_sibling;
		}
	}

	/**
	 * Ritorna tutti i figli di un nodo in un array.
	 *
	 * I figli vengono ritornati nel corretto ordine sinistra-destra, quindi
	 * array[0] indica il leftmost child.
	 *
	 * Se il nodo non ha figli ritorna un array vuoto.
	 *
	 * @param TreeNode $node il nodo da cui estrarre i figli
	 * @return array tutti i figli
	 */
	public function getChildren(TreeNode $node) {
		$out = array();
		if (!is_null($node)) {
			$c = $node->leftmost_child;
			while (!is_null($c)) {
				$out[] = $c;
				$c = $c->right_sibling;
			}
		}
		return $out;
	}
	
	/**
	 * Informa se il nodo sia l'ultimo figlio.
	 * 
	 * @return boolean TRUE se è l'ultimo figlio, FALSE altrimenti
	 */
	public function isLastChild() {
		return is_null($this->right_sibling);
	}
	
	/**
	 * Conta il numero di figli di un nodo.
	 * @return integer il numero di nodi presenti
	 */
	public function countChildren() {
		$n = 0;
		$c = $this->leftmost_child;
		while (!is_null($c)) {
			$n++;
			$n += $c->countChildren();
			$c = $c->rightSibling();
		}
		return $n;
	}
 
}

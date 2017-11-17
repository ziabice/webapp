<?php
/**
 * (c) 2008, 2009, 2010 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Attraversa un albero
 *
 * @author Luca Gambetta <l.gambetta@bluenine.it>
 */
class TreeNodeWalk {
	
	const
		RENDER_FLAT = 10,
		RENDER_FOLDED = 20;
	
	protected static 
		$build_matrix;

	/**
	 * Esegue un attraversamento di tipo preorder invocando la callback.
	 *
	 * La callback deve accettare un parametro di tipo TreeNodeInterface:
	 *
	 * callback(TreeNodeInterface $node)
	 *
	 * @param TreeNodeInterface $n albero su cui operare
	 * @param callback $callback funzione da invocare
	 */
	public static function preorder(TreeNodeInterface $n, $callback) {
		call_user_func($callback, $n);
		$c = $n->leftmostChild();
		while (!is_null($c)) {
			self::preorder($c, $callback);
			$c = $c->rightSibling();
		}
	}
	
	/**
	 * 
	 * Esegue un attraversamento di tipo preorder invocando TreeNodeSearchInterface::accept
	 * sui nodi e usando il valore di ritorno per decidere se continuare o meno
	 * nell'attraversamento.
	 * 
	 * @param TreeNodeInterface $n il nodo su cui operare
	 * @param TreeNodeSearchInterface $search il filtro da usare
	 */
	public static function preorderWalk(TreeNodeInterface $n, TreeNodeSearchInterface $search) {
		if (is_null($n)) return;
		$c = $n->leftmostChild();
		
		$search->setIsFirstNode(TRUE);
		
		while (!is_null($c)) {
			$search->setIsLastNode(is_null($c->rightSibling()));
			
			if ($search->accept($c)) {

				$search->moreDeep();
				self::preorderWalk($c, $search);
				$search->lessDeep();

			}
			$search->setIsFirstNode(FALSE);
			$c = $c->rightSibling();
		}
		
	}

	/**
	 * Esegue un attraversamento di tipo postorder invocando la callback.
	 *
	 * La callback deve accettare un parametro di tipo TreeNodeInterface:
	 *
	 * callback(TreeNodeInterface $node)
	 *
	 * @param TreeNodeInterface $n albero su cui operare
	 * @param callback $callback funzione da invocare
	 */
	public static function postorder(TreeNodeInterface $n, $callback) {
		$c = $n->leftmostChild();
		while (!is_null($c)) {
			self::postorder($c, $callback);
			$c = $c->rightSibling();
		}
		call_user_func($callback, $n);
	}


	/**
	 * Trasforma un albero in una matrice nella forma:
	 *
	 * array(
	 *	array(
	 *		'node_id' => ID del nodo
	 *		'parent_id' => ID del nodo parent (NULL per la radice)
	 *		'leftmostchild' => ID del nodo leftmostchild (o NULL se non presente)
	 *		'rightsibling' => ID del nodo rightsibling (o NULL se non presente)
	 *		'node' => il nodo serializzato usando {@link serializeNode}
	 *	)
	 *	array(
	 *		...
	 *	)
	 *	...
	 * )
	 *
	 * La matrice può essere poi utilizzata per ricostruire l'albero.
	 *
	 * Se l'albero è vuoto ritorna una matrice vuota.
	 * 
	 * @param TreeNodeInterface $n l'albero su cui operare
	 * @return array un array con la matrice
	 * @see serializeNode
	 */
	public function normalize(TreeNodeInterface $n, $parent_node_id = NULL) {
		$c = $n->leftmostChild();
		
		if (is_null($n->parent_node)) {
			$out = array();
			$parent_node_id = NULL;
		} else {
			$out = array( array( 'node_id' => $n->getID(), 'parent_id' => $parent_node_id, 'leftmostchild' => $this->NodeID($n->leftmostChild()), 'rightsibling' => $this->NodeID($n->rightSibling()), 'node' => $this->serializeTreeNode($n) ) );
			$parent_node_id = $this->NodeID($n);
		}
		
		while (!is_null($c)) {
			$out = array_merge($out, $this->normalize($c, $parent_node_id ) );
			$c = $c->rightSibling();
		}
		return $out;
	}

	/**
	 * Ritorna l'ID associato ad un nodo o NULL se il nodo è NULL.
	 * @param TreeNode $n il nodo su cui operare
	 * @return mixed NULL o l'ID del nodo
	 * @see normalize
	 */
	private function NodeID($n = NULL) {
		return is_null($n) ? NULL : $n->getID();
	}

	/**
	 * Serializza un nodo in modo da poter essere poi ricostruito.
	 *
	 * Di default ritorna il nodo stesso.
	 *
	 * Usata da normalize
	 *
	 * @param TreeNode $node il nodo su cui operare
	 * @return mixed la versione serializzata del nodo
	 * @see normalize
	 */
	public function serializeTreeNode(TreeNode $node) {
		return $node;
	}


	/**
	 * Restituisce una rappresentazione testuale dell'albero.
	 *
	 * Viene usato un approccio MVC: il Model è l'albero, il Controller
	 * un oggetto che implementa TreeNodeSearchInterface, il View un oggetto
	 * che implementa TreeNodeViewInterface.
	 *
	 * Viene ritornato un array: se l'albero è vuoto ritorna un array vuoto.
	 * 
	 * I vari elementi vengono disegnati nel view usando un modo, che può essere:
	 * 
	 * RENDER_FLAT - il nodo viene aperto, disegnato e chiuso
	 * RENDER_FOLDED - il nodo viene aperto, disegnato, disegnati i sottonodi, chiuso
	 * 
	 * Il primo va bene ad esempio per disegnare un campo di selezione, mentre il secondo
	 * la struttura dell'albero in una lista ordinata.
	 *
	 * @param TreeNode $node l'albero da elaborare
	 * @param TreeNodeSearchInterface $search il controller
	 * @param TreeNodeViewInterface $view il view
	 * @param integer $mode modo di disegno dei nodi
	 * @return array un array di stringhe
	 */
	public static function render(TreeNode $node, TreeNodeSearchInterface $search, TreeNodeViewInterface $view, $mode = self::RENDER_FOLDED) {
		/*
		if (is_null($node)) return array();

		$c = $node->leftmost_child;
		if (is_null($c)) return array();

		if ($search->accept($c)) {
			$first_node = $c;
			
			$search->setIsFirstNode(TRUE);
			// $search->setIsLastNode(is_null($c->right_sibling));

			// Qui va il view
			$out = array( $view->openFold($c, $search) );

			while (!is_null($c)) {
				$search->setIsLastNode(is_null($c->right_sibling));
				
				if ($mode == self::RENDER_FOLDED) $out[] = $view->openNode($c, $search).$view->renderNode($c, $search);
				else $out[] = $view->openNode($c, $search).$view->renderNode($c, $search, $mode).$view->closeNode($c, $search); 

				$search->moreDeep();
				$out = array_merge($out, self::render( $c, $search, $view, $mode ) );
				$search->lessDeep();
	
				if ($mode == self::RENDER_FOLDED) $out[] = $view->closeNode($c, $search);
								
				$search->setIsFirstNode(FALSE);
				
				$c = $c->right_sibling;
			}

			$out[] = $view->closeFold($first_node, $search);
			
			return $out;
		} else {
			return array();
		}
		*/
		// --------------------- NUOVA IMPLEMENTAZIONE
		if (is_null($node)) return array();
		
		$c = $node->leftmost_child;
		$out = array();
		$first_node = NULL;
		while (!is_null($c)) {
			$search->setIsFirstNode(FALSE);
			if ($search->accept($c)) {
				// non ottimale, potrebbe essere non accettato il prossimo nodo
				$search->setIsLastNode(is_null($c->right_sibling));
				
				if (is_null($first_node)) {
					$first_node = $c;
					$search->setIsFirstNode(TRUE);
					
					$out[] = $view->openFold($c, $search) ;
				}
				
				if ($mode == self::RENDER_FOLDED) $out[] = $view->openNode($c, $search).$view->renderNode($c, $search);
				else $out[] = $view->openNode($c, $search).$view->renderNode($c, $search, $mode).$view->closeNode($c, $search);
				
				$search->moreDeep();
				$out = array_merge($out, self::render( $c, $search, $view, $mode ) );
				$search->lessDeep();
				
				if ($mode == self::RENDER_FOLDED) $out[] = $view->closeNode($c, $search);
			}
			$c = $c->right_sibling;
		}
		if (!is_null($first_node)) $out[] = $view->closeFold($first_node, $search);
		return $out;
	}
	
	/**
	 * Ritorna una matrice di associazioni di tra i campi di un resultset e quelli 
	 * di una matrice di costruzione di un albero.
	 * 
	 * La matrice associativa deve avere i seguenti campi:
	 * 
	 * array(
	 * 		'node_id' => nome del campo che contiene l'ID del nodo
	 * 		'parent_id' => nome del campo che contiene l'ID del nodo parent
	 * 		'leftmostchild' => nome del campo che contiene l'ID del nodo leftmost child
	 * 		'rightsibling' => nome del campo che contiene l'ID del nodo right sibling
	 * );
	 * 
	 * @return array
	 * @see buildFromMatrix
	 */
	public static function getDefaultMatrixAssoc() {
		return array(
			'node_id' => 'node_id',
			'parent_id' => 'parent_id',
			'leftmostchild' => 'leftmostchild',
			'rightsibling' => 'rightsibling' 
		);
	}
	
	/**
	 * Ricostruisce un albero da una tabella creata usando normalize.
	 * 
	 * La callback per la creazione dei nodi è una funzione così definita:
	 * 
	 * callback( $node_arr )
	 * 
	 * Il parametro $node_arr è un array contenente una riga di dati generati 
	 * da {@link normalize}, ossia:
	 * 
	 * array(
	 *		'node_id' => ID del nodo
	 *		'parent_id' => ID del nodo parent (NULL per la radice)
	 *		'leftmostchild' => ID del nodo leftmostchild (o NULL se non presente)
	 *		'rightsibling' => ID del nodo rightsibling (o NULL se non presente)
	 *		'node' => il nodo serializzato usando {@link serializeNode}
	 *	)
	 * 
	 * Il primo nodo dell'albero è un oggetto TreeNode.
	 * Viene ritornato un albero vuoto se la matrice è vuota o non è stato possibile 
	 * ricostruire l'albero.
	 * 
	 * @param array $matrix la matrice creata da normalize
	 * @param callback $create_node_callback callback per la creazione dei nodi
	 * @param array $assoc_table una tabella per le associazioni tra una chiave della matrice di dati e il dato che rappresenta
	 * @return TreeNode
	 * @see normalize
	 */
	public static function buildFromMatrix($matrix, $create_node_callback, $assoc_table = NULL) {
		$tree = new TreeNode();
		if (count($matrix) == 0) return $tree;
		if (count($matrix) == 1) {
			$tree->setLeftmostChild( call_user_func($create_node_callback, reset($matrix)) );
			return $tree;
		}
		if (is_null($assoc_table)) $assoc_table = self::getDefaultMatrixAssoc();
		
		// salva la matrice
		self::$build_matrix =& $matrix;
		// Cerca il nodo root
		// Trova i nodi a profondità 0
		$level0 = array();
		$rs = NULL; // ID del nodo sibling da cercare
		foreach(self::$build_matrix as $k => $n) {
			if (is_null($n[$assoc_table['parent_id']])) {
				if (is_null($n[$assoc_table['rightsibling']])) $rs = $n[ $assoc_table['node_id'] ];
				else $level0[] = $k;
			}
		}

		// garantisce il numero giusto di iterazioni e evita loop 
		// su tabelle non corrette
		$iter = count($level0);
		while ($iter > 0) {
			foreach($level0 as $k => $l) {
				if (self::$build_matrix[ $l ][$assoc_table['rightsibling']] == $rs ) {
					$rs = self::$build_matrix[ $l ][$assoc_table['node_id']];
					unset($level0[$k]);
					break;
				}
			}
			$iter--;
		}


		$tree->setLeftmostChild( self::find_next_node($rs, $create_node_callback, $assoc_table) );

		TreeNode::fixParents($tree);
		self::$build_matrix = NULL;

		return $tree;
	}

	/**
	 * Trova il nodo con l'id nella matrice normalizzata e
	 * ritorna un nodo popolato. Popola anche i sottonodi
	 * 
	 * @return TreeNode
	*/
	private static function find_next_node($id, $create_node_callback, $assoc_table) {

		$node = NULL;
		foreach(self::$build_matrix as $k => $n) {
			$lmc = $n[$assoc_table['leftmostchild']];
			$rs = $n[$assoc_table['rightsibling']];
			if ($n[$assoc_table['node_id']] == $id) {
				Logger::getInstance()->debug("TreeNodeWalk::find_next_node: NODE: ".(is_null($id) ? 'NULL' : strval($id)).' FOUND!');
				$node = call_user_func($create_node_callback, $n);
				unset(self::$build_matrix[$k]);
				break;
			}
		}

		if (is_object($node)) {
			
			if (!is_null($lmc)) {
				$new_node = self::find_next_node($lmc, $create_node_callback, $assoc_table);
				if (!is_null($new_node)) $node->setLeftmostChild($new_node);
			}
			if (!is_null($rs)) {
				$new_node = self::find_next_node($rs, $create_node_callback, $assoc_table);
				if (!is_null($new_node)) $node->setRightSibling($new_node);
			}
		}
		return $node;
	}
	
	/**
	 * 
	 * Rimuove i nodi da un albero, riorganizzandolo.
	 * 
	 * L'attraversamento avviene in preorder, quindi in nodi discendenti
	 * vengono esclusi se il genitore non viene accettato.
	 * 
	 * @param TreeNode $node il nodo su cui operare
	 * @param TreeNodeSearchInterface $search filtro usato per selezionare i nodi
	 */
	public static function filter(TreeNode $node, TreeNodeSearchInterface $search) {
		if (is_null($node)) return;
		$n = $node->leftmostChild();
		while( !is_null($n) ) {
			if ($search->accept($n)) {
				self::filter($n, $search);
			} else {
				if ($n->isLeftmostChild()) {
					$n->parentNode()->setLeftmostChild( $n->rightSibling() );
				} else {
					$ls = $n->getPreviousSibling();
					if (!is_null($ls)) $ls->setRightSibling($n->rightSibling());
				}
			}
			$n = $n->rightSibling();
		}
	}
	
	/**
	 * 
	 * Ritorna il percorso verso un nodo.
	 * 
	 * Ritorna un array contenente, oltre al nodo di destinazione, anche tutti i
	 * nodi parent che bisogna attraversare per raggiungerlo.
	 * 
	 * @param TreeNode $node albero su cui lavorare
	 * @param TreeNodeSearchInterface $destination filtro per cercare il nodo di destinazione
	 * @return mixed FALSE se non ha trovato il nodo di destinazione, altrimenti un array di oggetti TreeNode
	 */
	public static function getPath(TreeNode $node, TreeNodeSearchInterface $destination) {
		$dn = $node->getNode($destination);
		if (!is_null($dn)) {
			$out = array( $dn );
			$p = $dn->parentNode();
			while (!is_null($p)) {
				array_unshift($out, $p);
				$p = $p->parentNode();
			}
			array_shift($out);
			return $out;
		} else {
			return FALSE;
		}
	}
	
	/**
	 * 
	 * Ritorna tutti gli ID di un albero.
	 * 
	 * Ritorna un array vuoto se non ci sono figli.
	 * 
	 * @param TreeNodeInterface $node il nodo su cui operare
	 * @return array un array con gli ID dei nodi
	 */
	public static function getAllIDs(TreeNodeInterface $node) {
		$out = array();
		if (!is_null($node)) {
			$n = $node->leftmostChild();
			while(!is_null($n)) {
				$out[] = $n->getID();
				$cid = self::getNodeIDs($n);
				if (!empty($cid)) $out = array_merge($out, $cid);
				$n = $n->rightSibling();
			}
		}
		return $out;
	}
	
	/**
	 * 
	 * Estrae tutti gli ID dei nodi, usando un filtro.
	 * 
	 * Ritorna un array vuoto se non ci sono figli.
	 * 
	 * @param TreeNodeInterface $node nodo su cui operare
	 * @param TreeNodeSearchInterface $search filtro di selezione
	 * @return array un array con gli ID dei nodi
	 */
	public static function getIDs(TreeNodeInterface $node, TreeNodeSearchInterface $search) {
		if (is_null($node)) return array();

		$c = $node->leftmostChild();
		if (is_null($c)) return array();

		if ($search->accept($c)) {
			$first_node = $c;
			
			$search->setIsFirstNode(TRUE);
			
			$out = array();

			while (!is_null($c)) {
				
				$out[] = $c->getID();
				
				$search->setIsLastNode(is_null($c->rightSibling()));
				
				$search->moreDeep();
				$nid = self::getIDs( $c, $search);
				if (!empty($nid)) $out = array_merge($out, $nid );
				$search->lessDeep();
				
				$search->setIsFirstNode(FALSE);
				
				$c = $c->rightSibling();
			}
			return $out;
		} else {
			return array();
		}
	}
}

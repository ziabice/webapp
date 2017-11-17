<?php
/**
 * (c) 2010 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 *	Interfaccia per l'implementazione di alberi leftmostchild-rightsibling
 *
 * @author Luca Gambetta <l.gambetta@bluenine.it>
 */
interface TreeNodeInterface extends WebAppObjectIDInterface {

	/**
	 * Ritorna il nodo leftmostchild.
	 *
	 * @return TreeNode il nodo figlio sinistro o NULL
	 */
	public function leftmostChild();

	/**
	 * Ritorna il nodo rightSibling
	 *
	 * @return TreeNode il nodo fratello destro o NULL
	 */
	public function rightSibling();

	/**
	 * Informa se questo nodo non ha figli.
	 *
	 * @return boolean TRUE se il nodo è vuoto, FALSE altrimenti
	 */
	public function isEmpty();

	/**
	 * Imposta il nodo leftmostchild.
	 *
	 * @param TreeNodeInterface $n nodo da impostare come leftmostchild
	 * @return TreeNodeInterface il nodo passato come parametro
	 */
	public function setLeftmostChild(TreeNodeInterface $n);

	/**
	 * Imposta il nodo rightsibling.
	 *
	 * @param TreeNodeInterface $n il nodo da impostare come rightsibling
	 * @return TreeNodeInterface il nodo passato come parametro
	 */
	public function setRightSibling(TreeNodeInterface $n);

	/**
	 * Ritorna il nodo parent di questo nodo.
	 *
	 * @return TreeNode il nodo parent o NULL se è il nodo root
	 */
	public function parentNode();

	/**
	 * Cerca il nodo indicato tra i sottonodi.
	 *
	 * @param TreeNodeSearchInterface $search il filtro per la ricerca
	 * @return TreeNode il nodo cercato o NULL se non è stato trovato
	 */
	public function getNode(TreeNodeSearchInterface $search);

	/**
	 * Informa se questo nodo sia la radice di un albero.
	 *
	 * @return boolean TRUE se è la radice, FALSE altrimenti
	 */
	public function isRoot();

	/**
	 * Informa se questo nodo sia il leftmostchild.
	 *
	 * @return boolean TRUE se è il leftmostchild, FALSE altrimenti
	 */
	public function isLeftmostChild();

	/**
	 * Ritorna il figlio a sinistra.
	 *
	 * @return TreeNodeInterface il figlio a sinistra o NULL se non c'è
	 */
	public function getPreviousSibling();

	/**
	 * Rimuove il nodo specificato dal sottoalbero.
	 *
	 * @param TreeNodeSearchInterface $search il filtro per la ricerca del nodo
	 * @return TreeNodeInterface il nodo rimosso o NULL se non ha rimosso niente
	 */
	public function deleteNode(TreeNodeSearchInterface $search);

	/**
	 * Inserisce il nodo in testa a i figli.
	 *
	 * @param TreeNodeInterface $node il nodo da inserire
	 * @return TreeNodeInterface il nodo inserito
	 */
	public function insert(TreeNodeInterface $n);

	/**
	 * Appende il nodo figlio in coda.
	 *
	 * @param TreeNodeInterface $node il nodo da aggiungere
	 * @return TreeNodeInterface il nodo aggiunto
	 */
	public function append(TreeNodeInterface $n);

	/**
	 * Svuota il sotto albero.
	 *
	 */
	public function clear();

	/**
	 * Informa se i nodi siano lo stesso nodo (di solito hanno lo stesso ID).
	 *
	 * @param TreeNodeInterface $node1
	 * @param TreeNodeInterface $node2
	 * @return boolean TRUE se hanno lo stesso ID, FALSE altrimenti
	 */
	public function isSameNode(TreeNodeInterface $node1, TreeNodeInterface $node2);
	
}

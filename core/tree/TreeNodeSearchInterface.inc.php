<?php
/**
 * (c) 2010-2011 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Permette di verificare se un nodo sia quello cercato o meno
 *
 * @author Luca Gambetta <l.gambetta@bluenine.it>
 */
interface TreeNodeSearchInterface {

	/**
	 * Informa se il nodo vada accettato (in un attraversamento)
	 *
	 * @param TreeNodeInterface $node
	 * @return boolean TRUE se il nodo va accettato, FALSE altrimenti
	 */
	public function accept(TreeNodeInterface $node);
	
	public function isFirstNode();
	public function isLastNode();
	public function deeplevel();
	public function moreDeep();
	public function lessDeep();

	public function setIsFirstNode($is_first);
	public function setIsLastNode($is_last);

	public function selectItems($items);
	public function deselectAll();
	public function isSelected(TreeNodeInterface $item);
	

}

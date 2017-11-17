<?php
/**
 * (c) 2008, 2009, 2010 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Restituisce una rappresentazione di un TreeNode.
 *
 * Lavora in coppia con un oggetto che implementa TreeNodeSearchInterface, che
 * sostanzialmente fa da Controller.
 *
 * @author Luca Gambetta <l.gambetta@bluenine.it>
 */
interface TreeNodeViewInterface {
    
	public function openFold(TreeNodeInterface $node, TreeNodeSearchInterface $search);

	public function closeFold(TreeNodeInterface $node, TreeNodeSearchInterface $search);

	public function openNode(TreeNodeInterface $node, TreeNodeSearchInterface $search);

	public function closeNode(TreeNodeInterface $node, TreeNodeSearchInterface $search);

	public function renderNode(TreeNodeInterface $node, TreeNodeSearchInterface $search);
	
}

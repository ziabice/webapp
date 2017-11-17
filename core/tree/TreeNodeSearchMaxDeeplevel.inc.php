<?php
/**
 * (c) 2011 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 * */

/**
 * 
 * Seleziona solo i nodi che sono più profondi di un certo livello.
 * 
 * @author Luca Gambetta
 *
 */
class TreeNodeSearchMaxDeeplevel extends TreeNodeSearchAdapter {
	
	protected 
		$max_deeplevel;
	
	/**
	 * 
	 * Inizializza impostando la profondità massima da raggiungere.
	 * 
	 * La profondità è indicata da un intero positivo. 0 indica la radice.
	 * Se si passa un intero negativo, la profondità viene ignorata
	 * 
	 * @param integer $max_deeplevel -1 per nessun limite, altrimenti un intero positivo con la profondità
	 */
	public function __construct($max_deeplevel = -1) {
		$this->max_deeplevel = $max_deeplevel;
		parent::__construct();
	}
	
	public function accept(TreeNodeInterface $node) {
		if ($this->max_deeplevel < 0) return TRUE;
		else return ($this->deeplevel() < $this->max_deeplevel);
	}
	
}

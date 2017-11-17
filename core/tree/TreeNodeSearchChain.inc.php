<?php
/**
 * (c) 2011 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 * */

/**
 * 
 * Crea una catena di filtri di ricerca.
 * 
 * @author luca
 *
 */
class TreeNodeSearchChain extends TreeNodeSearchAdapter {
	const
		CHAIN_MODE_AND = 10, // collaga mediante operatore AND
		CHAIN_MODE_OR = 20; // collega mediante operatore OR

	protected
		$mode, // modo di concatenazione
		$filters; // Array di oggetti che implementano TreeNodeSearchInterface
	
	/**
	 * Costruisce la catena di filtri.
	 *
	 * Occorre passare i filtri più un modo operativo che può essere:
	 *
	 * CHAIN_MODE_AND - collega mediante operatore AND
	 * CHAIN_MODE_OR - collega mediante operatore OR
	 *
	 * @param array $filters array di oggetti che implementano TreeNodeSearchInterface
	 * @param integer $chain_mode modalità di concatenazione
	 */
	public function __construct($filters, $chain_mode = self::CHAIN_MODE_AND) {
		parent::__construct();
		$this->mode = $chain_mode;
		$this->filters = $filters;
	}
	
	public function accept(TreeNodeInterface $node) {
		foreach($this->filters as $filter) {
			if ($this->mode == self::CHAIN_MODE_AND) {
				if ( !$filter->accept($node) ) return FALSE;
			} elseif ($this->mode == self::CHAIN_MODE_OR) {
				if ( $filter->accept($node)) return TRUE;
			}
		}
		return TRUE;
	}

	/**
	 * Copia i dati nei filtri figli.
	 * 
	 */
	private function copy_data() {
		foreach($this->filters as $f) {
			$f->deeplevel = $this->deeplevel;
			$f->is_first_node = $this->is_first_node;
			$f->is_last_node = $this->is_last_node;
		}
	}
	
	public function moreDeep() {
		parent::moreDeep();
		$this->copy_data();
	}
	
	public function lessDeep() {
		parent::lessDeep();
		$this->copy_data();
	}

	public function setIsFirstNode($is_first) {
		parent::setIsFirstNode($is_first);
		$this->copy_data();
	}

	public function setIsLastNode($is_last) {
		parent::setIsLastNode($is_last);
		$this->copy_data();
	}

	public function deselectAll() {
		parent::deselectAll();
		foreach ($this->filters as $f) $f->deselectAll();
	}

	public function selectItems($items) {
		parent::selectItems($items);
		foreach ($this->filters as $f) $f->sel_items = $this->sel_items;
	}
}

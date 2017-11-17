<?php
/**
 * (c) 2008, 2009 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Una catena di filtri di input.
 *
 * Permette di collegare due filtri usando un operatore logico.
 *
 * Il filtro che ha un valore TRUE (o che rende tutta la catena vera) viene
 * usato per generare il valore ritornato da getValue, usando i suoi metodi per
 * processare il valore raw.
 *
 * @author Luca Gambetta <l.gambetta@bluenine.it>
 */
class ChainedInputFilter extends InputFilter {
    
	const
		CHAIN_MODE_AND = 1,
		CHAIN_MODE_OR = 2;

	protected
		$chain_mode, $valid_filter,
		$filter1, $filter2; // I filtri che verranno concatenati

	/**
	 * Inizializza impostando i due filtri sui cui operare e il modo di confronto logico.
	 *
	 * Il parametro $chain_mode può valere:
	 * CHAIN_MODE_AND - usa l'operatore AND
	 * CHAIN_MODE_OR  - usa l'operatore OR
	 *
	 * @param string $fieldname
	 * @param InputFilter $filter1
	 * @param InputFilter $filter2
	 * @param integer $chain_mode
	 */
	public function __construct($fieldname, InputFilter $filter1, InputFilter $filter2, $chain_mode = self::CHAIN_MODE_OR) {
		$this->filter1 = $filter1;
		$this->filter2 = $filter2;
		$this->chain_mode = $chain_mode;
		parent::__construct($fieldname, FALSE);
	}

	protected function verifyValue($value) {
		$ok = self::WRONG_VALUE;
		if ($this->chain_mode == self::CHAIN_MODE_AND) {
			if ($this->filter1->isValid() && $this->filter2->isValid()) {
				$this->valid_filter = $this->filter1;
				$ok = self::VALID_VALUE;
			}
		} elseif ($this->chain_mode == self::CHAIN_MODE_OR) {
			if ($this->filter1->isValid() || $this->filter2->isValid()) {
				// Individua il filtro che ha avuto successo
				$this->valid_filter = ($this->filter1->isValid() ? $this->filter1 : $this->filter2);
				$ok = self::VALID_VALUE;
			}
		}
		return $ok;
	}

	protected function mustProcessValue() {
		if (is_object($this->valid_filter)) {
			return $this->valid_filter->mustProcessValue();
		}
		return FALSE;
	}

	public function processValue($value) {
		if (is_object($this->valid_filter)) {
			return $this->valid_filter->processValue($value);
		} else {
			return parent::processValue($value);
		}
	}

	/**
	 * Ritorna il filtro che ha un valore valido.
	 * 
	 * @return InputFilter
	 */
	public function getValidFilter() {
		return $this->valid_filter;
	}
}


<?php
/**
 * (c) 2008-2012 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Un Model di utilità che non implementa le operazioni CRUD, ma permette
 * di aggiungere una serie di dati come se fossero i record di una lettura
 * e poi li fornisce al paginatore.
 *
 *
 * @author Luca Gambetta <l.gambetta@bluenine.it>
 */
class CollectionModel extends Model {
	protected
		$data_rows;

	/**
	 * Costruisce aggiungendo già i dati
	 * 
	 * @param array $data
	 */
	public function __construct(array $data = array()) {
		$this->data_rows = $data;
		parent::__construct();
	}

	/**
	 * Aggiunge una riga di dati
	 * @param mixed $data dei dati da aggiugere
	 */
    public function addDatarow($data) {
		$this->data_rows[] = $data;
	}

	/**
	 * Imposta tutti i dati prendendoli da un array.
	 *
	 * @param array $data
	 */
	public function setDataFromArray(array $data) {
		$this->data_rows = $data;
	}

	public function getTotalCount(Criteria $criteria) {
		return count($this->data_rows);
	}

	public function read(Criteria $criteria) {
		// estrae i limiti, se ci sono, della lettura e ritorna i dati
		if ($criteria->hasLimit()) {
			$offset = ($criteria->hasStartRecord() ? $criteria->getStartRecord() : 0);
			if ($criteria->hasRecordCount()) {
				return array_slice($this->data_rows, $offset, $criteria->getRecordCount() );
			} else {
				return array_slice($this->data_rows, $offset);
			}
		} else {
			return $this->data_rows;
		}
	}
}

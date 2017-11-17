<?php

/**
 * (c) 2011 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */
 
/**
 * Un oggetto TreeNode che implementa l'interfaccia EditableObjectInterface.
 * 
 * @author Luca Gambetta <l.gambetta@bluenine.it>
 * */
class TreeNodeEditableObject extends TreeNode implements EditableObjectInterface {

	protected
		$status, // stringa con lo stato corrente dell'oggetto
		$creator_uid, // UID di chi ha creato l'oggetto
		$modifier_uid, // UID di chi ha fatto l'ultima modifica
		$creation_date, // DateTime di creazione
		$modification_date; // DateTime di modifica
	
	/**
	 * Crea un nuovo oggetto, imposta la data di creazione
	 * al momento corrente.
	 * 
	 * @param mixed $id id univoco dell'oggetto
	 * @param mixed $creator_uid NULL o l'uid del creatore (di solito un intero)
	 * @param mixed $obj NULL o un oggetto che si vuole agganciare al nodo
	*/
	public function __construct($id = NULL, $creator_uid = NULL, $obj = NULL) {
		$this->creator_uid = $creator_uid;
		$this->modifier_uid = NULL;
		$this->creation_date = new CDateTime();
		$this->creation_date->connect($this);
		$this->modification_date = new CDateTime();
		$this->modification_date->connect($this);
		$this->setStatus(self::STATUS_NONE);
		parent::__construct($id, $obj);
	}
	
	/**
	 * Legge lo stato dell'oggetto
	 * @return integer un intero con lo stato dell'oggetto
	*/
	public function getStatus() {
		return $this->status;
	}
	
	/**
	 * Imposta lo stato dell'oggetto.
	 * Gli stati vengono definiti in costanti di classe: di default viene
	 * definito solo lo stato STATUS_NONE.
	 * 
	 * @param integer $status un intero con lo stato dell'oggetto
	*/
	public function setStatus($status) {
		$this->status = $status;
		$this->touch();
	}
	
	/**
	 * Ritorna la data di creazione
	 * 
	 * @return CDateTime istanza di CDateTime
	*/
	public function getCreationDate() {
		return $this->creation_date;
	}
	
	/**
	 * Imposta la data di creazione dell'oggetto.
	 * 
	 * @param CDateTime $date la data di creazione
	 * @return CDateTime l'istanza di CDateTime
	*/
	public function setCreationDate(CDateTime $date) {
		if (is_object($this->creation_date)) $this->creation_date->disconnect();
		$this->creation_date = $date;
		$this->creation_date->connect($this);
		$this->touch();
		return $this->creation_date;
	}
	
	/**
	 * Ritorna la data dell'ultima modifica.
	 * 
	 * @return CDateTime istanza di CDateTime
	*/
	public function getModificationDate() {
		return $this->modification_date;
	}
	
	/**
	 * Imposta la data dell'ultima modifica dell'oggetto
	 * 
	 * @param CDateTime $date la data dell'ultima modifica
	 * @return CDateTime l'istanza di CDateTime
	*/
	public function setModificationDate(CDateTime $date) {
		if (is_object($this->modification_date)) $this->modification_date->disconnect();
		$this->modification_date = $date;
		$this->modification_date->connect($this);
		$this->touch();
		return $this->modification_date;
	}
	
	/**
	 * Imposta l'UID del creatore dell'oggetto
	 * 
	 * @param mixed $creator_uid  l'UID del creatore (di solito un intero)
	*/
	public function setCreatorUID($creator_uid) {
		$this->creator_uid = $creator_uid;
		$this->touch();
	}
	
	/**
	 * Ritorna l'uid di chi ha creato l'oggetto
	 *
	 * @return mixed NULL o un mixed con l'UID (di solito è un intero)
	*/
	public function getCreatorUID() {
		return $this->creator_uid;
	}
	
	/**
	 * Informa se questo oggetto abbia l'uid del creatore
	 * 
	 * @return boolean TRUE se ha l'UID è diverso da un valore nullo, FALSE altrimenti
	*/
	public function hasCreator() {
		return !is_null($this->creator_uid);
	}
	
	/**
	 * Imposta l'UID dell'ultimo modificatore dell'oggetto
	 * 
	 * @param mixed $modifier_uid l'UID del modificatore (di solito intero)
	*/
	public function setModifierUID($modifier_uid) {
		$this->modifier_uid = $modifier_uid;
		$this->touch();
	}
	
	/**
	 *  Ritorna l'uid di chi ha modificato l'oggetto
	 * 
	 * @return mixed NULL o un mixed con l'UID (di solito è un intero)
	*/
	public function getModifierUID() {
		return $this->modifier_uid;
	}
	
	/**
	 * Informa se questo oggetto abbia l'uid dell'ultimo modificatore
	 * @return boolean TRUE se ha l'UID, FALSE altrimenti
	*/
	public function hasModifier() {
		return !is_null($this->modifier_uid);
	}
}

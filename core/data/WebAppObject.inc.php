<?php
/**
 * (c) 2008-2012 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Un oggetto base da cui poter derivare gli altri. Permette di associare
 * ad ogni oggetto un ID univoco.
 * 
 * Ogni oggetto ha una collezione di proprietà ed una di attributi.
 * Le proprietà sono un insieme di stringhe che definiscono caratteristiche 
 * (ad esempio per marcare un oggetto in qualche modo), mentre gli attributi permettono
 * di associare una etichetta ad un valore.
 * 
 * Un oggetto a cui è associato un ID univoco ritiene di essere stato
 * salvato su uno storage, e quindi il flag di "nuovo oggetto" è impostato
 * a FALSE.
 *
 * @author Luca Gambetta <l.gambetta@bluenine.it>
*/
class WebAppObject extends ObjectConnectAdapter implements WebAppObjectInterface {
	protected
		$_id, // identificativo univoco dell'oggetto
		$_is_new, // flag per un nuovo oggetto
		$_deleted, // flag che indica se sia stato rimosso dal DB
		
		/**
		 * @var HashMap contiene gli attributi
		 */
		$_attributes,
		/**
		 * @var PropertyList contiene le proprietà
		 */
		$_properties; 
		
	/**
	 * Costruisce impostando l'ID univoco.
	 * 
	 * Se l'ID è diverso da NULL allora l'oggetto sarà creato come "non nuovo".
	 * Una chiamata a {@link isNew} ritornerà perciò FALSE.
	 * 
	 * @param mixed $id NULL o l'id univoco dell'oggetto (di solito un intero)
	*/
	public function __construct($id = NULL) {
		$this->setID($id);
		$this->_properties = new PropertyList();
		$this->_properties->connect($this);
		$this->_attributes = new HashMap();
		$this->_attributes->connect($this);
		
		$this->_deleted = FALSE;
		$this->_is_new = is_null($id);
	}
	
	/**
	 * Ritorna l'id dell'oggetto
	 * 
	 * @return mixed con l'id univoco dell'oggetto (di solito un intero)
	*/
	public function getID() {
		return $this->_id;
	}
	
	/**
	 * Imposta l'id dell'oggetto
	 * 
	 * @param mixed $id con l'id univoco dell'oggetto (di solito un intero)
	*/
	public function setID($id) {
		$this->_id = $id;
		$this->_is_new = is_null($id);
		$this->touch();
	}
	
	/**
	 * Ritorna le proprietà dell'oggetto
	 * 
	 * @return PropertyList una istanza di PropertyList
	*/
	public function getProperties() {
		return $this->_properties;
	}
	
	
	/**
	 * Ritorna gli attributi dell'oggetto
	 * 
	 * @return HashMap una istanza di HashMap
	*/
	public function getAttributes() {
		return $this->_attributes;
	}
	
	
	/**
	 * Informa se l'oggetto sia "nuovo", ossia non salvato nel database.
	 * Di solito indica che il suo ID univoco è stato assegnato
	 * 
	 * @return boolean TRUE se l'oggetto è nuovo, FALSE altrimenti
	*/
	public function isNew() {
		return $this->_is_new;
	}
	
	/**
	 * Imposta lo stato di "nuovo oggetto", ossia il valore ritornato
	 * da {@link isNew}
	 *
	 * @param boolean $is_new TRUE se questo è un nuovo oggetto, FALSE altrimenti
	*/
	public function setNew($is_new) {
		$this->_is_new = (boolean)$is_new;
	}
	
	/**
	 * Informa se l'oggetto sia stato marcato come rimosso dal database
	 *
	 * @return boolean TRUE se l'oggetto è stato rimosso dal DB, FALSE altrimenti
	*/
	public function isDeleted() {
		return $this->_deleted;
	}
	
	/**
	 * Imposta l'oggetto come rimosso dal database: ciò vuol dire che il suo ID
	 * non rappresenta più lo stesso oggetto sul database.
	 *
	 * @param boolean $is_deleted TRUE se questo è un nuovo oggetto, FALSE altrimenti
	*/
	public function setDeleted($is_deleted) {
		$this->_deleted = (boolean) $is_deleted;
		$this->touch();
	}
}


<?php
/**
 * (c) 2012 by Luca Gambetta <l.gambetta@bluenine.it>
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
interface WebAppObjectInterface extends WebAppObjectIDInterface {
	
	/**
	 * Ritorna le proprietà dell'oggetto
	 * 
	 * @return PropertyList una istanza di PropertyList
	*/
	public function getProperties();
	
	
	/**
	 * Ritorna gli attributi dell'oggetto
	 * 
	 * @return HashMap una istanza di HashMap
	*/
	public function getAttributes();
	
	
	/**
	 * Informa se l'oggetto sia "nuovo", ossia non salvato nel database.
	 * Di solito indica che il suo ID univoco è stato assegnato
	 * 
	 * @return boolean TRUE se l'oggetto è nuovo, FALSE altrimenti
	*/
	public function isNew();
	
	/**
	 * Imposta lo stato di "nuovo oggetto", ossia il valore ritornato
	 * da {@link isNew}
	 *
	 * @param boolean $is_new TRUE se questo è un nuovo oggetto, FALSE altrimenti
	*/
	public function setNew($is_new);
	
	/**
	 * Informa se l'oggetto sia stato marcato come rimosso dal database
	 *
	 * @return boolean TRUE se l'oggetto è stato rimosso dal DB, FALSE altrimenti
	*/
	public function isDeleted();
	
	/**
	 * Imposta l'oggetto come rimosso dal database: ciò vuol dire che il suo ID
	 * non rappresenta più lo stesso oggetto sul database.
	 *
	 * @param boolean $is_deleted TRUE se questo è un nuovo oggetto, FALSE altrimenti
	*/
	public function setDeleted($is_deleted);
}


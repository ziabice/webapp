<?php

/**
 * (c) 2008-2013 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Fornisce una interfaccia base per le operazioni CRUD e
 * per la paginazione degli elementi.
 *
 * @author Luca Gambetta <l.gambetta@bluenine.it>
 *
*/
interface CRUD {
	/**
	 * Lettura di uno o più oggetti.
	 * 
	 * @param Criteria $criteria criteri di selezione degli oggetti
	 * @return object NULL o l'oggetto letto
	 * @throws Exception in caso di errore
	*/
	public function read(Criteria $criteria);

	/**
	 * Creazione di un oggetto.
	 * 
	 * Imposta l'ID dell'oggetto in caso di successo.
	 * 
	 * @param object $object l'oggetto da creare
	 * @param array $params parametri aggiuntivi
	 * @return boolean TRUE se tutto ok, FALSE altrimenti
	 * @throws Exception in caso di errore
	*/
	public function create($object, $params = array());

	/**
	 * Aggiorna i dati usando un oggetto.
	 * L'ID dell'oggetto viene usato come chiave primaria
	 * 
	 * @param object $object l'oggetto da aggiornare
	 * @param array $params parametri aggiuntivi
	 * @return boolean TRUE se tutto ok, FALSE altrimenti
	 * @throws Exception in caso di errore
	*/
	public function update($object, $params = array());
	
	/**
	 * Rimuove un oggetto. 
	 * L'ID dell'oggetto viene usato come chiave primaria.
	 * In caso di successo l'oggetto viene marcato come rimosso.
	 * 
	 * @param object $object l'oggetto da aggiornare
	 * @return boolean TRUE se tutto ok, FALSE altrimenti
	 * @throws Exception in caso di errore
	*/
	public function delete($object);
	
	/**
	 * Conta il numero di elementi in base a criteri di selezione.
	 * 
	 * @param Criteria $criteria criteri di selezione degli oggetti
	 * @return boolean FALSE o un intero col numero di elementi
	 * @throws Exception in caso di errore non recuperabile
	*/
	public function getTotalCount(Criteria $criteria);
	
	/**
	 * Eseguita quando viene agganciato ad un oggetto
	 * @param mixed $container istanza a cui viene agganciato
	*/
	public function onBind($container);
}

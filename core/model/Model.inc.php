<?php
/**
 * (c) 2008-2013 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Implementazione base di un Model
 * 
 *
 * Astrazione per la gestione dei dati. In una implementazione minima deve fornire
 * almeno gli strumenti per l'interazione col pager (i metodi getTotalCount e read).
 * Nell'implementazione definitiva deve offrire anche la possibilità
 * di una completa gestione degli oggetti su storage (di solito server DB).
 * Non propriamente un ORM, ma ci va molto vicino.
*/
class Model implements CRUD {
	
	public function __construct() {
	}
	
	/**
	 * Ritorna il numero totale di elementi: il conteggio avviene in
	 * base ai criteri di selezione impostati.
	 * 
	 * @param Criteria $criteria criteri di selezione degli oggetti
	 * @return integer un intero col numero di elementi
	 * @throws Exception in caso di errori
	*/
	public function getTotalCount(Criteria $criteria) {
		return -1;
	}
	
	/**
	 * Crea un oggetto dai dati estratti dalla sorgente di dati.
	 *
	 * @param array $data array coi dati da cui costruire un oggetto
	 * @return una istanza di un oggetto o NULL
	*/
	public function buildObject($data) {
		return NULL;
	}
	
	/**
	 * Verifica se una chiave primaria sia valida. 
	 * 
	 * Viene utilizzato dalle istanze di Criteria collegate per verificare la
	 * correttezza delle chiavi primarie.
	 * In questa implementazione la chiave primaria deve essere un intero senza segno.
	 * 
	 * @param mixed $id intero con la chiave primaria
	 * @return boolean TRUE se la chiave è valida, FALSE altrimenti
	*/
	public function checkPrimaryKey($id) {
		return preg_match('/^[1-9][0-9]*$/', strval($id)) == 1;
	}
	
	
	
	/**
	 * Invocata quando il Model è agganciato ad un Controller
	 * o ad altro tipo di oggetto
	 * 
	 * @param object $obj istanza della classe a cui viene agganciato
	*/
	public function onBind($obj) {
	}
	
	// ----------------------- CRUD
	
	/**
	 * Legge uno o più elementi dallo storage usando i criteri di selezione impostati.
	 * 
	 * A seconda dei casi ritorna un oggetto, un array di oggetti, NULL se non sono
	 * stati trovati elementi rispondenti ai criteri di ricerca impostati
	 * 
	 * @param Criteria $criteria criteri di selezione degli oggetti
	 * @return mixed NULL se non ha trovato l'oggetto indicato dai criteri di selezione,
	 * un oggetto (nel caso di letture di singoli oggetti),  un array di oggetti.
	 * @throws Exception in caso di errori
	*/
	public function read(Criteria $criteria) {
		return NULL;
	}
	
	/**
	 * Salva un nuovo oggetto nello storage.
	 *
	 * Imposta l'ID dell'oggetto in caso di successo.
	 * 
	 * @param object $object l'oggetto da creare
	 * @param array $params parametri aggiuntivi
	 * @return boolean TRUE se tutto ok, FALSE altrimenti
	 * @throws Exception in caso di errore
	*/
	public function create($object, $params = array()) {
		return FALSE;
	}

	/**
	 * Aggiorna i dati usando un oggetto.
	 * L'ID dell'oggetto viene usato come chiave primaria
	 * 
	 * @param object $object l'oggetto da aggiornare
	 * @param array $params parametri aggiuntivi
	 * @return boolean TRUE se tutto ok, FALSE altrimenti
	 * @throws Exception in caso di errore
	*/
	public function update($object, $params = array()) {
		return FALSE;
	}
	
	/**
	 * Rimuove un oggetto.
	 * 
	 * L'ID dell'oggetto viene usato come chiave primaria.
	 * 	In caso di successo l'oggetto viene marcato come rimosso.
	 * 
	 * @param object $object l'oggetto da aggiornare
	 * @return boolean TRUE se tutto ok, FALSE altrimenti
	 * @throws Exception in caso di errore
	*/
	public function delete($object)  {
		return FALSE;
	}
}

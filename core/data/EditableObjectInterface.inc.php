<?php

/**
 * (c) 2008, 2009 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Interfaccia per la realizzazione di oggetti che registrano il
 * loro creatore e modificatore.
 *
 * Una interfaccia per modellare oggetti che vengono salvati
 * in uno storage, tenendo traccia di chi ha eseguito operazioni su di esso
 * e quando.
 *
 * E' possibile inoltre mantenere lo stato di lavorazione dell'oggetto.
 *
 * L'autore di modifiche viene individuato mediante un ID univoco, tipicamente
 * un intero che indica la chiave primaria di un record in una tabella di database.
 *
 * Il cambiamento delle date di modifica viene gestito dal Model (come un filesystem).
 *
 * @author Luca Gambetta <l.gambetta@bluenine.it>
*/
interface EditableObjectInterface {


	const
		STATUS_NONE = 0; // Stato base: nessuno stato


	/**
	 * Legge lo stato dell'oggetto.
	 *
	 * @return integer un intero con lo stato dell'oggetto
	*/
	public function getStatus();

	/**
	 * Imposta lo stato dell'oggetto.
	 *
	 * Gli stati vengono definiti in costanti di classe.
	 *
	 * @param integer $status un intero con lo stato dell'oggetto
	*/
	public function setStatus($status);

	/**
	 * Ritorna la data di creazione.
	 *
	 * @return CDateTime istanza di CDateTime
	*/
	public function getCreationDate();

	/**
	 * Imposta la data di creazione dell'oggetto
	 *
	 * @param CDateTime $date la data di creazione
	 * @return CDateTime l'istanza di CDateTime
	*/
	public function setCreationDate(CDateTime $date);

	/**
	 * Ritorna la data dell'ultima modifica.
	 *
	 * @return CDateTime istanza di CDateTime
	*/
	public function getModificationDate();

	/**
	 * Imposta la data dell'ultima modifica dell'oggetto
	 *
	 * @param CDateTime $date la data dell'ultima modifica
	 * 
	 * @return CDateTime l'istanza di CDateTime
	*/
	public function setModificationDate(CDateTime $date);

	/**
	 * Imposta l'UID del creatore dell'oggetto
	 *
	 * @param mixed $creator_uid  l'UID del creatore (di solito un intero)
	*/
	public function setCreatorUID($creator_uid);

	/**
	 * Ritorna l'uid di chi ha creato l'oggetto.
	 *
	 * @return mixed NULL o un mixed con l'UID (di solito è un intero)
	*/
	public function getCreatorUID();

	/**
	 * Informa se questo oggetto abbia l'uid del creatore.
	 *
	 * @return boolean TRUE se ha l'UID è diverso da un valore nullo, FALSE altrimenti
	*/
	public function hasCreator();

	/**
	 * Imposta l'UID dell'ultimo modificatore dell'oggetto.
	 *
	 * @param mixed $modifier_uid l'UID del modificatore (di solito intero)
	*/
	public function setModifierUID($modifier_uid);

	/**
	 *  Ritorna l'uid di chi ha modificato l'oggetto
	 *
	 * @return mixed NULL o un mixed con l'UID (di solito è un intero)
	*/
	public function getModifierUID();

	/**
	 * Informa se questo oggetto abbia l'uid dell'ultimo modificatore
	 * 
	 * @return boolean TRUE se ha l'UID, FALSE altrimenti
	*/
	public function hasModifier();

}


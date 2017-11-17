<?php

/**
 * (c) 2012 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Un'interfaccia per marcare un oggetto attraverso un identificativo
 * univoco.
 * 
 * @author Luca Gambetta <l.gambetta@bluenine.it>
 */
interface WebAppObjectIDInterface {
	/**
	 * Ritorna l'id dell'oggetto
	 * 
	 * @return mixed con l'id univoco dell'oggetto (di solito un intero)
	*/
	public function getID();
	
	/**
	 * Imposta l'id dell'oggetto
	 * 
	 * @param mixed $id con l'id univoco dell'oggetto (di solito un intero)
	*/
	public function setID($id);
}



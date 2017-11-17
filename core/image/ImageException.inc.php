<?php
/**
 * (c) 2008, 2009 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Una eccezione nella gestione delle immagini
*/
class ImageException extends Exception {
	const
		FILE_OPEN_ERROR = 10, // Apertura del file fallita
		IMAGETYPE_UNKNOW = 100, // Tipo di immagine sconosciuto
		IMAGETYPE_NOT_SUPPORTED = 110, // Tipo di immagine non supportato
		FAILED_CREATION_FROM_FILE = 120, // creazione da file fallita
		FAILED_CREATION = 200, // creazione dell'immagine fallita
		UNSUPPORTED_OPERATION = 500;  // Operazione non supportata dalle librerie
}


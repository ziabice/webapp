<?php
/**
 * (c) 2008, 2009 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Una eccezione nella gestione dell'upload dei file
 * @author Luca Gambetta <l.gambetta@bluenine.it>
*/
class FileUploadException extends Exception {

	/**
	 * Crea una nuova eccezione
	 *
	 * Se come parametro $err_code si fornisce una delle costanti UPLOAD_ERR_*,
	 * il messaggio viene ignorato e sostituito con uno approriato.
	 *
	 * @param string $msg messaggio di errore
	 * @param integer $err_code codice di errore
	*/
	public function __construct($msg = NULL, $err_code = 0) {
		switch($err_code) {
			case UPLOAD_ERR_INI_SIZE: parent::__construct(tr("The uploaded file exceeds the upload_max_filesize directive in php.ini"), $err_code);
			case UPLOAD_ERR_FORM_SIZE: parent::__construct(tr("The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form"), $err_code);
			case UPLOAD_ERR_PARTIAL: parent::__construct(tr("The uploaded file was only partially uploaded"), $err_code);
			case UPLOAD_ERR_NO_FILE: parent::__construct(tr("No file was uploaded"), $err_code);
			case UPLOAD_ERR_NO_TMP_DIR: parent::__construct(tr("Missing a temporary folder"), $err_code);
			case UPLOAD_ERR_CANT_WRITE: parent::__construct(tr("Failed to write file to disk"), $err_code);
			case UPLOAD_ERR_EXTENSION: parent::__construct(tr("File upload stopped by extension"), $err_code);
			default:
				parent::__construct($msg, $err_code);
		}
	}
}

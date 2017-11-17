<?php
/**
 * (c) 2011 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Verifica che sia presente un file in upload.
 * 
 * Viene salvato il descrittore del file, che può essere 
 * recuperato usando il metodo getValue.
 * 
 * Il descrittore del file è un oggetto UploadedFile
 *
 * @author Luca Gambetta <l.gambetta@bluenine.it>
 */
class UploadedFileFilter extends InputFilter {

	protected
		$upload_exception,
		$is_multiple,
		$allowed_mime;

	/**
	 * Inizializza la verifica dell'upload.
	 * 
	 * In caso di array di file in upload fornire comunque un nome singolo.
	 * 
	 * @param string $fieldname nome del campo
	 * @param array $allowed_mime stringhe coi tipi MIME validi
	 * @param boolean $is_multiple si tratta di un upload multiplo
	 * @param boolean $force_upload TRUE deve essere presente un file, FALSE può non essere presente
	 */
	public function  __construct($fieldname, $allowed_mime = array(), $is_multiple = FALSE, $force_upload = TRUE) {
		$this->allowed_mime = $allowed_mime;
		$this->is_multiple = $is_multiple;
		$this->upload_exception = NULL;
		parent::__construct($fieldname, !$force_upload);
	}
	
	protected function populateRawValue() {
		// Mette nel raw_value il file caricato
		try {
			$this->raw_value = new UploadedFile($this->getFieldName(), $this->is_multiple);
			// Se il campo c'è, ma non il file, allora sistema il raw value a NULL
			if (!$this->raw_value->hasUploadedFile() && $this->allowNULL()) {
				$this->raw_value = NULL;
			}
		}
		catch (FileUploadException $e) {
			$this->raw_value = NULL;
			$this->upload_exception = $e;
		}
	}
	
	/**
	 * Verifica il valore.
	 * 
	 * il parametro $uploaded_file sarà un oggetto UploadedFile
	 * 
	 * */
	public function checkValue($uploaded_file) {
		// Verifica i tipi mime dei file caricati
		if ($this->is_multiple) {
			foreach($uploaded_file as $u) {
				if ($uploaded_file->hasUploadedFile()) {
					if (!in_array($u->getMIME(), $this->allowed_mime)) {
						return FALSE;
					}
				} else {
					return FALSE;
				}
			}
		} else {
			if ($uploaded_file->hasUploadedFile()) {
				if (!empty($this->allowed_mime)) {
					return (in_array($uploaded_file->getCurrentFileDescriptor()->getMIME(), $this->allowed_mime));
				}
			} else {
				return FALSE;
			}
		}
		return TRUE;
	}
	
	/**
	 * In caso di upload fallito ritorna l'eventuale eccezione.
	 * 
	 * @return UploadedFileException l'oggetto con l'eccezione o NULL.
	 * */
	public function getUploadException() {
		return $this->upload_exception;
	}
}


<?php
/**
 * (c) 2008, 2009 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */
/**
 * Un descrittore di un file di cui si è fatto l'upload.
 *
 * Viene usata internamente da {@link UploadedFile}.
 *
 * Permette di associare uno o più nomi di output al file sorgente.
 * 
 * @author Luca Gambetta <l.gambetta@bluenine.it>
 * @see UploadedFile
*/
class UploadedFileDescriptor {
	private
		$name, 
		$size, 
		$client_mime_type, 
		$real_mime_type,
		$tmp_name,
		$out_name,
		$error;
	
	/**
	 * Crea un nuovo descrittore di file.
	 * 
	 * La condizione di errore è una delle costanti standard di PHP, UPLOAD_ERR_*.
	 *
	 * @param string $name nome sul client del file
	 * @param integer $size dimensione in byte del file
	 * @param string $client_mime_type tipo MIME del file sul client
	 * @param string $real_mime_type tipo MIME effettivo del file sul server
	 * @param string $tmp_name path completo del file sul server
	 * @param integer $error condizione di errore di questo upload (una delle costanti UPLOAD_ERR_*)
	 *
	*/
	public function __construct($name, $size, $client_mime_type, $real_mime_type, $tmp_name, $error) {
		$this->name = $name;
		$this->size = $size;
		$this->client_mime_type = $client_mime_type;
		$this->real_mime_type = $real_mime_type;
		$this->tmp_name = $tmp_name;
		$this->error = $error;
		$this->out_name = FileUtils::normalizeName($name);
	}
	
	/**
	 * Nome del file sul client.
	 *
	 * @return string nome del file sul client
	*/
	public function getName() {
		return $this->name;
	}
	
	/**
	 * Ritorna la dimensione in byte del file sul server.
	 *
	 * @return integer dimensione del file 
	*/
	public function getSize() {
		return $this->size;
	}
	
	/**
	 * Ritorna il tipo MIME reale del file (estratto dal file sul server).
	 *
	 * @return string tipo MIME reale del file
	*/
	public function getMIME() {
		return $this->real_mime_type;
	}
	
	/**
	 * Ritorna il tipo MIME del file come fornito dal client.
	 * 
	 * Non è una informazione veritiera o sicura.
	 *
	 * @return string tipo MIME fornito dal client (non sicuro)
	*/
	public function getClientMIME() {
		return $this->client_mime_type;
	}
	
	/**
	 * Ritorna il nome del file temporaneo sul server.
	 *
	 * Ritorna un path completo.
	 *
	 * @return string nome del file sul server
	*/
	public function getTempName() {
		return $this->tmp_name;
	}
	
	/**
	 * Ritorna il flag di errore associato all'upload.
	 *
	 * Il flag di errore è una delle costanti UPLOAD_ERR_*.
	 *
	 * Se non ci sono errori ritorna UPLOAD_ERR_OK.
	 *
	 * @return integer flag di errore associato al file
	*/
	public function getError() {
		return $this->error;
	}
	
	/**
	 * Informa se la dimensione del file sia pari a 0
	 *
	 * @return boolean TRUE se è zero, FALSE altrimenti
	*/
	public function isEmptySize() {
		return $this->size == 0;
	}
	
	/**
	 * Informa se il file sul server esista, ossia che il nome sia non nullo.
	 *
	 * @return boolean TRUE se il file esiste, FALSE altrimenti
	*/
	public function hasTempFile() {
		return !empty( $this->tmp_name );
	}
	
	/**
	 * Imposta il nome o un array di nomi da utilizzare per l'output di questo file.
	 * In caso di array di stringhe si può utilizzare un hash per identificare certi elementi.
	 * @param string|array $name stringa o array di stringhe con in nomi del file
	*/
	protected function setOutputName($name) {
		$this->out_name = $name;
	}
	
	/**
	 * Ritorna il nome ripulito da utilizzare per questo file al momento del salvataggio.
	 *
	 * Il nome ritornato viene ripulito da caratteri spuri usando {@link FileUtils::normalizeName}.
	 * 
	 * @return string stringa col nome
	 * @see FileUtils::normalizeName
	*/
	public function getOutputName() {
		return $this->out_name;
	}
	
	/**
	 * Informa se il file temporaneo in upload sia presente fisicamente sul disco.
	 *
	 * @return boolean TRUE se è presente il file, FALSE altrimenti
	*/
	public function fileExists() {
	  return @file_exists($this->tmp_name);
	}

	/**
	 * Sposta il file di cui si è fatto l'upload da una directory temporanea del server
	 * ad un'altra.
	 *
	 * In ingresso accetta un path completo, col quale si può anche cambiare il nome del
	 * file di destinazione. Se il file di destinazione è già presente viene sovrascritto.
	 *
	 * Ad esempio sposta il file in upload nel file "foo.ext" nella directory "/my_path":
	 * <code>
	 * $fd->moveTo('/my_path/foo.ext');
	 * </code>
	 *
	 * @param string $destination_path percorso di destinazione
	 * @return boolean TRUE se tutto ok, FALSE altrimenti
	 */
	public function moveTo($destination_path) {
		return @move_uploaded_file($this->tmp_name, $destination_path) !== FALSE;
	}
}


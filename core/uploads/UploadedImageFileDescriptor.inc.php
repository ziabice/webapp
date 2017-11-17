<?php
/**
 * (c) 2008, 2009 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Un descrittore di file specifico per immagini, usato da UploadedImage.
 *
 * @author Luca Gambetta <l.gambetta@bluenine.it>
 * @see UploadedImage
*/
class UploadedImageFileDescriptor extends UploadedFileDescriptor {
	private
		$width, $height; // interi con larghezza ed altezza dell'immagine

	/**
	 * Crea un nuovo descrittore di file.
	 * 
	 * La condizione di errore è una delle costanti standard di PHP, UPLOAD_ERR_*.
	 *
	 * @param string $name nome sul client del file
	 * @param integer $width largezza dell'immagine in pixel
	 * @param integer $height altezza dell'immagine in pixel
	 * @param integer $size dimensione in byte del file
	 * @param string $client_mime_type tipo MIME del file sul client
	 * @param string $real_mime_type tipo MIME effettivo del file sul server
	 * @param string $tmp_name path completo del file sul server
	 * @param integer $error condizione di errore di questo upload (una delle costanti UPLOAD_ERR_*)
	 *
	*/
	public function __construct($name, $width, $height, $size, $client_mime_type, $real_mime_type, $tmp_name, $error) {
		parent::__construct($name, $size, $client_mime_type, $real_mime_type, $tmp_name, $error);
		$this->width = $width;
		$this->height = $height;
	}
	
	/**
	 * Ritorna la larghezza dell'immagine
	 *
	 * @return integer larghezza dell'immagine
	*/
	public function getWidth() {
		return $this->width;
	}
	
	/**
	 * Ritorna l'altezza dell'immagine
	 *
	 * @return integer altezza dell'immagine
	*/
	public function getHeight() {
		return $this->height;
	}

	/**
	 * Crea un nuovo descrittore di file immagine da un descrittore di file normale.
	 *
	 * Ritorna FALSE se il file non è una immagine.
	 *
	 * @param UploadedFileDescriptor $uploadedfile
	 * @return UploadedImageFileDescriptor
	 */
	public static function newFromUploadedFileDescriptor(UploadedFileDescriptor $uploadedfile) {
		// Ricava dimensioni e tipo mime reale
		$info = @getimagesize($uploadedfile->getTempName());
		if ($info === FALSE) {
			return FALSE;
		} else {
			return new UploadedImageFileDescriptor($uploadedfile->getName(), $info[0], $info[1], $uploadedfile->getSize(), $uploadedfile->getClientMIME(), $info['mime'], $uploadedfile->getTempName(), $uploadedfile->getError());
		}
	}
}

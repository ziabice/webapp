<?php
/**
 * (c) 2008, 2009 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Nel caso si faccia l'upload di immagini, permette
 * di ricavarne le informazioni ed inglobarle in un descrittore
 * di file di tipo {@ļink UploadedImageFileDescriptor}.
 *
 * Funziona solo con le immagini supportate da getimagesize: nel caso l'immagine
 * non sia supportata utilizza il descrittore standard.
 *
 * @author Luca Gambetta <l.gambetta@bluenine.it>
 * @see UploadedImageFileDescriptor
*/
class UploadedImage extends UploadedFile {
	
	protected function createUploadedFileDescriptor($name, $index = null) {
		$w = 0; $h = 0;
		if ( is_null($index) ) {
			$n = (get_magic_quotes_gpc() ? stripslashes($_FILES[$name]['name']) : $_FILES[$name]['name']);
			$size = $_FILES[$name]['size'];
			$type = $_FILES[$name]['type'];
			$tmp_name = $_FILES[$name]['tmp_name'];
			$error = $_FILES[$name]['error'];
		} else {
			$n = (get_magic_quotes_gpc() ? stripslashes($_FILES[$name]['name'][$index]) : $_FILES[$name]['name'][$index]);
			$size = $_FILES[$name]['size'][$index];
			$type = $_FILES[$name]['type'][$index];
			$tmp_name = $_FILES[$name]['tmp_name'][$index];
			$error = $_FILES[$name]['error'][$index];
		}
		// Ricava dimensioni e tipo mime reale
		$info = @getimagesize($tmp_name);
		if ($info === FALSE) {
			$out = parent::createUploadedFileDescriptor($name, $index);
		} else {
			$out = new UploadedImageFileDescriptor($n, $info[0], $info[1], $size, $type, $info['mime'], $tmp_name, $error);
		}
		
		return $out;
	}
}

